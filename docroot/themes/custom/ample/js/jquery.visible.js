(function($) {
  'use strict';

  $.fn.isVisible = function(partial) {
    var $this = $(this),
     $window = $(window),
     viewTop = $window.scrollTop(),
     viewBottom = viewTop + $window.height(),
     elemTopPos = $this.offset().top,
     elemBottomPos = elemTopPos + $this.height(),
     compareTop = partial === true ? elemBottomPos : elemTopPos,
     compareBottom = partial === true ? elemTopPos : elemBottomPos;

    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
  };
})(jQuery);
