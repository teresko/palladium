<?php

namespace Palladium\Mapper;

/**
 * SQL code repsonsible for locating all of the identities, that have been associated
 * to a given account and discarding them in bulk.
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
        $sql = "UPDATE {$this->table}
                   SET status = :status
                 WHERE identity_id = :id";
        $statement = $this->connection->prepare($sql);

        foreach ($collection as $entity) {
            $statement->execute([
                ':id' => $entity->getId(),
                ':status' => $entity->getStatus(),
            ]);
        }
    }


    /**
     * @param Entity\IdentityCollection $collection
     */
    public function fetch(Entity\IdentityCollection $collection)
    {
        if ($collection->getParentId() !== null) {
            $this->fetchByParent($collection);
            return;
        }

        $this->fetchByAccount($collection);
    }


    private function fetchByAccount(Entity\IdentityCollection $collection)
    {
        $sql = "SELECT identity_id  AS id
                  FROM {$this->table}
                 WHERE status = :status
                   AND account_id = :account
                   AND type = :type";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':account', $collection->getAccountId());
        $statement->bindValue(':status', $collection->getStatus());
        $statement->bindValue(':type', $collection->getType());

        $statement->execute();

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $parameters) {
            $collection->addBlueprint($parameters);
        }
    }


    private function fetchByParent(Entity\IdentityCollection $collection)
    {
        $sql = "SELECT identity_id  AS id
                  FROM {$this->table}
                 WHERE status = :status
                   AND parent_id = :parent";

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':parent', $collection->getParentId());
        $statement->bindValue(':status', $collection->getStatus());

        $statement->execute();

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $parameters) {
            $collection->addBlueprint($parameters);
        }
    }
}
