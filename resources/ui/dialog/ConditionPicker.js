ext.wikiAutomations.ui.dialog.ConditionPicker = function ( config ) {
	config = config || {};
	config.selectionSize = 'small';
	ext.wikiAutomations.ui.dialog.TriggerPicker.parent.call( this, config );
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.ConditionPicker, ext.wikiAutomations.ui.dialog.ItemPicker );

ext.wikiAutomations.ui.dialog.ConditionPicker.static.name = 'conditionPicker';
ext.wikiAutomations.ui.dialog.ConditionPicker.static.title = mw.msg( 'wiki-automations-ui-action-add-condition' );

ext.wikiAutomations.ui.dialog.ConditionPicker.prototype.load = async function (){
	const conditions = await ext.wikiAutomations.api.getPageFilters();

	const options = [];
	for ( const conditionKey in conditions ) {
		const condition = conditions[ conditionKey ];
		options.push(
			new OO.ui.ButtonOptionWidget( {
				data: Object.assign( condition, { key: conditionKey } ),
				label: condition.label,
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
