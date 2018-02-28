<?php

namespace Palladium\Mapper;

/**
 * SQL logic for authentication attempts using username/password
 */

use Palladium\Component\DataMapper;
use Palladium\Entity as Entity;
use PDOStatement;
use PDO;

class StandardIdentity extends DataMapper
{

    /**
     * @param Entity\StandardIdentity $entity
     */
    public function exists(Entity\StandardIdentity $entity)
    {
        $sql = "SELECT 1
                  FROM {$this->table}
                 WHERE type = :type
                   AND fingerprint = :fingerprint
                   AND identifier = :email
                   AND (expires_on IS NULL OR expires_on > :now)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', Entity\StandardIdentity::TYPE_EMAIL);
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':email', $entity->getIdentifier());
        $statement->bindValue(':now', time());

        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        return empty($data) === false;
    }


    /**
     * @param Entity\StandardIdentity $entity
     */
    public function fetch(Entity\StandardIdentity $entity)
    {
        if ($entity->getId()) {
            $this->fetchById($entity);
            return;
        }

        $this->fetchByIdentifier($entity);
    }


    private function fetchByIdentifier(Entity\StandardIdentity $entity)
    {
        $sql = "SELECT identity_id      AS id,
                       account_id       AS accountId,
                       hash             AS hash,
                       status           AS status,
                       used_on          AS lastUsed,
                       token            AS token,
                       token_action     AS tokenAction,
                       token_expires_on AS tokenEndOfLife,
                       token_payload    AS tokenPayload
                  FROM {$this->table}
                 WHERE type = :type
                   AND fingerprint = :fingerprint
                   AND identifier = :email";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':email', $entity->getIdentifier());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());

        $statement->execute();

        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            if ($data['tokenPayload'] !== null) {
                $data['tokenPayload'] = json_decode($data['tokenPayload'], true);
            }
            $this->applyValues($entity, $data);
        }
    }


    private function fetchById(Entity\StandardIdentity $entity)
    {
        $sql = "SELECT identity_id      AS id,
                       identifier       AS identifier,
                       account_id       AS accountId,
                       hash             AS hash,
                       status           AS status,
                       used_on          AS lastUsed,
                       token            AS token,
                       token_action     AS tokenAction,
                       token_expires_on AS tokenEndOfLife,
                       token_payload    AS tokenPayload
                  FROM {$this->table}
                 WHERE type = :type
                   AND identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', $entity->getType());
        $statement->bindValue(':id', $entity->getId());

        $statement->execute();

        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            if ($data['tokenPayload'] !== null) {
                $data['tokenPayload'] = json_decode($data['tokenPayload'], true);
            }
            $this->applyValues($entity, $data);
        }
    }


    /**
     * @param Entity\StandardIdentity $entity
     */
    public function store(Entity\StandardIdentity $entity)
    {
        if ($entity->getId() === null) {
            $this->createIdentity($entity);
            return;
        }

        $this->updateIdentity($entity);
    }


    private function createIdentity(Entity\StandardIdentity $entity)
    {
        $sql = "INSERT INTO {$this->table}
                       (type, status, identifier, fingerprint, hash, created_on, token, token_action, token_expires_on, token_payload)
                VALUES (:type, :status, :email, :fingerprint, :hash, :created, :token, :action, :token_eol, :payload)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':type', Entity\StandardIdentity::TYPE_EMAIL);
        $statement->bindValue(':status', Entity\StandardIdentity::STATUS_NEW);
        $statement->bindValue(':email', $entity->getIdentifier());
        $statement->bindValue(':fingerprint', $entity->getFingerprint());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':created', time());

        $this->bindToken($statement, $entity);

        $statement->execute();

        $entity->setId($this->connection->lastInsertId());
    }


    private function updateIdentity(Entity\StandardIdentity $entity)
    {
        $sql = "UPDATE {$this->table}
                   SET hash = :hash,
                       status = :status,
                       used_on = :used,
                       expires_on = :expires,
                       token = :token,
                       token_action = :action,
                       token_expires_on = :token_eol,
                       token_payload = :payload
                 WHERE identity_id = :id";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':id', $entity->getId());
        $statement->bindValue(':hash', $entity->getHash());
        $statement->bindValue(':status', $entity->getStatus());
        $statement->bindValue(':used', $entity->getLastUsed());
        $statement->bindValue(':expires', $entity->getExpiresOn());

        $this->bindToken($statement, $entity);

        $statement->execute();
    }


    private function bindToken(PDOStatement $statement, Entity\StandardIdentity $entity)
    {
        $statement->bindValue(':token', $entity->getToken());
        $statement->bindValue(':action', $entity->getTokenAction());
        $statement->bindValue(':token_eol', $entity->getTokenEndOfLife());

        $payload = $entity->getTokenPayload();
        if ($payload !== null) {
            $payload = json_encode($payload);
        }

        $statement->bindValue(':payload', $payload);
    }
}
