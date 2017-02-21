(function($) {
  var markers = [];
  var imagesPath = drupalSettings.path.baseUrl + 'themes/custom/ample/images/';

  $.getJSON(drupalSettings.path.baseUrl + 'points', function(data) {
    for (var i = 0; i < data.features.length; i++) {
      var lng = data.features[i].geometry.coordinates[0];
      var lat = data.features[i].geometry.coordinates[1];
      var markerInfo = data.features[i].properties.description;
      var countryName = data.features[i].properties.name.toLowerCase();

      markers.push({'lat': lat, 'lng': lng, 'countryId': countryName});

      $('#map').after(markerInfo);
    }

    initMap();
  });

  function initMap() {
    var lightColoredMap = new google.maps.StyledMapType(lightStyles, {name: "Light colored"});
    var darkColoredMap = new google.maps.StyledMapType(darkStyles, {name: "Dark colored"});
    var mapCenter = {lat: 23.8577076, lng: 7.0058832};
    var mapOptions = {
      zoom: 3,
      center: mapCenter,
      scrollwheel: false,
      disableDoubleClickZoom: true,
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

    var i = 0;
    var pins = [];
    var interval = setInterval(function() {
      var data = markers[i];
      var countryId = data.countryId;
      var myLatlng = new google.maps.LatLng(data.lat, data.lng);
      var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        icon: imagesPath + 'pin.svg',
        animation: google.maps.Animation.DROP,
        id: countryId
      });

      pins.push(marker);

      google.maps.event.addListener(marker, 'mouseover', function() {
        marker.setIcon(imagesPath + 'pin-active.svg');
      });

      google.maps.event.addListener(marker, 'mouseout', function() {
        marker.setIcon(imagesPath + 'pin.svg');
      });

      google.maps.event.addListener(marker, "click", function() {
        var elementId = $('#' + this.id);
        var markerInfo = $('.marker-info');

        for (var i in pins) {
          pins[i].setAnimation(null);
        }

        if (!elementId.is('.active')) {
          markerInfo.removeClass('active');
          elementId.addClass('active');
        }
        else {
          elementId.removeClass('active');
        }

        if (marker.getAnimation() !== null) {
          marker.setAnimation(null);
        } else {
          marker.setAnimation(google.maps.Animation.BOUNCE);
        }
      });

      $('.close-btn').on('click', function() {
        $(this).parent('.marker-info').removeClass('active');
        marker.setAnimation(null);
      });

      i++;

      if (i == markers.length) {
        clearInterval(interval);
      }
    }, 200);
  }
})(jQuery);
