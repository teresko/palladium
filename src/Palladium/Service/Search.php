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


    /**
     * @param string $identifier
     */
    public function findEmailIdenityByIdentifier($identifier)
    {
        $identity = new Entity\EmailIdentity;
        $identity->setIdentifier($identifier);

        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);
        $mapper->fetch($identity);

        if ($identity->getId() === null) {
            $this->logger->warning('acount not found', [
                'input' => [
                    'identifier' => $identifier,
                ],
            ]);

            throw new IdentityNotFound;
        }

        return $identity;
    }


    /**
     * @param string $token
     * @param int $action
     */
    public function findEmailIdenityByToken($token, $action = Entity\Identity::ACTION_ANY)
    {
        $identity = new Entity\EmailIdentity;

        $identity->setToken($token);
        $identity->setTokenAction($action);
        $identity->setTokenEndOfLife(time());

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->fetch($identity);

        return $identity;
    }


    /**
     * @param int $userId
     * @param string $series
     */
    public function findCookieIdenity($userId, $series)
    {
        $cookie = new Entity\CookieIdentity;
        $cookie->setStatus(Entity\Identity::STATUS_ACTIVE);
        $cookie->setUserId($userId);
        $cookie->setSeries($series);

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);
        $mapper->fetch($cookie);

        return $cookie;
    }
}
