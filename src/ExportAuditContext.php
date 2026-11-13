<?php

namespace Laraigniter\Excel;

final class ExportAuditContext
{
    /**
     * @var array<string, array{format: string, model_class: string, row_count: int}>
     */
    private static array $exports = [];

    /**
     * @param string $filePath
     * @param string $modelClass
     * @param int $rowCount
     * @param string $format
     */
    public static function record(string $filePath, string $modelClass, int $rowCount, string $format): void
    {
        self::$exports[$filePath] = [
            'format' => $format,
            'model_class' => $modelClass,
            'row_count' => $rowCount,
        ];
    }

    /**
     * @return array{format: string, model_class: string, row_count: int}|null
     * @param string $filePath
     */
    public static function pull(string $filePath): ?array
    {
        $metadata = self::$exports[$filePath] ?? null;

        unset(self::$exports[$filePath]);

        return $metadata;
    }
}
