<?php echo '<?xml version="1.0" ?>';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title><?php echo _('OSM Pirate Map'); ?></title>
  <meta charset="UTF-8" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="imagetoolbar" content="no" />
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
  <meta name="robots" content="NOINDEX,NOFOLLOW" />

  <script type="text/javascript" src="js/PanoJS.min.js"></script>
  <script type="text/javascript" src="js/touchMapLite.js"></script>
  <script type="text/javascript" src="js/touchMapLite.tileUrlProvider.OSM.js"></script>
  <script type="text/javascript" src="js/touchMapLite.marker.js"></script>

  <link rel="stylesheet" type="text/css" href="style-mobile.css" />
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.0b2/jquery.mobile-1.0b2.min.css" />

  <script src="http://code.jquery.com/jquery-1.6.2.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.0b2/jquery.mobile-1.0b2.min.js"></script>

  <script type="text/javascript" src="js/touchMapLite.event.touch.js"></script>
  <script type="text/javascript" src="js/touchMapLite.event.wheel.js"></script>
  <script type="text/javascript" src="js/touchMapLite.geolocation.js"></script>


  <script type="text/javascript" src="js/map-mobile.js"></script>
  <script type="text/javascript">
    var isLoggedIn = <?php print User::current() ? 'true': 'false'; ?>;
    var posterFlags = <?php print json_encode(Data_Poster::getTypes()); ?>;
    var startPos = <?php print json_encode($this->getInitialPosition()); ?>;
    var loginData = <?php print json_encode($this->getUserData()); ?>;
  </script>
</head>
<body>

<div id="home" data-role="page">
  <div data-role="header">
    <h1>Karte</h1>
  </div>
  <div id="viewer">
    <div class="well"><!-- --></div>
    <div class="surface" id="touchArea"><!-- --></div>
    <div class="marker" id="markers"></div>
    <p class="controls">
      <span class="zoomIn" title="Zoom In">+</span>
      <span class="zoomOut" title="Zoom Out">-</span>
      <span class="options" title="Show Options">Options</span>
    </p>
  </div>
</div>

<div id="setmarker" data-role="page">
  <div data-role="header">
    <a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
    <h1>Marker setzten</h1>
  </div>
  <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
    <?php
foreach (Data_Poster::getTypes() as $key=>$value)
{
    if ($value != '') { ?>
      <li><a href="#home" onclick="setMarker('<?php echo $key; ?>');"><?php echo $value; ?></a></li>
    <?php }
}  ?></ul>
</div>

<div id="settings" data-role="page">
  <div data-role="header">
    <a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
    <h1>Menu</h1>
  </div>
  <div data-role="content">

<?php if (!User::current()) { ?>
    <form action="login.php" method="post" class="dialog" id="loginfrm" name="loginfrm">
<?php } else { ?>
    <form name="logout" id="logout" action="login.php?action=logout" method="post">
<?php } ?>
      <ul data-role="listview" data-theme="c" data-dividertheme="b">
        <li data-role="list-divider">Zugang</li>
<?php if (!User::current()) { ?>
        <li data-role="fieldcontain">
          <label for="username">Benutzername:</label>
          <input type="text" name="username" id="username" />
        </li>
        <li data-role="fieldcontain">
          <label for="password">Passwort:</label>
          <input type="password" name="password" id="password" />
        </li>
        <li data-role="fieldcontain">
          <label for="mail">Mail:</label>
          <input type="text" name="mail" id="mail" />
        </li>
        <li><a href="#home" onclick="document.forms['loginfrm'].submit();">Login/Create Account</a></li>
<?php } else { ?>
        <li><a href="#home" onclick="document.forms['logout'].submit();">Logout</a></li>
        <li><a href="#setmarker" >Marker auf aktueller Position</a></li>
<?php } ?>
        <li data-role="list-divider">Position</li>
        <li><a onclick="touchMap.findLocationHandler();" href="#home">Position suchen</a></li>
        <li data-role="fieldcontain">
          <label for="slider">Positionsverfolgung</label>
          <select name="slider" id="slider" data-role="slider" onchange="toggleWatchLocation(this);">
            <option value="off">Off</option>
            <option value="on">On</option>
          </select>
        </li>
      </ul>
    </form>
  </div>
</div>

<div id="editfrm" title="Details" data-role="page">
  <div data-role="header">
    <a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
    <h1>Details</h1>
  </div>
  <div data-role="content">
    <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
      <li><label id="info_typ" class="plaintxt" >&nbsp;</label></li>
      <li><label id="info_memo" class="plaintxt" >&nbsp;</label></li>
      <li><img src="images/noimg.png" id="info_image" width="250" /></li>
    </ul>
<?php if (User::current()) { ?>
    <!--a class="whiteButton" href="#home">Marker editieren</a-->
    <a href="#home" data-role="button" id="delMark" data-icon="delete">Marker löschen</a>
<?php } ?>
  </div>
</div>
</body>
</html>