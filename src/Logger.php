<?php
namespace Jankx\Logger;

use Jankx\Logger\ValidateDriver;

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
                        throw new Exception("The agrument `path` must be setted value for specify diretory store the log files.", 1);
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
            $this->args = array_merge(
                array(
                    'format' => '[%d][%T]%m',
                    'max_log_file_size' => '10M',
                ),
                $args
            );
        } else {
            throw new \Error(sprintf('The args is invalid for %s driver.', $this->driver));
        }
    }

    public function createMessage($message, $type, $date = null)
    {
        if (preg_match_all('/\%\w/', $this->args['format'], $matches)) {
            $ret = $this->args['format'];
            foreach ($matches[0] as $t) {
                switch ($t) {
                    case '%t':
                        $ret = str_replace($t, $type, $ret);
                        break;
                    case '%T':
                        $ret = str_replace($t, strtoupper($type), $ret);
                        break;
                    case '%d':
                        $ret = str_replace($t, $date, $ret);
                        break;
                    case '%m':
                        $ret = str_replace($t, $message, $ret);
                        break;
                }
            }
        } else {
            $ret = $message;
        }

        return $ret;
    }

    public function write($message, $type = 'info', $file = 'logs.log')
    {
        $message = $this->createMessage($message, $type, date('Y-m-d H:i:s'));
        $logPath = sprintf('%s/%s', $this->args['path'], $file);
        $h = fopen($logPath, 'w+');
        if (!$h) {
            throw new \Exception(sprintf('Can not open file %s to write log', $logPath));
        }
        if (!fwrite($h, $message)) {
            throw new \Exception('Jankx Logger error occur when write log.');
        }
        fclose($h);
    }
}
