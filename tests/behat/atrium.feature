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

  Scenario: The front page shows its designed sections to a visitor and hides them when switched off
    Given the following "courses" exist:
      | fullname       | shortname |
      | Marine Biology | MB1       |
    And the following config values are set as admin:
      | forcelogin   | 0 |
      | enablemyhome | 1 |
    And I am on site homepage
    Then I should see "Welcome to" in the ".atrium-fp-hero" "css_element"
    And I should see "Browse courses" in the ".atrium-fp-hero" "css_element"
    And I should see "Why learn here" in the ".atrium-fp-features" "css_element"
    And I should see "Marine Biology" in the ".atrium-fp-showcase" "css_element"
    And I should see "Courses" in the ".atrium-fp-stats" "css_element"
    And I should see "Ready to start learning?" in the ".atrium-fp-cta" "css_element"
    And "#atrium-sidebar" "css_element" should not exist
    And ".atrium-fp-testimonials" "css_element" should not exist
    When the following config values are set as admin:
      | fp_features_enable | 0 | theme_atrium |
      | fp_testimonials_enable | 1 | theme_atrium |
      | fp_testimonial1_quote | Best site I have studied on. | theme_atrium |
      | fp_testimonial1_name | Ada Lovelace | theme_atrium |
    And I am on site homepage
    Then ".atrium-fp-features" "css_element" should not exist
    And I should see "Best site I have studied on." in the ".atrium-fp-testimonials" "css_element"
    And I should see "Ada Lovelace" in the ".atrium-fp-testimonials" "css_element"
    When the following config values are set as admin:
      | fp_enable | 0 | theme_atrium |
    And I am on site homepage
    Then ".atrium-frontpage" "css_element" should not exist

  Scenario: The catalogue lists courses as cards, searches, and remembers the list view
    Given the following "categories" exist:
      | name    | category | idnumber |
      | Science | 0        | SCI      |
      | Arts    | 0        | ART      |
    And the following "courses" exist:
      | fullname          | shortname | category |
      | Marine Biology    | MB1       | SCI      |
      | Organic Chemistry | OC1       | SCI      |
      | Sculpture         | SC1       | ART      |
    And I log in as "student1"
    And I am on course index
    Then I should see "Marine Biology" in the ".atrium-catalogue" "css_element"
    And I should see "Sculpture" in the ".atrium-catalogue" "css_element"
    And I should see "Science" in the ".atrium-catalogue-chips" "css_element"
    And ".atrium-course-grid" "css_element" should exist
    When I click on "Science" "link" in the ".atrium-catalogue-chips" "css_element"
    Then I should see "Organic Chemistry" in the ".atrium-catalogue" "css_element"
    And I should not see "Sculpture" in the ".atrium-catalogue" "css_element"
    When I click on "List" "link" in the ".atrium-catalogue-view" "css_element"
    Then ".atrium-course-list" "css_element" should exist
    And I am on course index
    And ".atrium-course-list" "css_element" should exist
    When I set the field "Search courses" to "sculpt"
    And I press "Search"
    Then I should see "Sculpture" in the ".atrium-catalogue" "css_element"
    And I should not see "Marine Biology" in the ".atrium-catalogue" "css_element"
    When I set the field "Search courses" to "nothinghere"
    And I press "Search"
    Then ".atrium-empty" "css_element" should exist

  Scenario: The enrolment page is a course landing page around the enrolment forms
    Given the following "courses" exist:
      | fullname     | shortname | summary                     |
      | Astrophysics | AP1       | Stars, from birth to death. |
    And the following "activities" exist:
      | activity | course | name          | section |
      | page     | AP1    | Reading list  | 1       |
      | forum    | AP1    | Questions     | 1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | AP1    | editingteacher |
    And I log in as "admin"
    And I add "Self enrolment" enrolment method in "Astrophysics" with:
      | Custom instance name | Join Astrophysics |
    And I log out
    When I log in as "student1"
    And I am on "Astrophysics" course homepage
    Then I should see "Astrophysics" in the ".atrium-enrol-banner" "css_element"
    And I should see "Stars, from birth to death." in the ".atrium-enrol-summary" "css_element"
    And I should see "Reading list" in the ".atrium-enrol-outline" "css_element"
    And I should see "Grace Hopper" in the ".atrium-enrol-instructors" "css_element"
    And I should see "Enrolment options" in the ".atrium-enrol-card" "css_element"
    And "#theme_boost-drawers-courseindex" "css_element" should not exist
    When I press "Enrol me"
    Then I should see "Reading list"
    And ".atrium-enrol" "css_element" should not exist

  Scenario: The course banner shows progress and focus mode strips the page down and remembers it
    Given the following "activities" exist:
      | activity | course | name         | section | completion |
      | page     | C1     | Reading list | 1       | 1          |
    And I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Course 1" in the ".atrium-course-banner" "css_element"
    And I should see "0% complete" in the ".atrium-course-banner" "css_element"
    And "#atrium-sidebar" "css_element" should be visible
    When I click on "Focus mode" "link" in the ".atrium-course-banner" "css_element"
    Then "body.atrium-focus" "css_element" should exist
    And "#atrium-sidebar" "css_element" should not be visible
    And I should see "Exit focus mode" in the ".atrium-focus-bar" "css_element"
    When I click on "Reading list" "link" in the ".course-content" "css_element"
    Then "body.atrium-focus" "css_element" should exist
    And I should see "Reading list"
    When I click on "Exit focus mode" "link" in the ".atrium-focus-bar" "css_element"
    Then "body.atrium-focus" "css_element" should not exist
    And "#atrium-sidebar" "css_element" should be visible
