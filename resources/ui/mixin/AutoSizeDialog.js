/**
 * For dialogs that resize with their content, e.g. a tag multiselect gaining or losing rows
 */
ext.wikiAutomations.ui.mixin.AutoSizeDialog = function () {};

OO.initClass( ext.wikiAutomations.ui.mixin.AutoSizeDialog );

ext.wikiAutomations.ui.mixin.AutoSizeDialog.prototype.getBodyHeight = function () {
	// scrollHeight is rounded. At fractional heights (e.g. browser zoom) the body would be too
	// small by a fraction of a pixel, and the scrollbar makes the content wrap onto another row
	return OO.ui.Window.prototype.getBodyHeight.call( this ) + 1;
};

ext.wikiAutomations.ui.mixin.AutoSizeDialog.prototype.updateSize = function () {
	OO.ui.Window.prototype.updateSize.call( this );

	const frame = this.$frame[ 0 ];
	const $scrollables = this.$body.find( '*' ).addBack().filter(
		( i, el ) => /auto|scroll/.test( getComputedStyle( el ).overflowY )
	);
	this.$body.css( 'overflow', 'hidden' );
	cancelAnimationFrame( this.updateSizeRequest );
	const step = () => {
		$scrollables.each( ( i, el ) => {
			$( el ).triggerHandler( 'scroll' );
		} );
		if ( frame.getAnimations && frame.getAnimations().length ) {
			this.updateSizeRequest = requestAnimationFrame( step );
		} else {
			this.$body.css( 'overflow', '' );
		}
	};
	step();

	return this;
};
