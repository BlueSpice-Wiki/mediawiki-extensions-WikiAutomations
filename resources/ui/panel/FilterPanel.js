ext.wikiAutomations.ui.panel.FilterPanel = function ( config ) {
	ext.wikiAutomations.ui.panel.FilterPanel.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.panel.FilterPanel, ext.wikiAutomations.ui.panel.ItemPanel );

ext.wikiAutomations.ui.panel.FilterPanel.prototype.onAddButtonClick = function () {
	const wm = OO.ui.getWindowManager();
	const dialog = new ext.wikiAutomations.ui.dialog.ConditionPicker();
	wm.addWindows( [ dialog ] );
	wm.openWindow( dialog ).closed.then( async ( data ) => {
		if ( data && data.action === 'add' && data.item ) {
			this.addNew( data );
		}
	} );
};

ext.wikiAutomations.ui.panel.FilterPanel.prototype.getEmptyIconClass = function () {
	return 'automation-condition';
};

ext.wikiAutomations.ui.panel.FilterPanel.prototype.getEmptyMessage = function () {
	return mw.msg( 'wiki-automations-editor-empty-conditions' );
};

ext.wikiAutomations.ui.panel.FilterPanel.prototype.getAddButtonLabel = function () {
	return mw.msg( 'wiki-automations-ui-action-add-condition' );
};

ext.wikiAutomations.ui.panel.FilterPanel.prototype.getEntityType = function () {
	return 'pageFilter';
};
