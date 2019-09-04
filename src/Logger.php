<?php
namespace Jankx\Logger;

use Jankx\Logger\ValidateDriver;
use Jankx\Logger\Writers\FileWriter;
use Jankx\Logger\Abstracts\LogWriter;

class Logger
{
    protected static $intances;

    protected $driver;
    protected $args;

    public static function getInstance($driver, $args)
    {
        if ($driver === 'file') {
            $identityField = 'path';
        }

        if (isset($intances[$driver][$identityField])) {
            return $intances[$driver][$identityField];
        }
        return false;
    }

    public static function init($driver, $args)
    {
        $driver = trim($driver);
        if (!self::getInstance($driver, $args)) {
            switch ($driver) {
                case 'file':
                    if (empty($args['path'])) {
                        throw new Exception(
                            'The agrument `path` must be setted value for specify diretory store the log files.'
                        );
                    }
                    $logger = new self($driver);
                    $logger->setArgs($args);
                    self::$intances[$driver][$args['path']] = $logger;
                    return $logger;
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
            $this->args = $args;
        } else {
            throw new \Error(sprintf('The args is invalid for %s driver.', $this->driver));
        }
    }

    public function writters()
    {
        return apply_filters('jankx_logger_writters', array(
            'file' => FileWriter::class,
        ));
    }

    public function getWriter($driver)
    {
        $writters = $this->writters();
        if (isset($writters[$driver])) {
            return $writters[$driver];
        } else {
            throw new \Exception(
                sprintf('The writter %s is not supported', $driver)
            );
        }
    }

    public function write($message, $type = 'info')
    {
        $writer_class = $this->getWriter($this->driver);
        $writer = new $writer_class($message, $type, $this->args);
        if (!($writer instanceof LogWriter)) {
            throw new \Exception(
                sprintf('The log writer must be instance of %s', LogWriter::class)
			);
        }
        $writer->write();
    }
}
