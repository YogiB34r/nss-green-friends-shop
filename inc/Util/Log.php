<?php
namespace GF\Util;

/**
 * Class Log
 *
 * Class responsible for logging of messages.
 * @TODO Implement some proper logging component
 */
class Log
{
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_WARNING = 'WARNING';

    static function log($msg, $level = self::LEVEL_DEBUG)
    {
        $data = sprintf('%s: %s - %s', date('Y:m:d H:i:s'), $level, $msg);
        $filePath = LOG_PATH . 'gf-debug.log';
        file_put_contents($filePath, $data . PHP_EOL, FILE_APPEND);
    }
}