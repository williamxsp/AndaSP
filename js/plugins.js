
// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
    'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
    'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
    'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
    'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());


/*ANDASPMAP*/
var lat = -23.550483;
var lng = -46.633106;
var templateLatest = " <li data-longitude='{longitude}' data-latitude='{latitude}'> \
                            <img src='{image}' width='150px' height='150px' title='Exibir no mapa'/>\
                            <a href='http://instagram.com/{username}'>@{username}</a>\
                            <p>{description}</p>\
                            <div class='location'>{location}</div>\
                        </li>";
var andaSpMap = {};
andaSpMap.Images = [];
andaSpMap.Markers = [];
andaSpMap.Options = {
    zoom: 15,
    center: new google.maps.LatLng(lat, lng),
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    disableDefaultUI: true,
}


andaSpMap.addMarker = function(options) {
    var latlng = new google.maps.LatLng(options.latitude, options.longitude);
    var ltst = templateLatest.replace(/{longitude}/, options.longitude).replace(/{latitude}/, options.latitude).replace(/{image}/, options.images.low_resolution).replace(/{username}/g, options.username).replace(/{description}/, options.description).replace(/{location}/, options.location).replace("undefined", "");
    $("#mapa-social>ul").append(ltst);
    var marker = new google.maps.Marker(
    {
        position: latlng,
        map: andaSpMap.map,
        title: options.location,
        icon: "http://maps.google.com/mapfiles/marker" + String.fromCharCode(andaSpMap.Markers.length + 65) + ".png",
        image: options.images.high_resolution
    });

    google.maps.event.addListener(marker, 'click', function()
        {
            console.log(this)
           var infoWindow  = new google.maps.InfoWindow({
            content: "<div class='infowindow'><h2>"+ this.title +"</h2><br /><img width='300px' src='" + this.image + "' /><br /><a href='#' onclick='infoWindow.close()'>Fechar</a></div>",
            maxWidth:200
           });
            //var center = new google.maps.LatLng(this.position);
            andaSpMap.map.panTo(this.position);
            infoWindow.open(andaSpMap.map, this);
        });

    andaSpMap.Markers.push(marker);

}
//var infoWindow = new google.maps.InfoWindow();

andaSpMap.map = new google.maps.Map(document.getElementById("container-map"), andaSpMap.Options);
var trafficLayer = new google.maps.TrafficLayer();
trafficLayer.setMap(andaSpMap.map);




/* GEOLOCATION */


if(!!navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position)
        {
            lat = position.coords.latitude;
            lng = position.coords.longitude;
            var center = new google.maps.LatLng(lat, lng);
            andaSpMap.map.panTo(center);
        });
    }

(function()
{
    // $("#container-map").css("width",$(document).innerWidth() );
    // $("#container-map").css("height",$(document).innerHeight());
    // $("#latest").css("height",$(document).innerHeight()-120 );

})();

/*---------------------------- SHADOWBOX ----------------------------*/
Shadowbox.init({
    skipSetup: true
});

/*---------------------------- INSTAGRAM ----------------------------*/


var Instagram = {};
Instagram.Images = [];


(function()
{
    function toScreen (photos) {
        $.each(photos.data, function(index, photo)
        {
            var location = photo.location;
            if(location != null){

                //lat, lng, url, title, username, image, description, location
                var m = {
                    latitude: location.latitude,
                    longitude: location.longitude,
                    images: {
                        low_resolution: photo.images.low_resolution.url,
                        high_resolution: photo.images.standard_resolution.url
                    },
                    description: photo.caption.text,
                    username: photo.user.username,
                    location: photo.location.name

                };
                andaSpMap.addMarker(m);
               
            }

        });

        $("#mapa-social").jcarousel(
        {
            visible:8
        });

        $("#mapa-social li").click(function()
            {
                lat = $(this).attr('data-latitude');
                lng = $(this).attr('data-longitude');
                var center = new google.maps.LatLng(lat, lng);
                andaSpMap.map.panTo(center);

            });

    }
    function search (tag) {
     var url = "https://api.instagram.com/v1/tags/" + tag + "/media/recent?callback=?&amp;client_id=40ab1ffcecc74280832a78a6339b6660";
     $.getJSON(url, toScreen);
 }

 Instagram.search = search;

})();

