ext.wikiAutomations.ui.dialog.ItemPicker = function ( config ) {
	config = config || {};

	this.selectionSize = config.selectionSize || 'medium';
	config.size = this.selectionSize;
	ext.wikiAutomations.ui.dialog.ItemPicker.parent.call( this, config );

	this.currentItem = null;
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.ItemPicker, OO.ui.ProcessDialog );

ext.wikiAutomations.ui.dialog.ItemPicker.static.actions = [
	{
		action: 'cancel',
		label: mw.msg('wiki-automations-ui-action-cancel'),
		flags: 'safe', modes: [ 'select', 'add', 'add-direct' ]
	},
	{
		action: 'back',
		label: mw.msg('wiki-automations-ui-action-back' ),
		flags: 'safe', modes: [ 'add' ]
	},
	{
		action: 'add',
		label: mw.msg('wiki-automations-ui-action-add' ),
		flags: [ 'primary', 'progressive' ], modes: [ 'add', 'add-direct' ]
	},
	{
		action: 'next',
		label: mw.msg('wiki-automations-ui-action-next' ),
		flags: [ 'primary', 'progressive' ], modes: [ 'select' ]
	}
];

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.getReadyProcess = function ( data ) {
	return ext.wikiAutomations.ui.dialog.ItemPicker.parent.prototype.getReadyProcess.call( this, data )
		.next( function () {
			this.setPanel( 'selector' );
		}, this );
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.getSetupProcess = function ( data ) {
	return ext.wikiAutomations.ui.dialog.ItemPicker.parent.prototype.getSetupProcess.call( this, data )
		.next( function () {
			// Prevent flickering, disable all actions before init is done
			this.actions.setMode( 'INVALID' );
		}, this );
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.initialize = function () {
	ext.wikiAutomations.ui.dialog.ItemPicker.parent.prototype.initialize.call( this );

	this.mainBooklet = new OO.ui.BookletLayout( {
		outlined: false, expanded: false, padded: false, classes: [ 'ext-wikiAutomations-item-picker-booklet' ]
	} );

	const pickerPage = new OO.ui.PageLayout( 'selector', { expanded: false, padded: false } );
	const formPage = new OO.ui.PageLayout( 'form', { expanded: false, padded: false } );
	this.mainBooklet.addPages( [ pickerPage, formPage ] );

	this.mainBooklet.connect( this, {
		set: 'updateSize',
		add: 'updateSize'
	} );
	this.$body.append( this.mainBooklet.$element );
	this.load();
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.setPanel = function ( panel ) {
	if ( panel === 'selector' ) {
		this.mainBooklet.setPage( 'selector' );
		this.actions.setAbilities( {
			next: this.currentItem && this.currentItem.layout,
			add: this.currentItem && this.currentItem.layout === null,
			back: false
		} );
		this.actions.setMode( this.currentItem && this.currentItem.layout === null ? 'add-direct' : 'select' );
		this.setSize( this.selectionSize );
	}
	if ( panel === 'form' ) {
		this.mainBooklet.setPage( 'form' );
		this.actions.setAbilities( { next: false, add: true, back: true } );
		this.actions.setMode( 'add' );
		this.setSize( 'medium' );
	}
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.load = async function () {
	// NOOP
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.onItemSelected = function ( item ) {
	this.currentItem = {
		key: item.key,
		labels: {
			message: item.label,
			description: item.description
		},
		layout: item.layout
	}
	if ( item.layout ) {
		this.actions.setAbilities( { next: true, add: false } );
		this.actions.setMode( 'select' );
	} else {
		this.actions.setAbilities( { next: false, add: true } );
		this.actions.setMode( 'add-direct' );
	}
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.getActionProcess = function ( action ) {
	return ext.wikiAutomations.ui.dialog.ItemPicker.parent.prototype.getActionProcess.call( this, action )
		.next(
			function () {
				const dfd = $.Deferred();
				if ( action === 'add' || action === 'add-direct' ) {
					this.addItem( dfd );
				}
				if ( action === 'next' ) {
					if ( !this.currentItem || !this.currentItem.layout ) {
						dfd.reject();
					} else {
						this.initFormView( this.currentItem );
						dfd.resolve();
					}
				}
				if ( action === 'back' ) {
					this.currentItem = null;
					this.setPanel( 'selector' );
					dfd.resolve();
				}
				if ( action === 'cancel' ) {
					this.close();
				}
				return dfd.promise();
			},
			this
		)
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.addItem = function ( dfd ) {
	if ( !this.currentItem ) {
		dfd.reject();
	}
	if ( this.form ) {
		this.form.getValue().done( ( data ) => {
			this.close( { action: 'add', item: {
					key: this.currentItem.key,
					data: data,
					labels: this.currentItem.labels,
					layout: this.currentItem.layout
				} } );
		} ).fail( () => {
			dfd.reject();
		} );
	} else {
		this.close( { action: 'add', item: this.currentItem } );
	}
};

ext.wikiAutomations.ui.dialog.ItemPicker.prototype.initFormView = async  function ( item ) {
	this.form = new ext.wikiAutomations.ui.panel.FormPanel( { expanded: false, padded: true }, item.layout, {}, this );
	this.form.connect( this, { updateSize: 'updateSize' } );
	this.mainBooklet.getPage( 'form' ).$element.html( this.form.$element );
	this.setPanel( 'form' );
};
