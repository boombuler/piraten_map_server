<?php
class Login_Controller extends Controller
{
    function __construct()
    {
        $this->view = '';
    }

    public function index()
    {
        //currently redirect to login, but in future, this should display the login dialogue
        return $this->login();
    }

    public function logout()
    {
        User::logout();
        $this->displayMessage(_('Logout OK'), true);
    }

    public function login()
    {
        $username = $this->getParameter('username');
        $password = $this->getParameter('password');

        $user = User::login($username, $password);

        if ($user)
            $this->displayMessage(_('Login OK'), true, array(
                'username' => $user->getUsername(),
                'usertype' => $user->getType(),
                'admin' => $user->getAdmin()
            ));
        else
            $this->displayMessage(_('Login failed'), false);
    }

    public function register()
    {
        $username = $this->getParameter('username');
        $email = $this->getParameter('email');
        if (Data_User::isUsernameOrEmailInUse($username, $email)) {
            $this->displayMessage(_('Username or password alread in use'), false);
        }
        $user = new Data_User;
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setAdmin(false);
        $plain_password = $user->setRandomPassword();

        if (!EMail::sendPasswordMail($user, $plain_password, false)) {
            $this->displayMessage(_('Could not send email'), false);
        } else {
            $user->save();
            $this->displayMessage(_('We just send you an email with your new password'), true);
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
                $this->displayMessage(_('Could not send email'), false);
            } else {
                $user->save();
                $this->displayMessage(_('We just send you a new password'), true);
            }
        } else {
            $this->$this->displayMessage(_('Invalid User'), false);
        }
    }

    public function changepwd()
    {
        $newpass = $this->getParameter('newpass');
        $confirm = $this->getParameter('passconfirm');

        $user = User::current();
        if (!($user instanceof IChangableUser))
            $this->displayMessage(_('Password could not be changed'), false);
        else if ($newpass != $confirm)
            $this->displayMessage(_('The passwords do not match'), false);
        else {
            $user->setPassword($newpass);
            $user->save();

            $this->displayMessage(_('Password successfully changed'), true);
        }
    }
}
