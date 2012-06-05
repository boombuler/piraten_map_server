<?php

class Data_Log extends Data_Table
{
    const SUBJECT_ADD = 'add';
    const SUBJECT_DEL = 'del';
    const SUBJECT_CHANGE = 'change';

    protected $id;
    protected $plakat_id;
    protected $user;
    protected $timestamp;
    protected $subject;
    protected $what;

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

    public function getPlakatId()
    {
        return $this->plakat_id;
    }

    public function setPlakatId($pid)
    {
        $this->plakat_id = (int)$pid;
        $this->logModification('plakat_id');
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }
    public function setUser($user)
    {
        if ($user instanceof IUser) {
            $user = $user->getUsername();
        }
        $this->user = $user;
        $this->logModification('user');
        return $this;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subj)
    {
        if ($subj != Data_Log::SUBJECT_ADD && $subj != Data_Log::SUBJECT_DEL && $subj != Data_Log::SUBJECT_CHANGE) {
            throw new Exception('Invalid value for subject');
        }
        $this->subject = $subj;
        $this->logModification('subject');
        return this;
    }

    public function getWhat()
    {
        return $this->what;
    }

    public function setWhat($what)
    {
        $this->what = $what;
        $this->logModification('what');
    }

    public function validate() 
    {
        if (!$this->getPlakatId()) {
            return false;
        }
        if (!$this->getUser()) {
            return false;
        }
        if (!$this->getSubject()) {
            return false;
        }
        return true;
    }

    protected static function getTableName()
    {
        return "log";
    }

    protected function getPrimaryKeyValues()
    {
        return array(
            'id' => $this->getId()
        );
    }

    protected function insert($setvals)
    {
        parent::insert($setvals);
        $this->setId(System::lastInsertId());
    }

    public static function add($plakatid, $subject, $what = null) {
        $log = new Data_Log;
        $log->setPlakatId($plakatid)->setUser(User::current())->setSubject($subject);
        if ($what) {
            $log->setWhat($what);
        }
        $log->save();
        return $log;
    }

}