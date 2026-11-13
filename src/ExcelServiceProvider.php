<?php

namespace Laraigniter\Excel;

use Elegant\Contracts\Hook\Boot;
use Elegant\Contracts\Hook\PostControllerConstructor;
use Elegant\Support\ServiceProvider;

class ExcelServiceProvider extends ServiceProvider implements Boot, PostControllerConstructor
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if (!is_cli()) {
            return;
        }

        $this->publishes([
            dirname(__DIR__) . '/config/excel.php' => base_path('config/excel.php'),
        ], 'excel-config');
    }

    /**
     * @param mixed $params
     * @return void
     */
    public function postControllerConstructor(&$params): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/excel.php', 'excel');
    }
}
