<?php

namespace Handler\Logging;

use Component\MapperFactory;
use Mapper\Logging\AuthenticationViolation;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;


class AuthViolationLog extends AbstractProcessingHandler
{

    private $factory;
    private $mapper;

    public function __construct(MapperFactory $factory, $level = Logger::ERROR, $bubble = true)
    {
        $this->factory = $factory;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if ($this->mapper === null) {
            $this->mapper = $this->factory->create(AuthenticationViolation::class);
        }

        $this->mapper->store($record);
    }
}
