ext.wikiAutomations.ui.dialog.NewAutomation = function () {
	ext.wikiAutomations.ui.dialog.NewAutomation.super.call( this, {
		contentModels: [ 'automation' ]
	} );
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.NewAutomation, StandardDialogs.ui.NewPageDialog );

ext.wikiAutomations.ui.dialog.NewAutomation.prototype.makeSetupProcessData = function () {
	const data = ext.wikiAutomations.ui.dialog.NewAutomation.super.prototype.makeSetupProcessData.call( this );
	data.title = mw.message( 'wiki-automations-overview-action-create' ).plain();

	return data;
};

ext.wikiAutomations.ui.dialog.NewAutomation.prototype.makeDoneActionProcess = function () {
	this.newTitle = mw.Title.newFromText( this.appendSuffix( this.targetTitle.getValue() ) );
	return new OO.ui.Process( ( () => {} ), this );
};

ext.wikiAutomations.ui.dialog.NewAutomation.prototype.onTitleChange = function ( value ) {
	this.appendSuffix( value );
	ext.wikiAutomations.ui.dialog.NewAutomation.super.prototype.onTitleChange.call( this, value );
};

ext.wikiAutomations.ui.dialog.NewAutomation.prototype.appendSuffix = function ( value ) {
	if ( !value.endsWith( '.automation' ) ) {
		value += '.automation';
	}
	return value;
};
