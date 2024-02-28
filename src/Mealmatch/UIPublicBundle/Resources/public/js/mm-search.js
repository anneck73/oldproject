/*
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 * These functions are used in search_do.html.twig.
 */
var pushData = function (item) {
    $('#meal-search-results').append(item.html);
    $('#meal-search-results-xs').append(item.html_xs);
};

var push_marker = function (item) {
    markers_data.push({
        id: item.id,
        location: item.locationAddress,
        lat : item.lat,
        lng : item.lng,
        title : item.title,
        animation: google.maps.Animation.DROP,
        html: item.html,
        html_xs: item.html_xs,
        icon : {
            size : new google.maps.Size(64, 64),
            url : mmIcon
        },
        infoWindow: {
            content: item.locationMarker,
            maxWidth: 450
        }
    });
    $('#meal-search-results').append($(item.locationMarker).hide());
};

var loadResults = function loadResults (data) {

    var items = [];
    if (data.length > 0) {
        items = data;
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (item.lat !== undefined && item.lng !== undefined) {
                pushData(item);
            }
        }
    } else {
        console.error('Failed to load XHR!');
    }
    //map.addMarkers(markers_data);
};

var loadMarker =function (allMarkers) {
    for (var i = 0; i < allMarkers.length; i++) {
        var item = allMarkers[i];
        if (item.lat !== undefined && item.lng !== undefined) {
            push_marker(item);
        }
    }
    map.addMarkers(markers_data);
};

var addMarkerWithTimeout = function addMarkerWithTimeout(item, timeout) {
    window.setTimeout(function() {
        push_marker(item);
    }, timeout);
};

var clearMarkers = function clearMarkers() {
    for (var i = 0; i < markers_data.length; i++) {
        if(map.markers[i]){
            map.markers[i].setMap(null);
        }
    }
};

var printResults = function printResults(data) {
    $('#meal-results').text(JSON.stringify(data));
    prettyPrint();
};
var printResultsXS = function printResults(data) {
    $('#meal-results-xs').text(JSON.stringify(data));
    prettyPrint();
};
var zoomed = false;
$(document).on('click', '.pan-to-marker', function(e) {
    e.preventDefault();
    var position, lat, lng, marker, searchID;
    searchID = $(this).data('meal-id');
    marker = $.grep(map.markers, function(m) {
        return (m.id === searchID)
    });
    position = marker[0].getPosition();
    lat = position.lat();
    lng = position.lng();
    // map.setCenter(lat, lng);
    if(zoomed) {
        zoomed = false;
        map.setZoom(7);
    } else {
        zoomed = true;
        map.setZoom(12);
    }
    map.panTo(position);
});

function mapToggle() {
    if(showMap) {
        showMap = false;
        $("#map-canvas").addClass('hidden');
        $("#search-results").removeClass('hidden');

        $("#toggle-map").removeClass('toggle-active');
        $("#toggle-map").addClass('toggle-inactive');
        $("#toggle-list").addClass('toggle-active');
        $("#toggle-list").removeClass('toggle-inactive');
    } else {
        showMap = true;
        $("#map-canvas").removeClass('hidden');
        $("#search-results").addClass('hidden');

        $("#toggle-map").addClass('toggle-active');
        $("#toggle-map").removeClass('toggle-inactive');
        $("#toggle-list").removeClass('toggle-active');
        $("#toggle-list").addClass('toggle-inactive');

    }
}
var drop = function drop() {
    clearMarkers();
    for (var i = 0; i < markers_data.length; i++) {
        addMarkerWithTimeout(markers_data[i], i * 200);
    }
};


$(document).ready(function(){
    prettyPrint();

    $('#mm-bodywrap').height(maxHeight);
    $('#map-canvas').height(maxHeight);
    $('#a').height(maxHeight);
    $('#search-results').height(maxHeight);

    if($currentWidth < 768) {
        $().remove('#a');
        $('#b').width('100%');
    }

    map.on('marker_added', function (marker) {
        //console.log(marker);
        /* var index = map.markers.indexOf(marker);
         $('#meal-search-results').append(marker.html);
         $('#meal-search-results-xs').append(marker.html_xs);
         if (index === map.markers.length - 1) {
             map.fitZoom();
         }*/
        map.fitZoom();
    });

    xhr.done(printResults);
    xhr.done(printResultsXS);
    xhr.done(loadResults);
    loadMarker(allMarkers);
    //drop();

});

$( window ).resize(function(){
    var newHeight = $( window ).outerHeight(true) - 150;
    $('#mm-bodywrap').height(newHeight);
    $('#map-canvas').height(newHeight);
    $('#a').height(newHeight);
    $('#search-results').height(newHeight);
    clearMarkers();
    map.addMarkers(markers_data);
    //drop();
});

