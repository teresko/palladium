<?php

namespace Entity\Authentication;

use Component\Collection;

class IdentityCollection extends Collection
{

    private $userId;
    private $type;
    private $status;


    /**
     * @codeCoverageIgnore
     */
    protected function buildEntity()
    {
        return new Identity;
    }


    /**
     * @codeCoverageIgnore
     */
    public function forUserId($userId)
    {
        $this->userId = (int) $userId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getUserId()
    {
        return $this->userId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function forType($type)
    {
        $this->type = (int) $type;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @codeCoverageIgnore
     */
    public function forStatus($status)
    {
        $this->status = (int) $status;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getStatus()
    {
        return $this->status;
    }
}
