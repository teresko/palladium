<?php

namespace Mock;

use Palladium\Contract\HasId;

class RepoEntity implements HasId
{
    private $value;


    public function __construct($value = 'default')
    {
        $this->value = $value;
    }


    public function getId()
    {
        return $this->value;
    }


    public function setId($value)
    {
        $this->value = $value;
    }


    public function setAction($action)
    {
        $this->value = $action;
    }


    public function getAction()
    {
        return $this->value;
    }
}
