(function($) {
  $.fn.isVisible = function(partial) {
    var $this = $(this);
    var $window = $(window);
    var viewTop = $window.scrollTop();
    var viewBottom = viewTop + $window.height();
    var elemTopPos = $this.offset().top;
    var elemBottomPos = elemTopPos + $this.height();
    var compareTop = partial === true ? elemBottomPos : elemTopPos;
    var compareBottom = partial === true ? elemTopPos : elemBottomPos;

    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
  };
})(jQuery);
