<?php

namespace Palladium\Processor\Logging;

class UserData
{
    public function __invoke(array $record)
    {
        $account = [
            'user' => null,
            'identity' => null,
        ];

        if (isset($record['context']['account'])) {
            $account = $record['context']['account'];
            unset($record['context']['account']);
        }

        $record['extra']['account'] = $account;

        return $record;
    }
}
