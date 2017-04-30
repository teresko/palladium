<?php

namespace Mock;


use Palladium\Contract\HasId;

class Account implements HasId
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


    public function setAlpha($value)
    {

    }


    public function setBetaGamma($value)
    {

    }
}
