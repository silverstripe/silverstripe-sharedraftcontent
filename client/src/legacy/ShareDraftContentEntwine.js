import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector';

/**
 * Uses entwine to inject the ShareDraftContent React component into the DOM, when used
 * outside of a React context e.g. in the CMS
 */
jQuery.entwine('ss', ($) => {
  $('.js-injector-boot .cms-preview .share-draft-content__placeholder').entwine({
    onmatch() {
      const cmsContent = this.closest('.cms-content').attr('id');
      const context = (cmsContent)
        ? { context: cmsContent }
        : {};
      const ShareDraftContentComponent = loadComponent('ShareDraftContent', context);

      ReactDOM.render(
        <ShareDraftContentComponent
          links={{
            generateLink: this.data('url'),
            learnMore: this.data('helpurl'),
          }}
          contextKey={this.data('context-key')}
        />,
        this[0]
      );
    },

    onunmatch() {
      ReactDOM.unmountComponentAtNode(this[0]);
    }
  });
});
