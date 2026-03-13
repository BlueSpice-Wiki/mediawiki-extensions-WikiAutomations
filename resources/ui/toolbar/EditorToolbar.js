ext.wikiAutomations.ui.toolbar._createEditorToolbar = function ( automationEnabled ) {
	const toolFactory = new OO.ui.ToolFactory();
	const toolGroupFactory = new OO.ui.ToolGroupFactory();
	const toolbar = new OO.ui.Toolbar( toolFactory, toolGroupFactory );
	toolbar.$element.addClass( 'wiki-automations-editor-toolbar' );

	function SaveTool() {
		SaveTool.super.apply( this, arguments );
	}
	OO.inheritClass( SaveTool, OO.ui.Tool );

	SaveTool.static.name = 'save';
	SaveTool.static.title = mw.msg( "wiki-automations-ui-action-save" );
	SaveTool.static.flags = [ 'primary', 'progressive' ];
	SaveTool.prototype.onSelect = function () {
		toolbar.emit( 'save' );
		this.setDisabled( true );
		return true;
	};
	SaveTool.prototype.onUpdateState = function () {};
	toolFactory.register( SaveTool );

	function CancelTool() {
		CancelTool.super.apply( this, arguments );
	}
	OO.inheritClass( CancelTool, OO.ui.Tool );

	CancelTool.static.name = 'close';
	CancelTool.static.title = mw.msg( "wiki-automations-ui-action-cancel" );
	CancelTool.static.icon = 'close';
	CancelTool.static.flags = [ 'safe', 'close' ];
	CancelTool.prototype.onSelect = function () {
		toolbar.emit( 'cancel' );
		return true;
	};
	CancelTool.prototype.onUpdateState = function () {};
	toolFactory.register( CancelTool );

	function EnableTool() {
		EnableTool.super.apply( this, arguments );
		this.label = new OO.ui.LabelWidget( { label: mw.msg ( "wiki-automations-ui-label-enabled" ) } );
		this.check = new OO.ui.ToggleSwitchWidget( { value: automationEnabled } );
		this.check.connect( this, { change: 'onSelect' } );
		this.$element.html( new OO.ui.HorizontalLayout( {
			items: [ this.check, this.label ],
		} ).$element );
		this.$element.addClass( 'ext-wikiAutomations-toolbar-toggle-tool' );
	}
	OO.inheritClass( EnableTool, OO.ui.Tool );

	EnableTool.static.name = 'automation_enabled';
	EnableTool.prototype.onSelect = function () {
		if ( this.check.getValue() ) {
			toolbar.emit( 'enable' );
		} else {
			toolbar.emit( 'disable' );
		}
		return true;
	};
	EnableTool.prototype.onUpdateState = function () {};
	toolFactory.register( EnableTool );

	toolbar.setup( [
		{
			name: 'cancel',
			type: 'bar',
			include: [ 'close' ]
		},
		{
			name: 'actions',
			classes: [ 'default-actions' ],
			type: 'bar',
			include: [ 'automation_enabled', 'save' ]
		}
	] );
	return toolbar;
};