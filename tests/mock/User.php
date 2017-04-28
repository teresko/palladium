<?php

namespace Mock;


use Palladium\Contract\HasId;

class User implements HasId
{

    private $value;


    public function __construct($value)
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

}
