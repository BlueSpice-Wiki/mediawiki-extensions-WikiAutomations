ext.wikiAutomations.ui.dialog.DeleteAutomation = function ( cfg ) {
	cfg = cfg || {};
	ext.wikiAutomations.ui.dialog.DeleteAutomation.super.call( this, cfg );
	this.pageName = cfg.pageName;
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.DeleteAutomation, OO.ui.ProcessDialog );

ext.wikiAutomations.ui.dialog.DeleteAutomation.static.name = 'wikiAutomationsDeleteAutomation';
ext.wikiAutomations.ui.dialog.DeleteAutomation.static.title = mw.msg( 'wiki-automations-overview-delete-title' );
ext.wikiAutomations.ui.dialog.DeleteAutomation.static.size = 'medium';
ext.wikiAutomations.ui.dialog.DeleteAutomation.static.actions = [
	{
		action: 'delete',
		label: mw.msg( 'wiki-automations-ui-action-delete' ),
		flags: [ 'primary', 'destructive' ]
	},
	{
		title: mw.msg( 'cancel' ),
		flags: [ 'safe', 'close' ]
	}
];

ext.wikiAutomations.ui.dialog.DeleteAutomation.prototype.initialize = function () {
	ext.wikiAutomations.ui.dialog.DeleteAutomation.super.prototype.initialize.apply( this, arguments );

	this.panel = new OO.ui.PanelLayout( { padded: true, expanded: false } );
	this.panel.$element.append( new OO.ui.LabelWidget( {
		label: mw.msg( 'wiki-automations-overview-delete-confirm', this.pageName ),
		classes: [ 'wiki-automations-delete-confirm-label' ]
	} ).$element );
	this.$body.append( this.panel.$element );
};

ext.wikiAutomations.ui.dialog.DeleteAutomation.prototype.getSetupProcess = function ( data ) {
	return ext.wikiAutomations.ui.dialog.DeleteAutomation.super.prototype.getSetupProcess.call( this, data )
		.next( () => {
			this.$body.css( 'overflow', 'hidden' );
		} );
};

ext.wikiAutomations.ui.dialog.DeleteAutomation.prototype.getReadyProcess = function ( data ) {
	return ext.wikiAutomations.ui.dialog.DeleteAutomation.super.prototype.getReadyProcess.call( this, data )
		.next( () => {
			this.$body.css( 'overflow', '' );
		} );
};

ext.wikiAutomations.ui.dialog.DeleteAutomation.prototype.getActionProcess = function ( action ) {
	if ( action !== 'delete' ) {
		return ext.wikiAutomations.ui.dialog.DeleteAutomation.super.prototype.getActionProcess.call( this, action );
	}
	return new OO.ui.Process( () => new mw.Api().postWithToken( 'csrf', {
		action: 'delete',
		title: this.pageName
	} ).then( () => {
		this.emit( 'deleted' );
		this.close();
	}, () => {
		const error = new OO.ui.Error(
			mw.msg( 'wiki-automations-overview-delete-error', this.pageName ),
			{ recoverable: false }
		);
		return $.Deferred().reject( error ).promise();
	} ) );
};
