@javascript
Feature: Share the draft version of a page
  As a CMS author
  I want to share the draft version of a page
  So that unauthenticated users can view draft changes before I publish them

  Background:
    Given a "page" "My page" with "Content"="Initial content"
    And the "group" "EDITOR group" has permissions "CMS_ACCESS_LeftAndMain"

  Scenario: I can generate a share draft link
    # Setup
    Given I am logged in with "EDITOR" permissions
    And I go to "/admin/pages"
    And I click on "My page" in the tree

    # Test link generation works
    Given I press the "Share" button
    Then I should see "Share draft content"
    And I should see "Anyone with this link can view the draft version of this page"
    And I save the link to share draft content local storage
    
    # Test the link allows us to view content on the front end when not logged in
    When I go to "/Security/login"
    And I press the "Log in as someone else" button
    And I follow the link in share draft content local storage
    And I clear the link from share draft content local storage
    Then I should see "My page"

    # Test that still cannot view regular draft content
    When I go to "/my-page"
    Then I should not see "My Page"
