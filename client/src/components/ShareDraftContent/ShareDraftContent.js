import React, { useRef, useState } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import i18n from 'i18n';
import fetch from 'isomorphic-fetch';
import { inject } from 'lib/Injector';

/**
 * The "share draft content" component adds a CMS action to generate a unique token-based link
 * that can be shared with unauthenticated users to view the draft version of a page
 */
const ShareDraftContent = ({
  id = 'share-draft-content',
  className,
  button = {
    icon: 'share',
    title: i18n._t('ShareDraftContent.SHARE', 'Share'),
    tooltip: i18n._t('ShareDraftContent.SHARE_DRAFT_CONTENT', 'Share draft content'),
  },
  popover = {
    title: i18n._t('ShareDraftContent.SHARE_DRAFT_CONTENT', 'Share draft content'),
  },
  links = {
    learnMore: '',
  },
  PopoverField,
}) => {
  const [error, setError] = useState(null);
  const [isLoaded, setIsLoaded] = useState(false);
  const [previewUrl, setPreviewUrl] = useState(i18n._t('ShareDraftContent.LOADING', 'Loading...'));
  const linkRef = useRef(null);

  /**
   * Ensure the link input's contents is selected
   */
  const selectLink = () => {
    if (linkRef.current) {
      linkRef.current.select();
    }
  };

  /**
   * Generate and/or get the preview draft URL from the CMS, setting it to the state once
   * completed.
   */
  const generateShareDraftLink = () => {
    const { generateLink } = links;
    return fetch(generateLink, { credentials: 'same-origin' })
      .then(response => response.text())
      .then(responseText => {
        setIsLoaded(true);
        setPreviewUrl(responseText);
        selectLink();
      }, () => setError(true)
      );
  };

  /**
   * What to do when the "Share" button is clicked and the popover is opened. If it's already
   * loaded then the link should be selected, otherwise it should generate (and then select)
   * the link.
   */
  const handleToggle = () => {
    if (!isLoaded) {
      generateShareDraftLink();
    } else {
      selectLink();
    }
  };

  /**
   * Renders an error message when loading the share link fails
   *
   * @returns {Object|null}
   */
  const renderError = () => {
    if (!error) {
      return null;
    }
    return (
      <div className="alert alert-danger">
        {i18n._t(
          'ShareDraftContent.FETCH_ERROR',
          'There was a problem generating the shareable link!'
        )}
      </div>
    );
  };

  /**
   * Renders a help information paragraph with an optional link to learn more via userhelp
   * if the URL is defined or passed in.
   *
   * @returns {Object}
   */
  const renderHelp = () => {
    const { learnMore } = links;
    return (
      <p>
        {i18n._t(
          'ShareDraftContent.DESCRIPTION',
          'Anyone with this link can view the draft version of this page.'
        )} {learnMore && <a
          href={learnMore}
          className="share-draft-content__learn-more"
          target="_blank"
          rel="noopener noreferrer"
        >
          {i18n._t('ShareDraftContent.LEARN_MORE', 'Learn more')}
          <span className="share-draft-content__learn-more__icon font-icon-external-link" aria-hidden="true" />
          <span className="visually-hidden">({i18n._t('ShareDraftContent.EXTERNAL_LINK', 'external link')})</span>
        </a>}
      </p>
    );
  };

  /**
   * Renders a disabled input field which will display the share draft link once it is generated
   *
   * @returns {Object}
   */
  const renderLink = () => (
    <div className="share-draft-content__link-container">
      <input
        type="text"
        className="share-draft-content__link form-control no-change-track"
        title={i18n._t('ShareDraftContent.LINK_HELP', 'Link to share draft content')}
        value={previewUrl}
        ref={linkRef}
        readOnly
      />
    </div>
  );

  const popoverProps = {
    id,
    buttonClassName: button.className,
    buttonIcon: button.icon,
    title: button.title,
    data: {
      popoverTitle: popover.title,
      buttonTooltip: button.tooltip,
      placement: 'top',
    },
    toggleCallback: handleToggle,
  };

  const containerClassName = classnames('share-draft-content__container', className);

  return (
    <div className={containerClassName}>
      <PopoverField {...popoverProps}>
        { renderError() }
        { renderHelp() }
        { renderLink() }
      </PopoverField>
    </div>
  );
};

ShareDraftContent.propTypes = {
  id: PropTypes.string.isRequired,
  className: PropTypes.string,
  button: PropTypes.shape({
    className: PropTypes.string,
    title: PropTypes.string,
    tooltip: PropTypes.string,
  }),
  popover: PropTypes.shape({
    title: PropTypes.string,
  }),
  links: PropTypes.shape({
    generateLink: PropTypes.string.isRequired,
    learnMore: PropTypes.string,
  }),
  PopoverField: PropTypes.oneOfType([PropTypes.node, PropTypes.func]).isRequired,
};

export { ShareDraftContent as Component };

export default inject(
  ['PopoverField']
)(ShareDraftContent);
