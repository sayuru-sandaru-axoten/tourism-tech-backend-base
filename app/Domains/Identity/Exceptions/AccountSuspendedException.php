<?php

namespace App\Domains\Identity\Exceptions;

use Exception;

class AccountSuspendedException extends Exception
{
    public function __construct()
    {
        parent::__construct('This account has been suspended.');
    }
}
