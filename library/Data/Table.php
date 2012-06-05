<?php

abstract class Data_Table extends Data_Abstract
{
    abstract protected static function getTableName();

    abstract protected function getPrimaryKeyValues();

    abstract public function validate();



    private static function tableName()
    {
        return System::getConfig('tbl_prefix') . static::getTableName();
    }
    
    public static function find($attribute, $value)
    {
        if (is_array($attribute) && is_array($value)) {
            $filter = implode(" =? AND ", $attribute) . " = ?";
            $args = $value;
        } else {
            $filter = $attribute . "=?";
            $args = array($value);
        }

        return System::query('SELECT * FROM ' . self::tableName() . 'users WHERE ' . $filter, $args);
    }

    protected function isNew()
    {
        $pks = $this->getPrimaryKeyValues();
        if (count($pks) > 0) {
            $pkvalues = array_values($pks);
            foreach ($pkvalues as $variable) {
                if (!$variable)
                    return true;
            }
            return false;
        } else {
            return true;
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        if ($this->isNew()) {
            $this->insert($this->prepareValues($this->getModifications()));
        } else {
            $this->update($this->prepareValues($this->getModifications()));
        }
        
        $this->modifications = array();
        return $this;
    }

    protected function prepareValues($values)
    {
        return $values;
    }
    
    protected function insert($setvals)
    {
        $setvars = array_keys($setvals);
        $setvals = array_values($setvals);
        
        $params = implode(str_split(str_repeat('?', count($setvars))), ', ');
        $fields = implode($setvars, ', ');
        
        $query = 'INSERT INTO ' . static::tableName() . ' (' . $fields . ') VALUES (' . $params . ')';
        System::query($query, $setvals);
    }

    protected function update($setvals)
    {
        $setvars = array_keys($setvals);
        $setvals = array_values($setvals);

        if (empty($setvars)) {
            return;
        }

        $pkvalues = $this->getPrimaryKeyValues();
        $pknames = array_keys($pkvalues);
        $pkvalues = array_values($pkvalues);
        
        $setvals = array_merge($setvals, $pkvalues);
        $query = 'UPDATE ' . static::tableName() . ' SET ' 
               . implode('=?, ', $setvars) . '=? WHERE ' . implode('=?, ', $pknames) . '=?';
        System::query($query, $setvals);
    }
}