<?php
namespace Jankx\Logger\Writers;

use Jankx\Logger\Abstracts\LogWriter;

class FileWriter extends LogWriter
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
                'max_log_file_size' => '1M',
                'suffix_file_name_format' => 'date', // `sequence` count same file names.
            ),
            $args
        );

        $this->path = $args['path'];
        $this->messageFormat = $args['format'];
        $this->suffixFileNameFormat = $args['suffix_file_name_format'];
        $this->maxLogFileSize = $this->convertSizeFromTextToBytes($args['max_log_file_size']);

        $this->initHooks();
        $this->verifyLogFileSizes();
        $this->openLogFile();
    }

    public function __destruct()
    {
        if ($this->hWriter) {
            fclose($this->hWriter);
        }
    }

    public function initHooks()
    {
        add_filter('jankx_logger_backup_file', array($this, 'validate_backup_files'), 10, 4);
    }

    protected function verifyLogFileSizes()
    {
        if (!file_exists($this->path)) {
            return;
        }
        $filesize = filesize($this->path);
        if ($filesize > $this->maxLogFileSize) {
            $this->backupLogFile($this->path);
        }
    }

    protected function openLogFile()
    {
        $this->hWriter = fopen($this->path, 'a');
    }

    protected function isSupportGzip()
    {
    }

    public function validate_backup_files($backupFile, $backupFileName, $suffix, $ext, $index = 1)
    {
        if (!file_exists($backupFile)) {
            return $backupFile;
        }
        if (is_numeric(strpos($suffix, '-'))) {
            $suffix = $suffix . '-1';
        } else {
            $suffix = '.' . $index;
        }

        return $this->validate_backup_files(
            sprintf('%s%s.%s', $backupFileName, $suffix, $ext),
            $backupFileName,
            $suffix,
            $ext,
            $index + 1
        );
    }

    protected function backupLogFile($logFile)
    {
        $ext = pathinfo($logFile, PATHINFO_EXTENSION);
        $backupFileName = str_replace('.' . $ext, '', $logFile);

        if ($this->isSupportGzip()) {
            $ext = 'gz';
        }
        if ($this->suffixFileNameFormat !== 'sequence') {
            $suffix = '-' . date('YmdHis');
        } else {
            $searchSameBackupLogs = glob(sprintf('%s*.%s', $backupFileName, $ext));
            $totalFiles = count($searchSameBackupLogs);
            if ($totalFiles> 0) {
                $suffix = '.' . $totalFiles;
            } else {
                $suffix = '';
            }
            unset($searchSameBackupLogs);
        }

        $backupFile = apply_filters(
            'jankx_logger_backup_file',
            sprintf('%s%s.%s', $backupFileName, $suffix, $ext),
            $backupFileName,
            $suffix,
            $ext,
            $this->suffixFileNameFormat
        );

        if ($ext === 'gz') {
        } else {
            rename($logFile, $backupFile);
        }
    }

    protected function createMessage($message, $type, $messageFormat, $date = null)
    {
        if (preg_match_all('/\%\w/', $messageFormat, $matches)) {
            $ret = $messageFormat;
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

        return $ret . PHP_EOL;
    }

    public function convertSizeFromTextToBytes($value)
    {
        return preg_replace_callback(
            '/^\s*(\d+)\s*(?:([kmgt]?)b?)?\s*$/i',
            function ($m) {
                switch (strtolower($m[2])) {
                    case 't':
                        $m[1] *= 1024;
                    case 'g':
                        $m[1] *= 1024;
                    case 'm':
                        $m[1] *= 1024;
                    case 'k':
                        $m[1] *= 1024;
                }
                return $m[1];
            },
            $value
        );
    }

    public function write()
    {
        $message = $this->createMessage($this->message, $this->type, $this->messageFormat, date('Y-m-d H:i:s'));
        if (!$this->hWriter) {
            throw new \Exception(sprintf('Can not open file %s to write log', $logPath));
        }
        if (!fwrite($this->hWriter, $message)) {
            throw new \Exception('Jankx Logger error occur when write log.');
        }
    }
}
