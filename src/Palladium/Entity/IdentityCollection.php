<?php

namespace Palladium\Entity;

use Palladium\Component\Collection;
use Palladium\Contract\HasId;

class IdentityCollection extends Collection
{

    private $accountId;
    private $parentId;
    private $type;
    private $status;


    /**
     * @codeCoverageIgnore
     */
    protected function buildEntity(): HasId
    {
        return new Identity;
    }


    /**
     * @codeCoverageIgnore
     */
    public function forAccountId(int $accountId)
    {
        $this->accountId = $accountId;
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
    public function forParentId(int $parentId)
    {
        $this->parentId = $parentId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getParentId()
    {
        return $this->parentId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function forType(int $type = null)
    {
        $this->type = $type;
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
    public function forStatus(int $status)
    {
        $this->status = $status;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getStatus()
    {
        return $this->status;
    }
}
