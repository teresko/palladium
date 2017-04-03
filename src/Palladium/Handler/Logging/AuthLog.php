<?php

namespace Palladium\Handler\Logging;

use Palladium\Component\MapperFactory;
use Palladium\Mapper\Logging\AuthenticationHistory;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;


class AuthLog extends AbstractProcessingHandler
{

    private $factory;
    private $mapper;

    public function __construct(MapperFactory $factory, $level = Logger::INFO, $bubble = true)
    {
        $this->factory = $factory;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if ($this->mapper === null) {
            $this->mapper = $this->factory->create(AuthenticationHistory::class);
        }

        $this->mapper->store($record);
    }
}
