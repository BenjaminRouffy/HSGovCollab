@d8 @api @clonedb
Feature: Static pages feature

  @wysiwyg @javascript
  Scenario: Static page creation
    Given I am logged in with credentials:
      | username | admin     |
      | password | propeople |
    When I am on "node/add/basic_page"
    Then I should see "Create Static page" in the "h1" element
    When I click on "input[data-drupal-selector='edit-field-banner-add-more-add-more-button-infographic']"
    And wait until AJAX is finished
    And click on "field_banner_0_subform_field_infographic_item_infographic_item_add_more"
    And wait until AJAX is finished
    And fill "field_banner[0][subform][field_infographic_item][0][subform][field_title][0][value]" with "Infographic 1"
    And I select "Globe" from "field_banner[0][subform][field_infographic_item][0][subform][field_icon]"
    And fill "field_banner[0][subform][field_infographic_item][0][subform][field_infographic_description][0][value]" with "Infographic 1 description"
    And fill "field_banner[0][subform][field_infographic_item][0][subform][field_value][0][value]" with "99 % value 1"
    When I click on "a[href='#edit-group-summary']"
    Then I fill "edit-title-0-value" with "AT Static page feature 1"
    And I fill "edit-field-author-0-value" with "Static page AT author"
    Then I should see an "select#edit-langcode-0-value" element
    When I click on "a[href='#edit-group-content']"
    And I work with elements in "div#edit-field-content-paragraph" region
    And I click on "li.dropbutton-toggle"
    And I click on "Add Content text"
    And I wait until AJAX is finished
    And I checkout to whole page
    And I work with elements in "div#edit-field-content-paragraph-wrapper" region
    And I fill "field_content_paragraph[0][subform][field_title][0][value]" with "Content text title"
    And I press the "Edit summary" button
    And I fill in "field_content_paragraph[0][subform][field_content_text][0][summary]" with "Summary of the Content text paragraph"
    And I work with "Content text" WYSIWYG editor
    And I type "Content text in Static pages AT feature" in WYSIWYG editor
    And I checkout to whole page
    When I click on "a[href='#edit-group-timeline']"
    And click on "field_time_line_time_line_add_more"
    And I wait for AJAX to finish
    And fill "field_time_line[0][subform][field_title][0][value]" with "Timeline title"
    And fill "field_time_line[0][subform][field_headline][0][value]" with "Timeline headline"
    And I work with elements in "div[data-drupal-selector='edit-field-time-line-0-subform-field-time-line-item']" region
    And I click on "li.dropbutton-toggle"
    And I click on "Add Custom content"
    And I wait until AJAX is finished
    And I checkout to whole page
    And I work with elements in "div[data-drupal-selector='edit-field-time-line-0-subform-field-time-line-item-wrapper']" region
    And I fill "field_time_line[0][subform][field_time_line_item][0][subform][field_link][0][uri]" with "https://google.com"
    And I choose "October 12, 2018" in "Date" datepicker
    Then I check that "Date" datepicker contains "October 12, 2018" date
    And I work with "Body" WYSIWYG editor
    And I type "Custom text in Timeline - Static pages AT feature" in WYSIWYG editor
    And I checkout to whole page
    And I click on "a[href='#edit-group-bottom']"
    And I select "Interested in joining the P4H Network?" from "edit-field-join-block"
    And click on "edit-field-bottom-link-add-more-add-more-button-link"
    And I wait until AJAX is finished
    And fill "field_bottom_link[0][subform][field_link][0][uri]" with "https://google.com"
    And fill "field_bottom_link[0][subform][field_link][0][title]" with "Bottom link"
    And I click on "Save and publish"
    And I work with elements in "div.section-info" region
    Then I should see an "div.icon-wrapper" element
    And I should see "Infographic 1" in the "h2[role='heading']" element
    And I should see "99 % value 1" in the "span.value" element
    When I checkout to whole page
    And I execute Javascript "jQuery({{ELEMENT}}).css('top', 0)" on "div.top-banner-region div.paragraph div.infooverlay" element
    Then I should see "Infographic 1 description" in the "div.infooverlay > p.description" element
    And I scroll to "div.section-info" element
    And I work with elements in "div.anchor-links" region
    Then I should see an "a[href='#at_static_page_feature_1']" element
    And I should see an "a[href='#content_text_title']" element
    And I should see an "a[href='#timeline_title']" element
    When I checkout to whole page
    Then I should see "By: Static page AT author" in the "span.author" element
    And I should see "AT Static page feature 1" in the "h1.page-title" element
    And I should see "Content text title"
    And I should see "Summary of the Content text paragraph" in the "div.summary-text" element
    And I should see "Content text in Static pages AT feature"
    And I should see "Interested in joining the P4H Network?"
    And I scroll to "div#timeline_title" element
    Then I should see "Timeline title"
    And I work with elements in "div.timeline-wrapper" region
    And I should see "Timeline headline"
    And I should see "12 Oct. 2018"
    And I should see "Custom text in Timeline - Static pages AT feature"
    And I should see the "a[role='link']" element with "href" attribute having "https://google.com" value
    And I checkout to whole page
    And I work with elements in "div.bottom-link" region
    And I should see an "a[href='https://google.com']" element