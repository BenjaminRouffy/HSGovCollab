(function($, Drupal) {
  'use strict';

  Drupal.behaviors.accordionExposedFilter = {
    attach: function(context, settings) {
      var settings = jQuery.extend(true, settings, {
        'defaultFormSelector': 'form.views-exposed-form',
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
            // Gets all checked inputs and ...
            return $('input:checked + label', parent).map(function () {
                // ... and gets its labels.
                return $(this).html();
              })
              // Return all labels or default value of tab.
              .get().join() || $(settings.defaultFieldsetLegend, parent).html()
          });

      };
      var addLabel = function(form, parent) {
        var legend = $(settings.defaultFieldsetLegend, parent)
          .hide()
          .html();
        $(parent).hide()
        var template = '<div class="wrapper ' + settings.defaultAccordionTab.substr(1) + '">' +
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
          .filter('[' + settings.defaultAttributeSelector + '="' + attr + '"]').slideToggle(300).siblings("fieldset").slideUp("slow");
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

})(jQuery, Drupal);
