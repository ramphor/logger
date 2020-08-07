<?php
namespace Ramphor\Logger;

final class Logger
{
    protected static $instance;

    protected static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
    }

    private function __construct()
    {
    }
}
