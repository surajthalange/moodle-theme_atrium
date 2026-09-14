@theme @theme_atrium @javascript
Feature: Atrium theme
  In order to use the site comfortably
  As a user
  I need the sidebar, the colour scheme and the dashboard hero to work and to remember my choices

  Background:
    Given the following config values are set as admin:
      | theme            | atrium |
      | enablecompletion | 1      |
    And the following "users" exist:
      | username | firstname | lastname |
      | student1 | Ada       | Lovelace |
      | teacher1 | Grace     | Hopper   |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |

  Scenario: The sidebar carries the primary navigation, collapses to icons and remembers it
    Given I log in as "student1"
    Then "#atrium-sidebar" "css_element" should be visible
    And I should see "Dashboard" in the "#atrium-sidebar" "css_element"
    And "body.atrium-sidebar-collapsed" "css_element" should not exist
    When I click on "Collapse sidebar" "button"
    Then "body.atrium-sidebar-collapsed" "css_element" should exist
    And I reload the page
    And "body.atrium-sidebar-collapsed" "css_element" should exist
    When I click on "Expand sidebar" "button"
    Then "body.atrium-sidebar-collapsed" "css_element" should not exist
    And I reload the page
    And "body.atrium-sidebar-collapsed" "css_element" should not exist

  Scenario: Dark mode switches at once from the navigation bar and survives a reload
    Given I log in as "student1"
    Then the "data-bs-theme" attribute of "html" "css_element" should contain "light"
    When I click on "[data-action='atrium-scheme-toggle']" "css_element"
    Then the "data-bs-theme" attribute of "html" "css_element" should contain "dark"
    And I reload the page
    And the "data-bs-theme" attribute of "html" "css_element" should contain "dark"
    When I click on "#user-menu-toggle" "css_element"
    And I click on "Switch to light mode" "link"
    Then the "data-bs-theme" attribute of "html" "css_element" should contain "light"
    And I reload the page
    And the "data-bs-theme" attribute of "html" "css_element" should contain "light"

  Scenario: Disabling dark mode removes every switch
    Given the following config values are set as admin:
      | enabledarkmode | 0 | theme_atrium |
    And I log in as "student1"
    Then "[data-action='atrium-scheme-toggle']" "css_element" should not exist
    When I click on "#user-menu-toggle" "css_element"
    Then I should not see "Switch to dark mode"

  Scenario: The dashboard hero greets the learner with their counts and can be turned off
    Given I log in as "student1"
    Then I should see "Welcome back, Ada" in the ".atrium-hero" "css_element"
    And I should see "1" in the ".atrium-stat-inprogress .atrium-stat-count" "css_element"
    And I should see "0" in the ".atrium-stat-completed .atrium-stat-count" "css_element"
    When I am on the "C1" "Course" page
    Then ".atrium-hero" "css_element" should not exist
    Given the following config values are set as admin:
      | showhero | 0 | theme_atrium |
    And I visit "/my/"
    Then ".atrium-hero" "css_element" should not exist

  Scenario: The course page keeps Boost's editing tools
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    Then "#theme_boost-drawers-courseindex" "css_element" should exist
    And "#atrium-sidebar" "css_element" should be visible
    When I open the activity chooser
    Then I should see "Forum" in the ".modal" "css_element"
