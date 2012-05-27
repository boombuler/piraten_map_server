<?php
abstract class Data_Abstract
{
  protected $modifications = array();

  abstract public static function find($attribute, $value);

  abstract public function save();

  protected function logModification($varname)
  {
    if (!in_array($varname, $this->modifiedValues)) {
      $this->modifications[] = $varname;
    }
  }

  protected function getModifications()
  {
    return $this->modifications;
  }
}