(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.eventsCalendar = {
    attach: function (context, settings) {
      console.log('attached');
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
        // @TODO Create this object on backend side.
        'events': settings.calendar.url
      });

      // @TODO Add a ability to attach multiple calendars.
      $('#' + settings.calendar.wrapper).fullCalendar(options);

    }
  }

})(jQuery, Drupal, drupalSettings);
