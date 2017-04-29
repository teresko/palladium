<?php

namespace Mock;

use Palladium\Contract\CanCreateMapper;

class Factory implements CanCreateMapper
{

    private $pool = [];

    public function __construct(array $pool = [])
    {
        $this->pool = $pool;
    }


    public function create(string $name)
    {
        if (array_key_exists($name, $this->pool)) {
            return $this->pool[$name];
        }
    }
}
