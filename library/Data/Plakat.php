<?php
/**
 *
 * @deprecated
 */
class Data_Plakat extends Data_Table
{
    protected $id;
    protected $actual_id;
    protected $del;

    public function getId()
    {
        return $this->id;
    }

    private function setId($id)
    {
        if ($this->id) {
            throw new Exception('Changing Id is not possible');
        }

        $this->id = (int)$id;
        $this->logModification('id');
        return $this;
    }


    public function getActualId()
    {
        return $this->actual_id;
    }

    public function setActualId($id)
    {
        $this->actual_id = $id;
        $this->logModification('actual_id');
        return $this;
    }

    public function getDeleted()
    {
        $val = $this->del;
        if ($val === 0 || $val === '0')
            return false;
        if ($val === 1 || $val === '1')
            return true;
        return $val;
    }

    public function setDeleted($deleted)
    {
        if ($deleted !== true && $deleted !== false)
            throw new Exception('This is not a valid "deleted" value');
        $this->del = $deleted;
        $this->logModification('del');
        return $this;
    }


    protected static function getTableName()
    {
        return 'plakat';
    }

    protected function getPrimaryKeyValues()
    {
        return array(
            'id' => $this->getId()
        );
    }

    public function validate()
    {
        if (!$this->getActualId()) {
            return false;
        }
        return true;
    }


    protected function insert($setvals)
    {
        parent::insert($setvals);
        $this->setId(System::lastInsertId());
    }

    public static function get($id)
    {
        $result = Data_Plakat::find("id", $id);
        return $result->fetchObject(__CLASS__);
    }
}