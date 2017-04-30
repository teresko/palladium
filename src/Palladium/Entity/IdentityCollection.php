<?php

namespace Palladium\Entity;

use Palladium\Component\Collection;

class IdentityCollection extends Collection
{

    private $accountId;
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
    public function forAccountId($accountId)
    {
        $this->accountId = (int) $accountId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getAccountId()
    {
        return $this->accountId;
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
