ext.wikiAutomations.ui.panel.ActionsPanel = function ( config ) {
	this.actions = {};
	this.idMap = {};
	ext.wikiAutomations.ui.panel.ActionsPanel.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.panel.ActionsPanel, ext.wikiAutomations.ui.panel.ItemPanel );

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.onAddButtonClick = function () {
	const wm = OO.ui.getWindowManager();
	const dialog = new ext.wikiAutomations.ui.dialog.ActionPicker();
	wm.addWindows( [ dialog ] );
	wm.openWindow( dialog ).closed.then( async ( data ) => {
		if ( data && data.action === 'add' && data.item ) {
			const actionId = 'action-' + Math.random().toString( 36 ).substr( 2, 9 );
			const entity = new ext.wikiAutomations.ui.ActionEntity(
				actionId,
				data.item.key,
				data.item.data || {},
				null,
				true,
				data.item
			);
			this.addEntity( entity );
		}
	} );
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.addEntity = function ( entity ) {
	this.removeEmptyContent();
	entity.connect( this, {
		remove: function () {
			this.removeEntity( entity );
		}
	} );

	this.idMap[ entity.actionId ] = entity.key;
	this.actions[entity.actionId] = entity;
	this.itemPanel.$element.append( entity.$element );
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.removeEntity = function ( entity ) {
	delete this.actions[entity.actionId];
	delete this.idMap[entity.actionId];
	entity.$element.remove();
	if ( Object.keys( this.actions ).length === 0 ) {
		this.addEmptyContent();
	}
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.getValue = async function () {
	const value = [];
	for ( const actionId in this.actions ) {
		const action = this.actions[ actionId ];
		value.push( {
			key: this.idMap[ actionId ],
			data: action.data,
			enabled: action.enabled
		} );
	}
	return value;
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.getEmptyIconClass = function () {
	return 'automation-action';
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.getEmptyMessage = function () {
	return mw.msg( 'wiki-automations-editor-empty-actions' );
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.getAddButtonLabel = function () {
	return mw.msg( 'wiki-automations-ui-action-add-action' );
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.loadEntities = function () {
	for ( let i = 0; i < this.entities.length; i++ ) {
		const entityData = this.entities[i];
		const entityInfo = this.entityInfo[entityData.key] || {};
		const actionId = 'action-' + Math.random().toString( 36 ).substr( 2, 9 );
		const entity = new ext.wikiAutomations.ui.ActionEntity(
			actionId,
			entityData.key,
			entityData.data || {},
			entityData.displayData || null,
			entityData.enabled || false,
			entityInfo,
			this.editable
		);
		this.addEntity( entity );
	}
};

ext.wikiAutomations.ui.panel.ActionsPanel.prototype.getEntityType = function () {
	return 'action';
};
