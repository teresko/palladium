<?php

namespace Mock;

use Palladium\Component\SqlMapper;

class Mapper extends SqlMapper
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
