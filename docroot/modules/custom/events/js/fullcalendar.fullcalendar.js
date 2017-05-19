(function ($, Drupal) {

  Drupal.fullcalendar = function ($calendar, $view) {
    var _this = this;
    this.$view = $view;

    if (window.innerWidth < 768) {
      $('.fc-day-header').each(function(i, el) {
        $(this).text(weekShortName[i]);
      });
    }

    var regex = /\d{4}-\d{2}-\d{2}/g;
    var default_view = _this.getInput('format').val();
    this._calendar = $calendar.fullCalendar({
      header: {
        left: 'month year',
        center: 'prev title next',
        right: ''
      },
      theme: false,
      defaultView: 'year',
      yearColumns: 1,
      firstDay: 1,
      eventLimit: false,
      weekNumbers: true,
      columnFormat: 'dddd',
      events: function (start, end, timezone, callback) {
        var events = [];
        $('.views-row', _this.$view).each(function () {
          var color = 'rgb(' +
            (Math.floor(Math.random() * 256)) + ',' +
            (Math.floor(Math.random() * 256)) + ',' +
            (Math.floor(Math.random() * 256)) + ')';

          var times = $(this).find('.event-date time');
          var eventId = $(this).find('.event-id').text().trim();
          var langcode = drupalSettings.path.currentLanguage;
          var startDate = $(times).eq(0).attr('datetime').match(regex)[0];
          var endDate = $(times).eq(1).attr('datetime').match(regex)[0];
          var endDateIncr = new Date(endDate);

          endDateIncr.setDate(endDateIncr.getDate() + 1);

          endDateIncr = formatDate(endDateIncr);

          if (startDate !== endDate) {
            endDate = endDateIncr;
          }

          function formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
          }

          events.push({
            title: $(this).find('.event-title').text().trim(),
            start: startDate,
            end: endDate,
            url: drupalSettings.path.baseUrl + langcode + '/events/get-event/' + eventId,
            color: $(this).find('.event-color').text().trim()
          });
          $(this).hide();
        });
        callback(events);
      },
      eventAfterRender: function(event, element, view) {
        var $calendar = $('#calendar').find('.fc-day-number');
        var startIndex = 0;
        var endIndex = 0;

        $calendar.each(function(index, el) {
          var $this = $(this);
          var startDate = event.start._i;
          var endDate = event.end._i;

          if (startDate === $this.data('date')) {
            startIndex = index;
          }
          if (endDate === $this.data('date')) {
            endIndex = index;
          }
        });

        $calendar.slice(startIndex, endIndex).addClass('has-event');
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

    var $month = _this.getInput('month').val();
    var $year = _this.getInput('year').val();

    $($calendar).fullCalendar('gotoDate', new Date($year + "-" + $month));
    _this.onDisplayChange('.fc-month-button');
    _this.onDisplayChange('.fc-year-button');
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

      $('.has-event', context).on('click', function() {
        $(this).parents('.fc-year-monthly-td').find('.fc-year-monthly-name a').click();
      });
    }
  };
})(jQuery, Drupal);
