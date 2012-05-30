<?php
require_once('library/System.php');
require_once('includes.php');

class Login_Controller extends Controller
{
  public function index()
  {
    //currently redirect to login, but in future, this should display the login dialogue
    return $this->login();
  }

  public function logout()
  {
      global $_SESSION, $loginok;
      WikiConnection::logout();
      $loginok = 0;
      unset($_SESSION['siduserid']);
      unset($_SESSION['siduser']);
      unset($_SESSION['sidip']);
      unset($_SESSION['admin']);
      unset($_SESSION['sidusertype']);
      $this->displayMessage("Logout OK", true);
  }

  public function login()
  {
      global $_SESSION;
      $user = null;
      $result = false;
      $username = $this->getPostParameter('username');
      $password = $this->getPostParameter('password');
      try {
        $user = Data_User::login($username, $password);
        if ($user->getAdmin())
            $_SESSION['admin'] = true;

        $_SESSION['siduserid'] = $user->getId();
        $_SESSION['siduser'] = $user->getUsername();
        $_SESSION['sidusertype'] = 'local';
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
          $this->displayMessage("Login OK", true, System::getUserData());
      }
      else
        $this->displayMessage("Login fehlgeschlagen", false);
  }



  private function displayMessage($msg, $success, $data = null)
  {
    $message = array('message' => $msg, 'success' => $success);
    if ($data != null)
        $message = array_merge($message, $data);
    print json_encode($message);
  }
}