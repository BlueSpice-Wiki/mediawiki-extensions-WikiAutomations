ext.wikiAutomations.ui.panel.ItemPanel = function ( config ) {
	config = config || {};
	config.expanded = false;
	config.padded = true;
	config.framed = false;
	ext.wikiAutomations.ui.panel.ItemPanel.parent.call( this, config );

	this.entities = config.entities || {};
	this.entityInfo = config.entityInfo || {};
	this.editable = config.editable || false;

	const header = new OO.ui.LabelWidget( {
		label: config.label,
		classes: [ 'ext-wikiAutomations-header' ]
	} );
	this.$element.append( header.$element );

	this.itemPanel = new OO.ui.PanelLayout( {
		expanded: false,
		padded: false,
		classes: [ 'ext-wikiAutomations-itemPanel-items' ]
	} );
	this.$element.append( this.itemPanel.$element );

	this.addEmptyContent();
	if ( config.canAdd !== false && this.editable ) {
		this.appendAddButton();
	}

	this.items = {};
	this.loadEntities();

	this.$element.addClass( 'ext-wikiAutomations-itemPanel' );
};

OO.inheritClass( ext.wikiAutomations.ui.panel.ItemPanel, OO.ui.PanelLayout );

ext.wikiAutomations.ui.panel.ItemPanel.prototype.appendAddButton = function () {
	this.addButton = new OO.ui.ButtonWidget( {
		label: this.getAddButtonLabel(),
		icon: 'add',
		framed: false,
		flags: [ 'progressive' ],
		classes: [ 'ext-wikiAutomations-itemPanel-addButton' ]
	} );
	this.addButton.connect( this, { click: 'onAddButtonClick' } );
	this.$element.append( this.addButton.$element );
}

ext.wikiAutomations.ui.panel.ItemPanel.prototype.onAddButtonClick = function () {
	// NOOP
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.loadEntities = function () {
	for ( const key in this.entities ) {
		const entityData = this.entities[key];
		const entityInfo = this.entityInfo[key] || {};
		const entity = new ext.wikiAutomations.ui.AutomationEntity(
			this.getEntityType(),
			key,
			entityData.data || {},
			entityData.displayData || null,
			entityData.enabled || false,
			entityInfo,
			this.editable
		);
		this.addEntity( entity );
	}
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.addNew = function ( data ) {
	const entity = new ext.wikiAutomations.ui.AutomationEntity(
		this.getEntityType(),
		data.item.key,
		data.item.data || {},
		null,
		true,
		data.item
	);
	this.addEntity( entity );
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.addEntity = function ( entity ) {
	if ( this.items[entity.key] ) {
		this.removeEntity( this.items[entity.key] );
	}
	this.removeEmptyContent();
	this.items[entity.key] = entity;
	entity.connect( this, {
		remove: function () {
			this.removeEntity( entity );
		}
	} );
	this.items[entity.key] = entity;
	this.itemPanel.$element.append( entity.$element );
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.getAddButtonLabel = function () {
	return mw.msg( 'wiki-automations-ui-action-add' );
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.removeEntity = function ( entity ) {
	delete this.items[entity.key];
	entity.$element.remove();
	if ( Object.keys( this.items ).length === 0 ) {
		this.addEmptyContent();
	}
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.addEmptyContent = function () {
	if ( this.emptyContent ) {
		return;
	}
	this.emptyContent = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true,
		classes: [ 'ext-wikiAutomations-itemPanel-empty' ]
	} );
	const $icon = $( '<span>' )
		.addClass( 'ext-wikiAutomations-itemPanel-empty-icon' )
		.addClass( this.getEmptyIconClass() );
	const text = new OO.ui.LabelWidget( {
		label: new OO.ui.HtmlSnippet( this.getEmptyMessage() ),
		classes: [ 'ext-wikiAutomations-itemPanel-empty-text' ]
	} );
	this.emptyContent.$element.append( $icon, text.$element );
	this.itemPanel.$element.append( this.emptyContent.$element );
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.removeEmptyContent = function () {
	if ( !this.emptyContent ) {
		return;
	}
	this.emptyContent.$element.remove();
	this.emptyContent = null;
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.getEmptyIconClass = function () {
	return '';
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.getEmptyMessage = function () {
	return '';
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.getValue = async function () {
	const value = {};
	for ( const key in this.items ) {
		value[key] = {
			data: this.items[key].data || {},
			enabled: this.items[key].enabled || false
		};
	}
	return value;
};

ext.wikiAutomations.ui.panel.ItemPanel.prototype.getEntityType = function () {
	throw new Error( 'getAddButtonLabel method must be implemented by subclass' );
};
