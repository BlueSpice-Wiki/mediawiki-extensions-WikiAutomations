ext.wikiAutomations.ui.AutomationOverviewPanel = function ( cfg ) {
	cfg = cfg || {};

	const columns = {
		prefixed: {
			type: 'text',
			headerText: mw.msg( 'wiki-automations-overview-column-title' ),
			filter: { type: 'text' },
			sortable: true,
			valueParser: ( value, row ) => {
				// if value ends with `.automation`, remove it for better readability
				const displayValue = value.endsWith( '.automation' ) ? value.slice( 0, -11 ) : value;
				return new OO.ui.HtmlSnippet( `<a href="${ row.url }">${ displayValue }</a>` );
			}
		},
		namespace: {
			type: 'number',
			headerText: mw.msg( 'wiki-automations-overview-column-namespace' ),
			filter: { type: 'number' },
			sortable: true,
			hidden: true,
			valueParser: ( value, row ) => row.namespace_text
		}
	};

	this.store = new OOJSPlus.ui.data.store.RemoteRestStore( {
		path: 'mws/v1/title-query-store',
		filter: {
			content_model: { // eslint-disable-line camelcase
				type: 'string',
				value: 'automation'
			}
		}
	} );
	cfg.grid = {
		store: this.store,
		columns: columns,
		multiSelect: false,
		exportable: false,
		stateId: 'wiki-automations-overview-grid'
	};

	ext.wikiAutomations.ui.AutomationOverviewPanel.parent.call( this, cfg );
};

OO.inheritClass( ext.wikiAutomations.ui.AutomationOverviewPanel, OOJSPlus.ui.panel.ManagerGrid );

ext.wikiAutomations.ui.AutomationOverviewPanel.prototype.getToolbarActions = function () {
	return [ this.getAddAction( { icon: 'add', flags: [ 'progressive' ], displayBothIconAndLabel: true } ) ];
};

ext.wikiAutomations.ui.AutomationOverviewPanel.prototype.onAction = function ( action ) {
	if ( action === 'add' ) {
		const diag = new ext.wikiAutomations.ui.dialog.NewAutomation( {
			id: 'wiki-automations-new-automation'
		} );
		diag.on( 'actioncompleted', ( newTitle ) => {
			window.location.href = newTitle.getUrl( { action: 'edit', backTo: mw.config.get( 'wgPageName' ) } );
		} );
		diag.show();
	}
};
