try {window.gears = !!(typeof GearsFactory != 'undefined' || navigator.mimeTypes['application/x-googlegears'] || new ActiveXObject('Gears.Factory'));}catch(e){}

if(typeof(navigator.geolocation) == "undefined" && window.gears){
	navigator.geolocation = google.gears.factory.create('beta.geolocation');
}

if(typeof(navigator.geolocation) != "undefined"){

	touchMapLite.prototype.findLocationHandler = function(e) {
		if(findOnMap != null){
			  navigator.geolocation.getCurrentPosition(this.recenterLonLat, this.nolocationFound);
		}
		return false;
	};

} else if(typeof window.gears != "undefined"){
// android

	var geo = google.gears.factory.create('beta.geolocation');
	
	touchMapLite.prototype.findLocationHandler = function(e) {
		if(typeof(geo) != "undefined" && findOnMap != null){
			 geo.getCurrentPosition(this.recenterLonLat, this.nolocationFound);
		} else {
			alert('no geolocation service (gears)')
		}
		return false;
	}
} else if(typeof blackberry != "undefined" && typeof blackberry.location != "undefined"){
// blackberry
	touchMapLite.prototype.findLocationHandler = function(e) {
		if(blackberry.location.GPSSupported && findOnMap != null){
			 if(typeof blackberry.location.longitude  != "undefined"){
				 position = {coords:{longitude:blackberry.location.longitude, latitude:blackberry.location.latitude}};
				 this.prototype.recenterLonLat(position);
			 } else {
				 this.nolocationFound();
			 }
		} else {
			alert('no geolocation service (backberry)')
		}
		return false;
	}
} else {
// dummy

	touchMapLite.prototype.findLocationHandler = function(e) {
		alert('no geolocation services found')
		return false;
	};

}


touchMapLite.prototype.watchLocationHandler = function(e) {
	if(typeof(navigator.geolocation) != "undefined"){
		if(!watchId && findOnMap != null){
			watchId = navigator.geolocation.watchPosition(this.recenterLonLat, undefined, {enableHighAccuracy: true});
			return true;
		} else {
			navigator.geolocation.clearWatch(watchId);
			watchId = false;
		}
	} else {
		alert('no geolocation service')
	}
	return false;
};

touchMapLite.prototype.nolocationFound = function(error){
	if(error.code!=0){
		alert('cannot determin current location ['+error.code+']');
	} else {
		return false;
	}
}

touchMapLite.prototype.recenterLonLat = function(position){
    var wasZoomed=false;
	lon = position.coords.longitude;
	lat = position.coords.latitude;
	findOnMap.viewerBean.initialPan = { 'x' : findOnMap.lon2pan(lon), 'y' : findOnMap.lat2pan(lat)};
	if(!watchId && position.coords.accuracy){	
		// needs a more sophisticated technique
		zoomLevel = 18-Math.floor(Math.log(position.coords.accuracy));
        if(zoomLevel!=findOnMap.viewerBean.zoomLevel) wasZoomed=true;
		if(zoomLevel>findOnMap.viewerBean.zoomLevel) {
            findOnMap.viewerBean.zoomLevel = zoomLevel; 
        }
	}
	findOnMap.viewerBean.clear();
	findOnMap.viewerBean.init();
	findOnMap.viewerBean.notifyViewerMoved({x:findOnMap.viewerBean.x, y:findOnMap.viewerBean.y});
	if(wasZoomed==true) findOnMap.viewerBean.notifyViewerZoomed();
	if(typeof findOnMap.marker != 'undefined'){
		var home = new findOnMap.marker( {title:'GPS',lat: lat, lon: lon, onClick:null}, findOnMap, true);
	}
	return false;
}

var findOnMap = null;
var watchId = false;