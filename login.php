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

  require_once("includes.php");

  function logout()
  {
      global $snoopy, $apiPath, $_SESSION;
      if ($_SESSION['wikisession']) {
          $snoopy->cookies = $_SESSION['wikisession'];

          $request_vars = array('action' => 'logout', 'format' => 'php');
          if (!$snoopy->submit($apiPath, $request_vars))
              die("Snoopy error: {$snoopy->error}");
      }
      $loginok = 0;
      unset($_SESSION['siduser']);
      unset($_SESSION['wikisession']);
      unset($_SESSION['sidip']);
  }

  function login($username, $password)
  {
      global $tbl_prefix, $apiPath, $snoopy, $_SESSION;
      $passwordmd5 = getPWHash($username, $password);
      $db = openDB();
      $qry = $db->prepare("SELECT username, password FROM " . $tbl_prefix . "users WHERE username = ? AND password = ?");
      $qry->execute(array($username, $passwordmd5));

      $num = $qry->rowCount();
      $result = false;
      if ($num == 1) {
          $res = $qry->fetch();
          $_SESSION['siduser'] = $res->username;
          $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
          $result = true;
      } else {
          $username = strtoupper(substr($username, 0, 1)) . substr($username, 1, strlen($username) - 1);

          $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'format' => 'php');
          if (!$snoopy->submit($apiPath, $request_vars))
              die("Snoopy error: {$snoopy->error}");

          // We're only really interested in the cookies
          $snoopy->setcookies();
          $array = unserialize($snoopy->results);

          if ($array[login][result] == "NeedToken") {
              $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'lgtoken' => $array[login][token], 'format' => 'php');
              if (!$snoopy->submit($apiPath, $request_vars))
                  die("Snoopy error: {$snoopy->error}");

              // We're only really interested in the cookies
              $snoopy->setcookies();
              $array = unserialize($snoopy->results);
          }

          if ($array[login][result] == "Success") {
              $_SESSION['siduser'] = $username;
              $_SESSION['wikisession'] = $snoopy->cookies;
              $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
              $result = true;
          }
      }

      // Try to get the users location...
      if ($_SESSION['siduser']) {
          $request_vars = array('action' => 'query', 'prop' => 'categories', 'titles' => 'Benutzer:' . $_SESSION['siduser'], 'format' => 'php');
          if ($snoopy->submit($apiPath, $request_vars)) {
              $array = unserialize($snoopy->results);
              $categories = array('Germany');
              if (($array) && ($array['query']) && ($array['query']['pages'])) {
                  $pages = $array['query']['pages'];
                  reset($pages);
                  while (list($key, $val) = each($pages)) {
                      if (($val) && ($val['categories'])) {
                          $cats = $val['categories'];
                          reset($cats);
                          while (list($k, $cat) = each($cats)) {
                              if (($cat) && ($cat['title']))
                                  $categories[] = $cat['title'];
                          }
                      }
                  }
              }
              $filter = "category = ?";
              for ($i = 1; $i < count($categories); $i++)
                  $filter .= " OR category = ?";
              $query = "SELECT lat, lon,zoom FROM " . $tbl_prefix . "regions WHERE $filter order by zoom desc limit 1";
              $res = $db->prepare($query);
              $res->execute($categories);
              if ($obj = $res->fetch()) {
                  $_SESSION['deflat'] = $obj->lat;
                  $_SESSION['deflon'] = $obj->lon;
                  $_SESSION['defzoom'] = $obj->zoom;
              }
          }
      }
      $db = null;
      return $result;
  }


  if ($_GET['action'] == 'logout') {
      logout();
      header(infoMsgHeader("Logout OK"));
  } else {
      if (isset($_POST['username']) && isset($_POST['password'])) {
          if (login($_POST['username'], $_POST['password']))
              header(infoMsgHeader("Login OK"));
          else
              header(errorMsgHeader("Login fehlgeschlagen"));
      }
  }
?>