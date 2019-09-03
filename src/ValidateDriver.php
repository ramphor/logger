<?php
namespace Jankx\Logger;

class ValidateDriver
{
    public static function file($args)
    {
        return !empty($args['path']);
    }
}
