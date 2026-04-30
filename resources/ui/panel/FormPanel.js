ext.wikiAutomations.ui.panel.FormPanel = function ( config, layout, data, dialog ) {
	ext.wikiAutomations.ui.panel.FormPanel.parent.call( this, config );
	this.layout = layout;

	data = data || {};
	if ( dialog ) {
		// Hack to pass dialog's overlay to form fields
		for ( const field of this.layout.definition.items ) {
			if ( Object.prototype.hasOwnProperty.call( field, 'widget_$overlay' ) ) {
				field.widget_$overlay = dialog.$overlay; // eslint-disable-line camelcase, no-jquery/variable-pattern
			}
		}
	}

	this.form = this.form = new mw.ext.forms.standalone.Form(
		Object.assign( this.layout, { data: data } )
	);
	this.form.connect( this, {
		renderComplete: function ( form ) {
			for ( const inputKey in form.items.inputs || {} ) {
				form.items.inputs[ inputKey ].connect( this, {
					change: () => this.emit( 'updateSize' ),
					focus: () => this.emit( 'updateSize' ),
					blur: () => this.emit( 'updateSize' )
				} );
			}
			this.emit( 'updateSize' );
		}
	} );
	this.form.render();
	this.$element.append( this.form.$element );
};

OO.inheritClass( ext.wikiAutomations.ui.panel.FormPanel, OO.ui.PanelLayout );

ext.wikiAutomations.ui.panel.FormPanel.prototype.getValue = function () {
	const dfd = $.Deferred();
	this.form.connect( this, {
		dataSubmitted: function ( data ) {
			dfd.resolve( data );
		},
		validationFailed: function () {
			dfd.reject();
		}
	} );
	this.form.submit();
	return dfd.promise();
};
