(function ($) {
	$.entwine('ss', function ($) {
		// Select the input field when the tab is open.
		$('#share-draft-content').entwine({
			ontabsactivate: function () {
				jQuery('#share-draft-content-tab input').select();
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
