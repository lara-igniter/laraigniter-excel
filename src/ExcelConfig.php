<?php

namespace Laraigniter\Excel;

final class ExcelConfig
{
    /**
     * Read one excel config value. The built-in default is used when the
     * config file is not on a config path yet, or the key is empty.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default)
    {
        if (!self::canLoad()) {
            return $default;
        }

        $value = config('excel.' . $key);

        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    /**
     * @param string $key
     * @param array $default
     * @return array
     */
    public static function getArray(string $key, array $default): array
    {
        $value = self::get($key, $default);

        return is_array($value) ? $value : $default;
    }

    /**
     * @return bool
     */
    private static function canLoad(): bool
    {
        if (!function_exists('app') || !function_exists('config')) {
            return false;
        }

        try {
            $config = app('config');
        } catch (\Throwable $exception) {
            return false;
        }

        if (!is_object($config) || !isset($config->_config_paths) || !is_array($config->_config_paths)) {
            return false;
        }

        foreach ($config->_config_paths as $path) {
            $file = rtrim((string) $path, '/\\') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excel.php';

            if (is_file($file)) {
                return true;
            }
        }

        return false;
    }
}
