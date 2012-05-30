<?php
require_once(dirname(__FILE__). '/System.php');

class WikiConnection
{
    private $snoopy = null;
    private $apipath = "";
    private $wiki_session = false;

    public static function getInstance() {
        if (!$_SESSION['wikiconnection'] instanceof WikiConnection)
            $_SESSION['wikiconnection'] = new WikiConnection;
        return $_SESSION['wikiconnection'];
    }

    private function __construct() {
        $this->snoopy = new Snoopy();

        if (System::getConfig('use_ssl')) {
            $this->snoopy->curl_path = System::getConfig('curl_path');
            $wikiPath = "https://wiki.piratenpartei.de";
        } else {
            $this->snoopy->curl_path = false;
            $wikiPath = "http://wiki.piratenpartei.de";
        }
        $this->apiPath = "$wikiPath/wiki/api.php";
    }

    public static function login($username, $password) {
        $instance = self::getInstance();
        // Make sure that the first username char is uppercase:
        $username = strtoupper(substr($username, 0, 1)) . substr($username, 1, strlen($username) - 1);

        $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'format' => 'php');
        if (!$instance->snoopy->submit($instance->apiPath, $request_vars))
            die("Snoopy error: {$instance->snoopy->error}");
        // We're only really interested in the cookies
        $instance->snoopy->setcookies();
        $array = unserialize($instance->snoopy->results);

        if ($array['login']['result'] == "NeedToken") {
            $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'lgtoken' => $array['login']['token'], 'format' => 'php');
            if (!$instance->snoopy->submit($instance->apiPath, $request_vars))
                die("Snoopy error: {$instance->snoopy->error}");

            // We're only really interested in the cookies
            $instance->snoopy->setcookies();
            $array = unserialize($instance->snoopy->results);
        }

        if ($array['login']['result'] == "Success") {
              //TODO: move the next two lines to a user object:
              $_SESSION['siduser'] = $username;
              $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
              $_SESSION['sidusertype'] = "wiki";
              $instance->wiki_session = $instance->snoopy->cookies;

              return true;
        }
        return false;
    }

    public static function logout() {
        $instance = self::getInstance();
        if ($instance->wiki_session) {
            $instance->snoopy->cookies = $instance->wiki_session;
            $request_vars = array('action' => 'logout', 'format' => 'php');
            if (!$instance->snoopy->submit($instance->apiPath, $request_vars))
                die("Snoopy error: {$instance->snoopy->error}");
            unset($_SESSION['wikiconnection']); // drop current instance
        }
    }

    public static function isSessionValid() {
        $instance = self::getInstance();
        if (!$instance->wiki_session)
            return true; // No session is a valid session.

        $instance->snoopy->cookies = $instance->wiki_session;

        $request_vars = array('action' => 'query', 'meta' => 'userinfo',  'format' => 'php');
        if(!$instance->snoopy->submit($instance->apiPath, $request_vars))
            die("Snoopy error: {$instance->snoopy->error}");
        $array = unserialize($instance->snoopy->results);
        return $_SESSION['siduser'] == $array['query']['userinfo']['name'];
    }

    public static function getUserCategories() {
        $instance = self::getInstance();
        $categories = array('Germany');

        $request_vars = array('action' => 'query', 'prop' => 'categories', 'titles' => 'Benutzer:' . $_SESSION['siduser'], 'format' => 'php');

        if ($instance->snoopy->submit($instance->apiPath, $request_vars)) {
            $array = unserialize($instance->snoopy->results);
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

?>