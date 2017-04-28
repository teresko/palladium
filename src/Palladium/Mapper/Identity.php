<?php

namespace Palladium\Mapper;

/**
 * SQL code for locating identity data by token and updating last usage.
 */

use Palladium\Component\SqlMapper;
use Palladium\Entity as Entity;

class Identity extends SqlMapper
{

    /**
     * @param Entity\Identity $entity
     */
    public function store(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "UPDATE $table
                   SET used_on = :used
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':used', $entity->getLastUsed());
        $statement->execute();
    }


    /**
     * @param Entity\Identity $entity
     */
    public function fetch(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "SELECT identity_id      AS id,
                       user_id          AS userId,
                       status           AS status,
                       hash             AS hash,
                       token_expires_on AS tokenEndOfLife
                  FROM $table
                 WHERE token = :token
                   AND token_action = :action
                   AND token_expires_on > :expires";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':token', $entity->getToken());
        $statement->bindValue(':action', $entity->getTokenAction());
        $statement->bindValue(':expires', $entity->getTokenEndOfLife());

        $statement->execute();

        $data = $statement->fetch();

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }


    /**
     * @param Entity\Identity $entity
     */
    public function exists(Entity\Identity $entity) {}
}
