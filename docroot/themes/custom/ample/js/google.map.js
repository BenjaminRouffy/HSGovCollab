(function($) {
  var markers = [];
  var imagesPath = drupalSettings.path.baseUrl + 'themes/custom/ample/images/';

  $.getJSON(drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'points', function(data) {
    for (var i = 0; i < data.features.length; i++) {
      var lng = data.features[i].geometry.coordinates[0];
      var lat = data.features[i].geometry.coordinates[1];
      var markerInfo = data.features[i].properties.description;
      var countryID = data.features[i].properties.id;

      markers.push({'lat': lat, 'lng': lng, 'countryId': 'country_' + countryID});

      $('#map').after(markerInfo);
    }

    $(window).load(initMap();
  });

  function initMap() {
    var lightColoredMap = new google.maps.StyledMapType(lightStyles, {name: "Light"});
    var darkColoredMap = new google.maps.StyledMapType(darkStyles, {name: "Dark"});
    var mapCenter = {lat: 23.8577076, lng: 7.0058832};
    var mapOptions = {
      zoom: 3,
      center: mapCenter,
      scrollwheel: false,
      disableDoubleClickZoom: true,
      disableDefaultUI: true,
      mapTypeControlOptions: {
        mapTypeIds: ['dark', 'light'],
        position: google.maps.ControlPosition.RIGHT_BOTTOM
      }
    };

    var map = new google.maps.Map(document.getElementById('map'), mapOptions);
    map.mapTypes.set('light', lightColoredMap);
    map.setMapTypeId('light');
    map.mapTypes.set('dark', darkColoredMap);
    map.setMapTypeId('dark');

    var i = 0;
    var pins = [];
    var interval = setInterval(function() {
      var data = markers[i];
      var countryId = data.countryId;
      var myLatlng = new google.maps.LatLng(data.lat, data.lng);
      var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        icon: {
          url: imagesPath + 'pin.png',
          scaledSize: new google.maps.Size(15, 23)
        },
        optimized: false,
        animation: google.maps.Animation.DROP,
        id: countryId
      });

      pins.push(marker);

      google.maps.event.addListener(marker, 'mouseover', function() {
        marker.setIcon(imagesPath + 'pin-active.png');
      });

      google.maps.event.addListener(marker, 'mouseout', function() {
        marker.setIcon(imagesPath + 'pin.png');
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

    $('.color-switcher').on('click', function() {
      if (map.getMapTypeId() === 'dark') {
        map.mapTypes.set('light', lightColoredMap);
        map.setMapTypeId('light');
      }
      else {
        map.mapTypes.set('dark', darkColoredMap);
        map.setMapTypeId('dark');
      }
    });
  }
})(jQuery);
