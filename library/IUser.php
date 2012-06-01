<?php

interface IUser
{
    /**
     * returns the username
     */
    public function getUsername();
    
    /**
     * returns true if the user is an admin
     */
    public function getAdmin();
    
    /**
     * gives a string to check the users type
     */
    public function getType();
    
    /**
     * Checks if the user is still valid.
     * @return bool
     */
    public function isSessionValid();
    
    /**
     * get the default location for that user. Should return an array with lon, lat and zoom
     */
    public function getInitialPosition();
    
    /**
     * closes the session for that user.
     */
    public function logout();
    
    /**
     * Try to login with the given username and password.
     * if successful return the user object, else return null
     * @static
     */
    public static function login($username, $password);
}