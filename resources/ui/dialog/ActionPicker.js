ext.wikiAutomations.ui.dialog.ActionPicker = function ( config ) {
	config = config || {};
	config.selectionSize = 'small';
	ext.wikiAutomations.ui.dialog.ActionPicker.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.ActionPicker, ext.wikiAutomations.ui.dialog.ItemPicker );

ext.wikiAutomations.ui.dialog.ActionPicker.static.name = 'actionPicker';
ext.wikiAutomations.ui.dialog.ActionPicker.static.title = mw.msg( 'wiki-automations-ui-action-add-action' );

ext.wikiAutomations.ui.dialog.ActionPicker.prototype.load = async function () {
	const actions = await ext.wikiAutomations.api.getActions();

	const options = [];
	for ( const actionKey in actions ) {
		const action = actions[ actionKey ];
		options.push(
			new OO.ui.ButtonOptionWidget( {
				data: Object.assign( action, { key: actionKey } ),
				label: action.label,
				framed: false,
				classes: [ 'ext-wikiAutomations-itemPicker-entityOptionItem' ]
			} )
		);
	}

	this.picker = new OO.ui.ButtonSelectWidget( {
		items: options,
		classes: [ 'ext-wikiAutomations-itemPicker-itemSelect' ]
	} );

	this.mainBooklet.getPage( 'selector' ).$element.append( this.picker.$element );
	this.picker.connect( this, {
		select: ( item ) => this.onItemSelected( item.getData() )
	} );
	setTimeout( () => this.updateSize(), 1 );
}
