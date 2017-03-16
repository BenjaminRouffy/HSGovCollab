(function($, Drupal) {
  'use strict';

  var fieldWrapper = $('.user-invited-person-form'),
      countryField = fieldWrapper.find('.field--name-field-country-manager'),
      projectField = fieldWrapper.find('.field--name-field-project-manager'),
      countryCheckbox = fieldWrapper.find('#edit-roles-country-managers'),
      projectCheckbox = fieldWrapper.find('#edit-roles-projects-managers');

  countryCheckbox.is(':checked') ? countryField.addClass('active') : countryField.removeClass('active');
  projectCheckbox.is(':checked') ? projectField.addClass('active') : projectField.removeClass('active');

  fieldWrapper.on('change', function(e) {
    var $target = $(e.target);

    if ($target.is(countryCheckbox)) {
      countryField.toggleClass('active');
    }
    else if ($target.is(projectCheckbox)) {
      projectField.toggleClass('active');
    }
  });

})(jQuery, Drupal);
