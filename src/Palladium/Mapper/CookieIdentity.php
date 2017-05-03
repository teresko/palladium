<?php

namespace Palladium\Mapper;

/**
 * All of the SQL code related to the storage and retrieval of cookie-based identities.
 * Probably need some cleanup in `store()` method
 */

use Palladium\Component\DataMapper;
use Palladium\Entity as Entity;

class CookieIdentity extends DataMapper
{

    /**
     * @param Entity\CookieIdentity $entity
     */
    public function fetch(Entity\CookieIdentity $entity)
    {
        $sql = "SELECT identity_id  AS id,
                       parent_id    AS parentId,
                       hash         AS hash,
                       expires_on   AS expiresOn
                  FROM {$this->table} AS Identities
                 WHERE type = :type
                   AND account_id = :account
                   AND identifier = :identifier
                   AND fingerprint = :fingerprint
                   AND status = :status";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':account', $entity->getAccountId());
        $statement->bindValue(':identifier', $entity->getSeries());
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
                       (account_id, parent_id, type, status, identifier, fingerprint, hash, created_on, expires_on)
                VALUES (:account, :parent, :type, :status, :identifier, :fingerprint, :hash, :created, :expires)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':account', $entity->getAccountId());
        $statement->bindValue(':parent', $entity->getParentId());
        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':identifier', $entity->getSeries());
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
