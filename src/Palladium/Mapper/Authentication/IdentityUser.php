<?php

namespace Mapper\Authentication;

use Component\SqlMapper;
use Entity\Authentication as Entity;

class IdentityUser extends SqlMapper
{

    /**
     * @param Entity\Identity $entity
     */
    public function store(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "UPDATE $table
                   SET user_id = :user
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':user', $entity->getUserId());
        $statement->execute();
    }
}
