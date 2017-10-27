(function ($) {
  Drupal.behaviors.notifications = {
    attach: function (context, settings) {
      var $newsElement = $('.news', context);
      if (settings.notifications['news'] == '1') {
        $newsElement.once().addClass('notification-icon');
      }
    }
  }
}(jQuery));
