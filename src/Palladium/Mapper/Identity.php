<?php

namespace Palladium\Mapper;

/**
 * SQL code for locating identity data by token and updating last usage.
 */

use Palladium\Component\DataMapper;
use Palladium\Entity as Entity;
use PDO;

class Identity extends DataMapper
{

    /**
     * @param Entity\Identity $entity
     */
    public function store(Entity\Identity $entity)
    {
        $sql = "UPDATE {$this->table}
                   SET used_on = :used,
                       status = :status
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':used', $entity->getLastUsed());
        $statement->bindValue(':status', $entity->getStatus());

        $statement->execute();
    }


    /**
     * @param Entity\Identity $entity
     */
    public function remove(Entity\Identity $entity)
    {
        $sql = "DELETE FROM {$this->table} WHERE identity_id: id";
        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->execute();
    }


    /**
     * @param Entity\Identity $entity
     */
    public function fetch(Entity\Identity $entity)

    {
        if ($entity->getId()) {
            $this->fetchById($entity);
            return;
        }

        $this->fetchByToken($entity);
    }


    private function fetchById(Entity\Identity $entity)
    {
        $sql = "SELECT identity_id      AS id,
                       parent_id        AS parentId,
                       account_id       AS accountId,
                       status           AS status,
                       hash             AS hash,
                       token_expires_on AS tokenEndOfLife
                  FROM {$this->table}
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());

        $statement->execute();

        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }


    private function fetchByToken(Entity\Identity $entity)
    {
        $sql = "SELECT identity_id      AS id,
                       parent_id        AS parentId,
                       account_id       AS accountId,
                       status           AS status,
                       hash             AS hash,
                       token_expires_on AS tokenEndOfLife
                  FROM {$this->table}
                 WHERE token = :token
                   AND token_action = :action
                   AND token_expires_on > :expires";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':token', $entity->getToken());
        $statement->bindValue(':action', $entity->getTokenAction());
        $statement->bindValue(':expires', $entity->getTokenEndOfLife());

        $statement->execute();

        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }
}
