<?php

namespace Dewdrop\Model;

abstract class ModelAbstract
{
    protected $tableName;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
}
