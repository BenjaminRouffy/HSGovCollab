(function($, Drupal) {
  'use strict';

  Drupal.behaviors.accordionExposedFilter = {
    attach: function(context, settings) {
      var settings = jQuery.extend(true, settings, {
        // @TODO Add this setting.
        //'defaultFormSelector': 'form#views-exposed-form-news-events-block-1',
        //'defaultFormSelector': 'form.views-exposed-form',
        'defaultAttributeSelector': 'data-drupal-selector',
        'defaultAccordionTab': '.click-item',
        'defaultAccordionTabText': '.text-label',
        'defaultFieldsetLegend': '.fieldset-legend',
        'defaultClosed': 'closed'
      });
      var $form = $(settings.defaultFormSelector, context);

      var applyCheckboxesLabel = function (parent) {
        var parentAttribute = $(parent)
          .attr(settings.defaultAttributeSelector);

        // Get all tags of accordion.
        $(settings.defaultAccordionTab, $form)
        // Fitter that related to parent fieldset element.
          .filter('[' + settings.defaultAttributeSelector + '="' + parentAttribute + '"]')
          // Change a label of tab.
          .find(settings.defaultAccordionTabText).html(function () {
            return $(settings.defaultFieldsetLegend, parent).html();
            // Gets all checked inputs and ...
            /*return $('input:checked + label', parent).map(function () {
                // ... and gets its labels.
                return $(this).html();
              })
              // Return all labels or default value of tab.
              .get().join() || $(settings.defaultFieldsetLegend, parent).html()*/
          });

      };
      var addLabel = function(form, parent) {
        var legend = $(settings.defaultFieldsetLegend, parent)
          .hide()
          .html();
        $(parent).hide()
        var template = '<div class="wrapper accordion-button ' + settings.defaultAccordionTab.substr(1) + '">' +
                          '<div class="text">' +
                            '<span class="' + settings.defaultAccordionTabText.substr(1) + '"></span>' +
                            '<span class="button"></span>' +
                          '</div>' +
                        '</div>';

        var parentAttribute = $(parent)
          .attr(settings.defaultAttributeSelector);
        var new_div = $(template)
          .addClass(settings.defaultAccordionTab.substr(1))
          .addClass(settings.defaultClosed)
          .attr(settings.defaultAttributeSelector, parentAttribute);
        $(new_div).find(settings.defaultAccordionTabText).html(legend);
        $('.wrapper-filters', form).prepend(new_div);
        applyCheckboxesLabel(parent);
      }


      var _this = this;
      $('fieldset', $form)
        .filter('[' + settings.defaultAttributeSelector + ']').each(function () {
          addLabel($form, this);
        });

      $(".click-item", $form).click(function()
      {
        var attr = $(this).attr(settings.defaultAttributeSelector);
        $('fieldset')
          .filter('[' + settings.defaultAttributeSelector + '="' + attr + '"]').slideToggle(100).siblings("fieldset").slideUp(100);
        $(this).toggleClass(settings.defaultClosed).siblings(settings.defaultAccordionTab).addClass(settings.defaultClosed);

      });

      $('fieldset[' + settings.defaultAttributeSelector + '] input', $form).change(function () {
        var parent = $(this).parents('fieldset');
        applyCheckboxesLabel(parent);
      });

    }
  };

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

      if (!$('body').is('.group.logged')) {
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
    }
  };

  Drupal.behaviors.overlay = {
    attach: function(context, settings) {
      var bannerOverlay = $('.overlay-wrapper');

      setTimeout(function() {
        bannerOverlay.animate({'opacity': 0}, 2000, function() {
          if (bannerOverlay.siblings($('#map'))) {
            bannerOverlay.remove();
          }
        });
      }, 3000);
    }
  };

  Drupal.behaviors.carousel = {
    attach: function(context, settings) {
      $('.slider-wrapper').each(function(i, el) {
        var $slider = $(el);

        $slider.owlCarousel({
          loop: true,
          items: 1,
          nav: true,
          smartSpeed: 500
        });
      });
    }
  };

  Drupal.behaviors.thumbCarousel = {
    attach: function(context, settings) {
      var maxThumbs = 7;
      var speed = 500;
      var sliderSettings = {
        items: 1,
        smartSpeed: speed,
        dots: false,
        nav: true,
        navSpeed: speed,
        dotsSpeed: speed
      };

      $('.slider-main', context).each(function(i, el) {
        var $slider = $(el).find('.content-slider-wrapper', context);
        var $thumbs = $(el).find('.thumb-slider-wrapper', context);
        var thumbNumber = $thumbs.find('.slider-item-thumb').length;

        $slider.owlCarousel(sliderSettings);

        $thumbs.owlCarousel({
          items: thumbNumber > maxThumbs ? maxThumbs : thumbNumber,
          smartSpeed: speed,
          dots: false,
          nav: thumbNumber > maxThumbs ? true : false,
          navSpeed: speed,
          dotsSpeed: speed,
          autoWidth: true,
          slideBy: thumbNumber > maxThumbs ? thumbNumber % maxThumbs : 1,
          touchDrag: false,
          mouseDrag: false
        });

        $thumbs.find('.active').first().addClass('current');

        $thumbs.on('click', '.owl-item', function(property) {
          $(this).addClass('current').siblings().removeClass('current');
          $slider.trigger('to.owl.carousel', [$(property.target).parents('.owl-item').index(), speed, true]);
        });

        $slider.on('changed.owl.carousel', function(property) {
          $thumbs.trigger('to.owl.carousel', [property.item.index, speed, true]);
          $thumbs.find('.owl-item').eq(property.item.index).addClass('current')
            .siblings().removeClass('current');
        });
      });

      $('.trigger-full-page', context).on('click', function() {
        var $this = $(this);
        var $slider = $this.siblings('.content-slider-wrapper');
        var $thumbs = $this.siblings('.thumb-slider-wrapper');
        var thumbNumber = $thumbs.find('.slider-item-thumb').length;
        var sliderCurrentIndex = $slider.find('.active').index();
        var thumbCurrentIndex = $thumbs.find('.current').index();

        $('#slider-overlay').fadeToggle(300);
        $this.parent($('.slider-main')).add($('body')).toggleClass('fixed');

        $slider.add($thumbs).trigger('destroy.owl.carousel');

        $slider.owlCarousel(sliderSettings);
        $slider.trigger('to.owl.carousel', [sliderCurrentIndex, 0, true]);

        $thumbs.owlCarousel({
          items: thumbNumber > maxThumbs ? maxThumbs : thumbNumber,
          smartSpeed: speed,
          dots: false,
          nav: thumbNumber > maxThumbs ? true : false,
          navSpeed: speed,
          dotsSpeed: speed,
          autoWidth: true,
          slideBy: thumbNumber > maxThumbs ? thumbNumber % maxThumbs : 1,
          touchDrag: false,
          mouseDrag: false
        });
        $thumbs.trigger('to.owl.carousel', [thumbCurrentIndex, 0, true]);
        $thumbs.find('.owl-item').eq(thumbCurrentIndex).addClass('current');
      });
    }
  };

  Drupal.behaviors.showMoreLink = {
    attach: function(context, settings) {
      var hiddenContent = $('.more-content');

      if (!hiddenContent.children().length) {
        hiddenContent.parent().remove();
      }

      $('.show-more', context).on('click', function() {
        hiddenContent.slideToggle(300);
      });
    }
  };

  Drupal.behaviors.showSharing = {
    attach: function(context, settings) {
      $('.toggle-sharing-btn', context).on('click', function() {
        $(this).next().toggleClass('expanded');
      });
    }
  };

  Drupal.behaviors.mobileMenu = {
    attach: function(context, settings) {
      $('.mobile-menu-btn', context).on('click', function() {
        $(this).toggleClass('opened');
      });

      $.resizeAction(function() {
        return window.innerWidth <= 991;
      }, function(state) {
        $('.mobile-menu-btn', context).removeClass('opened');
      });
    }
  };

})(jQuery, Drupal);
