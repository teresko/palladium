<?php

namespace Mock;

class Mapper
{
    private $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function exists()
    {
        return $this->config['exists'];
    }

    public function store($entity)
    {
        $entity->setId(42);
    }
}
