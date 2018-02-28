<?php

namespace Mock;

use Palladium\Component\DataMapper;

class RepoMapper extends DataMapper
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function fetch(RepoEntity $entity)
    {
        $entity->setAction('loaded');
        return $this->value;
    }

    public function store(RepoEntity $entity)
    {
        $entity->setAction('saved');
        return $this->value;
    }

    public function remove(RepoEntity $entity)
    {
        $entity->setAction('deleted');
        return $this->value;
    }

    public function exists(RepoEntity $entity)
    {
        return $this->value;
    }
}
