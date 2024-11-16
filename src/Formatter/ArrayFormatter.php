<?php

namespace App\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

class ArrayFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        $message = $record['message'];
        $context = $record['context'];
        if (is_array($context)) {
            $context = json_encode($context, JSON_PRETTY_PRINT);
        }
        return sprintf(
            "[%s] %s: %s\n%s\n",
            $record['datetime']->format('Y-m-d H:i:s'),
            strtoupper($record['level_name']),
            $message,
            $context
        );
    }

    public function formatBatch(array $records): array
    {
        return array_map([$this, 'format'], $records);
    }
}