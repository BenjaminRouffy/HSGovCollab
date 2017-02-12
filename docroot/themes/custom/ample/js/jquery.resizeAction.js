(function($) {
  'use strict';

  $.resizeAction = function(conditionCallback, callback) {
    var ready = true;

    $(window).on('resize', function() {
      if (conditionCallback.call(this)) {
        if (ready) {
          callback.call(this, ready);
          ready = !ready;
        }
      }
      else {
        if (!ready) {
          callback.call(this, ready);
          ready = !ready;
        }
      }
    });
  };
})(jQuery);
