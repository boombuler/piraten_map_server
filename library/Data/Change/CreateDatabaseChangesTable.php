<?php
class Data_Change_CreateDatabaseChangesTable extends Data_Change
{
  public function __construct()
  {
    $this->setId('CreateDatabaseChangesTable');
  }

  public function __invoke()
  {
    $result = System::query('CREATE TABLE IF NOT EXISTS ' . System::getConfig('tbl_prefix') . 'database_changes '
                          . '(
                               id varchar(255) not null,
                   status tinyint(1) not null default 0,
                   primary key(id)
                 )');
    $this->setStatus(true);
    return true;
  }
  
  public function getDependencies()
  {
    return array();
  }
}
?>