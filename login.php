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
  
  
  
  function login($username, $password, $mail)
  {
      global $tbl_prefix, $apiPath, $snoopy, $_SESSION;
      $username = mysql_escape($username);
      $password = mysql_escape(MD5($password));
      $res = mysql_query("SELECT username, password FROM " . $tbl_prefix . "users WHERE username='" . $username."';");
      $num = mysql_num_rows($res);
      if ($num == 0) return createAccount($username, $password, $mail);
      $res = mysql_query("SELECT username, password FROM " . $tbl_prefix . "users WHERE username='" . $username."' AND active=true;");
      $num = mysql_num_rows($res);
      if ($num == 0) return "Account not yet activated";
      $res = mysql_query("SELECT username, password FROM " . $tbl_prefix . "users WHERE username='" . $username . "' AND password='" . $password . "' AND active=true");
      $num = mysql_num_rows($res);
      if ($num == 1) {
          $_SESSION['siduser'] = mysql_escape(mysql_result($res, 0, "username"));
          $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
      } else return "Wrong password";
      //~ else {
          //~ $username = strtoupper(substr($username, 0, 1)) . substr($username, 1, strlen($username) - 1);
          //~ 
          //~ $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'format' => 'php');
          //~ if (!$snoopy->submit($apiPath, $request_vars))
              //~ die("Snoopy error: {$snoopy->error}");
          //~ 
          //~ // We're only really interested in the cookies
          //~ $snoopy->setcookies();
          //~ $array = unserialize($snoopy->results);
          //~ 
          //~ if ($array[login][result] == "NeedToken") {
              //~ $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'lgtoken' => $array[login][token], 'format' => 'php');
              //~ if (!$snoopy->submit($apiPath, $request_vars))
                  //~ die("Snoopy error: {$snoopy->error}");
              //~ 
              //~ // We're only really interested in the cookies
              //~ $snoopy->setcookies();
              //~ $array = unserialize($snoopy->results);
          //~ }
          //~ 
          //~ 
          //~ if ($array[login][result] == "Success") {
              //~ $_SESSION['siduser'] = mysql_escape($username);
              //~ $_SESSION['wikisession'] = $snoopy->cookies;
              //~ $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
              //~ $result = true;
          //~ }
      //~ }
      
      
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
              $regionen = "'" . mysql_escape($categories[0]) . "'";
              for ($i = 1; $i < count($categories); $i++)
                  $regionen .= ",'" . mysql_escape($categories[$i]) . "'";
              $query = "SELECT lat, lon,zoom FROM " . $tbl_prefix . "regions WHERE category in (" . $regionen . ") order by zoom desc limit 1";
              $res = mysql_query($query);
              $num = mysql_num_rows($res);
              
              if ($num == 1) {
                  $_SESSION['deflat'] = mysql_result($res, 0, "lat");
                  $_SESSION['deflon'] = mysql_result($res, 0, "lon");
                  $_SESSION['defzoom'] = mysql_result($res, 0, "zoom");
              }
          }
      }
	  return "Login OK";
  }
  
  function createAccount($username, $password, $mail){
	  global $tbl_prefix;
	  if (!strstr($mail, '@piraten')) return "Mail-adresss must contain @piratenpartei";
	  $res = mysql_query("SELECT * FROM ".$tbl_prefix."users WHERE username='".$username."';") OR dieDB();
	  $num = mysql_num_rows($res);
	  if ($num > 0) return "Username already exists";
	  $date = new DateTime();
	  $hash = md5($date->getTimestamp().$username);
	  mysql_query("INSERT INTO ".$tbl_prefix."users (username,password,hash) VALUES('".$username."','".$password."','".$hash."');") OR dieDB();
	  $header = 'From: noreply@piratenpartei.de';
	  if (mail($mail, "piratemap account activation", "Visit the following page to activate your account:\r\n".
			$_SERVER["SERVER_NAME"].$_SERVER['PHP_SELF']."?action=activate&hash=".$hash."&username=".$username, $header))
		return "Account created";
	  else return "Delivering mail failed";
  }
  
  function activateAccount($hash, $username){
	  global $tbl_prefix;
	  $res = mysql_query("SELECT * FROM ".$tbl_prefix."users WHERE username='".$username."' AND hash='".$hash."';") OR dieDB();
	  $num = mysql_num_rows($res);
	  if ($num == 0) return;
	  mysql_query("UPDATE ".$tbl_prefix."users set active=true WHERE username='".$username."' AND hash='".$hash."';") OR dieDB();
	  header("Location: ./?message=Account%20activated");
  }
	  
  
  if ($_GET['action'] == 'logout') {
      logout();
      header("Location: ./?message=Logout%20OK");
  } else if ($_GET['action'] == 'activate') {
	  activateAccount($_GET['hash'], $_GET['username']);
  } else {
      if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['mail'])) {
		  $res = login($_POST['username'], $_POST['password'], $_POST['mail']);
		  header("Location: ./?message=".htmlspecialchars($res));
      }
  }
?>
