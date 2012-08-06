<?php

class EMail {
  private static function getMailSubject($reset) {
    if ($reset)
      return _('Map: Your new password');
    return _('Map: Your regestration');
  }

  private static function getMailBody($duser, $pass, $reset ) {
    $url = System::getConfig('url');
    $user = $duser->getUsername();

    $body = "";
    if ($reset)
      $body = _f("Reset Password Mail", $user, $url, $pass);
    else
      $body = _f("New User Mail",$user, $url, $pass);
    return wordwrap($body, 70);
  }

  private static function getMailHeader() {
    $send_mail_adr = System::getConfig('send_mail_adr');
    return "From: " . _('OSM Pirate Map') . " <$send_mail_adr>\n" .
           "Reply-To: $send_mail_adr\n" .
           "X-Mailer: PHP/" . phpversion();
  }

  public static function sendPasswordMail($user, $pass, $reset) {
    $send_mail_adr = System::getConfig('send_mail_adr');
    $email = $user->getEmail();
        if ($send_mail_adr != "")
          return mail($email, self::getMailSubject($reset), self::getMailBody($user, $pass, $reset), self::getMailHeader(), "-f$send_mail_adr");
    return false;
  }


}