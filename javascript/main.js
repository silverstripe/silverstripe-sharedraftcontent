(function ($) {
	$.entwine('ss', function ($) {
		// Select the input field when the tab is open.
		$('#share-draft-content .popup-trigger').entwine({
			onclick: function () {
				var $self = this;

				// Generate the link a maximum of once per page load.
				if (this.data('share-link') !== void 0) {
					return;
				}

				$.get(this.data('makelink-action'), function (shareLink) {
					$self.data('share-link', shareLink);
					jQuery('#share-draft-content-tab input').val(shareLink).select();
				});
			}
		});

		// Select the input text when the field is clicked.
		$('#share-draft-content-tab input').entwine({
			onclick: function () {
				jQuery('#share-draft-content-tab input').select();
			}
		});
	});
}(jQuery));
