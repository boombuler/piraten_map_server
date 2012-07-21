<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>OpenStreetMap Piraten Karte - Admin Interface</title>
        <link rel="stylesheet" href="bootstrap-1.1.0.min.css"></link>
        <script src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
        <script type="text/javascript" src="./js/jquery.validate.min.js"></script>
        <script src="./js/jquery.tablesorter.min.js"></script>

        <script language="JavaScript" src="./js/admin.js"></script>
    </head>


    <body>
        <div class="container" id="messages" style="margin-top: 18px;">
        </div>
        <div class="container" id="tabcontrol">
            <ul class="tabs" style="margin-top: 5px;">
                <li id="tabHeadCategories"><a href="#" onclick="selectTab('Categories');">Wiki-Kategorien</a></li>
                <li id="tabHeadUsers"><a href="#" onclick="selectTab('Users');">Benutzer</a></li>
            </ul>
            <div id="tabUsers">
                Todo
            </div>
            <div id="tabCategories">
                <div class="row">
                    <div class="span9 columns">
                        <h3>Vorhandene Einträge</h3>
                        <table class="common-table zebra-striped" id="tableCategories">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Zoom</th>
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
                        <h3>Neuer Eintrag</h3>
                        <form id="postwikicat" action="" method="GET">
                            <fieldset>
                                <input type="hidden" name="action" value="add" />
                                <div class="clearfix">
                                    <label>Kategorie</label>
                                    <div class="input">
                                        <input type="text" name="name" />
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <label>Latitude</label>
                                    <div class="input">
                                        <input type="text" name="lat"/>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <label>Longitude</label>
                                    <div class="input">
                                        <input type="text" name="lon"/>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <label>Zoom</label>
                                    <div class="input">
                                        <input type="text" name="zoom"/>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="input">
                                        <button type="submit" class="btn primary">Speichern</button>
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