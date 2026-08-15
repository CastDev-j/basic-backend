<?php

declare(strict_types=1);

namespace Src;

final class Logger
{
    private static ?string $file = null;

    public static function init(string $file): void
    {
        $dir = dirname($file);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException("No se pudo crear el directorio de logs: {$dir}");
        }

        self::$file = $file;
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function success(string $message): void
    {
        self::write('SUCCESS', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARN', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    private static function write(string $level, string $message): void
    {
        $file = self::$file ?? __DIR__ . '/logs/app.log';
        $line = sprintf(
            "[%s] [%s] %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            PHP_EOL
        );

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
