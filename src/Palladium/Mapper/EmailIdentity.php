<?php

namespace Palladium\Mapper;

/**
 * SQL logic for authentication attemps using username/password
 */

use Palladium\Component\SqlMapper;
use Palladium\Entity as Entity;
use Palladium\Contract\CanPersistIdentity;

class EmailIdentity extends SqlMapper implements CanPersistIdentity
{

    /**
     * @param Entity\Identity $entity
     */
    public function exists(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "SELECT 1
                  FROM {$table}
                 WHERE type = :type
                   AND fingerprint = :fingerprint
                   AND identifier = :identifier
                   AND (expires_on IS NULL OR expires_on > :now)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', Entity\Identity::TYPE_PASSWORD);
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':identifier', $entity->getIdentifier());
        $statement->bindValue(':now', time());

        $statement->execute();
        $data = $statement->fetch();

        return empty($data) === false;
    }


    /**
     * @param Entity\Identity $entity
     */
    public function fetch(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "SELECT identity_id      AS id,
                       user_id          AS userId,
                       hash             AS hash,
                       status           AS status,
                       token            AS token,
                       token_action     AS tokenAction,
                       token_expires_on AS tokenEndOfLife
                  FROM $table
                 WHERE type = :type
                   AND fingerprint = :fingerprint
                   AND identifier = :identifier";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':identifier', $entity->getIdentifier());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());

        $statement->execute();

        $data = $statement->fetch();

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }


    /**
     * @param Entity\Identity $entity
     */
    public function store(Entity\Identity $entity)
    {
        if ($entity->getId() === null) {
            $this->createIdentity($entity);
            return;
        }

        $this->updateIdentity($entity);
    }


    private function createIdentity(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "INSERT INTO {$table}
                       (type, status, identifier, fingerprint, hash, created_on, token, token_action, token_expires_on )
                VALUES (:type, :status, :identifier, :fingerprint, :hash, :created, :token, :action, :token_eol)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', Entity\Identity::TYPE_PASSWORD);
        $statement->bindValue(':status', Entity\Identity::STATUS_NEW);
        $statement->bindValue(':identifier', $entity->getIdentifier());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':token', $entity->getToken());
        $statement->bindValue(':action', $entity->getTokenAction());
        $statement->bindValue(':token_eol', $entity->getTokenEndOfLife());
        $statement->bindValue(':created', time());


        $statement->execute();

        $entity->setId($this->connection->lastInsertId());
    }


    private function updateIdentity(Entity\Identity $entity)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "UPDATE {$table}
                   SET hash = :hash,
                       status = :status,
                       expires_on = :expires,
                       token = :token,
                       token_action = :action,
                       token_expires_on = :token_eol
                 WHERE identity_id = :id";

         $statement = $this->connection->prepare($sql);

         $statement->bindValue(':id', $entity->getId());
         $statement->bindValue(':hash', $entity->getHash());
         $statement->bindValue(':status', $entity->getStatus());
         $statement->bindValue(':expires', $entity->getExpiresOn());
         $statement->bindValue(':token', $entity->getToken());
         $statement->bindValue(':action', $entity->getTokenAction());
         $statement->bindValue(':token_eol', $entity->getTokenEndOfLife());

         $statement->execute();
    }
}
