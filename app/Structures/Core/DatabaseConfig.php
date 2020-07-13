<?php

namespace TheProject\Structures\Core;

use TheProject\Helpers\Traits\FromArrayTrait;

class DatabaseConfig
{
    use FromArrayTrait;

    public string $host = '';
    public string $port = '';
    public string $name = '';
    public string $username = '';
    public string $password = '';
}
