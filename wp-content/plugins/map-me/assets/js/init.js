function initialize(){

    var locations = location_marker;

    var mapType = map_options[0][4];
    if (!mapType) mapType = "roadmap";    
    
    map = new google.maps.Map(document.getElementById('googleMap'), {
      zoom: Number(map_options[0][0]),
      scrollwheel: map_options[0][1],
      center: new google.maps.LatLng(center_at[0], center_at[1]),
      mapTypeId: mapType,
      disableDefaultUI: map_options[0][2],
      styles: window[map_options[0][3]]

    });

    var infowindow = new google.maps.InfoWindow({maxWidth: 250});

    var marker, i;    
    for (i = 0; i < locations.length; i++) { 
      /*      
      Locations Array:

      locations[i][0] => longitude
      locations[i][1] => latitude      
      locations[i][2] => featured      
      locations[i][3] => icon
      locations[i][4] => featured animation
      locations[i][5] => show info window     
      locations[i][6] => info window data
      */

      var latlng = new google.maps.LatLng(locations[i][0], locations[i][1]);

      
        marker = new google.maps.Marker({
          position: latlng,
          map: map //remove if offset is taking place        
        });       
      

      marker.setIcon(locations[i][3]);      

      if (locations[i][2] == 'yes') {    
          var marker_animation = locations[i][4];

          marker.setZIndex(google.maps.Marker.MAX_ZINDEX + 1);

          if (marker_animation == "BOUNCE"){
            marker.setAnimation(google.maps.Animation.BOUNCE);
          };
          if (marker_animation == "DROP"){
            marker.setAnimation(google.maps.Animation.DROP);
          };        

        };

      if (locations[i][5] == "show") {
        google.maps.event.addListener(marker, 'click', (function(marker, i) {          
        return function() {          
          infowindow.setContent( locations[i][6] );
          infowindow.open(map, marker);           
        }
      })(marker, i));

      }; 

    }

}

google.maps.event.addDomListener(window, 'load', initialize);


