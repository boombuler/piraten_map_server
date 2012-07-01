<?php

class CronUser implements IUser
{
    public function getUsername()
    {
        return 'CRONJOB';
    }

    public function getAdmin()
    {
        return false;
    }

    public function getType()
    {
        return 'cron';
    }

    public function isSessionValid()
    {
        return true;
    }

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
    }

    public static function login($username = '', $password = '')
    {
        return new CronUser();
    }

}