(function ($, Drupal, drupalSettings) {
  var weekFullName = [], weekShortName = [];

  Drupal.fullcalendar = function ($calendar, $view) {
    var _this = this;
    var colorCache = {};
    this.$view = $view;

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
      // Uncomment to have fixed settings among all locales.
      // firstDay: 1,
      // firstWeek: 4,
      timezone: 'local',
      eventLimit: false,
      weekNumbers: true,
      columnFormat: 'dddd',
      events: function (start, end, timezone, callback) {
        var calendar = this;
        var events = [];
        // Collect events.
        $('.views-row', _this.$view).hide().each(function () {

          var times = $(this).find('.event-date time');
          var eventId = $(this).find('.event-id').text().trim();
          // Attribute datetime holds date in UTC.
          var startDate = calendar.moment($(times).eq(0).attr('datetime'));
          var endDate = calendar.moment($(times).eq(1).attr('datetime')) || startDate;

          // Check event is in the given range.
          if (!startDate.isWithin(start, end) && !endDate.isWithin(start, end)) {
            return;
          }

          if (!colorCache.hasOwnProperty(eventId)) {
            colorCache[eventId] = colors[Math.floor(Math.random() * colors.length)];
          }
          var event = {
            id: eventId,
            title: $(this).find('.event-title').text().trim(),
            start: startDate,
            end: endDate,
            url: drupalSettings.path.baseUrl + drupalSettings.path.currentLanguage + '/events/get-event/' + eventId,
            className: 'use-ajax',
            color: colorCache[eventId],
            hideTime: true
          };
          events.push(event);

        });

        callback(events);
      },
      eventAfterRender: function(event, element, view) {
        if (view.type === 'year') {
          var $fcDayNumbers = $(element).closest('.fc-row').find('.fc-day, .fc-day-number'),
            days = -event.start.diff(event.end, 'd') || 0, date;

          while (days >= 0) {
            date = event.start.clone().add(days--, 'd').format('YYYY-MM-DD');
            $fcDayNumbers.filter('[data-date*="' + date + '"]').addClass('has-event');
          }
        }
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
          // Attach add event text to the days if we are in month view.
          $('.fc-month-view .fc-day-number')
              .prepend('<span class="add-event">' + Drupal.t('Add Event') + '</span>');

          $('.fc-day-number, .fc-bg .fc-day', _this.$view).click(function () {
            // Do not bother in case of year view dots clicked.
            if ($(this).hasClass('has-event') && $(this).closest('.fc-year-view').length) {
              return false;
            }

            var date = $(this).attr('data-date'),
                path = drupalSettings.events.group_node_event_path;
            window.location.href = path + '?date=' + encodeURIComponent(date);
          });
        }
        if (view.type === 'year') {
          $('.fc-year-monthly-name a', _this.$view).click(function (event) {
            event.preventDefault();
          });
        }
        $('.fc-day-number.fc-today', _this.$view).wrapInner('<span class="today"></span>');
        Drupal.attachBehaviors($calendar.get(0));
      },
      dayClick: function(date, jsEvent, view) {
        if (view.type === 'year' && $(this).hasClass('has-event')) {
          // Do not bother in case of year view dots clicked.
          $(this).closest('.fc-year-monthly-td').find('.fc-year-monthly-name a').click();
        }
        else {
          if (drupalSettings.events && drupalSettings.events.group_node_event_path) {
            // /group/{group}/content/create/{plugin_id}
            window.location.href = drupalSettings.events.group_node_event_path
              + '?date=' + encodeURIComponent(date.toISOString())
              + '&destination=' + encodeURIComponent(window.location.pathname);
          }
        }
      },
      windowResize: function(view) {
        if (!weekFullName.length || !weekShortName.length) {
          var date = view.calendar.getNow();
          for (var n=0; n<7; n++) {
            weekFullName[date.day()] = date.format('dddd');
            weekShortName[date.day()] = date.format('ddd');
            date = date.add(1, 'd');
          }
        }

        if (view.type === 'month') {
          if (window.innerWidth > 767) {
            $('.fc-day-header').each(function(i, el) {
              $(this).text(weekFullName[i]);
            });
          }
          else {
            $('.fc-day-header').each(function(i, el) {
              $(this).text(weekShortName[i]);
            });
          }
        }
      },
      viewDestroy: function (view, element) {
        var date = view.calendar.getDate();
        _this.getInput('format').val(view.type);
        _this.getInput('month').val(date.month() + 1);
        _this.getInput('year').val(date.year());
      }
    });

    window.setTimeout(function () {
      // Trigger windowResize now to shorten week names.
      if (window.innerWidth < 768) {
        var view = $calendar.fullCalendar('getView');
        // Sometimes it gets triggered too early, so check we have real view.
        if (view.hasOwnProperty('calendar')) {
          view.calendar.options.windowResize(view);
        }
      }
    });

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
          date = view.calendar.getDate();
      _this.getInput('format').val(view.type);
      _this.getInput('month').val(date.month() + 1);
      _this.getInput('year').val(date.year());

    });
  };

  Drupal.behaviors.eventsCalendar = {
    attach: function (context, settings) {

      if (settings.views && settings.views.ajaxViews) {
        var ajaxViews = settings.views.ajaxViews;
        for (var i in ajaxViews) {
          if (ajaxViews.hasOwnProperty(i)) {
            var $view = $('.js-view-dom-id-' + ajaxViews[i]['view_dom_id'], context);
            var $calendar = $('#calendar', $view);
            new Drupal.fullcalendar($calendar, $view);
          }
        }
      }

    }
  };
})(jQuery, Drupal, drupalSettings);
