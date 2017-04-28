<?php

namespace Palladium\Service;


/**
 * Class for finding indentities based on various conditions
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\UserNotFound;
use Palladium\Exception\IdentityNotFound;
use Palladium\Exception\TokenNotFound;

use Palladium\Contract\CanCreateMapper;
use Psr\Log\LoggerInterface;


class Search
{

    private $mapperFactory;
    private $logger;


    public function __construct(CanCreateMapper $mapperFactory, LoggerInterface $logger)
    {
        $this->mapperFactory = $mapperFactory;
        $this->logger = $logger;
    }


    public function retrieveIdenityByToken(Entity\Identity $identity)
    {
        $identity->setTokenEndOfLife(time());

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->fetch($identity);

        return $identity;
    }
}
