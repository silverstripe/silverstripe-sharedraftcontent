<?php

namespace SilverStripe\ShareDraftContent\Extensions;

use SilverStripe\Assets\Shortcodes\FileShortcodeProvider;
use SilverStripe\Assets\File;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\ShareDraftContent\Controllers\ShareDraftController;

class ShareDraftContentFileShortcodeProviderExtension extends Extension
{
    /**
     * @param bool $grant
     * @param File|null $record
     * @param array|null $args Shortcode args as passed to FileShortcodeProvider::handle_shortcode()
     */
    public function updateGrant(bool &$grant, ?File $record = null, ?array $args = null): void
    {
        if ($grant) {
            return;
        }

        $controller = Controller::curr();

        if (!$controller) {
            return;
        }

        $session = $controller->getRequest()->getSession();

        if (!$session) {
            return;
        }

        if (!ShareDraftController::getIsViewingPreview()) {
            return;
        }

        if (!$record && $args) {
            $record = FileShortcodeProvider::find_shortcode_record($args);
        }

        if ($record) {
            $grant = !$record->hasRestrictedAccess();
        }
    }
}
