<?php
/*
       Licensed to the Apache Software Foundation (ASF) under one
       or more contributor license agreements.  See the NOTICE file
       distributed with this work for additional information
       regarding copyright ownership.  The ASF licenses this file
       to you under the Apache License, Version 2.0 (the
       "License"); you may not use this file except in compliance
       with the License.  You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0

       Unless required by applicable law or agreed to in writing,
       software distributed under the License is distributed on an
       "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
       KIND, either express or implied.  See the License for the
       specific language governing permissions and limitations
       under the License.
    */
ob_start("ob_gzhandler");
require('includes.php');
if (!isAdmin())
    die();

?><!DOCTYPE html
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

        <script language="JavaScript">
            function selectTab(name) {
                $('#tabcontrol > div').hide();
                $('ul.tabs > li').removeClass('active');
                $('#tab'+name).show();
                $('#tabHead'+name).addClass('active');
            }

            function removeElementFn(element) {
                setTimeout(function() {
                    element.slideUp('slow', function() { element.remove(); })
                }, 5000);

                return function() {
                    element.slideUp('slow', function() { element.remove(); })
                }
            }

            function displayPostResult(fn) {
                return function (data) {
                    result = jQuery.parseJSON(data);
                    if (result.status && result.message) {
                        msgdiv = jQuery('<div/>', {
                            class: 'alert-message '+result.status,
                        });
                        closelink = jQuery('<a />', {
                            class: 'close',
                            href: '#',
                            text: '×',
                            click: removeElementFn(msgdiv)
                        });
                        msgdiv.append(closelink);
                        msgdiv.append(jQuery('<p />', {
                            text: result.message
                        }));
                        msgdiv.hide();
                        msgdiv.appendTo($('#messages')).slideDown('slow');
                    }
                    if (fn) {
                        fn(result);
                    }
                };
            }

            function dropwikicat(id) {
                $.get('adminctrl.php', {
                    'action': 'drop',
                    'id' : id
                }, displayPostResult(function(d) {
                    if (d.status == 'success') {
                        tableRow = $('#trwikicat'+id);
                        tableRow.hide('slow', function(){ tableRow.remove(); });
                    }
                }));
            }

            $(document).ready(function(e) {
                selectTab('Categories');
                $('#tableCategories').tablesorter({
                    sortList: [[0,0]],
                    headers: {
                        4: { sorter: false }
                    }
                });

                $('#postwikicat').validate({
                    debug: false,
                    rules: {
                        name: {
                            required: true,
                            maxlength: 50
                        },
                        zoom: {
                            required: true,
                            min: 6,
                            max: 12
                        },
                        lat: {
                            required: true,
                            number: true
                        },
                        lon: {
                            required: true,
                            number: true
                        }
                    },
                    submitHandler: function(form) {
                        $.get('adminctrl.php', $('#postwikicat').serialize(), displayPostResult(function(data) {
                            if (data.status == 'success') {
                                cat = data.data;
                                row = "<tr id=\"trwikicat"+cat.id+"\"><td>"+cat.name+"</td><td>"
                                    + cat.lat + "</td><td>" + cat.lon + "</td><td>" + cat.zoom + "</td><td>"
                                    + "<a class=\"close\" onclick=\"javascript:dropwikicat("
                                    + cat.id+");\">&times;</a></td></tr>";
                                $('#tableCategories tbody').append(row);
                                $('#tableCategories').trigger("update");
                                $('#postwikicat')[0].reset();
                            }
                        }));
                    }
                });
            });
        </script>
    </head>


    <body>
        <div class="container" id="messages" style="margin-top: 18px;">
        </div>
        <div class="container" id="tabcontrol">
            <ul class="tabs" style="margin-top: 5px;">
                <li id="tabHeadCategories"><a href="#" onclick="javascript:selectTab('Categories');">Wiki-Kategorien</a></li>
                <li id="tabHeadUsers"><a href="#" onclick="javascript:selectTab('Users');">Benutzer</a></li>
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

    $db = openDB();
    foreach ($db->query("SELECT * FROM  ".$tbl_prefix."regions") as $cat) {
        echo "<tr id=\"trwikicat".$cat->id."\"><td>";
        echo $cat->category;
        echo "</td><td>";
        echo $cat->lat;
        echo "</td><td>";
        echo $cat->lon;
        echo "</td><td>";
        echo $cat->zoom;
        echo "</td><td>";
        echo "<a class=\"close\" onclick=\"javascript:dropwikicat(".$cat->id.");\">&times;</a>";
        echo "</td></tr>";
    }
    $db = null;
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