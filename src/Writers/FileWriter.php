<?php
namespace Jankx\Logger\Writers;

use Jankx\Logger\Abstracts\LogWritter;

class FileWriter extends LogWritter
{
	protected $message;
	protected $type;
	protected $path;
	protected $messageFormat;
	protected $maxLogFileSize;
	protected $hWriter;

	public function __construct($message, $type, $args)
    {
		$this->message = $message;
		$this->type = $type;

		$args = array_merge(
			array(
				'format' => '[%d][%T]%m',
				'max_log_file_size' => '10M',
			),
			$args
		);
		$this->path = $args['path'];
		$this->messageFormat = $args['format'];
		$this->maxLogFileSize = $this->convertSizeFromTextToBytes($args['max_log_file_size']);
		$this->hWriter = fopen($this->path, 'w+');
	}

	public function __destruct() {
		fclose($this->hWriter);
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

	public function convertSizeFromTextToBytes($values) {
		return $values;
	}

    public function write()
    {
        $message = $this->createMessage($message, $type, date('Y-m-d H:i:s'));
        if (!$this->hWriter) {
            throw new \Exception(sprintf('Can not open file %s to write log', $logPath));
        }
        if (!fwrite($this->hWriter, $message)) {
            throw new \Exception('Jankx Logger error occur when write log.');
        }
	}

}
