<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<title><?php echo _('Admin Interface'); ?></title>
        <link rel="stylesheet" href="bootstrap-1.1.0.min.css"></link>
        <script src="js/jquery-1.5.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.validate.min.js"></script>
        <script src="js/jquery.tablesorter.min.js"></script>

        <script language="JavaScript" src="./js/admin.js"></script>
    </head>


    <body>
        <div class="container" id="messages" style="margin-top: 18px;">
        </div>
        <div class="container" id="tabcontrol">
            <ul class="tabs" style="margin-top: 5px;">
				<li id="tabHeadCategories"><a href="#" onclick="selectTab('Categories');"><?php echo _('Wiki Categories'); ?></a></li>
				<li id="tabHeadUsers"><a href="#" onclick="selectTab('Users');"><?php echo _('Users'); ?></a></li>
            </ul>
            <div id="tabUsers">
				<?php echo _('NotYetImplemented'); ?>
            </div>
            <div id="tabCategories">
                <div class="row">
                    <div class="span9 columns">
						<h3><?php echo _('Existing Entries'); ?></h3>
                        <table class="common-table zebra-striped" id="tableCategories">
                            <thead>
                                <tr>
									<th><?php echo _('Name'); ?></th>
                                    <th><?php echo _('Latitude'); ?></th>
                                    <th><?php echo _('Longitude'); ?></th>
                                    <th><?php echo _('Zoom'); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
<?php
    foreach ($this->getCategories() as $cat) {
        printf('<tr id="trwikicat%1$d"><td>%2$s</td><td>%3$f</td><td>%4$f</td><td>%5$d</td>'
             . '<td><a class="close" onclick="javascript:dropwikicat(%1$d);">&times;</a></td></tr>',
               $cat->id, $cat->category, $cat->lat, $cat->lon, $cat->zoom);
    }
?>
                            </tbody>
                        </table>
                    </div>
                    <div class="span7 columns">
						<h3><?php echo _('Add New'); ?></h3>
                        <form id="postwikicat" action="" method="GET">
                            <fieldset>
                                <input type="hidden" name="action" value="add" />
                                <div class="clearfix">
									<label><?php echo _('Category'); ?></label>
                                    <div class="input">
                                        <input type="text" name="name" />
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <label><?php echo _('Latitude'); ?></label>
                                    <div class="input">
                                        <input type="text" name="lat"/>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <label><?php echo _('Longitude'); ?></label>
                                    <div class="input">
                                        <input type="text" name="lon"/>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <label><?php echo _('Zoom'); ?></label>
                                    <div class="input">
                                        <input type="text" name="zoom"/>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="input">
										<button type="submit" class="btn primary"><?php echo _('Save'); ?></button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                <div>
            </div>
        </div>
    </body>
</html>