@d8 @api
Feature: Header feature

  Scenario: Header navigation anonymous
    Given I am on the homepage
    And I should use the "1600x1050" screen resolution
    Then I should see an "li.font-social-icon.facebook-icon" element
    And I should see an "li.font-social-icon.linkedin-icon" element
    And I should see an "li.font-social-icon.twitter-icon" element
    When I click on "#block-headermenu-3 > ul > li:nth-child(1) > a"
    Then I should be redirected on "en/content/why-p4h"
    When I click on "#block-headermenu-3 > ul > li:nth-child(2) > a"
    Then I should be redirected on "en/content/how-does-p4h-work"
    When I click on "#block-headermenu-3 > ul > li:nth-child(3) > a"
    Then I should be redirected on "en/where"
    When I click on "#block-headermenu-3 > ul > li:nth-child(4) > a"
    Then I should be redirected on "en/who"
    When I click on "#block-headermenu-3 > ul > li:nth-child(5) > a"
    Then I should be redirected on "en/news-and-events"
    When I click on "#block-headermenu-3 > ul > li:nth-child(6) > a"
    Then I should be redirected on "en/user/sign-up"
    When I click on "div.desktop-service-links #block-usermenu-2 > ul > li:nth-child(1) > a"
    Then I should be redirected on "en/user/sign-in"
    When I click on "div.desktop-service-links #block-usermenu-2 > ul > li:nth-child(2) > a"
    Then I should be redirected on "en/contact-us"
    When I am on the homepage
    And I click on "a[href='/fr']"
    Then I should be on "fr"

  Scenario: Header navigation logged in user
    Given I am on "en/user/sign-in"
    And I fill the following:
      | edit-name | one@ilo.org |
      | edit-pass | 123         |
    And I click on "Submit"
    And I should use the "1600x1050" screen resolution
    Then I should see an "div#block-membericons-3" element
    When I click on "#block-headerdashboardmenu a[href='/en/news-and-events']"
    Then I should be redirected on "en/news-and-events"
    When I click on "#block-headerdashboardmenu a[href='/countries_and_regions']"
    Then I should be redirected on "countries_and_regions"
    When I click on "a[href='/en/user/my-settings']"
    Then I should be redirected on "en/user/my-settings"
    When I click on "a.theme-switcher"
    And I work with elements in "div.logout-popup" region
    And click on "a.logout-link"
    Then I should be redirected on "en"