<?php

namespace Palladium\Mapper;

/**
 * All of the SQL code related to the storage and retrieval of cookie-based identities.
 * Probably need some cleanup in `store()` method
 */

use Palladium\Component\SqlMapper;
use Palladium\Entity\Authentication as Entity;
use Palladium\Contract\CanPersistIdentity;

class CookieIdentity extends SqlMapper implements CanPersistIdentity
{

    /**
     * @param Entity\CookieIdentity $entity
     */
    public function exists(Entity\CookieIdentity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "SELECT 1
                  FROM $table AS Identities
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
        $table = $this->config['accounts']['identities'];

        $sql = "SELECT identity_id                  AS id,
                       hash                         AS hash,
                       UNIX_TIMESTAMP(expires_on)   AS expiresOn
                  FROM $table AS Identities
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
        $table = $this->config['accounts']['identities'];
        $sql = "INSERT INTO {$table}
                       (user_id, type, status, identifier, fingerprint, hash, expires_on)
                VALUES (:user, :type, :status, :series, :fingerprint, :hash, FROM_UNIXTIME(:expires))";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':user', $entity->getUserId());
        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':series', $entity->getSeries());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':expires', $entity->getExpiresOn());

        $statement->execute();

        $entity->setId($this->connection->lastInsertId());
    }


    private function updateCookie(Entity\CookieIdentity $entity)
    {
        $table = $this->config['accounts']['identities'];
        $active = Entity\Identity::STATUS_ACTIVE;

        $sql = "UPDATE {$table}
                   SET status = :status,
                       hash = :hash,
                       used_on = NOW(),
                       expires_on = FROM_UNIXTIME(:expires)
                 WHERE identity_id = :id
                   AND status = {$active}";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':expires', $entity->getExpiresOn());

        $statement->execute();
    }
}
