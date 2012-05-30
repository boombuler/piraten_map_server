<?php
abstract class Data_Abstract
{
  protected $modifications = array();

  abstract public static function find($attribute, $value);

  abstract public function save();

  protected function logModification($varname)
  {
    if (!in_array($varname, $this->modifications)) {
      $this->modifications[] = $varname;
    }
  }

  protected function getModifications()
  {
    $result = array();
    foreach ($this->modifications as $variable) {
        $result[$variable] = $this->$variable;
    }
    return $result;
  }
}