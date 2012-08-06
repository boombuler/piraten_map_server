touchMapLite.prototype.TileUrlProviderOSM = function(baseUri, prefix, extension) {
		var uris = ['http://tile.openstreetmap.org'];
	if (baseUri)
	    uris = [baseUri];
	var basePos = 0;
	this.baseUri = function() {
	   basePos = ++basePos % uris.length;
	   return uris[basePos];
	}

	this.prefix = prefix;
	this.extension = extension;
}

touchMapLite.prototype.TileUrlProviderOSM.prototype = {
	assembleUrl: function(xIndex, yIndex, zoom) {
		return this.baseUri() + '/' +
			this.prefix + zoom + '/' + xIndex + '/' + yIndex + '.' + this.extension;
	}
}