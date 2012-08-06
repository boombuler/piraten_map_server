<?php 
	header("Content-type: application/xhtml+xml");
	echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
  <title><?php echo _('OSM Pirate Map'); ?></title>
  <link rel="stylesheet" href="bootstrap-1.1.0.min.css" />
  <link rel="stylesheet" href="style-desktop.css" />
  <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
  <script type="text/javascript" src="js/OpenLayers.js"></script>
  <script type="text/javascript" src="js/map-desktop.js"></script>
  
  <script type="text/javascript" src="js/jsrender.js"></script>
  <script type="text/javascript">
//<![CDATA[
    var startPos = <?php print json_encode($this->getInitialPosition()); ?>;
    var posterFlags = <?php print json_encode(Data_Poster::getTypes()); ?>;
    var loginData = <?php print json_encode($this->getUserData()); ?>;
	var mapDefaults = {
		'rawlabel': <?php echo json_encode(_('Raw')); ?>,
		'unit': <?php echo json_encode(System::getConfig('mapunit')); ?>
	}
	
    function onPageLoaded() {
        <?php
        if ($this->getMessage()) {
            echo "displaymessage('success', '".$this->getMessage()."');";
        }
        if ($this->getError()) {
            echo "displaymessage('error', '".$this->getError()."');";
        } ?>
    }
//]]>
  </script>
  <?php include('dialogs/markerdetail.php'); ?>
</head>

<body>
    <div id="mask"></div>
    <div class="topbar">
        <div class="fill">
            <div class="container">
                <h3><a href="#"><?php echo _('OSM Pirate Map'); ?></a></h3>
                <ul>
                    <li class="depUsrWiki"><a href="#" onclick="auth.logout();"><?php echo _('Logout'); ?></a></li>

                    <li class="menu depUsrLocal">
                        <a href="#" class="menu" id="menuusername"></a>
                        <ul class="menu-dropdown">

							<li class="depAdmin"><a href="admin.php" onclick="window.open(this.href); return false;"><?php echo _('Administration'); ?></a></li>
                            <li class="divider depAdmin" />

                            <?php if (System::canSendMails()) { ?>
							<li><a href="#" onclick="javascript:showModalId('chpwform');"><?php echo _('Change Password'); ?></a></li>
                            <li class="divider" />
                            <?php } ?>
                            <li><a href="#" onclick="auth.logout();"><?php echo _('Logout'); ?></a></li>
                        </ul>
                    </li>

					<li class="depLogin"><a href="#" onclick="showModalId('exportCity');"><?php _('Export Markers'); ?></a></li>

                    <li class="depLogout"><a href="#" onclick="javascript:showModalId('loginform');"><?php echo _('Login'); ?></a></li>
                    <?php if (System::canSendMails()) { ?>
					<li class="depLogout"><a href="#" onclick="javascript:showModalId('registerform');"><?php echo _('Register'); ?></a></li>
                    <?php } /* canSendMails */ ?>
					<li><a href="#" onclick="togglemapkey();"><?php echo _('Help'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div style="display:none;" id="dlgBag">
    <?php
        include('dialogs/loginform.php');
        include('dialogs/exportCity.php');
        if (System::canSendMails()) {
            include('dialogs/newpassform.php');
            include('dialogs/registerform.php');
            include('dialogs/chpwform.php');
        }
    ?>
    </div>
    <div id="msgBag"></div>
    <div id="map"></div>
    <div id="mapkey">
        <div class="modal">
            <div class="modal-header">
				<h3><?php echo _('Legend'); ?></h3>
                <a href="#" onclick="javascript:togglemapkey();" class="close">&times;</a>
            </div>
            <div class="modal-body">
                <ul class="unstyled">
					<li class="depLogout"><?php echo _('Marks will be editable after login'); ?></li>
					<li class="depLogout"><?php echo _('Use local or wiki login'); ?></li>

					<li class="depLogin"><?php echo _('Ctrl+Mouseclick: new mark'); ?></li>

                    <li>
                        <ul>
                        <?php foreach (Data_Poster::getTypes() as $key=>$value) {
                            if ($value!="") { ?>
                            <li><img  style="vertical-align:text-top;" src="./images/markers/<?php echo $key?>.png" width="20" alt="<?php echo $key?>" />=<?php echo $value?></li>
                        <?php
                            }
                        } ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>