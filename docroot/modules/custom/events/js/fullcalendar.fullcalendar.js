(function ($, Drupal, drupalSettings) {
  function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  Drupal.behaviors.eventsCalendar = {
    attach: function (context, settings) {
      var options = jQuery.extend(true, {
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
        columnFormat: 'dddd'
      }, {
        eventClick: function (event, jsEvent, view) {
          //set the values and open the modal
          // @TODO
          // $(".event-content").html(event.description);
          // $(".event-content").dialog({modal: true, title: event.title});
        },
        events: function (start, end, timezone, callback) {
          // @TODO Add ajax request here to reduce the site of data.
          // ex. /user/my-calendar/json
          var events = [];
          // @TODO Change element ID to class, it is not allow use few calenar on same page.
          $('#' + settings.calendar.wrapper + ' .views-row').each(function () {
            events.push({
              // @TODO Delete completely.
              title: $(this).find('.views-field-title .field-content').text().trim(),
              start: $(this).find('.views-field-field-date .field-content time:eq(0)').attr('datetime').trim(),
              end: $(this).find('.views-field-field-date .field-content time:eq(1)').attr('datetime').trim(),
              color: 'rgb(' + getRandomInt(0, 255) + ',' + getRandomInt(0, 255) + ',' + getRandomInt(0, 255) + ')',
              description: $(this).find('.views-field-rendered-entity .field-content').html()
            });
            $(this).hide();
          });
          callback(events);
        }
      });

      // @TODO Add a ability to attach multiple calendars.
      $('#' + settings.calendar.wrapper).fullCalendar(options);

    }
  }

})(jQuery, Drupal, drupalSettings);
