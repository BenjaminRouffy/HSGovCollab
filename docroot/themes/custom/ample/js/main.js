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

  Drupal.behaviors.anchorLink = {
    attach: function(context, settings) {
      var $bottomHead = $('.bottom-head', context);
      var $anchorLink = $('[data-anchor-id]', context);

      if ($anchorLink.length) {
        $bottomHead.append('<div class="anchor-links"></div>');

        $anchorLink.each(function(index, element) {
          var title = $(element).find('.anchor-title').first().text();
          var id = title.trim();

          // Remove  special characters from string.
          id = id.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '_');
          id = id.toLowerCase();

          $bottomHead.find('.anchor-links').append('<a href="#' + id + '">' + title + '</a>');
        });
      }
    }
  };

  Drupal.behaviors.overlay = {
    attach: function(context, settings) {
      $('.overlay-wpapper').delay(3000).queue(function() {
        $(this).css('opacity', 0).dequeue();
      });
    }
  };

})(jQuery, Drupal);
