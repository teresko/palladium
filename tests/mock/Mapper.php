<?php

namespace Mock;

use Palladium\Component\DataMapper;

class Mapper extends DataMapper
{
    private $foo;
    private $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }


    public function getConnection()
    {
        return $this->foo;
    }


    public function getTable()
    {
        return $this->bar;
    }
}
