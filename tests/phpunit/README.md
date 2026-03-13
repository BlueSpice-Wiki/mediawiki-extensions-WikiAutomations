# WikiAutomations Unit Tests

This directory contains PHPUnit tests for the WikiAutomations MediaWiki extension.

## Test Coverage

The following test files have been created to cover the core functionality:

### Core Classes
- **AutomationTest.php** - Tests for the `Automation` class
  - Constructor and default values
  - Getters for triggers, filters, and actions
  - Enable/disable functionality
  - JSON serialization
  
- **AutomationEntityTest.php** - Tests for the abstract `AutomationEntity` class
  - Data storage and retrieval
  - Enable/disable state management
  - Default values

- **EntityFactoryTest.php** - Tests for the `EntityFactory` class
  - Trigger types retrieval
  - Listing triggers, filters, and actions
  - Creating automation from data
  - Handling invalid entities
  - Filter by trigger type

### Content
- **Content/AutomationContentTest.php** - Tests for `AutomationContent`
  - Content model initialization
  - JSON data storage
  - Validation of JSON content

### Filters
- **PageFilter/NamespaceFilterTest.php** - Tests for `NamespaceFilter`
  - Page namespace matching
  - Empty namespace handling
  - Multiple namespace filtering
  - Form layout generation

- **PageFilter/ContentPagesTest.php** - Tests for `ContentPages`
  - Content namespace detection
  - isContent flag handling
  - Multiple content namespaces
  - Form layout generation

- **PageFilter/OnlyMajorTest.php** - Tests for `OnlyMajor`
  - Major/minor edit detection
  - onlyMajor flag handling
  - Revision lookup
  - Missing revision handling

### Triggers
- **Trigger/PageEventTriggerTest.php** - Tests for `PageEventTrigger`
  - Page provision functionality
  - Setting and getting pages
  - Inheritance from AutomationEntity

### Exceptions
- **Exception/EntityNotFoundExceptionTest.php** - Tests for `EntityNotFoundException`
  - Exception message formatting
  - HTTP 404 error code
  - Different entity types

## Running Tests

To run these tests, you'll need to execute them within the MediaWiki test framework:

```bash
cd /path/to/mediawiki
php tests/phpunit/phpunit.php extensions/WikiAutomations/tests/phpunit/
```

To run a specific test file:

```bash
php tests/phpunit/phpunit.php extensions/WikiAutomations/tests/phpunit/AutomationTest.php
```

## Test Structure

Tests follow PHPUnit best practices:
- Each test class covers one source class (@covers annotation)
- Mock objects are used for dependencies
- Tests are isolated and independent
- Descriptive test method names explain what is being tested

## Coverage Areas

The tests cover:
- ✅ Core automation logic
- ✅ Entity factory and creation
- ✅ Page filters
- ✅ Triggers
- ✅ Content handling
- ✅ Exception handling
- ⚠️  AutomationRunner (integration test needed)
- ⚠️  AutomationStore (integration test needed)
- ⚠️  REST API handlers (integration test needed)
- ⚠️  Hook handlers (integration test needed)

## Notes

- Tests use PHPUnit mocks to isolate units under test
- MediaWiki-specific classes are mocked to avoid database dependencies
- Some integration tests may be needed for database-dependent components
