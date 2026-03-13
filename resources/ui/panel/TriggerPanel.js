ext.wikiAutomations.ui.panel.TriggerPanel = function ( config ) {
	ext.wikiAutomations.ui.panel.TriggerPanel.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.panel.TriggerPanel, ext.wikiAutomations.ui.panel.ItemPanel );

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.onAddButtonClick = function () {
	const wm = OO.ui.getWindowManager();
	const dialog = new ext.wikiAutomations.ui.dialog.TriggerPicker();
	wm.addWindows( [ dialog ] );
	wm.openWindow( dialog ).closed.then( async ( data ) => {
		if ( data && data.action === 'add' && data.item ) {
			this.addNew( data );
		}
	} );
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.getEmptyIconClass = function () {
	return 'automation-trigger';
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.getEmptyMessage = function () {
	return mw.msg( 'wiki-automations-editor-empty-trigger' );
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.getAddButtonLabel = function () {
	return mw.msg( 'wiki-automations-ui-action-add-trigger' );
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.getEntityType = function () {
	return 'trigger';
};
