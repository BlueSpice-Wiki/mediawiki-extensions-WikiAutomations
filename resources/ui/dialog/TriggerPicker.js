ext.wikiAutomations.ui.dialog.TriggerPicker = function ( config ) {
	config = config || {};
	config.selectionSize = 'large';
	ext.wikiAutomations.ui.dialog.TriggerPicker.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.TriggerPicker, ext.wikiAutomations.ui.dialog.ItemPicker );

ext.wikiAutomations.ui.dialog.TriggerPicker.static.name = 'triggerPicker';
ext.wikiAutomations.ui.dialog.TriggerPicker.static.title = mw.msg( 'wiki-automations-ui-action-add-trigger' );

ext.wikiAutomations.ui.dialog.TriggerPicker.prototype.load = async function () {
	this.triggerBooklet = new OO.ui.BookletLayout( {
		outlined: true, expanded: false, classes: [ 'ext-wikiAutomations-triggerPicker-booklet' ]
	} );
	this.triggerBooklet.connect( this, {
		set: 'updateSize',
		add: 'updateSize'
	} );
	this.mainBooklet.getPage( 'selector' ).$element.append( this.triggerBooklet.$element );

	const triggers = await ext.wikiAutomations.api.getTriggers();
	const pages = [];
	for ( const triggerTypeKey in triggers ) {
		const triggerType = triggers[ triggerTypeKey ];
		function pageLayout ( name, config ) {
			pageLayout.super.call( this, name, { expanded: false, padded: false } );
			const options = [];
			for ( const triggerKey in config.triggers ) {
				const trigger = config.triggers[ triggerKey ];
				options.push( new OO.ui.ButtonOptionWidget( {
					data: Object.assign( trigger, { key: triggerKey } ),
					label: trigger.label,
					framed: false,
					classes: [ 'ext-wikiAutomations-itemPicker-entityOptionItem' ]
				} ) );
			}
			this.picker = new OO.ui.ButtonSelectWidget( {
				items: options,
				classes: [ 'ext-wikiAutomations-itemPicker-itemSelect' ]
			} );
			this.picker.connect( this, {
				select: ( item ) => this.emit( 'triggerSelected', item.getData() )
			} );
			this.$element.append( this.picker.$element );
		}

		OO.inheritClass( pageLayout, OO.ui.PageLayout );

		pageLayout.prototype.setupOutlineItem = function () {
			this.outlineItem.setLabel( triggerType.message );
		};

		const page = new pageLayout( triggerTypeKey, triggerType );
		page.connect( this, {
			triggerSelected: 'onItemSelected'
		} );
		pages.push( page );
	}

	this.triggerBooklet.addPages( pages );
	this.triggerBooklet.connect( this, {
		set: function ( page ) {
			const selected = page.picker.findFirstSelectedItem();
			if ( selected ) {
				this.onItemSelected( selected.getData() );
			} else {
				page.picker.selectItem( page.picker.findFirstSelectableItem() );
			}
		}
	} );

	this.triggerBooklet.selectFirstSelectablePage();
	const currentPage = this.triggerBooklet.getCurrentPage();
	currentPage.picker.selectItem( currentPage.picker.findFirstSelectableItem() );

	setTimeout( () => this.updateSize(), 1 );
}
