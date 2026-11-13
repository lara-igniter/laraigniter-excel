<?php

namespace Laraigniter\Excel;

use Elegant\Database\Model\Model;
use Elegant\Support\Collection;
use Laraigniter\Excel\Contracts\ExportInterface;

abstract class BaseExporter implements ExportInterface
{
    /**
     * The collection of data to be exported.
     *
     * @var \Elegant\Support\Collection
     */
    protected Collection $collection;

    /**
     * The array of extra columns to add.
     *
     * @var array
     */
    protected array $columns;

    /**
     * The file extension for the export.
     *
     * @var string
     */
    protected string $extension;

    /**
     * The query model instance.
     *
     * @var \Elegant\Database\Model\Model
     */
    protected Model $query;

    /**
     * The model name.
     *
     * @var string
     */
    protected string $model;

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns ?? [];
    }

    /**
     * @param array $columns
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setColumns(array $columns): ExportInterface
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return \Elegant\Support\Collection
     */
    public function getData(): Collection
    {
        return $this->collection;
    }

    /**
     * @param \Elegant\Support\Collection $collection
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setData(Collection $collection): ExportInterface
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension ?? null;
    }

    /**
     * @param string $extension
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setExtension(string $extension): ExportInterface
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setModel(string $model): ExportInterface
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return \Elegant\Database\Model\Model
     */
    public function getQuery(): Model
    {
        return $this->query;
    }

    /**
     * @param \Elegant\Database\Model\Model $query
     * @return \Laraigniter\Excel\Contracts\ExportInterface
     */
    public function setQuery(Model $query): ExportInterface
    {
        $this->query = $query;

        $this->setModel(get_class($query));

        $this->setData($this->getQuery()->all());

        return $this;
    }

    /**
     * Get the default file extension for the export.
     *
     * @return string
     */
    abstract public function getDefaultExtension(): string;
}
