(function ($) {
  'use strict';

  Drupal.behaviors.notifications = {
    attach: function (context, settings) {


      var $element = $('#block-dashboardmenu', context);

      if ($element.length === 0) {
        return;
      }

      var conf = settings.notifications;

      if ('undefined' !== typeof conf) {
        for (var key in conf) {
          if (conf.hasOwnProperty(key)) {
            var $link = $(conf[key]['selector'], context);
            $link.once().addClass('notification-icon');
            if (typeof conf[key]["args"] === 'string') {
              var href = $link.attr('href');
              $link.attr('href', href + "?" + conf[key]["args"]);
            }
          }
        }
      }
    }
  }

}(jQuery));
