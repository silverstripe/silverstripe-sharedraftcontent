@javascript
Feature: Share the draft version of a page
  As a CMS author
  I want to share the draft version of a page
  So that unauthenticated users can view draft changes before I publish them

  Background:
    Given a "page" "Home" with "Content"="Initial content"
    And I am logged in with "ADMIN" permissions
    And I go to "/admin/pages"
    And I click on "Home" in the tree
    And I set the CMS mode to "Preview mode"

  Scenario: I can generate a share draft link
    Given I press the "Share" button
    Then I should see "Share draft content"
    And I should see "Anyone with this link can view the draft version of this page"
