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
        User::logout();
        $this->displayMessage("Logout OK", true);
    }

    public function login()
    {
        $username = $this->getPostParameter('username');
        $password = $this->getPostParameter('password');

        $user = User::login($username, $password);
    
        if ($user)
            $this->displayMessage("Login OK", true, array(
                'username' => $user->getUsername(),
                'usertype' => $user->getType(),
                'admin' => $user->getAdmin()
            ));
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