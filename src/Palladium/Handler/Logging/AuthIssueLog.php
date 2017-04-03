<?php

namespace Handler\Logging;

use Component\MapperFactory;
use Mapper\Logging\AuthenticationIssue;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;


class AuthIssueLog extends AbstractProcessingHandler
{

    private $factory;
    private $mapper;

    public function __construct(MapperFactory $factory, $level = Logger::WARNING, $bubble = true)
    {
        $this->factory = $factory;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if ($this->mapper === null) {
            $this->mapper = $this->factory->create(AuthenticationIssue::class);
        }

        $this->mapper->store($record);
    }
}
