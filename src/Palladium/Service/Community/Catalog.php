<?php

namespace Palladium\Service\Community;

use Palladium\Component\MapperFactory;
use Palladium\Entity\Community as Entity;
use Palladium\Mapper\Community as Mapper;

use Palladium\Exception\Community\DuplicatedClient;

class Catalog
{

    private $mapperFactory;



    public function __construct(MapperFactory $mapperFactory)
    {
        $this->mapperFactory = $mapperFactory;
    }


    public function createUser($name)
    {
        $user = new Entity\User;
        $user->setName($name);

        $mapper = $this->mapperFactory->create(Mapper\User::class);
        $mapper->store($user);

        return $user;
    }


    public function retrieveUser($userId)
    {
        $user = new Entity\User;
        $user->setId($userId);

        $mapper = $this->mapperFactory->create(Mapper\User::class);

        $mapper->fetch($user);

        return $user;
    }

}
