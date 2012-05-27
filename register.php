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
  require_once('library/System.php');

  function resetPassword($username, $email) {
    global $tbl_prefix;

    $user = Data_User::find('username', $username)->fetchObject('Data_User');
    if ($user && strtolower($user->getEmail()) == strtolower($email)) {
        $plain_password = $user->setRandomPassword();
        if (!EMail::sendPasswordMail($user, $plain_password, true))
            return errorMsgHeader("Fehler beim versenden der EMail!");
        $user->save();
        return infoMsgHeader("Neues Passwort per EMail versand");
    }
  }

  function changePassword($newpass, $confirm) {
    global $tbl_prefix, $_SESSION;
    if (!isset($_SESSION['siduserid']) || isset($_SESSION['wikisession']))
      return errorMsgHeader("Passwort konnte nicht geändert werden");
    if ($newpass != $confirm)
        return errorMsgHeader("Passwörter stimmen nicht überein");

    $user = Data_User::find('id', $_SESSION['siduserid'])->fetchObject('Data_User');
    $user->setPassword($newpass);
    $user->save();

    return infoMsgHeader("Passwort wurde geändert");
  }

  function register($username, $email) {
    global $tbl_prefix;

    if (Data_User::find('username', strtolower($username)).rowCount() > 0 ||
        Data_User::find('email', strtolower($email)).rowCount() > 0)
        return errorMsgHeader("Benutzername oder EMail-Adresse wird bereits verwendet");

    $user = new Data_User;
    $user->setUsername($username);
    $user->setEmail($email);
    $plain_password = $user->setRandomPassword();

    if (!EMail::sendPasswordMail($user, $plain_password, false)) {
      return errorMsgHeader("Email konnte nicht gesendet werden!");
    }
    $user->save();
    return infoMsgHeader("Ihr Passwort wurde ihnen zugesandt");
  }

  if ($_POST['action'] == 'register') {
      if (isset($_POST['username']) && isset($_POST['email'])) {
        header(register($_POST['username'], $_POST['email']));
      }
  } else if ($_POST['action'] == 'changepw') {
      if (isset($_POST['newpass']) && isset($_POST['passconfirm'])) {	  
        header(changePassword($_POST['newpass'], $_POST['passconfirm']));
      }
  } else if ($_POST['action'] == 'resetpw') {
      if (isset($_POST['username']) && isset($_POST['email'])) {
        header(resetPassword($_POST['username'], $_POST['email']));
      }
  }

?>