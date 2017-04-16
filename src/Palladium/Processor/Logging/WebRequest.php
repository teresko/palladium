<?php

namespace Palladium\Processor\Logging;

class WebRequest
{
    public function __construct(array $serverData = null)
    {
        if (null === $serverData) {
            $serverData = &$_SERVER;
        }

        $this->serverData = $serverData;
    }


    public function __invoke(array $record)
    {
        if (!isset($this->serverData['REQUEST_URI'])) {
            return $record;
        }

        $record['extra'] = $this->collectParams($record['extra'], $this->serverData);

        return $record;
    }


    private function collectParams(array $extra, array $data)
    {
        $fields = [
            'client' => [
                'user_agent' => $data['HTTP_USER_AGENT'],
                'accept_language' => $data['HTTP_ACCEPT_LANGUAGE'],
                'request' => $data['REQUEST_URI'],
                'ip' => $data['REMOTE_ADDR'],
                'forwarded' => isset($data['HTTP_X_FORWARDED_FOR']) ? $data['HTTP_X_FORWARDED_FOR'] : null,
            ],
            'server' => [
                'name' => $data['SERVER_NAME'],
                'software' => $data['SERVER_SOFTWARE'] . '; PHP ' . phpversion(),
                'timezone' => date_default_timezone_get(),
            ],
        ];

        return $fields + $extra;
    }
}
