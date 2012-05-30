<?php

class EMail {
  private static function getMailSubject($reset) {
    if ($reset)
      return "Plakat Karte: Neues Passwort";
    return "Plakat Karte: Ihre Anmeldung";
  }

  private static function getMailBody($duser, $pass, $reset ) {
    $url = System::getConfig('url');
    $user = $duser->getUsername();

    $body = "";
    if ($reset)
      $body = "Hallo $user,\nwir haben dein Passwort für $url zurück gesetzt.\nUm dich einzuloggen benutzte folgendes Passwort: $pass\nDu kannst dein Passwort dort ändern!\n\nViel Erfolg beim Plakatieren ;-)";
    else
      $body = "Hallo $user,\nvielen Dank für deine Anmeldung auf $url.\nUm dich einzuloggen benutzte folgendes Passwort: $pass\nDu kannst dein Passwort dort ändern!\n\nViel Erfolg beim Plakatieren ;-)";
    return wordwrap($body, 70);
  }

  private static function getMailHeader() {
    $send_mail_adr = System::getConfig('send_mail_adr');
    return "From: Plakat Karte <$send_mail_adr>\n" .
           "Reply-To: $send_mail_adr\n" .
           "X-Mailer: PHP/" . phpversion();
  }

  public static function sendPasswordMail($user, $pass, $reset) {
    $send_mail_adr = System::getConfig('send_mail_adr');
    $email = $user->getEmail();
        if ($send_mail_adr != "")
          return mail($email, getMailSubject($reset), getMailBody($user, $pass, $reset), getMailHeader(), "-f$send_mail_adr");
    return false;
  }


}

?>