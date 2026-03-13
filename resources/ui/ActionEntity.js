ext.wikiAutomations.ui.ActionEntity = function (
	actionId, key, data, displayData, enabled, editorInfo, editable
) {

	this.actionId = actionId;
	ext.wikiAutomations.ui.ActionEntity.parent.call(
		this, 'action', key, data, displayData, enabled, editorInfo, editable
	);
};

OO.inheritClass( ext.wikiAutomations.ui.ActionEntity, ext.wikiAutomations.ui.AutomationEntity );
