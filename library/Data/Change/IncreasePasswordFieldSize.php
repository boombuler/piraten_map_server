<?php
class Data_Change_IncreasePasswordFieldSize extends Data_Change
{
  public function __construct()
  {
    $this->setId('IncreasePasswordFieldSize');
  }

  public function __invoke()
  {
    $result = System::query('ALTER TABLE ' . System::getConfig('tbl_prefix') . 'users MODIFY password VARCHAR(60) NOT NULL');
    $this->setStatus(true);
    return true;
  }
}
?>