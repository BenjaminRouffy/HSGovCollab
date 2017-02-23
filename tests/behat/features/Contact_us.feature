@d8 @api
Feature: Contact us feature - checking elements, validation

  @wysiwyg
  Scenario: Events creation
    Given I am on "contact-us"
    Then I should see "Welcome to the P4H platform" in the "h1" element
    And I should see the link "Contact us"
    And I should see an "div.user-info" element
    When I click on "edit-submit"
    Then I should see "First name is required." in the "#edit-sender-name-first-error" element
    And I should see "Last name is required." in the "#edit-sender-name-last-error" element
    And I should see "Email is required." in the "#edit-email-error" element
    And I should see "Accept term & conditions is required." in the "#i_accept_terms_conditions-error" element
    And I should see "Topic is required." in the "#edit-subject-error" element
    And I should see "Message is required." in the "#edit-message-error" element
    When I select "Mr." from "edit-title"
    And select "World Bank" from "edit-organisation"
    And fill "edit-sender-name-first" with "First"
    And fill "edit-sender-name-last" with "Last"
    And fill "edit-subject" with "Topic"
    And fill "edit-message" with "Test message Contact us feature"
    And fill "edit-email" with "wrong@test"
    And I check the box "edit-i-accept-terms-conditions"
    And click on "edit-submit"
    Then I should see "The email address wrong@test is not valid."
    And should see "The answer you entered for the CAPTCHA was not correct."
    When I fill "edit-email" with "email@test.com"
    And click on "edit-submit"
    Then should see "The answer you entered for the CAPTCHA was not correct."