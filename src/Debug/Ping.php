<?php

namespace WCSPO\Debug;

final class Ping
{
    public static function check(): string
    {
        return 'autoload-ok';
    }
}
