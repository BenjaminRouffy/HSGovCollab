(function($, Drupal) {
  'use strict';

  Drupal.behaviors.fadeOut = {
    attach: function(context, settings) {
      var $blockquote = $('blockquote');

      $blockquote.each(function(i, el) {
        var $el = $(el);

        if (!$el.isVisible(true)) {
          $el.css({
            'opacity': 0,
            'transform': 'translateY(15px)'
          });
        }
      });

      $(window).scroll(function() {
        $blockquote.each(function(i, el) {
          var $el = $(el);

          if ($el.isVisible(true)) {
            $el.addClass("visible");
          }
        });
      });
    }
  };

})(jQuery, Drupal);
