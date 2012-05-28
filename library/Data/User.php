<?php
class Data_User extends Data_Abstract
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
    $username = strtolower($username);
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
    $this->password = $this->getPWHash($this->getUsername(), $password);
    $this->logModification('password');
    return $this;
  }

  public function setRandomPassword() {
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

  public function getAdmin() {
    return $this->admin;
  }

  public function setAdmin($admin) {
    if ($admin !== true && $admin !== false)
        throw new Exception('This is not a valid admin value');
    $this->admin = $admin;
    $this->logModification('admin');
    return $this;
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

  public static function get($id) {
    $result = Data_User::find("id", $id);
    return $result->fetchObject(__CLASS__);
  }

  public static function isUsernameOrPasswordInUse($username, $email) {
    $result = System::query('SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE username=? OR LOWER(email)=?',
                            array(strtolower($username), strtolower($email)));
    return $result->rowCount();
  }

  public static function login($username, $password)
  {
    if ($username == '' || $password == '') {
      throw new Exception('Wrong password');
    }
    $username = strtolower($username);
    $password = $this->getPWHash($username, $password);
    $result = System::query('SELECT * FROM ' . System::getConfig('tbl_prefix') . 'users WHERE username=? AND password=?', array($username, $password));
    $user = $result->fetchObject(__CLASS__);

    return $user;
  }

  public function save()
  {
    if (!$this->validate()) {
      return false;
    }
    if ($this->getId()) {
      $setvals = $this->getModifications();
      $setvars = array_keys($setvals);

      if (empty($setvars)) {
        return 0;
      }

      $setvals[] = $this->getId();
      System::query('UPDATE ' . System::getConfig('tbl_prefix') . 'users SET ' . implode(', ', $setvars) . ' WHERE id=?', $setvals);
    } else {}
        $this->setId(System::query('INSERT INTO ' . System::getConfig('tbl_prefix') . 'users (username, password, admin, email) VALUES (?, ?, ?, ?)',
                               array(strtolower($this->getUsername()), $this->getPassword(), $this->getAdmin(), $this->getEmail())));
    }

    return $this;
  }

  function getPWHash($user, $pass) {
    return md5(strtolower($user).":".$pass);
  }

  public function validate() {
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