<?php
namespace Jankx\Logger;

class ValidateDriver
{
    public static function file($args)
    {
        if (empty($args['path'])) {
            return false;
        }

        $dir = dirname($args['path']);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return true;
    }
}
