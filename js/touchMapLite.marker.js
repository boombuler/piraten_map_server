/*	markers used under the terms of the Creative Commons Attribution licence
 *	http://www.mapito.net/map-marker-icons.html
 */


touchMapLite.prototype.MARKERS = [];


touchMapLite.prototype.placeMarkerHandler = function(){
	coords = this.currentLonLat(this.viewerBean);
	marker = new this.marker('GPS',coords.y, coords.x,this,true);
}


touchMapLite.prototype.getMarkersFormUrlParams = function(){
		if(window.location.href.split('?' )[1]){
			var params = window.location.href.split('?')[1].split('&');
			for(index=0; index<params.length; index++) {  
				keyValue = params[index].split('=');
				if(keyValue[0]=='markers'){
					markers = keyValue[1].split('|');
					for(markersIndex=0; markersIndex < markers.length; markersIndex++) {  
						markerParams = markers[markersIndex].split(',');
							this.MARKERS[markersIndex] = new this.marker(markerParams[2], parseFloat(markerParams[0]), parseFloat(markerParams[1]),this);
					}
				}
			}
		}
}
		

touchMapLite.prototype.marker = function(options, map, live) {
	var applyFn = function(src, targ) {
		for(var key in src)
			if (key)
				targ[key] = src[key];
		return targ;
	}
	options = applyFn(options,{
		divx: -10,
		divy: -25,
		x: 0,
		y: 0,
		title: '',
		markerSrc: "images/markers/posmarker.png"
	});
	
	applyFn(options, this);
	
	if(live){
		this.id = 0;
		found = false;
		if(typeof map.MARKERS == 'undefined') map.MARKERS = [];
		for(var id=0; id<map.MARKERS.length; id++){
			if(map.MARKERS[id].title == this.title && map.MARKERS[id].element){
				document.getElementById('markers').removeChild(map.MARKERS[id].element);
				map.MARKERS[id] = this;
				this.id = id;
				found = true;
				continue;
			}
		}
		if(!found){
			this.id = map.MARKERS.length;
			map.MARKERS[this.id] = this;
		}
	} else {
		this.id = map.MARKERS.length;
		map.MARKERS[this.id] = this;
	}
	this.initialized = false;
	this.map = map;
	this.viewer = map.viewerBean;
	var marker = this;	
	this.viewer.addViewerMovedListener(marker);
	this.viewer.addViewerZoomedListener(marker);
	this.createDOMelement();
	this.placeMarker();
	this.updateMarker(this.viewer);

}

touchMapLite.prototype.marker.prototype = {
	
	placeMarker: function(){
		fullSize = this.viewer.tileSize * Math.pow(2, this.viewer.zoomLevel);
		this.x = Math.floor(this.map.lon2pan(this.lon)*fullSize);
		this.y = Math.floor(this.map.lat2pan(this.lat)*fullSize);

	},
	createDOMelement: function(){
		this.element = document.createElement("div");
		this.element.setAttribute("class","marker");
		var image = document.createElement("img");
		image.src=this.markerSrc;
		document.getElementById('markers').appendChild(this.element)
		this.element.appendChild(image)
	
		this.element.marker = this;
		this.element.onclick = this.onClick;
	},
	drop: function() {
		var parent = this.element.parentNode;
		parent.removeChild(this.element);
		this.map.MARKERS[this.id] = null;
	},
	onClick: function(event){
		return false;
	},
	updateMarker: function(e){	
		var top = (e.y+this.y+this.divy);
		var left = (e.x+this.x+this.divx);
		if(top>=0 && top<this.viewer.height && left>=0 && left<this.viewer.width){
			this.element.style.top = top+"px";
			this.element.style.left = left+"px";
			if(this.element.style.display == 'none'){
				this.element.style.display = 'block';
			}
		} else {
			if(this.element.style.display != 'none'){
				this.element.style.display = 'none';
			}		
		}
	},

	viewerMoved: function(e){
		this.updateMarker(e);
	},

	viewerZoomed: function(e){
		this.placeMarker();
		this.updateMarker(e);
	}

}
