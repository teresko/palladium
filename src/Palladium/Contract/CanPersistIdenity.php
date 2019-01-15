<?php

namespace Palladium\Contract;

interface CanPersistIdenity
{
    public function load($identity, string $override = null);
    public function save($identity, string $override = null);
    public function delete($identity, string $override = null);
    public function has($identity, string $override = null);
}
