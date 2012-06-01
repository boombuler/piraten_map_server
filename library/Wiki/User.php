<?php

class Wiki_User implements IUser
{
    private $session;
    private $username;
    private $loginip;
    private $initposition = null;

    /**
     * returns the username
     */
    public function getUsername() 
    {
        return $this->username;
    }
    
    /**
     * returns true if the user is an admin
     */
    public function getAdmin()
    {
        return false;
    }
    
    /**
     * gives a string to check the users type
     */
    public function getType()
    {
        return 'wiki';
    }
    
    /**
     * Checks if the user is still valid.
     * @return bool
     */
    public function isSessionValid() 
    {
        return ($_SERVER['REMOTE_ADDR'] == $this->loginip) 
            && ($this->username == Wiki_Connection::getSessionUsername($this->session));
    }
    
    /**
     * get the default location for that user. Should return an array with lon, lat and zoom
     */
    public function getInitialPosition()
    {
        if ($this->initposition == null) {
            $categories = Wiki_Connection::getUserCategories($this->session, $this->username);
            $filter = "category = ?";
            for ($i = 1; $i < count($categories); $i++) 
                $filter .= " OR category = ?";
            $query = 'SELECT lat, lon, zoom FROM ' 
                   . System::getConfig('tbl_prefix') . 'regions'
                   . ' WHERE ' . $filter . 'order by zoom desc limit 1';
            $res = System::query($query, $categories);
            if ($obj = $res->fetch()) {
                $this->initposition = array(
                    'lat' => $obj->lat,
                    'lon' => $obj->lon,
                    'zoom' => $obj->zoom
                );
            } else {
                $this->initposition = array(
                    'lat' => System::getConfig('start_lat'),
                    'lon' => System::getConfig('start_lon'),
                    'zoom' => System::getConfig('start_zoom'),
                );
            }
        }
        return $this->initposition;
    }
    
    /**
     * closes the session for that user.
     */
    public function logout()
    {
        Wiki_Connection::logout($this->session);
    }
    
    /**
     * Try to login with the given username and password.
     * if successful return the user object, else return null
     * @static
     */
    public static function login($username, $password) 
    {
        $userdata = Wiki_Connection::login($username, $password);
        if (!$userdata)
            return null;
        $user = new Wiki_User;
        $user->session = $userdata['session'];
        $user->username = $userdata['username'];
        $user->loginip = $_SERVER['REMOTE_ADDR'];
        $user->getInitialPosition();
        return $user;
    }
}