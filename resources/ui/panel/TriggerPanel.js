ext.wikiAutomations.ui.panel.TriggerPanel = function ( config ) {
	this.triggers = {};
	this.idMap = {};
	ext.wikiAutomations.ui.panel.TriggerPanel.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.panel.TriggerPanel, ext.wikiAutomations.ui.panel.ItemPanel );

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.onAddButtonClick = function () {
	const wm = OO.ui.getWindowManager();
	const dialog = new ext.wikiAutomations.ui.dialog.TriggerPicker();
	wm.addWindows( [ dialog ] );
	wm.openWindow( dialog ).closed.then( async ( data ) => {
		if ( data && data.action === 'add' && data.item ) {
			const triggerId = 'trigger-' + Math.random().toString( 36 ).substr( 2, 9 );
			const entity = new ext.wikiAutomations.ui.AutomationEntity(
				this.getEntityType(),
				data.item.key,
				data.item.data || {},
				null,
				true,
				data.item,
				true,
				this.automation
			);
			this.addEntity( entity, triggerId );
		}
	} );
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.loadEntities = function () {
	if ( Array.isArray( this.entities ) ) {
		for ( let i = 0; i < this.entities.length; i++ ) {
			const entityData = this.entities[i] || {};
			if ( !entityData.key ) {
				continue;
			}
			const entityInfo = this.entityInfo[entityData.key] || {};
			const triggerId = 'trigger-' + Math.random().toString( 36 ).substr( 2, 9 );
			const entity = new ext.wikiAutomations.ui.AutomationEntity(
				this.getEntityType(),
				entityData.key,
				entityData.data || {},
				entityData.displayData || null,
				entityData.enabled || false,
				entityInfo,
				this.editable,
				this.automation
			);
			this.addEntity( entity, triggerId );
		}
		return;
	}

	for ( const key in this.entities ) {
		const entityData = this.entities[key] || {};
		const entityInfo = this.entityInfo[key] || {};
		const triggerId = 'trigger-' + Math.random().toString( 36 ).substr( 2, 9 );
		const entity = new ext.wikiAutomations.ui.AutomationEntity(
			this.getEntityType(),
			key,
			entityData.data || {},
			entityData.displayData || null,
			entityData.enabled || false,
			entityInfo,
			this.editable,
			this.automation
		);
		this.addEntity( entity, triggerId );
	}
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.addEntity = function ( entity, triggerId ) {
	if ( !triggerId ) {
		triggerId = 'trigger-' + Math.random().toString( 36 ).substr( 2, 9 );
	}
	this.removeEmptyContent();
	entity.connect( this, {
		remove: function () {
			this.removeEntity( entity );
		}
	} );

	entity.triggerId = triggerId;
	this.idMap[triggerId] = entity.key;
	this.triggers[triggerId] = entity;
	this.items[triggerId] = entity;
	this.itemPanel.$element.append( entity.$element );
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.removeEntity = function ( entity ) {
	let triggerId = entity.triggerId || null;
	if ( !triggerId ) {
		for ( const id in this.triggers ) {
			if ( this.triggers[id] === entity ) {
				triggerId = id;
				break;
			}
		}
	}
	if ( triggerId ) {
		delete this.triggers[triggerId];
		delete this.idMap[triggerId];
		delete this.items[triggerId];
	}
	entity.$element.remove();
	if ( Object.keys( this.triggers ).length === 0 ) {
		this.addEmptyContent();
	}
};

ext.wikiAutomations.ui.panel.TriggerPanel.prototype.getValue = async function () {
	const value = [];
	for ( const triggerId in this.triggers ) {
		const trigger = this.triggers[triggerId];
		value.push( {
			key: this.idMap[triggerId],
			data: trigger.data || {},
			enabled: trigger.enabled || false
		} );
	}
	return value;
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
