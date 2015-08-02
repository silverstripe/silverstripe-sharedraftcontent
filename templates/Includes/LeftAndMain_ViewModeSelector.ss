<span id="$SelectID" class="preview-mode-selector preview-selector field dropdown">
	<select title="<% _t('SilverStripeNavigator.ChangeViewMode', 'Change view mode') %>" id="$SelectID-select" class="preview-dropdown dropdown nolabel no-change-track" autocomplete="off" name="Action">

		<option data-icon="icon-split" class="icon-split icon-view first" value="split"><% _t('SilverStripeNavigator.SplitView', 'Split mode') %></option>
		<option data-icon="icon-preview" class="icon-preview icon-view" value="preview"><% _t('SilverStripeNavigator.PreviewView', 'Preview mode') %></option>
		<option data-icon="icon-edit" class="icon-edit icon-view last" value="content"><% _t('SilverStripeNavigator.EditView', 'Edit mode') %></option>
		<!-- Dual window not implemented yet -->
		<!--
			<option data-icon="icon-window" class="icon-window icon-view last" value="window"><% _t('SilverStripeNavigator.DualWindowView', 'Dual Window') %></option>
		-->
	</select>

	<div id="share-draft-content" class="ss-tabset ss-ui-action-tabset action-menus">
		<ul>
			<% if $CurrentPage.ShareDraftLinkAction %>
				<li><a class="popup-trigger" href="#share-draft-content-tab" data-makelink-action="{$CurrentPage.ShareDraftLinkAction}">Share draft</a></li>
			<% else %>
				<li><a class="popup-trigger" href="#share-draft-content-tab" data-makelink-action="{$Controller.CurrentPage.ShareDraftLinkAction}">Share draft</a></li>
			<% end_if %>
		</ul>
		<div id="share-draft-content-tab">
			<div>
				<p>
					Copy and send this draft link to anyone.
				</p>
				<input class="text" type="text" readonly="readonly" />
			</div>
		</div>
	</div>
</span>
