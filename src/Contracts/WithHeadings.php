<?php

namespace Laraigniter\Excel\Contracts;

interface WithHeadings
{
    /**
     * @return array
     */
    public function headings(): array;
}
