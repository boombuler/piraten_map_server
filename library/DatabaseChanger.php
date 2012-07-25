<?php
class DatabaseChanger
{
  const CHANGER_CLASS_PREFIX = 'Data_Change_';

  private $todo = array();

  private $succeeded = array();

  private $simulate = false;

  public function __construct()
  {
    $this->readTodoList();
  }

  public function simulate()
  {
    $this->simulate = true;
    $this->execute();
  }

  public function execute()
  {
    if (count($this->todo) == 0) {
      print "Nothing todo.\n";
      return;
    }
    print "The following Database Changes need to be executed:\n";
    foreach (array_keys($this->todo) as $task) {
      print $task . PHP_EOL;
    }

    print "\nExecuting tasks:\n";
    foreach ($this->todo as $change) {
      try {
        $this->executeChange($change);
      } catch (Exception $e) {
        print 'Failed in Line ' . $e->getLine() . ': ' . $e->getMessage() . PHP_EOL;
    $success = false;
      }
    }

    print "\nDatabase Changes finished.\n";
  }

  private function executeChange(Data_Change $change)
  {
    $dependencies = $change->getDependencies();
    foreach (array_diff($change->getDependencies(), $this->succeeded) as $dependentChange) {
      $this->executeChange($this->todo[$dependentChange]);
    }
    $start = microtime(true);
    print $change->getId() . "\t";
    if (!$this->simulate) {
      $change();
      System::query('INSERT INTO ' . System::getConfig('tbl_prefix') . 'database_changes SET id=?, status=?', array($change->getId(), $change->getStatus()));
    } else {
      $change->simulate();
    }
    print "success, took " . (round(microtime(true) - $start)) . " seconds.\n";
  }

  private function readTodoList()
  {
    $this->todo = array();
    $this->succeeded = array();
    $todo = array_filter(scandir(dirname(__FILE__) . '/Data/Change/'), array($this, 'filterTodo'));
    try {
      $changeresult = System::query('SELECT id FROM ' . System::getConfig('tbl_prefix') . 'database_changes WHERE status=1');
      while ($change = $changeresult->fetch()) {
        $this->succeeded[] = $change->id;
      }
    } catch (Exception $e) {
      //tk: assume that the table does not exist so far, and continue with all tasks.
    }
	array_walk($todo, array($this, 'fixName'));
    foreach (array_diff($todo, $this->succeeded) as $id) {
      $classname = self::CHANGER_CLASS_PREFIX . $id;
      $this->todo[$id] = new $classname();
    }

    return $this->todo;
  }

  public function filterTodo($item)
  {
    return stripos($item, '.php') !== false && stripos($item, 'exception') === false;
  }

  public function fixName(&$item)
  {
    $item = str_replace('.php', '', $item);
  }
}
?>