<?php

namespace Palladium\Contract;

use Palladium\Entity\Identity;

interface CanPersistIdentity
{
    public function exists(Identity $entity);
    public function store(Identity $entity);
    public function fetch(Identity $entity);
}
