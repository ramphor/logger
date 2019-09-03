<?php
namespace Jankx\Logger;

use Jankx\Logger\ValidateDriver;

class Logger
{
    protected static $intances;

    protected $driver;
    protected $args;

    public static function getInstance($defaultDriver, $args)
    {
    }

    public static function init($driver, $args)
    {
        $driver = trim($driver);
        if (!self::getInstance($driver, $args)) {
            switch ($driver) {
                case 'file':
                    if (empty($args['path'])) {
                        throw new Exception("The agrument `path` must be setted value for specify diretory store the log files.", 1);
                    }
                    $logger = new self($driver);
                    $logger->setArgs($args);
                    self::$intances[$driver][$args['path']] = $logger;
                    break;
            }
        }
    }

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setArgs($args)
    {
        $validateCb = array(ValidateDriver::class, $this->driver);
        if (is_callable($validateCb) && call_user_func($validateCb, $args)) {
            $this->args = $this->args;
        } else {
            throw new \Error(sprintf('The args is invalid for %s driver.', $this->driver));
        }
    }

    public function validateFileDriver($args)
    {
    }
}
