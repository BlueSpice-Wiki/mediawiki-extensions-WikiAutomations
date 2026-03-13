ext.wikiAutomations.api = {
	triggers: null,
	actions: null,
	conditions: null,

	getTriggers: async function () {
		if ( this.triggers ) {
			return this.triggers;
		}

		ext.wikiAutomations.api.triggers = await ext.wikiAutomations.api.fetch( '/wiki-automations/v0/triggers' );
		return ext.wikiAutomations.api.triggers;
	},

	getPageFilters: async function () {
		if ( this.pageFilters ) {
			return this.pageFilters;
		}

		ext.wikiAutomations.api.pageFilters = await ext.wikiAutomations.api.fetch( '/wiki-automations/v0/page_filters' );
		return ext.wikiAutomations.api.pageFilters;
	},

	getActions: async function () {
		if ( this.actions ) {
			return this.actions;
		}

		ext.wikiAutomations.api.actions = await ext.wikiAutomations.api.fetch( '/wiki-automations/v0/actions' );
		return ext.wikiAutomations.api.actions;
	},


	saveAutomation: async function ( page, automationData ) {
		return await ext.wikiAutomations.api.fetch( `/wiki-automations/v0/store`, {
			title: page,
			data: JSON.stringify( automationData )
		}, 'POST' );
	},

	getDisplayData: async function ( type, key, data ) {
		return await ext.wikiAutomations.api.fetch( `/wiki-automations/v0/get_display_data`, {
			entityType: type,
			entityKey: key,
			data: data
		}, 'POST' );
	},

	fetch: async function ( endpoint, params, method ) {
		try {
			method = method || 'GET';
			let url = mw.util.wikiScript( 'rest' ) + endpoint;
			if ( method === 'GET' && params ) {
				const urlParams = new URLSearchParams( params );
				url += '?' + urlParams.toString();
			}
			const response = await fetch( url, {
				method: method,
				headers: {
					'Content-Type': 'application/json'
				},
				body: method !== 'GET' ? JSON.stringify( params ) : null
			} );
			if ( !response.ok ) {
				throw new Error( `API request failed with status ${ response.status }` );
			}
			return await response.json();
		} catch ( e ) {
			console.error( e ); // eslint-disable-line no-console
		}
	}
}