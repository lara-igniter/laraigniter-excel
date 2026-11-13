<?php

namespace Laraigniter\Excel\Contracts;

use Elegant\Database\Model\Model;
use Elegant\Support\Collection;

interface ExportInterface
{
    /**
     * @return array
     */
    public function getColumns(): array;

    /**
     * @param array $columns
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setColumns(array $columns): ExportInterface;

    /**
     * @return \Elegant\Support\Collection
     */
    public function getData(): Collection;

    /**
     * @param \Elegant\Support\Collection $collection
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setData(Collection $collection): ExportInterface;

    /**
     * @return string|null
     */
    public function getExtension(): ?string;

    /**
     * @param string $extension
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setExtension(string $extension): ExportInterface;

    /**
     * @return string
     */
    public function getModel(): string;

    /**
     * @param string $model
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setModel(string $model): ExportInterface;

    /**
     * @return \Elegant\Database\Model\Model
     */
    public function getQuery(): Model;

    /**
     * @param \Elegant\Database\Model\Model $query
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setQuery(Model $query): ExportInterface;

    /**
     * @param bool $mergeColumns
     * @return string
     */
    public function store(bool $mergeColumns = true): string;
}
