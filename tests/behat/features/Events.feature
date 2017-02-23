@d8 @api @clonedb
Feature: Events feature

  @wysiwyg
  Scenario: Events creation
    Given I am logged in with credentials:
      | username | admin     |
      | password | propeople |
    And I should use the "1600x1200" screen resolution
    When I am on "node/add/event"
    Then I should see "Create Event" in the "h1" element
    When I fill "edit-title-0-value" with "AT Event feature 1"
    And I fill "edit-field-event-author-0-value" with "Event AT author"
    When I click on "a[href='#edit-group-summary']"
    Then I should see an "select#edit-langcode-0-value" element
    And I work with elements in "#edit-body-wrapper" region
    And I press the "Edit summary" button
    And I fill "edit-body-0-summary" with "Summary for Event in Events feature"
    And I work with "Body" WYSIWYG editor
    And I type "Body text in Events AT feature" in WYSIWYG editor
    And I checkout to whole page
    And I click on "a[href='#edit-group-event-date-time']"
    And I choose "October 12, 2018" in "edit-field-date-0-value-date" datepicker
    Then I check that "edit-field-date-0-value-date" datepicker contains "October 12, 2018" date
    And I choose "October 13, 2018" in "edit-field-date-0-end-value-date" datepicker
    Then I check that "edit-field-date-0-end-value-date" datepicker contains "October 13, 2018" date
    And  I fill "edit-field-time-text-0-value" with "Time field text"
    And I select "UTC+01:00" from "edit-field-timezone"
    And fill "edit-field-location-0-uri" with "https://www.google.com.ua"
    And fill "edit-field-location-0-title" with "Google site"
    And fill "edit-field-event-link-0-uri" with "https://www.facebook.com"
    And fill "edit-field-event-link-0-title" with "Event link"
    And I click on "a[href='#edit-group-bo']"
    And I select "Interested in joining the P4H Network?" from "edit-field-join-block"
    And I select "Health Financing" from "edit-field-category"
    And I select "World Bank" from "edit-field-organization"
    And I click on "Save and publish"
    Then I should see "Event AT Event feature 1 has been created."
    And I should see "12 Oct. 2018" in the "time" element
    And I should see "By: Event AT author" in the "span.author" element
    And I should see "AT Event feature 1" in the "h1.page-title" element
    And I should see "Time field text"
    And I should see "Summary for Event in Events feature" in the "div.summary-text" element
    And I should see "Body text in Events AT feature"
    And I should see "Interested in joining the P4H Network?"
    When I click on "Event link"
    And I switch to opened window
    Then I should be on "https://www.facebook.com"
    And I switch back to main window
    When I click on "Google site"
    And I switch to opened window
    Then I should be on "https://www.google.com.ua"
    And I switch back to main window
    #Next - checking Event appearance on the News&Events page
    And I am on "user/logout"
    When I am on "/news-and-events"
    Then I should see "News and events" in the "h1" element
    And I should see an "div[data-drupal-selector='edit-gid']" element
    And I should see an "select#edit-year" element
    And I should see an "select#edit-month" element
    And I should see an "a[href='/en/join-the-network']" element
    When I click on "div[data-drupal-selector='edit-category']"
    And I check the box "Health Financing"
    And I wait until AJAX is finished
    Then I should see "AT Event feature 1"
    When I click on "div[data-drupal-selector='edit-organization']"
    And I check the box "World Bank"
    And I wait until AJAX is finished
    Then I should see "AT Event feature 1"