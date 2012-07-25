<?php
abstract class Data_Change
{
  protected $id;

  protected $status;

  public function getId()
  {
    return $this->id;
  }

  protected function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  public function getStatus()
  {
    return $this->status;
  }

  protected function setStatus($status)
  {
    $this->status = $status;

    return $this;
  }

  public function simulate()
  {
  }

  /**
   * return an array of changes, that have to be executed successfully before this can be run.
   */
  public function getDependencies()
  {
	return array('CreateDatabaseChangesTable', 'CreateDatabase');
  }

  abstract public function __invoke();
}
?>