<?php

namespace Palladium\Mapper;

use Palladium\Component\SqlMapper;
use Palladium\Entity as Entity;

class IdentityUser extends SqlMapper
{

    /**
     * @param Entity\Identity $entity
     */
    public function store(Entity\Identity $entity)
    {
        $sql = "UPDATE {$this->table}
                   SET user_id = :user
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':user', $entity->getUserId());
        $statement->execute();
    }
}
