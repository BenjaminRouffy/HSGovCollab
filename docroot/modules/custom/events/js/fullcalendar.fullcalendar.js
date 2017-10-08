(function ($, Drupal) {

  Drupal.fullcalendar = function ($calendar, $view) {
    var _this = this;
    var colorCache = {};
    this.$view = $view;

    if (window.innerWidth < 768) {
      $('.fc-day-header').each(function(i, el) {
        $(this).text(weekShortName[i]);
      });
    }

    var colors = [
      '#2f434b',
      '#627178',
      '#97a0a4',
      '#e73b39',
      '#ed6867',
      '#f4989a',
      '#f9d6d5',
      '#4e7d93',
      '#7a9daf',
      '#a6bec9',
      '#dce5ea',
      '#a8575b',
      '#c48d91',
      '#e7d1d3',
      '#61a9ba',
      '#89bdcb',
      '#afd4dc'
    ];

    // var regex = /\d{4}ss-\d{2}-\d{2}/g;
    var default_view = _this.getInput('format').val();
    this._calendar = $calendar.fullCalendar({
      header: {
        left: 'month year',
        center: 'prev title next',
        right: ''
      },
      lang: drupalSettings.path.currentLanguage,
      theme: false,
      defaultView: default_view,
      yearColumns: 1,
      firstDay: 1,
      eventLimit: false,
      weekNumbers: true,
      columnFormat: 'dddd',
      events: function (start, end, timezone, callback) {
        var events = [];
        $('.views-row', _this.$view).each(function () {

          var times = $(this).find('.event-date time');
          var eventId = $(this).find('.event-id').text().trim();
          var langcode = drupalSettings.path.currentLanguage;
          var startDate = $(times).eq(0).text() || $.fullCalendar.moment().format("YYYY-MM-DD");
          var endDate = $(times).eq(1).text() || startDate;

          $(this).hide();
          if (!startDate || !endDate) {
            return;
          }

          if (typeof(colorCache[eventId]) == 'undefined' || colorCache[eventId] === null) {
            colorCache[eventId] = colors[Math.floor(Math.random() * colors.length)];
          }
          var event = {
            title: $(this).find('.event-title').text().trim(),
            start: $.fullCalendar.moment(startDate + "T06:00:00+00:00"),
            end: $.fullCalendar.moment(endDate + "T18:00:00+00:00"),
            url: drupalSettings.path.baseUrl + langcode + '/events/get-event/' + eventId,
            color: colorCache[eventId],
            hideTime: true
          };
          events.push(event);

        });

        callback(events);
      },
      eventAfterRender: function(event, element, view) {
        var $calendar = $('#calendar').find('.fc-day-number');
        var startIndex = 0;
        var endIndex = 0;

        $calendar.each(function(index, el) {
          var $this = $(this);
          var startDate = event.start.format("YYYY-MM-DD");
          var endDate;

          if (event.end !== null) {
            endDate = event.end.format("YYYY-MM-DD");
          }

          if (startDate === $this.data('date')) {
            startIndex = index;
          }
          if (endDate === $this.data('date')) {
            endIndex = index;
          }
        });

        $calendar.slice(startIndex, endIndex + 1).addClass('has-event');
      },
      eventAfterAllRender: function (view) {
        weekFullName = $('.fc-day-header').map(function() {
          return $(this).text();
        });

        weekShortName = $('.fc-day-header').map(function() {
          return $(this).text().substring(0, 3);
        });

        $('.fc-year-monthly-name a', _this.$view).click(function (event) {
          event.preventDefault();
        });
        $('.fc-event-container a', _this.$view).addClass('use-ajax');
        $('.fc-day-number.fc-today').wrapInner('<span class="today"></span>');

        if (drupalSettings.events && drupalSettings.events.group_node_event_path) {
          var $addEventModal = $('.add-event-popup-form');

          $('.fc-day-number')
            .prepend('<span class="add-event">' + Drupal.t('Add Event') + '</span>');
          $(_this.$view).addClass('add-event-calendar');

          $addEventModal.iziModal({
            title: Drupal.t('Add Event'),
            headerColor: '#283b44',
            padding: 15,
            restoreDefaultContent: true,
            onOpening: function (modal) {
              modal.startLoading();

              Drupal.detachBehaviors($('.add-event-popup-form').get()[0]);
              Drupal.attachBehaviors($('.add-event-popup-form').get()[0]);

              // Append date to input.


              modal.stopLoading();
            }
          });

          $('.fc-day-number', _this.$view).click(function () {
            // The date that has been clicked.
            var date = $(this).attr('data-date');

            // Used to append the correct date to the add event form.
            $addEventModal.attr('data-date', date);
            // Open the event modal.
            $addEventModal.iziModal('open');
          });

          $('.fc-day-number, .fc-bg .fc-day', _this.$view).click(function () {
            // Do not bother in case of year view dots clicked.
            if ($(this).hasClass('has-event') && $(this).closest('.fc-year-view').length) {
              return false;
            }

            // /group/{group}/content/create/{plugin_id}
            // @TODO Remove commented code bellow after event popup works.
            // var date = $(this).attr('data-date'),
            //     path = drupalSettings.events.group_node_event_path,
            //     uri = path + '?date=' + encodeURIComponent(date) + '&destination=' + encodeURIComponent(window.location.pathname);
            // window.location.href = uri;
          });
        }
        Drupal.attachBehaviors($calendar.get()[0]);
      },
      windowResize: function(view) {
        if (view.type === "month" && window.innerWidth > 767) {
          $('.fc-day-header').each(function(i, el) {
            $(this).text(weekFullName[i]);
          });
        }
        else {
          $('.fc-day-header').each(function(i, el) {
            $(this).text(weekShortName[i]);
          });
        }
      },
      viewDestroy: function (view, element) {
        var _calendar = this,
          view = _calendar.calendar.getView(),
          date = _calendar.calendar.getDate();
        _this.getInput('format').val(view['type']);
        _this.getInput('month').val(date.month() + 1);
        _this.getInput('year').val(date.year());
      }
    });

    // var $month = _this.getInput('month').val();
    // var $year = _this.getInput('year').val();
    //
    // $($calendar).fullCalendar('gotoDate', new Date($year + "-" + $month));
    // _this.onDisplayChange('.fc-month-button');
    // _this.onDisplayChange('.fc-year-button');
    return this;
  };
  Drupal.fullcalendar.prototype.getInput = function (selector) {
    return $('input[data-drupal-selector="edit-' + selector + '"]', this.$view);
  };

  Drupal.fullcalendar.prototype.onDisplayChange = function (selector) {
    var _this = this;
    $(selector, _this._calendar).click(function (event) {
      event.preventDefault();
      var view = $(_this._calendar).fullCalendar('getView'),
        date = $(_this._calendar).fullCalendar('getDate');
      _this.getInput('format').val(view['type']);
      _this.getInput('month').val(date.month() + 1);
      _this.getInput('year').val(date.year());

    });
  };

  Drupal.behaviors.eventsCalendar = {
    attach: function (context, settings) {

      if (drupalSettings && drupalSettings.views && drupalSettings.views.ajaxViews) {
        var ajaxViews = drupalSettings.views.ajaxViews;
        for (var i in ajaxViews) {
          if (ajaxViews.hasOwnProperty(i)) {
            var $view = $(".js-view-dom-id-" + ajaxViews[i]['view_dom_id'], context);
            var $calendar = $('#calendar', $view);
            new Drupal.fullcalendar($calendar, $view);
          }
        }
      }

      $('.fc-year-view .has-event', context).on('click', function() {
        $(this).parents('.fc-year-monthly-td').find('.fc-year-monthly-name a').click();
      });
    }
  };
})(jQuery, Drupal);
