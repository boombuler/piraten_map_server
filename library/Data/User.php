<?php
class Data_User extends Data_Abstract implements IChangableUser
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
        $this->password = Data_User::getPWHash($this->getUsername(), $password);
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
    
    public function logout() {
        unset($_SESSION['sidip']);
    }
    
    
    public static function find($attribute, $value)
    {
        if (!array_key_exists($attribute, get_class_vars(__CLASS__))) {
            throw new Exception('Attribute does not exist');
        }

        if (is_array($attribute) && is_array($value)) {
            $filter = implode(" =? AND ", $attribute) . " = ?";
            $args = $value;
        } else {
            $filter = $attribute . "=?";
            $args = array($value);
        }

        return System::query('SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE ' . $filter, $args);
    }

    public static function get($id) 
    {
        $result = Data_User::find("id", $id);
        return $result->fetchObject(__CLASS__);
    }

    public static function isUsernameOrPasswordInUse($username, $email) 
    {
        $query = 'SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE LOWER(username)=? OR LOWER(email)=?';
        $result = System::query($query, array(strtolower($username), strtolower($email)));
        return $result->rowCount();
    } 

    public static function login($username, $password)
    {
        if ($username == '' || $password == '') {
            return null;
        }
        $username = strtolower($username);
        $password = Data_User::getPWHash($username, $password);
        
        $query = 'SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE LOWER(username)=? AND password=?';
        $result = System::query($query, array($username, $password));
        $user = $result->fetchObject(__CLASS__);
        if (!$user)
            return null;
        $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
        return $user;
    }

    public function saveChanges()
    {
        $this->save();
    }
    
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        if ($this->getId()) {
            $setvals = $this->getModifications();
            $setvars = array_keys($setvals);
            $setvals = array_values($setvals);

            if (empty($setvars)) {
                return 0;
            }

            $setvals[] = $this->getId();
            $query = 'UPDATE ' . System::getConfig('tbl_prefix') . 'users SET ' 
                   . implode('=?, ', $setvars) . '=? WHERE id=?';
            System::query($query, $setvals);
        } else {
            $query = 'INSERT INTO ' . System::getConfig('tbl_prefix') . 'users (username, password, admin, email) VALUES (?, ?, ?, ?)';
            $this->setId(System::query($query, array(
                strtolower($this->getUsername()), 
                $this->getPassword(), 
                $this->getAdmin(), 
                $this->getEmail()
            )));
        }
        return $this;
    }

    static function getPWHash($user, $pass) 
    {
        return md5(strtolower($user).":".$pass);
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
}