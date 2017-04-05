(function($) {
  'use strict';

  $.fn.toggleClassCondition = function(condition, cssClass) {
    return this.each(function() {
      $(this)[(Boolean(condition) ? 'add' : 'remove') + 'Class'](cssClass);
    });
  };
})(jQuery);
