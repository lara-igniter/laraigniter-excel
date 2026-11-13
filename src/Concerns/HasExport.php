<?php

namespace Laraigniter\Excel\Concerns;

trait HasExport
{
    /**
     * Get the exportable attributes for the model.
     *
     * @return array
     */
    public function getExportables(): array
    {
        return !empty($this->exportable) ? $this->exportable : $this->fillable;
    }
}
