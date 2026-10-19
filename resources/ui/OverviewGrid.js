ext.wikiAutomations.ui.AutomationOverviewPanel = function ( cfg ) {
	cfg = cfg || {};

	const columns = {
		status: {
			headerText: mw.msg( 'wiki-automations-overview-column-status' ),
			filter: {
				type: 'list',
				list: [
					{ data: 'enabled', label: mw.msg( 'wiki-automations-ui-active' ) },
					{ data: 'disabled', label: mw.msg( 'wiki-automations-ui-inactive' ) }
				],
				closePopupOnChange: true
			},
			sortable: true,
			width: 90,
			valueParser: ( value ) => {
				const label = mw.msg( value === 'enabled' ? 'wiki-automations-ui-active' : 'wiki-automations-ui-inactive' );
				return new OO.ui.LabelWidget( { // eslint-disable-line mediawiki/class-doc
					label: label,
					title: label,
					classes: [ 'wiki-automations-status', 'wiki-automations-status-' + value ]
				} ).$element;
			}
		},
		name: {
			type: 'url',
			urlProperty: 'page_url',
			headerText: mw.msg( 'wiki-automations-overview-column-title' ),
			filter: { type: 'text' },
			sortable: true
		},
		namespace_text: { // eslint-disable-line camelcase
			type: 'text',
			headerText: mw.msg( 'wiki-automations-overview-column-namespace' ),
			filter: { type: 'text' },
			sortable: true
		},
		actionEdit: {
			type: 'action',
			actionId: 'edit',
			icon: 'edit',
			title: mw.msg( 'wiki-automations-ui-action-edit' ),
			headerText: mw.msg( 'wiki-automations-ui-action-edit' ),
			invisibleHeader: true,
			width: 30,
			visibleOnHover: true
		},
		actionDelete: {
			type: 'action',
			actionId: 'delete',
			icon: 'trash',
			title: mw.msg( 'wiki-automations-ui-action-delete' ),
			headerText: mw.msg( 'wiki-automations-ui-action-delete' ),
			invisibleHeader: true,
			width: 30,
			visibleOnHover: true
		}
	};

	this.store = new OOJSPlus.ui.data.store.RemoteRestStore( {
		path: 'wiki-automations/v0/overview-store'
	} );
	cfg.grid = {
		store: this.store,
		columns: columns,
		multiSelect: false,
		exportable: false,
		stateId: 'wiki-automations-overview-grid-v2'
	};

	ext.wikiAutomations.ui.AutomationOverviewPanel.parent.call( this, cfg );
};

OO.inheritClass( ext.wikiAutomations.ui.AutomationOverviewPanel, OOJSPlus.ui.panel.ManagerGrid );

ext.wikiAutomations.ui.AutomationOverviewPanel.prototype.getToolbarActions = function () {
	return [ this.getAddAction( { icon: 'add', flags: [ 'progressive' ], displayBothIconAndLabel: true } ) ];
};

ext.wikiAutomations.ui.AutomationOverviewPanel.prototype.onAction = function ( action, row ) {
	if ( action === 'add' ) {
		this.showNewAutomationDialog();
	}
	if ( action === 'edit' && row ) {
		window.location.href = mw.Title.newFromText( row.page_prefixed_text ).getUrl( {
			action: 'edit',
			backTo: mw.config.get( 'wgPageName' )
		} );
	}
	if ( action === 'delete' && row ) {
		this.deleteAutomation( row );
	}
};

ext.wikiAutomations.ui.AutomationOverviewPanel.prototype.deleteAutomation = function ( row ) {
	const dialog = new ext.wikiAutomations.ui.dialog.DeleteAutomation( {
		pageName: row.page_prefixed_text
	} );
	dialog.on( 'deleted', () => {
		mw.notify( mw.msg( 'wiki-automations-overview-delete-success', row.page_prefixed_text ), { type: 'success' } );
		this.store.reload();
	} );
	const windowManager = OO.ui.getWindowManager();
	windowManager.addWindows( [ dialog ] );
	windowManager.openWindow( dialog ).closed.then( () => {
		windowManager.removeWindows( [ dialog.constructor.static.name ] );
	} );
};

ext.wikiAutomations.ui.AutomationOverviewPanel.prototype.showNewAutomationDialog = function () {
	const diag = new ext.wikiAutomations.ui.dialog.NewAutomation( {
		id: 'wiki-automations-new-automation'
	} );
	diag.on( 'actioncompleted', ( newTitle ) => {
		window.location.href = newTitle.getUrl( { action: 'edit', backTo: mw.config.get( 'wgPageName' ) } );
	} );
	diag.show();
};
