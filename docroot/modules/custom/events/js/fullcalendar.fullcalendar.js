(function ($, Drupal) {
  Drupal.behaviors.eventsCalendar = {
    attach: function (context, settings) {
      var options = {
        header: {
          left: 'month year',
          center: 'prev title next',
          right: ''
        },
        defaultView: 'month',
        yearColumns: 4,
        firstDay: 1,
        eventLimit: true,
        weekNumbers: true,
        columnFormat: 'dddd',
        events: function (start, end, timezone, callback) {
          var events = [];
          $('.view-my-calendar .views-row').each(function () {
            var times = $(this).find('.event-date time');
            events.push({
              title: $(this).find('.event-title').text().trim(),
              start: $(times).eq(0).attr('datetime').trim(),
              end: $(times).eq(1).attr('datetime').trim(),
            });
            $(this).hide();
          });
          this.options.useajax = true;
          callback(events);
        }
      };
      $('#calendar').fullCalendar(options);
    }
  }
})(jQuery, Drupal);
