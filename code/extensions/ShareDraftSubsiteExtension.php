<?php

class ShareDraftSubsiteExtension extends Extension
{
    /**
     * If the page has a subsite ID, add it to the URL. This ensures that subsite specific data is honoured
     * when generating previews. See {@link Subsite::currentSubsiteID()}.
     *
     * @param SS_HTTPRequest $request
     * @param SiteTree $page
     */
    public function updatePageRequest(SS_HTTPRequest $request, SiteTree $page)
    {
        if (!$page->SubsiteID) {
            return;
        }

        // @todo don't modify superglobals directly, once Subsite::currentSubsiteID() stops doing it
        $_GET['SubsiteID'] = (int) $page->SubsiteID;
    }
}
