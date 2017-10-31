(function($) {
  'use strict';

  if (!window.plyr) {
    return;
  }

  var players = plyr.setup({
    controls: [],
    volume: 0
  });

  if (players) {
    var offset = $('.w-background-video').height();

    players.forEach(function(player) {
      player.on('ready', function() {
        // Autoplay video.
        var playing = true;

        $(window).on('scroll', (function scroll() {
          if (window.scrollY > offset) {
            if (!playing) {
              playing = !playing;
              player.pause();
            }
          }
          else {
            // Do an autoplay only if available.
            if (playing) {
              playing = !playing;
              player.play();
            }
          }

          return scroll;
        })()).on('blur', function() {
          // Stop playing when browser tab is inactive.
          if (playing) {
            player.pause();
          }
        }).on('focus', function() {
          // Resume playing when user got back to site.
          if (!playing) {
            player.play();
          }
        });
      });

      // Loop video.
      player.on('ended', function() {
        player.play();
      });
    });
  }
})(jQuery);
