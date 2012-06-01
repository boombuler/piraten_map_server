<?php

interface IChangableUser extends IUser
{
    public function setPassword($password);
    
    public function setAdmin($value);
    
    public function saveChanges();
}