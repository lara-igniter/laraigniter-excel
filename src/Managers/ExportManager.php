<?php

namespace Laraigniter\Excel\Managers;

use Laraigniter\Excel\Contracts\ExportInterface;
use Laraigniter\Excel\Writers\ExcelExporter;

class ExportManager
{
    public static function make(string $format, object $export = null): ExportInterface
    {
        switch ($format) {
            case 'excel':
                $instance = new ExcelExporter();
                $instance->export = $export;
                return $instance;
            default:
                throw new \InvalidArgumentException("Unsupported export format [$format]");
        }
    }
}
