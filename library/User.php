<?php

class User
{
    public static function current()
    {
        if (isset($_SESSION['user']))
            return unserialize($_SESSION['user']);
        return null;
    }
    
    public static function setCurrent($user)
    {
        if ($user instanceof IUser)
            $_SESSION['user'] = serialize($user);
        else
            unset($_SESSION['user']);
    }

    public static function login($username, $password)
    {
        $user = Data_User::login($username, $password);
        if (!($user instanceof IUser))
            $user = Wiki_User::login($username, $password);
            
        self::setCurrent($user);
        
        return $user;
    }
    
    public static function logout()
    {
        $user = self::current();
        if ($user instanceof IUser)
            $user->logout();
        self::setCurrent(null);
    }
    
    public static function validateSession()
    {
        $user = self::current();
        if ($user instanceof IUser) {
            if ($user->isSessionValid())
                return true;
            else
                self::logout();
        }
        return false;
    }
    
    public static function isAdmin()
    {
        $user = self::current();
        if ($user instanceof IUser) {
            return $user->getAdmin();
        }
        return false;
    }
}