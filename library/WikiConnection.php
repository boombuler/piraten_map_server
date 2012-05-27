<?php
require_once(dirname(__FILE__). '/System.php');

class WikiConnection
{
    private $snoopy;
    private $apipath;
    private $wiki_session = false;

    public static function getInstance() {
        if (!isset($_SESSION['wikiconnection']))
            $_SESSION['wikiconnection'] = new WikiConnection;
        return $_SESSION['wikiconnection'];
    }

    private function __construct() {
        $this->$snoopy = new Snoopy;

        if ($use_ssl) {
            $snoopy->curl_path = $curl_path;
            $wikiPath = "https://wiki.piratenpartei.de";
        } else {
            $snoopy->curl_path = false;
            $wikiPath = "http://wiki.piratenpartei.de";
        }
        $this->apiPath = "$wikiPath/wiki/api.php";
    }

    public static function __callStatic($method, $arguments) {
        if (!is_callable(array(self::getInstance(), $method))) {
            throw new Exception('Method ' . $method . ' does not exist.');
        }

        return call_user_func(array(self::getInstance(), $method), $arguments);
    }

    public function login($username, $password) {
        // Make sure that the first username char is uppercase:
        $username = strtoupper(substr($username, 0, 1)) . substr($username, 1, strlen($username) - 1);

        $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'format' => 'php');
        if (!$this->snoopy->submit($this->apiPath, $request_vars))
            die("Snoopy error: {$this->snoopy->error}");
        // We're only really interested in the cookies
        $this->snoopy->setcookies();
        $array = unserialize($this->snoopy->results);

        if ($array['login']['result'] == "NeedToken") {
            $request_vars = array('action' => 'login', 'lgname' => $username, 'lgpassword' => $password, 'lgtoken' => $array['login']['token'], 'format' => 'php');
            if (!$this->snoopy->submit($this->apiPath, $request_vars))
                die("Snoopy error: {$this->snoopy->error}");

            // We're only really interested in the cookies
            $this->snoopy->setcookies();
            $array = unserialize($this->snoopy->results);
        }

        if ($array['login']['result'] == "Success") {
              //TODO: move the next two lines to a user object:
              $_SESSION['siduser'] = $username;
              $_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];
              $this->wiki_session = $this->snoopy->cookies;

              return true;
        }
        return false;
    }

    public function logout() {
        if ($this->wiki_session) {
            $this->snoopy->cookies = $this->wiki_session;
            $request_vars = array('action' => 'logout', 'format' => 'php');
            if (!$this->snoopy->submit($this->apiPath, $request_vars))
                die("Snoopy error: {$this->snoopy->error}");
            $this->wiki_session = false;
        }
    }

    public function isSessionValid() {
        if (!$this->wiki_session)
            return true; // No session is a valid session.

        $this->snoopy->cookies = $this->wiki_session;

        $request_vars = array('action' => 'query', 'meta' => 'userinfo',  'format' => 'php');
        if(!$this->snoopy->submit($this->$apiPath, $request_vars))
            die("Snoopy error: {$this->snoopy->error}");
        $array = unserialize($this->snoopy->results);
        return $_SESSION['siduser'] == $array['query']['userinfo']['name'];
    }

    public function getUserCategories() {
        $categories = array('Germany');

        $request_vars = array('action' => 'query', 'prop' => 'categories', 'titles' => 'Benutzer:' . $_SESSION['siduser'], 'format' => 'php');

        if ($this->snoopy->submit($this->apiPath, $request_vars)) {
            $array = unserialize($this->snoopy->results);
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