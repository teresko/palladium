<?php

namespace Palladium\Mapper;

/**
 * SQL code repsonsible for locating all of the identities, that have been associated
 * to a given user and discarding them in bulk.
 * Used mostly in case of password reset or, if cookie has been compromised.
 */


use Palladium\Component\SqlMapper;
use Palladium\Entity as Entity;

class IdentityCollection extends SqlMapper
{

    /**
     * @param Entity\IdentityCollection $collection
     */
    public function store(Entity\IdentityCollection $collection)
    {
        if ($collection->getUserId() !== null) {
            $this->updateStatus($collection);
        }
    }


    private function updateStatus(Entity\IdentityCollection $collection)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "UPDATE {$table}
                   SET status = :status
                 WHERE identity_id = :id";
        $statement = $this->connection->prepare($sql);

        foreach ($collection as $entity) {
            $statement->bindValue(':id', $entity->getId());
            $statement->bindValue(':status', $entity->getStatus());
            $statement->execute();
        }
    }


    /**
     * @param Entity\IdentityCollection $collection
     */
    public function fetch(Entity\IdentityCollection $collection)
    {
        $table = $this->config['accounts']['identities'];

        $sql = "SELECT identity_id  AS id
                  FROM {$table}
                 WHERE status = :status
                   AND user_id = :user
                   AND type = :type";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':user', $collection->getUserId());
        $statement->bindValue(':status', $collection->getStatus());
        $statement->bindValue(':type', $collection->getType());

        $statement->execute();

        foreach ($statement as $parameters) {
            $collection->addBlueprint($parameters);
        }
    }
}
