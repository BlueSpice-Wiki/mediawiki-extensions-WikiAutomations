ext.wikiAutomations.ui.ActionEntity = function (
	actionId, key, data, displayData, enabled, editorInfo, editable, automation
) {

	this.actionId = actionId;
	ext.wikiAutomations.ui.ActionEntity.parent.call(
		this, 'action', key, data, displayData, enabled, editorInfo, editable, automation
	);
};

OO.inheritClass( ext.wikiAutomations.ui.ActionEntity, ext.wikiAutomations.ui.AutomationEntity );
