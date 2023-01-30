import jQuery from 'jquery';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { loadComponent } from 'lib/Injector';

/**
 * Uses entwine to inject the ShareDraftContent React component into the DOM, when used
 * outside of a React context e.g. in the CMS
 */
jQuery.entwine('ss', ($) => {
  $('.js-injector-boot .share-draft-content__placeholder').entwine({
    ReactRoot: null,

    onmatch() {
      const cmsContent = this.closest('.cms-content').attr('id');
      const context = (cmsContent)
        ? { context: cmsContent }
        : {};
      const ShareDraftContentComponent = loadComponent('ShareDraftContent', context);

      // Get a piece of context for whether the button is in the "edit" or "split"/"preview"
      // part of the CMS
      const contextKey = this.closest('.cms-preview').length > 0 ? 'preview' : 'edit';

      let root = this.getReactRoot();
      if (!root) {
        root = createRoot(this[0]);
        this.setReactRoot(root);
      }
      root.render(
        <ShareDraftContentComponent
          id={`share-draft-content-${contextKey}`}
          links={{
            generateLink: this.data('url'),
            learnMore: this.data('helpurl'),
          }}
        />
      );
    },

    onunmatch() {
      const root = this.getReactRoot();
      if (root) {
        root.unmount();
        this.setReactRoot(null);
      }
    }
  });
});
