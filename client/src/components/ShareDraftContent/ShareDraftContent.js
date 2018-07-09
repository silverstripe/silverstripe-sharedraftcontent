import React, { Component, PropTypes } from 'react';
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
   * Returns the URL for the "learn more" link
   *
   * @returns {string}
   */
  getLearnMoreLink() {
    return this.props.learnMoreUrl;
  }

  /**
   * Do nothing, workaround for un/controlled component warnings in React
   */
  handleInputChange() {
    // noop
  }

  /**
   * What to do when the share draft link input is clicked
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
    const { generateLinkUrl } = this.props;

    return fetch(generateLinkUrl, { credentials: 'same-origin' })
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

  render() {
    const { PopoverField } = this.props;
    const { previewUrl } = this.state;

    const popoverProps = {
      id: 'share-draft-content',
      buttonClassName: 'font-icon-share',
      title: i18n._t('ShareDraftContent.SHARE', 'Share'),
      data: {
        popoverTitle: i18n._t('ShareDraftContent.SHARE_DRAFT_CONTENT', 'Share draft content'),
        buttonTooltip: i18n._t('ShareDraftContent.SHARE_DRAFT_CONTENT', 'Share draft content'),
        placement: 'top',
      },
      toggleCallback: this.handleToggle,
    };

    return (
      <div className="share-draft-content__container">
        <PopoverField {...popoverProps}>
          { this.renderError() }
          <p>
            {i18n._t(
              'ShareDraftContent.DESCRIPTION',
              'Anyone with this link can view the draft version of this page.'
            )} <a
              href={this.getLearnMoreLink()}
              className="share-draft-content__learn-more"
              target="_blank"
              rel="noopener"
            >
              {i18n._t('ShareDraftContent.LEARN_MORE', 'Learn more')}
            </a>
          </p>

          <div className="share-draft-content__link-container">
            <input
              type="text"
              className="share-draft-content__link form-control"
              title={i18n._t('ShareDraftContent.LINK_HELP', 'Link to share draft content')}
              value={previewUrl}
              onChange={this.handleInputChange}
              ref={(linkRef) => { this.linkRef = linkRef; }}
              readOnly
            />
          </div>
        </PopoverField>
      </div>
    );
  }
}

ShareDraftContent.propTypes = {
  generateLinkUrl: PropTypes.string.isRequired,
  learnMoreUrl: PropTypes.string,
  PopoverField: PropTypes.oneOfType([PropTypes.node, PropTypes.func]).isRequired,
};

ShareDraftContent.defaultProps = {
  learnMoreUrl: 'https://google.com/testing',
};

export { ShareDraftContent as Component };

export default inject(
  ['PopoverField']
)(ShareDraftContent);
