<?php

namespace Palladium\Mapper\Logging;

use Palladium\Component\SqlMapper;
use Palladium\Entity\Authentication as Entity;


class AuthenticationViolation extends SqlMapper
{

    public function store(array $entity)
    {
        $table = $this->config['accounts']['violations'];

        $sql = "INSERT INTO $table(`host`, `channel`, `message`, `ip`, `context`, `metadata`, `user_id`, `identity_id`)
                VALUES (:host, :channel, :message, :ip, :context, :metadata, :user, :identity)";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':host', $entity['extra']['server']['name']);
        $statement->bindValue(':channel', $entity['channel']);
        $statement->bindValue(':message', $entity['message']);
        $statement->bindValue(':ip', $entity['extra']['client']['ip']);
        $statement->bindValue(':context', json_encode($entity['context']));
        $statement->bindValue(':metadata', json_encode($entity['extra']));
        $statement->bindValue(':user', $entity['extra']['account']['user']);
        $statement->bindValue(':identity', $entity['extra']['account']['identity']);

        $statement->execute();
    }

}
