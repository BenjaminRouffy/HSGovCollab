(function ($, Drupal) {
  Drupal.behaviors.countryMap = {
    attach: function (context, settings) {

      // Google maps is hidden based on field conditions and when it is displayed again we need to
      // trigger window resize event in order for google map to calculate its new dimensions.
      $(document).bind('state:checked', function (e) {
        var geographicalObject = $('.js-form-item-field-geographical-object-value .form-checkbox', context)[0];

        if (e.target === geographicalObject && e.value === true) {
          window.dispatchEvent(new Event('resize'));
        }
      });
    }
  };
})(jQuery, Drupal);
