<?php

namespace Palladium\Mapper;

/**
 * SQL logic for authentication attemps using username/password
 */

use Palladium\Component\DataMapper;
use Palladium\Entity as Entity;
use PDOStatement;

class OneTimeIdentity extends DataMapper
{

    /**
     * @param Entity\OneTimeIdentity $entity
     */
    public function fetch(Entity\OneTimeIdentity $entity)
    {
        $status = Entity\Identity::STATUS_ACTIVE;

        $sql = "SELECT identity_id      AS id,
                       account_id       AS accountId,
                       hash             AS hash,
                       status           AS status
                  FROM {$this->table}
                 WHERE type = :type
                   AND status = {$status}
                   AND identifier = :nonce";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':nonce', $entity->getNonce());
        $statement->bindValue(':type', $entity->getType());

        $statement->execute();

        $data = $statement->fetch();

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }


    /**
     * @param Entity\OneTimeIdentity $entity
     */
    public function store(Entity\OneTimeIdentity $entity)
    {
        if ($entity->getId() === null) {
            $this->createIdentity($entity);
            return;
        }

        $this->updateIdentity($entity);
    }


    private function createIdentity(Entity\OneTimeIdentity $entity)
    {
        $sql = "INSERT INTO {$this->table}
                       (account_id, type, status, identifier, fingerprint, hash, created_on, expires_on)
                VALUES (:account, :type, :status, :identifier, :fingerprint, :hash, :created, :expires)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':account', $entity->getAccountId());
        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':identifier', $entity->getNonce());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':created', time());
        $statement->bindValue(':expires', $entity->getExpiresOn());

        $statement->execute();
    }


    private function updateIdentity(Entity\OneTimeIdentity $entity)
    {
        $sql = "UPDATE {$this->table}
                   SET status = :status,
                       used_on = :used
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':used', time());

        $statement->execute();
    }

}
