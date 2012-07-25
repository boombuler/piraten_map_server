<?php
class Data_User extends Data_Table implements IChangableUser
{
    protected $id;

    protected $username;

    protected $password;

    protected $email;

    protected $admin;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    private function setId($id)
    {
        if ($this->id) {
            throw new Exception('Changing Id is not possible');
        }

        $this->id = (int) $id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        $this->logModification('username');
        return $this;
    }

    private function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = Data_User::getPWHash($password);
        $this->logModification('password');
        return $this;
    }

    public function setRandomPassword()
    {
        $chars = "abcdefghijkmnopqrstuvwxyz-_~!+%&§@<()>?ABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";
        $len = rand(7, 10);
        $pass = "";

        while (strlen($pass) <= $len) {
            $num = rand(0, strlen($chars));
            $pass = $pass . substr($chars, $num, 1);
        }
        $this->setPassword($pass);
        return $pass;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('This is not a valid email address.');
        }
        $this->email = $email;
        $this->logModification('email');
        return $this;
    }

    public function getAdmin()
    {
        $val = $this->admin;
        if ($val === 0 || $val === '0')
            return false;
        if ($val === 1 || $val === '1')
            return true;
        return $val;
    }

    public function setAdmin($admin)
    {
        if ($admin !== true && $admin !== false)
            throw new Exception('This is not a valid admin value');
        $this->admin = $admin;
        $this->logModification('admin');
        return $this;
    }

    /**
     * gives a string to check the users type
     */
    public function getType()
    {
        return 'local';
    }

    /**
     * Checks if the user is still valid.
     * @return bool
     */
    public function isSessionValid()
    {
        return $_SESSION['sidip'] == $_SERVER["REMOTE_ADDR"];
    }

    /**
     * get the default location for that user. Should return an array with lon, lat and zoom
     */
    public function getInitialPosition()
    {
        return array(
            'lat' => System::getConfig('start_lat'),
            'lon' => System::getConfig('start_lon'),
            'zoom' => System::getConfig('start_zoom'),
        );
    }

    public function logout()
    {
        unset($_SESSION['sidip']);
    }

    public static function get($id)
    {
        $result = self::find("id", $id);
        return $result->fetchObject(__CLASS__);
    }

    public static function isUsernameOrEmailInUse($username, $email)
    {
        $query = 'SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE LOWER(username)=? OR LOWER(email)=?';
        $result = System::query($query, array(strtolower($username), strtolower($email)));
        return $result->rowCount() > 0;
    }

    public static function login($username, $password)
    {
        if ($username == '' || $password == '') {
            return null;
        }
        $username = strtolower($username);

        $query = 'SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE LOWER(username)=?';
        $result = System::query($query, array($username));
        $user = $result->fetchObject(__CLASS__);

        $dbpwd = $user->getPassword();
        if (strlen($dbpwd) == 32) {
            // Old MD5 Password
            if (Data_User::getObsoletePWHash($username, $password) == $dbpwd) {
                // Password is correct --> Update the Database Value!
                $user->setPassword($password)->save();
            } else {
                return null;
            }
        } elseif (crypt($password, $dbpwd) != $dbpwd) {
            return null;
        }

        if (!$user)
            return null;
        $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
        return $user;
    }

    public function saveChanges()
    {
        $this->save();
    }

    static function getObsoletePWHash($user, $pass)
    {
        return md5(strtolower($user).":".$pass);
    }

    static function getPWHash($pass)
    {
        $salt = substr(str_shuffle( './0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 22);
        return crypt($pass, '$2a$10$' . $salt);
    }

    public function validate()
    {
        if (!$this->getUsername()) {
            return false;
        }
        if (!$this->getPassword()) {
            return false;
        }
        if (!$this->getEmail()) {
            return false;
        }
        return true;
    }

    protected static function getTableName()
    {
        return "users";
    }

    protected function getPrimaryKeyValues()
    {
        return array(
            'id' => $this->getId()
        );
    }

    protected function prepareValues($values)
    {
        $values = parent::prepareValues($values);
        if (array_key_exists('username', $values)) {
            $values['username'] = strtolower($values['username']);
        }
        return $values;
    }

    protected function insert($setvals)
    {
        parent::insert($setvals);
        $this->setId(System::lastInsertId());
    }
}