<?php

namespace Palladium\Mapper;

/**
 * SQL logic for authentication attemps using username/password
 */

use Palladium\Component\DataMapper;
use Palladium\Entity as Entity;

class NonceIdentity extends DataMapper
{

    /**
     * @param Entity\NonceIdentity $entity
     */
    public function fetch(Entity\NonceIdentity $entity)
    {
        $sql = "SELECT identity_id      AS id,
                       account_id       AS accountId,
                       hash             AS hash,
                       status           AS status,
                       expires_on       AS expiresOn
                  FROM {$this->table}
                 WHERE type = :type
                   AND status = :status
                   AND identifier = :identifier";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':identifier', $entity->getIdentifier());
        $statement->bindValue(':status', Entity\Identity::STATUS_ACTIVE);
        $statement->bindValue(':type', $entity->getType());

        $statement->execute();

        $data = $statement->fetch();

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }


    /**
     * @param Entity\NonceIdentity $entity
     */
    public function store(Entity\NonceIdentity $entity)
    {
        if ($entity->getId() === null) {
            $this->createIdentity($entity);
            return;
        }

        $this->updateIdentity($entity);
    }


    private function createIdentity(Entity\NonceIdentity $entity)
    {
        $sql = "INSERT INTO {$this->table}
                       (account_id, type, status, identifier, fingerprint, hash, created_on, expires_on)
                VALUES (:account, :type, :status, :identifier, :fingerprint, :hash, :created, :expires)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':account', $entity->getAccountId());
        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':identifier', $entity->getIdentifier());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':created', time());
        $statement->bindValue(':expires', $entity->getExpiresOn());

        $statement->execute();
    }


    private function updateIdentity(Entity\NonceIdentity $entity)
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
