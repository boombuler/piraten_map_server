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
  require_once('library/System.php');
  require_once("includes.php");

  function logout()
  {
      global $_SESSION;
      WikiConnection::logout();
      $loginok = 0;
      unset($_SESSION['siduser']);
      unset($_SESSION['wikisession']);
      unset($_SESSION['sidip']);
      unset($_SESSION['admin']);
  }

  function login($username, $password)
  {
      global $_SESSION;
      $passwordmd5 = getPWHash($username, $password);

      $qry = System::query("SELECT username, password, admin FROM " . System::getConfig('tbl_prefix') . "users WHERE username = ?", array($username));
      $num = $qry->rowCount();

      $result = false;
      $_SESSION['admin'] = false;
      if ($num == 1) {
          $res = $qry->fetch();
          if ($res->password == $passwordmd5) {
            $_SESSION['siduser'] = $res->username;
            $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
            if ($res->admin == 1)
              $_SESSION['admin'] = true;
            $result = true;
          } else {
            return false;
          }
      } else {
          $result = WikiConnection::login($username, $password);
      }

      // Try to get the users location...
      if ($_SESSION['siduser']) {
          $categories = WikiConnection::getUserCategories();
          $filter = "category = ?";
          for ($i = 1; $i < count($categories); $i++) 
              $filter .= " OR category = ?";
          $query = "SELECT lat, lon,zoom FROM " . System::getConfig('tbl_prefix') . "regions WHERE $filter order by zoom desc limit 1";
          $res = System::query($query, $categories);
          if ($obj = $res->fetch()) {
              $_SESSION['deflat'] = $obj->lat;
              $_SESSION['deflon'] = $obj->lon;
              $_SESSION['defzoom'] = $obj->zoom;
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