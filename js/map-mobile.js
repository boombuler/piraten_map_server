var touchMap = null;

function createMarker(data)
{
    var marker = new touchMap.marker({
    title: data.type,
    lat: data.lat*1.0,
    lon: data.lon*1.0,
    divx: -8,
    divy: -8,
    markerSrc: 'images/markers/'+data.type+'.png',
    onClick: function(event) {
        document.getElementById('info_typ').innerHTML = posterFlags[data.type];
        document.getElementById('info_memo').innerHTML = data.comment;
        document.getElementById('info_image').src = data.image ? data.image : 'images/noimg.png';
        document.getElementById('delMark').onclick = function() {
            makeAJAXrequest("./json.php?action=del&id="+data.id);
        }
        $.mobile.changePage($("#editfrm"));
        return false;
    }
    } , findOnMap, false);
}

function makeAJAXrequest(url, readyFn)
{
    var createXMLHttpRequest = function() {
    try { return new XMLHttpRequest(); } catch(e) {}
    return null;
    }
    if (!readyFn)
    readyFn = gmlreload;
    var xhReq = createXMLHttpRequest();
    xhReq.open("get", url, true);
    xhReq.onreadystatechange = function() {
    if ( xhReq.readyState == 4 && xhReq.status == 200 ) {
        readyFn(xhReq.responseText);
    }
    };
    xhReq.send(null);
}

function setMarker(aType)
{
    navigator.geolocation.getCurrentPosition(function(pos) {
    makeAJAXrequest("./json.php?action=add&typ="+aType+"&lon="+
                    pos.coords.longitude+"&lat="+pos.coords.latitude);
    }, undefined, {enableHighAccuracy: true});
}

function gmlreload(result)
{
    for(var i = 0; i < touchMap.MARKERS.length; i++) {
    if (touchMap.MARKERS[i])
        touchMap.MARKERS[i].drop()
    }
    var new_markers = JSON.parse( result );
    if (new_markers != null) {
    for(var i = 0; i < new_markers.length; i++)
        createMarker(new_markers[i]);
    }
}

EventUtils.addEventListener(window, 'load', function()
{
    touchMap = new touchMapLite("viewer");
    for (var i in startPos) {
    touchMap[i] = startPos[i];
    }
    touchMap.init();
    findOnMap = touchMap;
    makeAJAXrequest('json.php');
}, false);

EventUtils.addEventListener(window, 'resize', function(){
    touchMap.reinitializeGraphic();
}, false);

PanoJS.optionsHandler = function(e)
{
    $.mobile.changePage($("#settings"));
    return false;
}

toggleWatchLocation = function(node)
{
    if(!touchMap.watchLocationHandler()){
    var myswitch = $("#slider");
    myswitch[0].selectedIndex = myswitch[0].selectedIndex == 0 ? 1 : 0;
    myswitch.slider("refresh");
    }
}