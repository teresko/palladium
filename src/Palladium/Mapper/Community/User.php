<?php

namespace Palladium\Mapper\Community;

use Palladium\Component\SqlMapper;
use Palladium\Entity\Community as Entity;

class User extends SqlMapper
{

    /**
     * @param Entity\User $entity
     */
    public function exists(Entity\User $entity)
    {
        $table = $this->config['accounts']['users'];

        $sql = "SELECT 1
                  FROM $table AS Users
                 WHERE user_id = :user";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':user', $entity->getId());

        $statement->execute();

        $data = $statement->fetch();

        return empty($data) === false;
    }


    /**
     * @param Entity\User $entity
     */
    public function store(Entity\User $entity)
    {
        $table = $this->config['accounts']['users'];

        $sql = "INSERT INTO $table (name) VALUES (:name)";

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':name', $entity->getName());

        $statement->execute();

        $entity->setId($this->connection->lastInsertId());
    }


    /**
     * @param Entity\User $entity
     */
    public function fetch(Entity\User $entity)
    {
        $table = $this->config['accounts']['users'];

        $sql = "SELECT user_id     AS id,
                       name
                  FROM $table
                 WHERE user_id = :user";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':user', $entity->getId());

        $statement->execute();

        $data = $statement->fetch();

        if ($data) {
            $this->applyValues($entity, $data);
        }
    }
}
