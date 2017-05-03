(function ($, Drupal) {

  Drupal.fullcalendar = function ($calendar, $view) {
    var _this = this;
    this.$view = $view;

    var default_view = _this.getInput('format').val();
    this._calendar = $calendar.fullCalendar({
      header: {
        left: 'month year',
        center: 'prev title next',
        right: ''
      },
      theme: false,
      defaultView: default_view,
      yearColumns: 4,
      firstDay: 1,
      eventLimit: false,
      weekNumbers: true,
      columnFormat: 'dddd',
      events: function (start, end, timezone, callback) {
        var events = [];
        $('.views-row', _this.$view).each(function () {
          var times = $(this).find('.event-date time');
          events.push({
            title: $(this).find('.event-title').text().trim(),
            start: $(times).eq(0).attr('datetime'),
            end: $(times).eq(1).attr('datetime'),
            description: $(this).find('.event-desc').html(),
          });
          $(this).hide();
        });
        callback(events);
      },
      eventAfterAllRender: function (view) {
        $('.fc-year-monthly-name a', _this.$view).click(function (event) {
          event.preventDefault();
        });
      },
      viewDestroy: function (view, element) {
        var _calendar = this,
          view = _calendar.calendar.getView(),
          date = _calendar.calendar.getDate();
        _this.getInput('format').val(view['type']);
        _this.getInput('month').val(date.month() + 1);
        _this.getInput('year').val(date.year());
      },
      eventClick: function(calEvent, jsEvent, view) {
        $('.event-content', _this.$view).html('').append(calEvent.description);
      }
    });

    var $month = _this.getInput('month').val()
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

    })
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

    }
  }
})(jQuery, Drupal);
