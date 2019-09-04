<?php
namespace Jankx\Logger;

class ValidateDriver
{
    public static function file($args)
    {
        if (empty($args['path'])) {
            return false;
        }
        if (!file_exists($args['path'])) {
            mkdir($args['path'], 0755, true);
        }
        return true;
    }
}
