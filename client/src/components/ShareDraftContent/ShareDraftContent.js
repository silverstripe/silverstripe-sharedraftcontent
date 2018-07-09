import React, { Component, PropTypes } from 'react';
import { inject } from 'lib/Injector';

class ShareDraftContent extends Component {
  render() {
    const { PopoverField } = this.props;

    return (
      <div className="share-draft-content__wrapper">
        <PopoverField
          id={"testing"}
          title={"testing"}
        />
      </div>
    );
  }
}

export { ShareDraftContent as Component };

export default inject(
  ['PopoverField']
)(ShareDraftContent);
