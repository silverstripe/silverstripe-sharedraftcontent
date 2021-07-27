import React, { Component } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import i18n from 'i18n';
import fetch from 'isomorphic-fetch';
import { inject } from 'lib/Injector';

/**
 * The "share draft content" component adds a CMS action to generate a unique token-based link
 * that can be shared with unauthenticated users to view the draft version of a page
 */
class ShareDraftContent extends Component {
  constructor(props) {
    super(props);

    this.state = {
      error: null,
      isLoaded: false,
      previewUrl: i18n._t('ShareDraftContent.LOADING', 'Loading...'),
    };

    this.handleToggle = this.handleToggle.bind(this);
  }

  /**
   * Do nothing, workaround for un/controlled component warnings in React
   */
  handleInputChange() {
    // noop
  }

  /**
   * What to do when the "Share" button is clicked and the popover is opened. If it's already
   * loaded then the link should be selected, otherwise it should generate (and then select)
   * the link.
   */
  handleToggle() {
    const { isLoaded } = this.state;
    if (!isLoaded) {
      // Generate the link
      this.generateShareDraftLink();
    } else {
      // Auto select the link text if it's already been generated
      this.selectLink();
    }
  }

  /**
   * Generate and/or get the preview draft URL from the CMS, setting it to the state once
   * completed.
   */
  generateShareDraftLink() {
    const { links: { generateLink } } = this.props;

    return fetch(generateLink, { credentials: 'same-origin' })
      .then(response => response.text())
      .then(
        (response) => {
          this.setState({
            isLoaded: true,
            previewUrl: response,
          });

          // Auto-select the link text
          this.selectLink();
        },
        () => {
          this.setState({ error: true });
        }
      );
  }

  /**
   * Ensure the link input's contents is selected
   */
  selectLink() {
    if (this.linkRef) {
      this.linkRef.select();
    }
  }

  /**
   * Renders an error message when loading the share link fails
   *
   * @returns {Object|null}
   */
  renderError() {
    const { error } = this.state;
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
  }

  /**
   * Renders a help information paragraph with an optional link to learn more via userhelp
   * if the URL is defined or passed in.
   *
   * @returns {Object}
   */
  renderHelp() {
    const { links: { learnMore } } = this.props;

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
        </a>}
      </p>
    );
  }

  /**
   * Renders a disabled input field which will display the share draft link once it is generated
   *
   * @returns {Object}
   */
  renderLink() {
    const { previewUrl } = this.state;

    return (
      <div className="share-draft-content__link-container">
        <input
          type="text"
          className="share-draft-content__link form-control no-change-track"
          title={i18n._t('ShareDraftContent.LINK_HELP', 'Link to share draft content')}
          value={previewUrl}
          onChange={this.handleInputChange}
          ref={(linkRef) => { this.linkRef = linkRef; }}
          readOnly
        />
      </div>
    );
  }

  /**
   * Renders the popover field with the share draft contents inside it
   *
   * @returns {Object}
   */
  render() {
    const {
      id,
      PopoverField,
      className,
      button,
      popover,
    } = this.props;

    const popoverProps = {
      id,
      buttonClassName: button.className,
      title: button.title,
      data: {
        popoverTitle: popover.title,
        buttonTooltip: button.tooltip,
        placement: 'top',
      },
      toggleCallback: this.handleToggle,
    };

    const containerClassName = classnames('share-draft-content__container', className);

    return (
      <div className={containerClassName}>
        <PopoverField {...popoverProps}>
          { this.renderError() }
          { this.renderHelp() }
          { this.renderLink() }
        </PopoverField>
      </div>
    );
  }
}

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

ShareDraftContent.defaultProps = {
  id: 'share-draft-content',
  button: {
    className: 'font-icon-share',
    title: i18n._t('ShareDraftContent.SHARE', 'Share'),
    tooltip: i18n._t('ShareDraftContent.SHARE_DRAFT_CONTENT', 'Share draft content'),
  },
  popover: {
    title: i18n._t('ShareDraftContent.SHARE_DRAFT_CONTENT', 'Share draft content'),
  },
  links: {
    learnMore: '',
  },
  popoverIcon: 'font-icon-share',
};

export { ShareDraftContent as Component };

export default inject(
  ['PopoverField']
)(ShareDraftContent);
