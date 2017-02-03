(function($, Drupal) {
  'use strict';

  Drupal.behaviors.fadeOut = {
    attach: function(context, settings) {
      var $blockquote = $('blockquote');
      var $timelineItem = $('.timeline-content').find('.paragraph');

      $blockquote.add($timelineItem).each(function(i, el) {
        var $el = $(el);

        if (!$el.isVisible(true)) {
          $el.css({
            'opacity': 0,
            'transform': 'translateY(15px)'
          });
        }
      });

      $(window).scroll(function() {
        var $window = $(window);
        var thirdhWinHeight = $window.height() / 3;
        var viewTop = $window.scrollTop();
        var viewBottom = viewTop + $window.height();

        $timelineItem.each(function(i, el) {
          var $el = $(el);
          var elemTopPos = $el.offset().top;

          if ($el.isVisible(true) && elemTopPos < (viewBottom - thirdhWinHeight)) {
            $el.addClass("faded");
          }
        });

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
      var $bottomHead = $('.bottom-head .wrapper', context);
      var $anchorLink = $('[data-anchor-id]', context);

      if ($anchorLink.length && $anchorLink.length > 1) {
        $bottomHead.append('<div class="anchor-links"></div>');

        $anchorLink.each(function(index, element) {
          var title = $(element).find('.anchor-title').first().text();
          var id = title.trim();

          // Remove  special characters from string.
          id = id.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '_');
          id = id.toLowerCase();
          element.id = id;

          $bottomHead.find('.anchor-links').append('<a href="#' + id + '">' + title + '</a>');
        });

        $('.anchor-links').on('click', 'a', function(event) {
          var headerHeight = $('.header-fixed').height();
          var adminMenuHeight = parseInt($('body').css('padding-top'));
          var totalHeaderHegiht = headerHeight + adminMenuHeight;

          event.preventDefault();

          $('html, body').animate({
            scrollTop: $('[id="' + $.attr(this, 'href').substr(1) + '"]').offset().top - totalHeaderHegiht
          }, 1000);
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

  Drupal.behaviors.carousel = {
    attach: function(context, settings) {
      $('.slider-wrapper').each(function(i, el) {
        var $slider = $(el);

        $slider.owlCarousel({
          loop: true,
          items: 1,
          smartSpeed: 500
        });
      });
    }
  };

  Drupal.behaviors.thumbCarousel = {
    attach: function(context, settings) {
      $('.slider-main').each(function(i, el) {
        var $slider = $(el).find('.content-slider-wrapper', context);
        var $thumbs = $(el).find('.thumb-slider-wrapper', context);

        $thumbs.owlCarousel({
          loop: true,
          items: 3,
          dots: false,
          nav: true,
          smartSpeed: 500
        });

        $slider.owlCarousel({
          loop: true,
          items: 1,
          smartSpeed: 500,
          dots: false,
          nav: true
        });

        $thumbs.on('changed.owl.carousel',function(property) {
          $slider.trigger('to.owl.carousel', [property.item.index + 1, 500, false]);
        });
      });
    }
  };

})(jQuery, Drupal);
