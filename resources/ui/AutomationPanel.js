ext.wikiAutomations.ui.AutomationPanel = function ( config, data ) {
	config = config || {};
	config.expanded = false;
	config.padded = false;
	ext.wikiAutomations.ui.AutomationPanel.parent.call( this, config );

	this.data = data.automationData || {};
	this.entityInfo = data.entityInfo || {};
	this.enabled = this.data.enabled || false;

	this.action = data.action || 'view';
	this.automationId = data.automationId || null;

	this.triggerPanel = new ext.wikiAutomations.ui.panel.TriggerPanel( {
		label: mw.msg( 'wiki-automations-ui-panel-triggers' ),
		entities: this.data.triggers || {},
		entityInfo: this.entityInfo.triggers || {},
		editable: this.action !== 'view',
		automation: this
	} );

	this.filterPanel = new ext.wikiAutomations.ui.panel.FilterPanel( {
		label: mw.msg( 'wiki-automations-ui-panel-filters' ),
		entities: this.data.pageFilters || {},
		entityInfo: this.entityInfo.filters || {},
		editable: this.action !== 'view',
		automation: this
	} );

	this.actionsPanel = new ext.wikiAutomations.ui.panel.ActionsPanel( {
		label: mw.msg( 'wiki-automations-ui-panel-actions' ),
		classes: [ 'ext-wikiAutomations-actionsPanel' ],
		entities: this.data.actions || {},
		entityInfo: this.entityInfo.actions || {},
		editable: this.action !== 'view',
		automation: this
	} );

	this.enabled = typeof data.enabled === 'undefined' ? true : data.enabled;

	this.$element.append( this.triggerPanel.$element, this.filterPanel.$element, this.actionsPanel.$element );
	this.$element.addClass( 'ext-wikiAutomations-automationPanel' );
};

OO.inheritClass( ext.wikiAutomations.ui.AutomationPanel, OO.ui.PanelLayout );

ext.wikiAutomations.ui.AutomationPanel.prototype.getValue = async function (){
	const triggers = await this.triggerPanel.getValue();
	const filters = await this.filterPanel.getValue();
	const actions = await this.actionsPanel.getValue();

	return {
		triggers: triggers,
		pageFilters: filters,
		actions: actions,
		enabled: this.enabled
	};
};

ext.wikiAutomations.ui.AutomationPanel.prototype.setEnabled = function ( enabled ) {
	this.enabled = enabled;
};
