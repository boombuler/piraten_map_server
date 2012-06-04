<?php

abstract class Controller
{
    protected $view;
    public function __invoke()
    {
        try {
            if (!is_callable(array($this, $this->getGetParameter('action')))) {
                return $this->index();
            }
            return call_user_func(array($this, $this->getGetParameter('action')));
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function handleException(Exception $e)
    {
        if (System::getConfig('debug')) {
            print '<h1>' . $e->getMessage() . '</h1>';
            print '<p>File: ' . $e->getFile() . ' Line: ' . $e->getLine() . '</p>';
            print '<p>Query: ' . System::getLastRequest() . '</p>';
            print '<h3>Trace:</h3><pre>' . $e->getTraceAsString() . '</pre>';
        }
    }

    abstract public function index();

    protected function getGetParameter($parameter, $type = FILTER_SANITIZE_STRING)
    {
        return filter_input(INPUT_GET, $parameter, $type);
    }

    protected function getPostParameter($parameter, $type = FILTER_SANITIZE_STRING)
    {
        return filter_input(INPUT_POST, $parameter, $type);
    }

    protected function display()
    {
        ob_start("ob_gzhandler");
        if ($this->view) {
            $path = dirname(__FILE__) . '/' . str_replace('_', '/', $this->view) . '.php';
            if (file_exists) {
                include(dirname(__FILE__) . '/' . str_replace('_', '/', $this->view) . '.php');
            }
        }
    }
}
?>