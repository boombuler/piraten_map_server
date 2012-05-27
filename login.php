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
      global $_SESSION, $loginok;
      WikiConnection::logout();
      $loginok = 0;
      unset($_SESSION['siduserid']);
      unset($_SESSION['siduser']);
      unset($_SESSION['wikisession']);
      unset($_SESSION['sidip']);
      unset($_SESSION['admin']);
  }

  function login($username, $password)
  {
      global $_SESSION;
      $user = null;
      try {
        $user = Data_User::login($username, $password);
        if ($user->getAdmin())
            $_SESSION['admin'] = true;

        $_SESSION['siduserid'] = $user->getId();
        $_SESSION['siduser'] = $user->getUsername();
        $result = true;
      } catch (Exception $e) {
        $result = WikiConnection::login($username, $password);
      }

      // Try to get the users location...
      if ($result) {
          $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];

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