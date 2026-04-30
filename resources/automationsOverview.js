$( () => {
	const $cnt = document.getElementById( 'automations-overview-app-container' );

	if ( !$cnt ) {
		return;
	}
	$cnt.appendChild(
		new ext.wikiAutomations.ui.AutomationOverviewPanel().$element[ 0 ]
	);
} );
