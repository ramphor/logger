<?php
namespace Ramphor\Logger;

use Throwable;
use Psr\Log\LoggerInterface;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

final class Logger
{
    const LOGGER_ID = 'ramphor-logger';

    protected static $instance;

    // This variables store the loggers
    protected static $loggers = [];

    protected $id;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->registerDefaultLogger();
    }

    public function __call($name, $args)
    {
        $logger = $this->getLogger($this->id);
        try {
            return call_user_func_array(
                array($logger, $name),
                $args
            );
        } catch (Throwable $e) {
            $error_handler = @fopen('php://stderr', 'w');
            @fwrite($error_handler, $e->getMessage());
            @fclose($error_handler);
        }
    }

    public function get($id = null)
    {
        if (is_null($id) || $id === static::LOGGER_ID) {
            $this->id = static::LOGGER_ID;
        } elseif (isset(static::$loggers[$id])) {
            $log = clone $this;
            $log->id = $id;
            return $log;
        }
        return $this;
    }

    public function getLogger($id = null)
    {
        if (is_null($id)) {
            $id = $this->id ? $this->id : static::LOGGER_ID;
        }

        if (isset(static::$loggers[$id])) {
            return static::$loggers[$id];
        }
        return static::$loggers[static::LOGGER_ID];
    }

    public function registerDefaultLogger()
    {
        $syslog  = sprintf('%s/logs.log', WP_CONTENT_DIR);
        $handler = new StreamHandler(
            apply_filters('ramphor_logger_default_stream_path', $syslog),
            Monolog::DEBUG
        );

        // Setup Monolog as default logger
        $log = new Monolog(strtoupper(static::LOGGER_ID));
        $log->pushHandler($handler);

        $this->registerLogger(static::LOGGER_ID, $log);
    }

    public function registerLogger($id, &$logger)
    {
        if (!$logger instanceof LoggerInterface) {
            $this->get(static::LOGGER_ID)->warning('The logger must be follow psr/log');
            return;
        }
        if (!isset(static::$loggers[$id])) {
            static::$loggers[$id] = $logger;
        } else {
            $this->get(static::LOGGER_ID)->warning(sprintf('The logger "%s" is already exists', $id));
        }
        do_action_ref_array('ramphor_logger_register_logger', array(&$logger, $id));
    }
}
