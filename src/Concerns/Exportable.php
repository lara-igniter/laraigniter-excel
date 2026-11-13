<?php

namespace Laraigniter\Excel\Concerns;

use Elegant\Support\Str;
use Laraigniter\Excel\ExcelConfig;
use Laraigniter\Excel\ExportAuditContext;
use Throwable;

trait Exportable
{
    /**
     * Download the exported file.
     *
     * @param string|null $filename
     * @param string $format
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function download(string $filename = null, string $format = 'excel'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filePath = (new static($format))->export();

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $dateFormat = ExcelConfig::get('exports.date_format', 'Ymd_Hi');
        $dateFormat = is_string($dateFormat) && $dateFormat !== '' ? $dateFormat : 'Ymd_Hi';

        $name = $filename ?? Str::of(class_basename(static::class))->snake()->toString() . '_' . now()->format($dateFormat) . '.' . $extension;

        static::logDownload($filePath, $name, $extension);

        return response()->download($filePath, $name)->deleteFileAfterSend();
    }

    private static function logDownload(string $filePath, string $filename, string $format): void
    {
        try {
            $metadata = ExportAuditContext::pull($filePath);
            $ionAuth = app('ion_auth');
            $log = ExcelConfig::get('activity.log', 'exports');
            $logger = activity(is_string($log) && $log !== '' ? $log : 'exports');

            if ($ionAuth->logged_in()) {
                $causer = $ionAuth->user()->row();

                if (is_object($causer)) {
                    $logger->causedBy($causer, app('activitylog.causer')->typeFor($causer));
                } else {
                    $logger->causedByAnonymous();
                }
            } else {
                $logger->causedByAnonymous();
            }

            $event = ExcelConfig::get('activity.event', 'export.downloaded');

            $logger
                ->event(is_string($event) && $event !== '' ? $event : 'export.downloaded')
                ->withProperties([
                    'export_class' => static::class,
                    'report_type' => class_basename(static::class),
                    'model_class' => $metadata['model_class'] ?? null,
                    'filename' => $filename,
                    'format' => $metadata['format'] ?? $format,
                    'row_count' => $metadata['row_count'] ?? null,
                    'filters' => static::safeFilters(),
                    'date_range' => static::dateRange(),
                    'result' => 'success',
                ])
                ->log('Export downloaded');
        } catch (Throwable $exception) {
            log_message('error', 'Failed to log export download activity: ' . $exception->getMessage());
        }
    }

    /**
     * @return array<string, bool|int|float|string>
     */
    private static function safeFilters(): array
    {
        $filters = [];
        $maxLength = ExcelConfig::get('filters.max_length', 100);
        $maxLength = is_int($maxLength) ? $maxLength : (int) $maxLength;

        foreach ((array) request()->all() as $key => $value) {
            if (! is_string($key) || static::isSensitiveFilter($key)) {
                continue;
            }

            if (is_bool($value) || is_int($value) || is_float($value)) {
                $filters[$key] = $value;
                continue;
            }

            if (is_string($value) && mb_strlen($value) <= $maxLength) {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    /**
     * @return array<string, string>
     */
    private static function dateRange(): array
    {
        $dateRange = [];

        foreach ((array) request()->all() as $key => $value) {
            if (! is_string($key) || ! is_string($value) || ! static::isDateFilter($key)) {
                continue;
            }

            $dateRange[$key] = mb_substr($value, 0, 25);
        }

        return $dateRange;
    }

    private static function isSensitiveFilter(string $key): bool
    {
        $sensitiveTerms = ExcelConfig::getArray('filters.sensitive', [
            'address',
            'comment',
            'email',
            'identity',
            'lastname',
            'mobile',
            'name',
            'note',
            'number',
            'password',
            'phone',
            'search',
            'ssn',
            'tax',
            'token',
        ]);

        $key = strtolower($key);

        foreach ($sensitiveTerms as $term) {
            if (!is_string($term)) {
                continue;
            }

            if (strpos($key, strtolower($term)) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function isDateFilter(string $key): bool
    {
        if (strpos(strtolower($key), 'date') === false) {
            return false;
        }

        $suffixes = ExcelConfig::getArray('filters.date_suffixes', ['_from', '_to']);

        foreach ($suffixes as $suffix) {
            if (!is_string($suffix) || $suffix === '') {
                continue;
            }

            if (substr($key, -strlen($suffix)) === $suffix) {
                return true;
            }
        }

        return false;
    }

    /**
     * Masks a mobile number, keeping the first 2 and last 2 characters.
     * E.g. 6912345678 → 69******78
     * @param string $mobile
     * @return string
     */
    public function maskMobile(string $mobile): string
    {
        $mobile = trim($mobile);

        if (mb_strlen($mobile) <= 4) {
            return $mobile;
        }

        $start  = mb_substr($mobile, 0, 2);
        $end    = mb_substr($mobile, -2);
        $masked = str_repeat('*', mb_strlen($mobile) - 4);

        return $start . $masked . $end;
    }

    /**
     * Masks an email address, keeping the first 2 characters of the local part and the TLD.
     * E.g. test@example.com → te*******@*******.com
     * @param string $email
     * @return string
     */
    public function maskEmail(string $email): string
    {
        $email = trim($email);

        if (strpos($email, '@') === false) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        $maskedLocal = mb_strlen($local) > 2
            ? mb_substr($local, 0, 2) . str_repeat('*', mb_strlen($local) - 2)
            : $local;

        $dotPos       = strrpos($domain, '.');
        $maskedDomain = $dotPos !== false
            ? str_repeat('*', $dotPos) . mb_substr($domain, $dotPos)
            : str_repeat('*', mb_strlen($domain));

        return $maskedLocal . '@' . $maskedDomain;
    }
}
