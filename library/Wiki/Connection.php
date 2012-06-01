<?php
/**
 * Encapsulates the communication with the wiki system.
 */
class Wiki_Connection
{
    /**
     * @var bool
     * @static
     */
    private static $initiated = false;

    /**
     * @var Snoopy
     */
    private static $snoopy = null;
    /**
     * @var string
     */
    private static $apiPath = '';

    private static function init() 
    {
        if (self::$initiated) {
            return;
        }

        self::$snoopy = new Snoopy();
        if (System::getConfig('use_ssl')) {
            self::$snoopy->curl_path = System::getConfig('curl_path');
            $wikiPath = 'https://wiki.piratenpartei.de';
        } else {
            self::$snoopy->curl_path = false;
            $wikiPath = 'http://wiki.piratenpartei.de';
        }
        self::$apiPath = $wikiPath . '/wiki/api.php';

        self::$initiated = true;
    }

    public static function login($username, $password) 
    {
        self::init();
        // Make sure that the first username char is uppercase:
        $username = strtoupper(substr($username, 0, 1)) . substr($username, 1, strlen($username) - 1);

        $request_vars = array(
            'action' => 'login', 
            'lgname' => $username, 
            'lgpassword' => $password, 
            'format' => 'php'
        );
        if (!self::$snoopy->submit(self::$apiPath, $request_vars)) {
            if (System::getConfig('debug'))
                die('Snoopy error: ' . self::$snoopy->error);
            return null;
        }
        // We're only really interested in the cookies
        self::$snoopy->setcookies();
        $array = unserialize(self::$snoopy->results);

        if ($array['login']['result'] == 'NeedToken') {
            $request_vars = array(
                'action' => 'login', 
                'lgname' => $username, 
                'lgpassword' => $password, 
                'lgtoken' => $array['login']['token'], 
                'format' => 'php'
            );
            if (!self::$snoopy->submit(self::$apiPath, $request_vars))
                die('Snoopy error: ' . self::$snoopy->error);

            // We're only really interested in the cookies
            self::$snoopy->setcookies();
            $array = unserialize(self::$snoopy->results);
        }

        if ($array['login']['result'] == 'Success') {
            return array(
                'username' => $username,
                'session' => self::$snoopy->cookies
            );
        }
        return null;
    }

    public static function logout($session) 
    {
        self::init();
        if ($session) {
            self::$snoopy->cookies = $session;
            $request_vars = array(
                'action' => 'logout', 
                'format' => 'php'
            );
            if (!self::$snoopy->submit(self::$apiPath, $request_vars) && System::getConfig('debug'))
                die('Snoopy error: ' . self::$snoopy->error);
        }
    }

    public static function getSessionUsername($session) 
    {
        self::init();

        self::$snoopy->cookies = $session;

        $request_vars = array(
            'action' => 'query', 
            'meta' => 'userinfo',  
            'format' => 'php'
        );
        if(!self::$snoopy->submit(self::$apiPath, $request_vars) && System::getConfig('debug'))
            die('Snoopy error: ' . self::$snoopy->error);
        $array = unserialize(self::$snoopy->results);
        return $array['query']['userinfo']['name'];
    }

    public static function getUserCategories($session, $username) 
    {
        $instance = self::getInstance();
        self::$snoopy->cookies = $session;
        
        $categories = array('Germany');

        $request_vars = array(
            'action' => 'query', 
            'prop' => 'categories', 
            'titles' => 'Benutzer:' . $username, 
            'format' => 'php'
        );

        if (self::$snoopy->submit(self::$apiPath, $request_vars)) {
            $array = unserialize(self::$snoopy->results);
            if (($array) && ($array['query']) && ($array['query']['pages'])) {
                $pages = $array['query']['pages'];
                reset($pages);
                while (list($key, $val) = each($pages)) {
                    if (($val) && ($val['categories'])) {
                        $cats = $val['categories'];
                        reset($cats);
                        while (list($k, $cat) = each($cats)) {
                            if (($cat) && ($cat['title']))
                                $categories[] = $cat['title'];
                        }
                    }
                }
            }
        }
        return $categories;
    }
}