<?php
namespace Ramphor\Logger\Autoload;

class ClassLoader
{
    protected static $monologDir;

    protected static function setMonologDir($dir)
    {
        self::$monologDir = $dir;
    }

    public static function loadMonologClass($class)
    {
        if (preg_match('/^\/?Monolog\//', $class) !== false) {
            $filePath = sprintf('%s/%s.php', self::$monologDir, ltrim(str_replace('\\', '/', $class), '/'));

            if (file_exists($filePath)) {
                require $filePath;
            }
        }
    }

    public static function initializer()
    {
        $monologVer = PHP_VERSION_ID >= 70200 ? '2.x' : '1.x';
        $monologDir = sprintf('%s/%s/%s/src', dirname(__FILE__), 'monolog', $monologVer);

        self::setMonologDir($monologDir);

        spl_autoload_register(
            array(RamphorLoggerMonologAutoload::class, 'loadMonologClass'),
            true,
            true
        );
    }
}

ClassLoader::initializer();
