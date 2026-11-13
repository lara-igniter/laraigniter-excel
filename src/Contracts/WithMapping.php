<?php

namespace Laraigniter\Excel\Contracts;

interface WithMapping
{
    /**
     * @param object $row
     * @return array
     */
    public function map(object $row): array;
}
