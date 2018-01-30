import jQuery from 'jquery';

jQuery.entwine('ss', $ => {
    // Select the input field when the tab is open.
    $('#share-draft-content .popup-trigger').entwine({
        onclick() {
            const $self = this;

            // Generate the link a maximum of once per page load.
            if (this.data('share-link') !== void 0) {
                return;
            }

            $.get(this.data('makelink-action'), shareLink => {
                $self.data('share-link', shareLink);
                jQuery('#share-draft-content-tab input')
                    .val(shareLink)
                    .select();
            });
        }
    });

    // Select the input text when the field is clicked.
    $('#share-draft-content-tab input').entwine({
        onclick() {
            jQuery('#share-draft-content-tab input').select();
        }
    });
});
