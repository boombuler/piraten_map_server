var map;
var gmlLayers = new Array();

$.fn.clearForm = function() {
  return this.each(function() {
    var type = this.type, tag = this.tagName.toLowerCase();
    if (tag == 'form')
      return $(':input',this).clearForm();
    if (type == 'text' || type == 'password' || tag == 'textarea')
      this.value = '';
    else if (type == 'checkbox' || type == 'radio')
      this.checked = false;
    else if (tag == 'select')
      this.selectedIndex = -1;
  });
};

function makeAJAXrequest(url, data) {
    $.ajax({
        url: url,
        data: data,
        success: function(msg){
            gmlreload();
        }
    });
}

function removeElementFn(element) {
    setTimeout(function() {
        element.slideUp('slow', function() { element.remove(); })
    }, 5000);

    return function() {
        element.slideUp('slow', function() { element.remove(); })
    }
}

function displaymessage(type, msg) {
    msgdiv = jQuery('<div/>', {
        class: 'alert-message '+type,
    });
    closelink = jQuery('<a />', {
        class: 'close',
        href: '#',
        text: '×',
        click: removeElementFn(msgdiv)
    });
    msgdiv.append(closelink);
    msgdiv.append(jQuery('<p />', {
        text: msg
    }));
    msgdiv.hide();
    msgdiv.appendTo($('#msgBag')).slideDown('slow');
}

var auth = {
  isLoggedIn: false,

  hideLoginElements: function() {
    $('.depLogin').hide();
    $('.depLogout').hide();
    $('.depAdmin').hide();
    $('.depUsrWiki').hide();
    $('.depUsrLocal').hide();
  },

  goToLoggedInState: function(userdata)
  {
    this.hideLoginElements();
    $('#menuusername').text(userdata.username);
    if (userdata.admin)
        $('.depAdmin').show();
    if (userdata.usertype == 'wiki')
        $('.depUsrWiki').show();
    if (userdata.usertype == 'local')
        $('.depUsrLocal').show();
    $('.depLogin').show();
    this.isLoggedIn = true;
  },

  goToLoggedOutState: function()
  {
    this.hideLoginElements();
    $('.depLogout').show();
    this.isLoggedIn = false;
  },

  login: function()
  {
    $.ajax({
      type: "POST",
      url: "login.php",
      data: $('#formlogin').serialize(),
      dataType: 'json',
      success: function(data) {
        if (data.success) {
          displaymessage('success', data.message);
          auth.goToLoggedInState(data.data);
        } else {
          displaymessage('error', data.message);
        }
      }
    });
    closeModalDlg(false);
  },

  logout: function() {
    $.ajax({
      type: "POST",
      url: "login.php",
      data: "action=logout",
      dataType: 'json',
      success: function(data) {
        if (data.success) {
          displaymessage('success', data.message);
          auth.goToLoggedOutState();
        } else {
          displaymessage('error', data.message);
        }
      }
    });
    closeModalDlg(false);
  },

  getForm: function(id) {
    $.ajax({
      type: "POST",
      url: "login.php",
      data: $(id).serialize(),
      dataType: 'json',
      success: function(data) {
        if (data.success) {
          $(id).clearForm();
          displaymessage('success', data.message);
        } else {
          displaymessage('error', data.message);
        }
      }
    });
    closeModalDlg(false);
  },

  register: function() {
    this.getForm('#formregister');
  },

  resetpwd: function() {
    this.getForm('#formnewpass');
  },

  changepwd: function() {
    this.getForm('#formchpw');
  }
}


function closeModal() {
    if (selectedFeature != null) {
        sf = selectedFeature;
        selectedFeature = null;
        closeModalDlg(true);
        selectControl.unselect(sf);
    }
}

function closeModalDlg(shouldRemove, oncomplete) {
    $('#mask').fadeTo("fast",0, function() {$(this).css('display', 'none')});
    $('body > .modal').fadeOut(function() {
        $(this).remove();
        if(!shouldRemove)
            $('#dlgBag').append($(this));
        if (oncomplete)
            oncomplete();
    });
}

function togglemapkey() {
    show = $('#mapkey').css('display') == 'none';
    if (show)
        $('#mapkey').fadeIn();
    else
        $('#mapkey').fadeOut(function() { $('#mapkey').css('display', 'none') });
}

function showModal(content) {
    var maskHeight = $(window).height();
    var maskWidth = $(window).width();

    //Set height and width to mask to fill up the whole screen
    $('#mask').css({'width':maskWidth,'height':maskHeight});

    //transition effect
    $('#mask').fadeTo("fast",0.8);
    //Get the window height and width
    var winH = $(window).height();

    $('body').append(content);
    //Set the popup window to center
    $('body > .modal')
        .css('z-index', '10101')
        .css('top',  maskHeight/2-$('body > .modal').height()/2)
        .fadeIn();
}

function showModalId(id) {
    showModal($('#'+id));
}

