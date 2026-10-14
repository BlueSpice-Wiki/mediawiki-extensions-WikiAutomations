$( () => {
	const $cnt = document.getElementById( 'automations-overview-app-container' );

	if ( !$cnt ) {
		return;
	}
	const panel = new ext.wikiAutomations.ui.AutomationOverviewPanel();
	$cnt.appendChild( panel.$element[ 0 ] );

	$( document ).on( 'click', '.wiki-automations-create-automation', ( e ) => {
		e.preventDefault();
		panel.showNewAutomationDialog();
	} );
} );
