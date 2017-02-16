(function($) {
  var markers = [];

  Drupal.behaviors.markers = {
    attach: function(context, settings) {
      $('.country-location').each(function(i, el) {
        var $el = $(el);
        var lat = $el.find('meta[itemprop="latitude"]').attr('content');
        var lng = $el.find('meta[itemprop="longitude"]').attr('content');
        //console.log($(this).find('meta[itemprop="latitude"]').attr('content'));
        markers.push({'lat': lat, 'lng': lng});
      });
      console.log(markers);
    }
  };

  function initMap() {
    var lightColoredMap = new google.maps.StyledMapType(lightStyles, {name: "Light colored"});
    var darkColoredMap = new google.maps.StyledMapType(darkStyles, {name: "Dark colored"});
    var mapCenter = {lat: 37.4430681, lng: 31.3592876};
    var mapOptions = {
      zoom: 3,
      center: mapCenter,
      scrollwheel: false,
      zoomControl: false,
      streetViewControl: false,
      mapTypeControlOptions: {
        mapTypeIds: ['dark_style', 'light_style'],
        position: google.maps.ControlPosition.RIGHT_BOTTOM
      }
    };

    var map = new google.maps.Map(document.getElementById('map'), mapOptions);
    map.mapTypes.set('light_style', lightColoredMap);
    map.setMapTypeId('light_style');
    map.mapTypes.set('dark_style', darkColoredMap);
    map.setMapTypeId('dark_style');

    var infoWindow = new google.maps.InfoWindow();
    var i = 0;
    var interval = setInterval(function() {
      var data = markers[i];
      var myLatlng = new google.maps.LatLng(data.lat, data.lng);
      var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        icon: '../images/pin.svg',
        title: data.title,
        animation: google.maps.Animation.DROP
      });

      google.maps.event.addListener(marker, 'mouseover', function() {
        this.style.color = "red";
      });

      (function(marker, data) {
        google.maps.event.addListener(marker, "click", function(e) {
          infoWindow.setContent(data.description);
          infoWindow.open(map, marker);
        });
      })(marker, data);

      i++;

      if (i == markers.length) {
        clearInterval(interval);
      }
    }, 200);
  }
})(jQuery);
