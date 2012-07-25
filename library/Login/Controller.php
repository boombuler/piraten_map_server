<?php
class Login_Controller extends Controller
{
    function __construct()
    {
        $this->view = 'GlobalView_Json';
    }

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
        $username = $this->getParameter('username');
        $password = $this->getParameter('password');

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

    public function register()
    {
        $username = $this->getParameter('username');
        $email = $this->getParameter('email');
        if (Data_User::isUsernameOrEmailInUse($username, $email)) {
            $this->displayMessage("Benutzername oder EMail-Adresse wird bereits verwendet", false);
        }
        $user = new Data_User;
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setAdmin(false);
        $plain_password = $user->setRandomPassword();

        if (!EMail::sendPasswordMail($user, $plain_password, false)) {
            $this->displayMessage("Email konnte nicht gesendet werden!", false);
        } else {
            $user->save();
            $this->displayMessage("Ihr Passwort wurde ihnen zugesandt", true);
        }
    }

    public function resetpwd()
    {
        $username = strtolower($this->getParameter('username'));
        $email = strtolower($this->getParameter('email'));
        $query = Data_User::find(array('LOWER(username)', "LOWER(email)"), array($username, $email));
        $user = $query->fetchObject('Data_User');
        if ($user) {
            $plain_password = $user->setRandomPassword();
            if (!EMail::sendPasswordMail($user, $plain_password, true)) {
                $this->displayMessage("Fehler beim versenden der EMail!", false);
            } else {
                $user->save();
                $this->displayMessage("Neues Passwort per EMail versand", true);
            }
        } else {
            $this->$this->displayMessage("Unbekannter Benutzer!", false);
        }
    }

    public function changepwd()
    {
        $newpass = $this->getParameter('newpass');
        $confirm = $this->getParameter('passconfirm');

        $user = User::current();
        if (!($user instanceof IChangableUser))
            $this->displayMessage("Passwort konnte nicht geändert werden", false);
        else if ($newpass != $confirm)
            $this->displayMessage("Passwörter stimmen nicht überein", false);
        else {
            $user->setPassword($newpass);
            $user->save();

            $this->displayMessage("Passwort wurde geändert", true);
        }
    }
}