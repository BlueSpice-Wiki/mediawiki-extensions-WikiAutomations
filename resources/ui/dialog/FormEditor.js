ext.wikiAutomations.ui.dialog.FormEditor = function ( config, entity ) {
	config = config || {};
	ext.wikiAutomations.ui.dialog.FormEditor.parent.call( this, config );
	this.entity = entity;
};

OO.inheritClass( ext.wikiAutomations.ui.dialog.FormEditor, OO.ui.ProcessDialog );

ext.wikiAutomations.ui.dialog.FormEditor.static.name = 'entityEditor';
ext.wikiAutomations.ui.dialog.FormEditor.static.title = mw.msg( 'wiki-automations-ui-action-edit-entity' );
ext.wikiAutomations.ui.dialog.FormEditor.static.actions = [
	{ action: 'cancel', label: mw.msg( 'wiki-automations-ui-action-cancel' ), flags: 'safe' },
	{ action: 'save', label: mw.msg( 'wiki-automations-ui-action-save' ), flags: 'primary' }
];

ext.wikiAutomations.ui.dialog.FormEditor.prototype.initialize = function () {
	ext.wikiAutomations.ui.dialog.FormEditor.parent.prototype.initialize.call( this );

	this.form = new ext.wikiAutomations.ui.panel.FormPanel( {
		expanded: false, padded: true
	}, this.entity.layout, this.entity.data, this );

	this.$body.append( this.form.$element );
};

ext.wikiAutomations.ui.dialog.FormEditor.prototype.getActionProcess = function ( action ) {
	return ext.wikiAutomations.ui.dialog.FormEditor.parent.prototype.getActionProcess.call( this, action )
		.next( function () {
			const dfd = $.Deferred();

			if ( action === 'save' ) {
				this.form.getValue().done( data => {
					this.close( { action: 'save', data: data } );
				} ).fail( () => {
					dfd.reject();
				} );
			}
			if ( action === 'cancel' ) {
				this.close();
			}

			return dfd.promise();
		}, this );
};
