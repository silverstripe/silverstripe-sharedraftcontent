import Injector from 'lib/Injector';
import ShareDraftContent from 'components/ShareDraftContent/ShareDraftContent';

export default () => {
  Injector.component.registerMany({
    ShareDraftContent,
  });
};
