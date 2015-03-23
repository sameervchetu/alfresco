@mod @totara @mod_facetoface
Feature: Manager approval
  In order to control seminar attendance
  As a manager
  I need to authorise seminar signups

  Background:
    Given the following "users" exists:
      | username | firstname | lastname | email               |
      | teacher1 | Terry1    | Teacher1 | teacher1@moodle.com |
      | teacher2 | Terry2    | Teacher2 | teacher2@moodle.com |
      | student1 | Sam1      | Student1 | student1@moodle.com |
    And the following "courses" exists:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exists:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |
      | student1 | C1     | student        |

    And I log in as "admin"
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Enrolments" node
    And I follow "Manage enrol plugins"
    And I click on "Enable" "link" in the "Face-to-face direct enrolment" "table_row"
    And I expand "Activity modules" node
    And I expand "Face-to-face" node
    And I follow "General Settings"
    And I fill in "Select position on signup" with "checked_checkbox"
    And I press "Save changes"
    And I log out

    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                      | Test facetoface name        |
      | Description               | Test facetoface description |
      | Approval required         | 1                           |
      | Select position on signup | 1                           |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2020 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2020 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | capacity              | 1    |
    And I press "Save changes"
    And I log out

    And I log in as "admin"
    And I expand "Site administration" node
    And I expand "Hierarchies" node
    And I expand "Positions" node
    And I follow "Manage positions"
    And I press "Add new position framework"
    And I set the following fields to these values:
      | Name | PosHierarchy1 |
    And I press "Save changes"
    And I follow "PosHierarchy1"
    And I press "Add new position"
    And I set the following fields to these values:
      | Name | Position1 |
    And I press "Save changes"
    And I press "Return to position framework"
    And I press "Add new position"
    And I set the following fields to these values:
      | Name | Position2 |
    And I press "Save changes"
    And I expand "Users" node
    And I expand "Accounts" node
    And I follow "Browse list of users"
    And I follow "Sam1 Student1"
    And I expand "Positions" node
    And I follow "Primary position"
    And I press "Choose position"
    And I click on "Position1" "link_or_button"
    And I click on "OK" "link_or_button" in the "div[aria-describedby='position']" "css_element"
    And I press "Update position"
    And I press "Choose manager"
    And I click on "Terry1 Teacher1" "link_or_button"
    And I click on "OK" "link_or_button" in the "div[aria-describedby='manager']" "css_element"
    And I press "Update position"
    And I follow "Secondary position"
    And I press "Choose position"
    And I click on "Position2" "link_or_button"
    And I click on "OK" "link_or_button" in the "div[aria-describedby='position']" "css_element"
    And I press "Update position"
    And I press "Choose manager"
    And I click on "Terry2 Teacher2" "link_or_button"
    And I click on "OK" "link_or_button" in the "div[aria-describedby='manager']" "css_element"
    And I press "Update position"
    And I log out

  @javascript
  Scenario: Student signs up with two managers assigned
    When I log in as "student1"
    And I follow "Course 1"
    And I should see "Sign-up"
    And I follow "Sign-up"
    And I should see "This session requires manager approval to book."
    And I set the following fields to these values:
      | Select a position | Position2 |
    And I press "Sign-up"
    And I should see "Your booking has been completed but requires approval from your manager."
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Test facetoface name"
    And I follow "Attendees"
    And I should not see "Approval required"
    And I log out
    And I log in as "teacher2"
    And I follow "Course 1"
    And I follow "Test facetoface name"
    And I follow "Attendees"
    And I follow "Approval required"
    And I click on "input[value='2']" "css_element" in the "Sam1 Student1" table row
    And I press "Update requests"