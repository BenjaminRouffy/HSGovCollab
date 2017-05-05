(function($, Drupal) {
  'use strict';

  var fieldWrapper = $('.user-form'),
      countryField = fieldWrapper.find('.field--name-field-country-manager'),
      projectField = fieldWrapper.find('.field--name-field-project-manager'),
      kvField = fieldWrapper.find('.field--name-field-knowledge-vault-manager'),
      countryCheckbox = fieldWrapper.find('#edit-roles-country-managers'),
      projectCheckbox = fieldWrapper.find('#edit-roles-projects-managers'),
      kvCheckbox = fieldWrapper.find('#edit-roles-knowledge-vault-manager');

  countryCheckbox.is(':checked') ? countryField.addClass('active') : countryField.removeClass('active');
  projectCheckbox.is(':checked') ? projectField.addClass('active') : projectField.removeClass('active');
  kvCheckbox.is(':checked') ? kvField.addClass('active') : kvField.removeClass('active');

  fieldWrapper.on('change', function(e) {
    var $target = $(e.target);

    if ($target.is(countryCheckbox)) {
      countryField.toggleClass('active');
    }
    else if ($target.is(projectCheckbox)) {
      projectField.toggleClass('active');
    }
    else if ($target.is(kvCheckbox)) {
      kvField.toggleClass('active');
    }
  });

})(jQuery, Drupal);
