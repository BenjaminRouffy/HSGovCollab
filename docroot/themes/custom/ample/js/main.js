(function($, Drupal) {
  'use strict';

  // Detect IE browser.
  var ua = window.navigator.userAgent;
  var msie = ua.indexOf("MSIE ");

  if (msie > 0) {
    $('body').addClass('ie ie' + parseInt(ua.substring(msie + 5, ua.indexOf(".", msie))));
  }
  else if (!!navigator.userAgent.match(/Trident.*rv\:11\./)) {
    $('body').addClass('ie ie11');
  }

  // Detect mobile device.
  function isTouchDevice() {
    var $body = $('body');

    if ('ontouchstart' in document) {
      $body.addClass('mobile');

      if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        $body.addClass('ios');
      }

      return true;
    }
  }

  isTouchDevice();

  Drupal.behaviors.accordionExposedFilter = {
    attach: function(context, settings) {

      var $forms = $('[data-accordion]', context);

      $.each($forms, function (i, v) {
        var $form = v;
        // @TODO Refactoring needed.
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

          $(parent).hide();

          var template = '<div class="wrapper accordion-button ' + settings.defaultAccordionTab.substr(1) + '">' +
                            '<div class="text">' +
                              '<span class="' + settings.defaultAccordionTabText.substr(1) + '"></span>' +
                              '<span class="button"></span>' +
                            '</div>' +
                          '</div>';

          var parentAttribute = $(parent).attr(settings.defaultAttributeSelector);
          var new_div = $(template)
            .addClass(settings.defaultAccordionTab.substr(1))
            .addClass(settings.defaultClosed)
            .attr(settings.defaultAttributeSelector, parentAttribute);

          $(new_div).find(settings.defaultAccordionTabText).html(legend);
          $('.wrapper-filters', form).prepend(new_div);
          applyCheckboxesLabel(parent);
        };

        var _this = this;
        $('fieldset', $form)
          .filter('[' + settings.defaultAttributeSelector + ']').each(function () {
            addLabel($form, this);
          });

        $(".click-item", $form).click(function() {
          var attr = $(this).attr(settings.defaultAttributeSelector);
          $('fieldset', $form).filter('[' + settings.defaultAttributeSelector + '="' + attr + '"]').slideToggle(100).siblings("fieldset").slideUp(100);
          $(this).toggleClass(settings.defaultClosed).siblings(settings.defaultAccordionTab).addClass(settings.defaultClosed);
        });

        $('fieldset[' + settings.defaultAttributeSelector + '] input', $form).change(function () {
          var parent = $(this).parents('fieldset');
          applyCheckboxesLabel(parent);
        });
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

  function showRelated() {
    var $window = $(window);
    var $document = $(document);
    var scrollTop = $window.scrollTop();
    var halfDocOffset = $document.height() / 2;
    var halfWinOffset = $window.height() / 2;
    var $relatedContent = $('.related-wrapper');

    if ((scrollTop + halfWinOffset) > halfDocOffset) {
      $relatedContent.addClass('visible');
    } else {
      $relatedContent.removeClass('visible');
    }
  }

  Drupal.behaviors.relatedContent = {
    attach: function(context, settings) {
      var $relatedContent = $('.related-wrapper', context);

      if ($relatedContent.length) {
        showRelated();

        $(window).scroll(function() {
          showRelated();
        });
      }
    }
  };

  Drupal.behaviors.anchorLink = {
    attach: function(context, settings) {
      var $anchorLink = $('[data-anchor-id]', context);
      var anchorSettings = {
        'region': []
      };
      $anchorLink.each(function(index, element) {
        if (this.hasAttribute('data-region')) {
          anchorSettings['region'].push($(this).attr('data-region'));
        }
      });
      anchorSettings = $.extend(true, {
        'region': ['.bottom-head']
      }, anchorSettings);
      var $bottomHead = $(anchorSettings.region.join(','), context);

      var $body = $('body');

      if (!$body.is('.group.logged')) {
        if ($anchorLink.length && $anchorLink.length > 1) {
          $bottomHead.append('<div class="anchor-links"><ul></ul></div>');

          var headerHeight = $('.header-fixed').height() + 10;

          if ($body.is('.logged')) {
            headerHeight += 90;
          }

          $anchorLink.each(function(index, element) {

            var anchorLabel = false;
            if (this.hasAttribute('data-anchor-label')) {
              anchorLabel = $(this).attr('data-anchor-label');
            }

            var title = anchorLabel || $(element).find('.anchor-title').first().text();
            var id = title.trim();
            var lastId;

            // Remove  special characters from string.
            id = id.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '_');
            id = id.toLowerCase();
            element.id = id;

            $bottomHead.find('.anchor-links ul').append('<li><a href="#' + id + '">' + title + '</a></li>');

            $(window).on('scroll', function() {
              var menuItems = $('.anchor-links').find("a");

              var scrollItems = menuItems.map(function(){
                var item = $($(this).attr("href"));

                if (item.length) {
                  return item;
                }
              });

              var fromTop = $(this).scrollTop() + headerHeight;
              var cur = scrollItems.map(function(){
                if ($(this).offset().top < fromTop)
                  return this;
              });

              cur = cur[cur.length-1];

              var id = cur && cur.length ? cur[0].id : "";

              if (lastId !== id) {
                lastId = id;

                menuItems
                  .parent().removeClass("active")
                  .end().filter("[href='#" + id + "']").parent().addClass("active");
              }
            });
          });
        }

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
      var bannerOverlay = $('.overlay-wrapper', context);

      setTimeout(function() {
        bannerOverlay.animate({'opacity': 0}, 2000, function() {
          if (bannerOverlay.siblings('#map').size() || isTouchDevice()) {
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

        $('#overlay').fadeToggle(300);
        $this.parent($('.slider-main')).toggleClass('fixed');
        $('body').toggleClass('no-scroll');

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
      var mobileMenuBtn = $('.mobile-menu-btn', context);
      var $body = $('body');

      mobileMenuBtn.on('click', function() {
        var $this = $(this);

        $('.dashboard-sidebar').is('.expanded-menu') ? closeMobileMenu(context) : false;

        if (!$this.is('.opened')) {
          mobileMenuBtn.removeClass('opened');
          $body.removeClass('no-scroll');
        }

        $body.toggleClass('no-scroll');
        $this.toggleClass('opened');
      });

      $.resizeAction(function() {
        return window.innerWidth >= 991;
      }, function(state) {
        mobileMenuBtn.removeClass('opened');
      });
    }
  };

  function closeMobileMenu(context) {
    $('body', context).removeClass('no-scroll');
    $('.mobile-menu-btn', context).removeClass('opened');
    $('.dashboard-sidebar', context).removeClass('expanded-menu');
  }

  Drupal.behaviors.sidebarMenu = {
    attach: function(context, settings) {
      var sidebarMenu = $('.dashboard-sidebar', context);

      localStorage.getItem('dashboard') ? sidebarMenu.addClass('expanded-menu') : false;

      $('.expand-menu-btn, .mobile-dashboard-menu-btn').on('click', function() {
        $('.mobile-menu-btn').is('.opened') ? closeMobileMenu(context) : false;

        if (!sidebarMenu.is('.expanded-menu')) {
          localStorage.setItem('dashboard', true);
          sidebarMenu.addClass('expanded-menu');
        }
        else {
          localStorage.removeItem('dashboard');
          sidebarMenu.removeClass('expanded-menu');
        }
      });

      $.resizeAction(function() {
        return window.innerWidth >= 767;
      }, function(state) {
        sidebarMenu.removeClass('expanded-menu');
      });
    }
  };

  function hidePopup(context) {
    $('body', context).on('click', function(e) {
      var $target = $(e.target);

      if ($target.is('.cancel-link')) {
        e.preventDefault();

        $('.popup-wrapper').add($('#overlay')).removeClass('showed');
      }
    });
  }

  Drupal.behaviors.themeSwitcher =  {
    attach: function(context, settings) {
      $('.theme-switcher', context).on('click', function(e) {
        e.preventDefault();

        $('.popup-wrapper').add($('#overlay')).addClass('showed');
      });

      hidePopup(context);
    }
  };

  Drupal.behaviors.stopScroll = {
    attach: function(context, settings) {
      var $bodyHTML = $('body, html', context);

      $bodyHTML.on('scroll mousedown DOMMouseScroll mousewheel keyup', function(e) {
        if (e.which > 0 || e.type === 'mousedown' || e.type === 'mousewheel') {
          $bodyHTML.stop();
        }
      });
    }
  };

  Drupal.behaviors.accordion = {
    attach: function(context, settings) {
      $('.accordion-item', context).on('click', '.accordion-title', function() {
        var $this = $(this);
        var $parent = $this.parent();
        var $content = $('.accordion-content');

        $parent.siblings().removeClass('expanded').find($content).slideUp();

        if (!$parent.is('.expanded')) {
          $parent.addClass('expanded');
          $this.siblings($content).slideToggle();
        }
        else {
          $parent.removeClass('expanded').find($content).slideUp();
        }
      });
    }
  };

  Drupal.behaviors.header = {
    attach: function(context, settings) {
      var $header = $('header', context);

      $.scrollAction(function() {
        return this.scrollY > 0;
      }, function(isTrue) {
        $header.toggleClassCondition(isTrue, 'collapsed');
      });
    }
  };

  Drupal.behaviors.comments = {
    attach: function(context, settings) {
      $('.comment-show', context).on('click', function() {
        var $this = $(this);

        $this.parents('.comment-item').siblings('.indented').first().addClass('expanded');
        $this.remove();
      });
    }
  };

  Drupal.behaviors.mobileFeatures = {
    attach: function(context, settings) {
      $('.section-info', context).on('touchstart', '.paragraph', function() {
        $(this).toggleClass('hover').siblings().removeClass('hover');
      });

      if (isTouchDevice()) {
        $('.wrapper-filters', context).on('click', '.accordion-button', function() {
          var $this = $(this),
              $parent = $this.parent(),
              parentHeight = $parent.height(),
              parentTopOffset = $parent.offset().top,
              headerHeight = $('header').height();

          if (!$this.is('.closed')) {
            $('html, body').animate({
              scrollTop: parentHeight + parentTopOffset - headerHeight
            }, 1000);
          }
        });
      }
    }
  };

  Drupal.behaviors.ieFixes = {
    attach: function(context, settings) {
      // IE 9
      if ($('body').is('.ie9')) {
        // Multiple select
        $('.form-select[multiple]', context).each(function() {
          $(this).parent().addClass('chosen-select-wrapper');
        });

        // Placeholder
        $('input, textarea').placeholder();
      }
    }
  };

})(jQuery, Drupal);
