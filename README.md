# WikiAutomations

WikiAutomations is a MediaWiki extension that allows wiki editors to define automations — combinations of **triggers**, **page filters**, and **actions** — via special wiki pages. When a trigger fires, the configured page filters narrow down the set of affected pages, and the configured actions are executed against those pages.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Architecture Overview](#architecture-overview)
- [Integrating from Another Extension](#integrating-from-another-extension)
  - [Registering a Trigger](#registering-a-trigger)
  - [Registering a Page Filter](#registering-a-page-filter)
  - [Registering an Action](#registering-an-action)
- [Interfaces Reference](#interfaces-reference)

---

## Requirements

- MediaWiki >= 1.43.0

---

## Installation

1. Clone or download this extension into your MediaWiki `extensions/` directory.
2. Add the following line to `LocalSettings.php`:
   ```php
   wfLoadExtension( 'WikiAutomations' );
   ```
3. Run `php maintenance/update.php` to apply the required database schema changes.

---

## Architecture Overview

Automations are stored as wiki pages with a special `automation` content model. Each automation defines:

| Component | Description |
|-----------|-------------|
| **Trigger** | Defines *when* the automation fires (e.g. on page edit, on a schedule). |
| **Page Filters** | Optional conditions that narrow down *which pages* are processed (e.g. namespace filter). |
| **Actions** | Define *what happens* when the automation runs (e.g. send a notification, stabilize a page). |

The `AutomationRunner` service resolves the trigger, evaluates all page filters against the candidate pages, and executes the configured actions.

---

## Integrating from Another Extension

Other extensions can contribute their own triggers, page filters, and actions by declaring them under the `WikiAutomations` attribute key in their `extension.json`.

All three component types use MediaWiki's [ObjectFactory](https://www.mediawiki.org/wiki/ObjectFactory) spec format (`class` + optional `services` array) to declare how instances are constructed.

### Registering a Trigger

Triggers define when an automation should fire and which pages are in scope.

**Two trigger types are available:**

| Type | Description |
|------|-------------|
| `page` | Fires in response to a page event (edit, delete, etc.). Pages are provided externally by the hook that detects the event. |
| `time` | Fires on a schedule. The trigger itself is responsible for providing the list of pages to process. |

**`extension.json` example:**

```json
"attributes": {
    "WikiAutomations": {
        "Triggers": {
            "my-extension-custom-trigger": {
                "type": "page",
                "message": "my-extension-trigger-label-i18n-key",
                "spec": {
                    "class": "MediaWiki\\Extension\\MyExtension\\WikiAutomations\\MyTrigger"
                }
            }
        }
    }
}
```

**PHP implementation:**

For a `page`-type trigger, extend `PageEventTrigger` (or implement `IAutomationTrigger` directly). Pages are injected at runtime by the hook handler via `setPages()`:

```php
use MediaWiki\Extension\WikiAutomations\Trigger\PageEventTrigger;

class MyTrigger extends PageEventTrigger {
    // PageEventTrigger already handles providePages(). No further implementation needed
    // unless you want to add a configuration form via getLayout().
}
```

For a `time`-type trigger that needs a schedule form and provides its own page list, extend `TimeTrigger` or implement `IAutomationTrigger` from scratch.

To fire a page-type trigger programmatically from your extension (e.g. from a hook handler), inject `WikiAutomations.Runner` and call:

```php
// $runner is MediaWiki\Extension\WikiAutomations\AutomationRunner
$runner->scheduleTrigger( 'my-extension-custom-trigger', [ $pageIdentity ], $authority );
```

> **Note:** `scheduleTrigger()` is asynchronous (uses `ProcessManager`). Use `trigger()` for synchronous execution if needed.

---

### Registering a Page Filter

Page filters let users restrict which pages an automation acts upon. A filter receives a `PageIdentity` and returns `true` if the page should be included.

**`extension.json` example:**

```json
"attributes": {
    "WikiAutomations": {
        "PageFilters": {
            "my-extension-category-filter": {
                "class": "MediaWiki\\Extension\\MyExtension\\WikiAutomations\\CategoryFilter",
                "services": [ "WikiPageFactory" ]
            }
        }
    }
}
```

**PHP implementation:**

Extend `GenericPageFilter` and implement `pageFits()`. Optionally implement `getLayout()` to provide a configuration form shown in the automation editor UI:

```php
use MediaWiki\Extension\WikiAutomations\PageFilter\GenericPageFilter;
use MediaWiki\Page\PageIdentity;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

class CategoryFilter extends GenericPageFilter {

    public function pageFits( PageIdentity $page ): bool {
        $requiredCategory = $this->getData()['category'] ?? null;
        if ( !$requiredCategory ) {
            return true;
        }
        // ... check if $page is in $requiredCategory
        return true;
    }

    public function getLayout(): ?IFormSpecification {
        $spec = new StandaloneFormSpecification();
        $spec->setItems( [
            [
                'type' => 'text',
                'name' => 'category',
                'label' => 'Category name',
            ],
        ] );
        return $spec;
    }
}
```

If no configuration form is needed, `getLayout()` can return `null`.

---

### Registering an Action

Actions define what happens when an automation fires. There are two action interfaces:

| Interface | Method | When to use |
|-----------|--------|-------------|
| `IAutomationAction` | `execute()` | The action does not operate per-page (e.g. send a generic notification). |
| `IPageScopedAutomationAction` | `executeForPage( PageIdentity $page )` | The action operates on each individual page in scope. |

The `type` field in `extension.json` is for UI grouping purposes and can be any string (e.g. `"generic"`).

**`extension.json` example:**

```json
"attributes": {
    "WikiAutomations": {
        "Actions": {
            "my-extension-my-action": {
                "type": "generic",
                "message": "my-extension-action-label-i18n-key",
                "spec": {
                    "class": "MediaWiki\\Extension\\MyExtension\\WikiAutomations\\MyAction",
                    "services": [ "TitleFactory" ]
                }
            }
        }
    }
}
```

**PHP implementation (generic action):**

Extend `GenericAutomationAction` and implement `execute()` and `getLayout()`:

```php
use MediaWiki\Extension\WikiAutomations\Action\GenericAutomationAction;
use MediaWiki\Status\Status;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;

class MyAction extends GenericAutomationAction {

    public function getLayout(): IFormSpecification {
        $spec = new StandaloneFormSpecification();
        $spec->setItems( [
            [
                'type' => 'text',
                'name' => 'someOption',
                'label' => 'Some option',
            ],
        ] );
        return $spec;
    }

    public function execute(): Status {
        $data = $this->getData();
        // $this->triggeredBy is the Authority that triggered the automation (may be null)
        // Perform your action here ...
        return Status::newGood();
    }
}
```

**PHP implementation (page-scoped action):**

For actions that should run once per page, also implement `IPageScopedAutomationAction`:

```php
use MediaWiki\Extension\WikiAutomations\Action\GenericAutomationAction;
use MediaWiki\Extension\WikiAutomations\IPageScopedAutomationAction;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Status\Status;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

class MyPageAction extends GenericAutomationAction implements IPageScopedAutomationAction {

    public function getLayout(): IFormSpecification {
        // Return form specification or a StandaloneFormSpecification with items
    }

    public function execute(): Status {
        // Called when no specific pages are in scope
        return Status::newGood();
    }

    public function executeForPage( PageIdentity $page ): Status {
        // Called once for each page that passed all configured page filters
        return Status::newGood();
    }
}
```

---

## Interfaces Reference

| Interface | Location | Purpose |
|-----------|----------|---------|
| `IAutomationTrigger` | `src/IAutomationTrigger.php` | Defines when and for which pages an automation fires. |
| `IPageFilter` | `src/IPageFilter.php` | Filters the page set produced by a trigger. |
| `IAutomationAction` | `src/IAutomationAction.php` | Executes when an automation is triggered. |
| `IPageScopedAutomationAction` | `src/IPageScopedAutomationAction.php` | Extends `IAutomationAction`; `executeForPage()` is called per page. |

Base classes available for extension:

| Base class | Location | Purpose |
|------------|----------|---------|
| `AutomationEntity` | `src/AutomationEntity.php` | Base for all entities; provides `getData()`/`setData()`/`isEnabled()`. |
| `GenericTrigger` | `src/Trigger/GenericTrigger.php` | Minimal `IAutomationTrigger` base. |
| `PageEventTrigger` | `src/Trigger/PageEventTrigger.php` | `page`-type trigger; accepts pages via `setPages()`. |
| `TimeTrigger` | `src/Trigger/TimeTrigger.php` | `time`-type trigger with a cron schedule form. |
| `GenericPageFilter` | `src/PageFilter/GenericPageFilter.php` | Minimal `IPageFilter` base (always returns `true`). |
| `GenericAutomationAction` | `src/Action/GenericAutomationAction.php` | `IAutomationAction` base; provides `$triggeredBy`. |
