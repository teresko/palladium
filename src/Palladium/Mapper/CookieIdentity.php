<?php

namespace Palladium\Mapper;

/**
 * All of the SQL code related to the storage and retrieval of cookie-based identities.
 * Probably need some cleanup in `store()` method
 */

use Palladium\Component\SqlMapper;
use Palladium\Entity as Entity;

class CookieIdentity extends SqlMapper
{

    /**
     * @param Entity\CookieIdentity $entity
     */
    public function exists(Entity\CookieIdentity $entity)
    {
        $sql = "SELECT 1
                  FROM {$this->table} AS Identities
                 WHERE type = :type
                   AND user_id = :user
                   AND identifier = :series
                   AND fingerprint = :fingerprint";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':user', $entity->getUserId());
        $statement->bindValue(':series', $entity->getSeries());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());

        $statement->execute();

        $data = $statement->fetch();

        return empty($data) === false;
    }


    /**
     * @param Entity\CookieIdentity $entity
     */
    public function fetch(Entity\CookieIdentity $entity)
    {
        $sql = "SELECT identity_id  AS id,
                       hash         AS hash,
                       expires_on   AS expiresOn
                  FROM {$this->table} AS Identities
                 WHERE type = :type
                   AND user_id = :user
                   AND identifier = :series
                   AND fingerprint = :fingerprint
                   AND status = :status";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':user', $entity->getUserId());
        $statement->bindValue(':series', $entity->getSeries());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());

        $statement->execute();

        $data = $statement->fetch();

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }


    public function store(Entity\CookieIdentity $entity)
    {
        if ($entity->getId() === null) {
            $this->createCookie($entity);
            return;
        }

        $this->updateCookie($entity);
    }


    private function createCookie(Entity\CookieIdentity $entity)
    {
        $sql = "INSERT INTO {$this->table}
                       (user_id, type, status, identifier, fingerprint, hash, created_on, expires_on)
                VALUES (:user, :type, :status, :series, :fingerprint, :hash, :created, :expires)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':user', $entity->getUserId());
        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':series', $entity->getSeries());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':expires', $entity->getExpiresOn());
        $statement->bindValue(':created', time());

        $statement->execute();

        $entity->setId($this->connection->lastInsertId());
    }


    private function updateCookie(Entity\CookieIdentity $entity)
    {
        $active = Entity\Identity::STATUS_ACTIVE;

        $sql = "UPDATE {$this->table}
                   SET status = :status,
                       hash = :hash,
                       used_on = :used,
                       expires_on = :expires
                 WHERE identity_id = :id
                   AND status = {$active}";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':expires', $entity->getExpiresOn());
        $statement->bindValue(':used', time());

        $statement->execute();
    }
}
