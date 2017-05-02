<?php

namespace Palladium\Mapper;

use Palladium\Component\SqlMapper;
use Palladium\Entity as Entity;

class IdentityAccount extends SqlMapper
{

    /**
     * @param Entity\Identity $entity
     */
    public function store(Entity\Identity $entity)
    {
        $sql = "UPDATE {$this->table}
                   SET account_id = :account
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':account', $entity->getAccountId());
        $statement->execute();
    }
}
