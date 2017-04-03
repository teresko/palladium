<?php

namespace Entity\Community;

use Component\Identifiable;

class User implements Identifiable
{

    private $userId;
    private $name;


    public function setId($userId)
    {
        $data = (int) $userId;

        if ($data > 0) {
            $this->userId = $data;
        }
    }


    public function getId()
    {
        return $this->userId;
    }


    public function setName($name)
    {
        $this->name = $name;
    }


    public function getName()
    {
        return $this->name;
    }
}
