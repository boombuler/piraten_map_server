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

  function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz-_~!+%&§@<()>?ABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";
    $len = rand(7, 10);
    $pass = "";

    while (strlen($pass) <= $len) {
        $num = rand(0, strlen($chars));
        $pass = $pass . substr($chars, $num, 1);
    }
    return $pass;
  }

  function getMailSubject($reset) {
    if ($reset)
      return "Plakat-Karte: Neues Passwort";
    return "Plakat-Karte: Deine Anmeldung";
  }

  function getMailBody($user, $pass, $reset) {
    global $url;
	$body = "";
    if ($reset)
      $body = "Hallo $user,\nwir haben dein Passwort für $url zurück gesetzt.\nUm dich einzuloggen, benutzte folgendes Passwort: $pass\nDu kannst dein Passwort nach dem Login ändern.\n\nViel Erfolg beim Plakatieren ;-)";
	else
      $body = "Hallo $user,\nvielen Dank für deine Anmeldung auf $url.\nUm dich einzuloggen, benutzte folgendes Passwort: $pass\nDu kannst dein Passwort nach dem Login ändern.\n\nViel Erfolg beim Plakatieren ;-)";
	return wordwrap($body, 70);
  }

  function getMailHeader() {
	global $send_mail_adr;
	return "From: Plakat-Karte <$send_mail_adr>\n" .
		   "Reply-To: $send_mail_adr\n" .
		   "Content-Type: text/plain;charset=utf-8\n" .
		   "X-Mailer: PHP/" . phpversion();
  }

  function sendPasswordMail($email, $user, $pass, $reset) {
	global $send_mail_adr;
        if ($send_mail_adr != "")
          return mail($email, getMailSubject($reset), getMailBody($user, $pass, $reset), getMailHeader());
        return false;
  }

  function resetPassword($username, $email) {
    global $tbl_prefix;
    $lusername = strtolower($username);
    $db = openDB();
    $stmt = $db->prepare("Select * from ".$tbl_prefix."users WHERE username = ? AND email = ?");
    $stmt->execute(array($lusername, $email));
    $res = $stmt->fetch();
    if (!$res) {
      return errorMsgHeader("Benutzername oder E-Mail-Adresse nicht gefunden!");
    }
    $plain_password = createRandomPassword();
    $pwhash = getPWHash($plain_password);
    $db->prepare("UPDATE ".$tbl_prefix."users SET password = ? WHERE username = ? AND email = ?")
       ->execute(array($pwhash, $lusername, $email));

    $db = null;
    if (!sendPasswordMail($email, $username, $plain_password, true))
      return errorMsgHeader("Fehler beim Versenden der E-Mail!");
    return infoMsgHeader("Neues Passwort wurde per E-Mail verschickt.");
  }

  function changePassword($newpass, $confirm) {
    global $tbl_prefix, $_SESSION;
    if (!isset($_SESSION['siduser']) || isset($_SESSION['wikisession']))
      return errorMsgHeader("Passwort konnte nicht geändert werden!");
    if ($newpass != $confirm)
        return errorMsgHeader("Passwörter stimmen nicht überein!");
    $lusername = strtolower($_SESSION['siduser']);
    $pwhash = getPWHash($newpass);
    $db = openDB();
    $db->prepare("UPDATE ".$tbl_prefix."users SET password = ? WHERE username = ?")
       ->execute(array($pwhash, $lusername));
    $db = null;
    return infoMsgHeader("Passwort wurde geändert.");
  }

  function register($username, $email) {
    global $tbl_prefix;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return errorMsgHeader("Ungültige E-Mail Adresse!");
    }
    $lusername = strtolower($username);
    $db = openDB();
    $stmt = $db->prepare("Select * from ".$tbl_prefix."users WHERE username = ? OR email = ?");
    $stmt->execute(array($lusername, $email));

    if ($stmt->rowCount() > 0) {
      return errorMsgHeader("Benutzername oder E-Mail-Adresse wird bereits verwendet!");
    }
    $plain_password = createRandomPassword();
    $pwhash = getPWHash($plain_password);
    $db->prepare("INSERT INTO ".$tbl_prefix."users (username, password, email) VALUES(?, ?, ?)")
       ->execute(array($lusername, $pwhash, $email));
    $db = null;
    if (!sendPasswordMail($email, $username, $plain_password, false)) {
      return errorMsgHeader("E-Mail konnte nicht gesendet werden!");
    }
    return infoMsgHeader("Dein Passwort wurde dir zugesandt.");
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
