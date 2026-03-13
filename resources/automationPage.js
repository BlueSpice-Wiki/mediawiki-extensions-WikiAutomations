$( () =>  {
	$( '.wiki-automation-page' ).each( function () {
		const $cnt = $( this );
		const action = $cnt.data( 'action' );
		const automationData = $cnt.data( 'automationData' ) || {};
		const enabled = typeof automationData.enabled === 'undefined' ? true : automationData.enabled;

		const panel = new ext.wikiAutomations.ui.AutomationPanel( {}, $cnt.data() );
		$cnt.html( panel.$element );

		if ( action !== 'view' ) {
			const toolbar = ext.wikiAutomations.ui.toolbar._createEditorToolbar( enabled );
			toolbar.on( 'save', async () => {
				const value = await panel.getValue();
				console.log( value );
				const res = await ext.wikiAutomations.api.saveAutomation( panel.automationId, value );
				console.log( res );
				window.location.href = mw.Title.newFromText( panel.automationId ).getUrl();
			} );
			toolbar.on( 'cancel', () => {
				window.location.href = mw.Title.newFromText( panel.automationId ).getUrl();
			} );
			toolbar.on( 'enable', async () => {
				panel.setEnabled( true );
			} );
			toolbar.on( 'disable', async () => {
				panel.setEnabled( false );
			} );
			toolbar.$element.insertBefore( panel.$element );
		}
	} );
} );