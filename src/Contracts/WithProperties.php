<?php

namespace Laraigniter\Excel\Contracts;

interface WithProperties
{
    /**
     * Workbook properties that replace the config for this export.
     *
     * @return array<string, string>
     */
    public function properties(): array;
}