function getGML(filter, display) {
    if (!display)
        display = "Unbearbeitet";

    var filterurl = "./kml.php?filter="+filter;

    var mygml = new OpenLayers.Layer.Vector(display, {
        projection: map.displayProjection,
        strategies: [
            new OpenLayers.Strategy.BBOX()
        ],
        protocol: new OpenLayers.Protocol.HTTP({
            url: filterurl,
            format: new OpenLayers.Format.KML({
                extractStyles: true,
                extractAttributes: true
            }),
        })
    });

    map.addLayer(mygml);

    return mygml;
}

function onFeatureUnselect(feature) {
    closeModal();
}

function onFeatureSelect(feature) {
    selectedFeature = feature;
    showModal(createPopup(feature.attributes.description));
}

function delid(id){
    selectControl.unselect(selectedFeature);
    makeAJAXrequest("./kml.php", {"action":"del", "id":id});
}

function change(id){
    makeAJAXrequest("./kml.php", {
        "id"      : id,
        "action"  : "change",
        "type"    : document.getElementById('typ['+id+']').value,
        "comment" : document.getElementById('comment['+id+']').value,
        "city"    : document.getElementById('city['+id+']').value,
        "street"  : document.getElementById('street['+id+']').value,
        "image"   : document.getElementById('image['+id+']').value
    });
    selectControl.unselect(selectedFeature);
}

function gmlreload(){
    for(var i = 0; i < gmlLayers.length; i++) {
        var val = gmlLayers[i];
        //setting loaded to false unloads the layer//
        val.loaded = false;
        //setting visibility to true forces a reload of the layer//
        val.setVisibility(true);
        //the refresh will force it to get the new KML data//
        val.refresh({ force: true, params: { 'random': Math.random()} });
    }
}

//Initialise the 'map' object
function init() {
    OpenLayers.ImgPath = "../theme/default/";
    var options = {
			theme: "../theme/default/style.css",
            controls:[
                    new OpenLayers.Control.Navigation(),
                    new OpenLayers.Control.PanZoomBar(),
                    new OpenLayers.Control.Attribution(),
                    new OpenLayers.Control.LayerSwitcher({
                        roundedCornerColor: 'black'
                    }),
                    new OpenLayers.Control.Permalink()],
            maxResolution: 156543.0399,
            maxExtent: new OpenLayers.Bounds(-2037508.34,-2037508.34,2037508.34,2037508.34),
            numZoomLevels: 19,
            units: 'm',
            projection: new OpenLayers.Projection("EPSG:900913"),
            displayProjection: new OpenLayers.Projection("EPSG:4326")
    };

    map = new OpenLayers.Map ("map",  options );	
    layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
    map.addLayer(layerMapnik);
    layerTilesAtHome = new OpenLayers.Layer.OSM.Osmarender("Osmarender");
    map.addLayer(layerTilesAtHome);
    layerCycleMap = new OpenLayers.Layer.OSM.CycleMap("CycleMap");
    map.addLayer(layerCycleMap);

    var control = new OpenLayers.Control();
    OpenLayers.Util.extend(control, {
            draw: function () {
                this.point = new OpenLayers.Handler.Point( control,
                    {"done": this.notice},
                    {keyMask: OpenLayers.Handler.MOD_CTRL});
                this.point.activate();
            },
            notice: function (point) {
                lonlat = point.transform(
                    map.getProjectionObject(),new OpenLayers.Projection("EPSG:4326"));

                makeAJAXrequest("./kml.php", {
                    "action": "add",
                    "lon": lonlat.x,
                    "lat" :lonlat.y
                });
            }
    });
    map.addControl(control);

    for (var i in posterFlags) {
        gmlLayers.push(getGML(i, posterFlags[i]));
    }

    selectControl = new OpenLayers.Control.SelectFeature(gmlLayers, {
        onSelect: onFeatureSelect,
        onUnselect: onFeatureUnselect
    });

    map.addControl(selectControl);
    selectControl.activate();

    var lonLat = new OpenLayers.LonLat(startPos.lon, startPos.lat)
                    .transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
    map.setCenter (lonLat, startPos.zoom);
}


$(document).ready(function(e) {
    init();

    if (loginData != null)
        auth.goToLoggedInState(loginData);
    else
        auth.goToLoggedOutState();

    $(window).resize(function() {
        var maskHeight = $(window).height();
        var maskWidth = $(window).width();
        $('#mask').css({'width':maskWidth,'height':maskHeight});
        $('body > .modal').css('top',  maskHeight/2-$('body > .modal').height()/2);
    });

    $("body").bind("click", function(e) {
        $("ul.menu-dropdown").hide();
        $('a.menu').parent("li").removeClass("open").children("ul.menu-dropdown").hide();
    });

    $("a.menu").click(function(e) {
        var $target = $(this);
        var $parent = $target.parent("li");
        var $siblings = $target.siblings("ul.menu-dropdown");
        var $parentSiblings = $parent.siblings("li");
        if ($parent.hasClass("open")) {
            $parent.removeClass("open");
            $siblings.hide();
        } else {
            $parent.addClass("open");
            $siblings.show();
        }
        $parentSiblings.children("ul.menu-dropdown").hide();
        $parentSiblings.removeClass("open");
        return false;
    });
    onPageLoaded();
});