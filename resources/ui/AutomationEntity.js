ext.wikiAutomations.ui.AutomationEntity = function ( type, key, data, displayData, enabled, editorInfo, editable ) {
	ext.wikiAutomations.ui.AutomationEntity.parent.call( this, {
		expanded: false,
		padded: true
	} );

	this.type = type;
	this.key = key;
	this.data = data || {};
	console.log( "SET D", this.data );
	this.displayData = displayData || null;
	this.enabled = enabled || false;
	this.labels = editorInfo.labels || {};
	this.layout = editorInfo.layout || null;
	this.editable = editable || false;

	this.$element.addClass( 'ext-wikiAutomations-automationEntity' );
	this.render();
};

OO.inheritClass( ext.wikiAutomations.ui.AutomationEntity, OO.ui.PanelLayout );

ext.wikiAutomations.ui.AutomationEntity.prototype.setEditable = function ( editable ) {
	this.editable = editable;
	if ( this.optionsWidget ) {
		this.optionsWidget.$element.hide();
	}
};

ext.wikiAutomations.ui.AutomationEntity.prototype.render = async function () {
	this.$element.empty();
	const label = this.getHeaderLabel();
	const activeBadge = this.getActiveBadge();
	const options = this.getOptionsWidget();
	if ( !this.editable ) {
		options.$element.hide();
	}

	const header = new OO.ui.HorizontalLayout( {
		items: [ label, activeBadge, options ]
	} );
	this.$element.append( header.$element );



	this.$displayData = $( '<div>' ).addClass( 'ext-wikiAutomations-automationEntity-displayData' );
	this.$element.append( this.$displayData );

	if ( !this.displayData && this.data && Object.keys( this.data ).length > 0 ) {
		this.$displayData.html( new OO.ui.ProgressBarWidget( { progress: false } ).$element );
		this.displayData = await this.retrieveEntityDisplayData( this.key, this.data );
	}

	if ( this.displayData ) {
		this.$displayData.empty();
		for ( let i = 0; i < this.displayData.length; i++ ) {
			const dataItem = this.displayData[i];
			let text = '';
			if ( dataItem.key ) {
				text += dataItem.key + ': ';
			}
			if ( dataItem.value ) {
				text += dataItem.value;
			}
			this.$displayData.append( new OO.ui.LabelWidget( {
				label: new OO.ui.HtmlSnippet( text ),
				classes: [ 'ext-wikiAutomations-automationEntity-displayDataItem' ]
			} ).$element );
		}
	}
};

ext.wikiAutomations.ui.AutomationEntity.prototype.getHeaderLabel = function () {
	return new OO.ui.LabelWidget( {
		label: this.labels.message || this.key,
		classes: [ 'ext-wikiAutomations-automationEntity-label' ]
	} );
};

ext.wikiAutomations.ui.AutomationEntity.prototype.getActiveBadge = function () {
	return new OO.ui.LabelWidget( {
		label: this.enabled ? mw.msg( 'wiki-automations-ui-active' ) : mw.msg( 'wiki-automations-ui-inactive' ),
		classes: [ 'ext-wikiAutomations-automationEntity-badge', this.enabled ? 'active' : 'inactive' ]
	} );
};

ext.wikiAutomations.ui.AutomationEntity.prototype.setEnabled = function ( enabled ) {
	this.enabled = enabled;
	this.render();
}

ext.wikiAutomations.ui.AutomationEntity.prototype.getOptionsWidget = function () {
	this.optionsWidget = new OO.ui.ButtonMenuSelectWidget( {
		icon: 'verticalEllipsis',
		label: mw.msg( 'wiki-automations-ui-options' ),
		invisibleLabel: true,
		framed: false,
		menu: {
			items: [
				new OO.ui.MenuOptionWidget( {
					data: this.enabled ? 'disable' : 'enable',
					icon: this.enabled ? 'block' : 'unBlock',
					label: this.enabled ?
						mw.msg( 'wiki-automations-ui-action-disable' ) : mw.msg( 'wiki-automations-ui-action-enable' )
				} ),
				new OO.ui.MenuOptionWidget( {
					icon: 'edit',
					data: 'edit',
					label: mw.msg( 'wiki-automations-ui-action-edit' ),
					disabled: this.layout === null
				} ),
				new OO.ui.MenuOptionWidget( {
					icon: 'trash',
					flags: [ 'destructive' ],
					data: 'delete',
					label: mw.msg( 'wiki-automations-ui-action-delete' )
				} )
			]
		}
	} );
	this.optionsWidget.getMenu().connect( this, {
		choose: function ( item ) {
			if ( item.getData() === 'delete' ) {
				this.emit( 'remove', this );
			}
			if ( item.getData() === 'disable' ) {
				this.setEnabled( false );
			}
			if ( item.getData() === 'enable' ) {
				this.setEnabled( true );
			}
			if ( item.getData() === 'edit' && this.layout ) {
				this.openEditor( this );
			}
 		}
	} );
	return this.optionsWidget;
};

ext.wikiAutomations.ui.AutomationEntity.prototype.getEntityType = function () {
	return this.type;
};

ext.wikiAutomations.ui.AutomationEntity.prototype.retrieveEntityDisplayData = async function ( key, data ) {
	return await ext.wikiAutomations.api.getDisplayData( this.getEntityType(), key, data );
};

ext.wikiAutomations.ui.AutomationEntity.prototype.openEditor = function () {
	const wm = OO.ui.getWindowManager();
	const dialog = new ext.wikiAutomations.ui.dialog.FormEditor( {}, this );
	wm.addWindows( [ dialog ] );
	wm.openWindow( dialog ).closed.then( async ( data ) => {
		if ( data && data.action === 'save' && data.data ) {
			console.log( "DD", data.data );
			this.data = data.data;
			// Will be retrieved on render
			this.displayData = null;
			this.render();
		}
	} );
};
