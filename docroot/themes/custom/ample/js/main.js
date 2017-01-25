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

  Drupal.behaviors.relatedContent = {
    attach: function(context, settings) {
      var $relatedContent = $('.related-wrapper', context);

      if ($relatedContent.length) {

        function showRelated() {
          var $window = $(window);
          var $document = $(document);
          var scrollTop = $window.scrollTop();
          var halfDocOffset = $document.height() / 2;
          var halfWinOffset = $window.height() / 2;

          if ((scrollTop + halfWinOffset) > halfDocOffset) {
            $relatedContent.addClass('visible');
          } else {
            $relatedContent.removeClass('visible');
          }
        }

        showRelated();

        $(window).scroll(function() {
          showRelated();
        });
      }

    }
  };

})(jQuery, Drupal);
