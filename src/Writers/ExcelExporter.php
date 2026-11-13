<?php

namespace Laraigniter\Excel\Writers;

use Elegant\Support\Facades\Date;
use Elegant\Support\Str;
use Laraigniter\Excel\BaseExporter;
use Laraigniter\Excel\Contracts\WithHeadings;
use Laraigniter\Excel\Contracts\WithMapping;
use Laraigniter\Excel\Contracts\WithProperties;
use Laraigniter\Excel\ExcelConfig;
use Laraigniter\Excel\ExportAuditContext;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter extends BaseExporter
{
    public ?object $export;

    /**
     * The default file extension for the export.
     *
     * @return string
     */
    public function getDefaultExtension(): string
    {
        $extension = ExcelConfig::get('exports.extension', 'xlsx');

        return is_string($extension) && $extension !== '' ? $extension : 'xlsx';
    }

    /**
     * @param bool $mergeColumns
     * @return string
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function store(bool $mergeColumns = true): string
    {
        $model = (new $this->model());

        $spreadsheet = new Spreadsheet();

        $this->applyProperties($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $extraColumns = $this->getColumns();

        $fields = $mergeColumns
            ? array_unique(array_merge($model->getExportables(), $extraColumns))
            : $extraColumns;

        $customHeadings = [];

        if ($this->export instanceof WithHeadings) {
            $customHeadings = $this->export->headings();
        }

        if (array_is_list($customHeadings) && !empty($customHeadings)) {
            foreach ($customHeadings as $colIndex => $field) {
                $heading = $customHeadings[$colIndex] ?? Str::of($field)->replace('.', ' ')->title()->toString();

                $sheet->setCellValueByColumnAndRow(
                    $colIndex + 1,
                    1,
                    $heading,
                );
            }
        } else {
            foreach ($fields as $colIndex => $field) {

                $column = Str::of($field)->replace('.', '_')->toString();
                $heading = Str::of($column)->replace('_', ' ')->title()->toString();

                $sheet->setCellValueByColumnAndRow(
                    $colIndex + 1,
                    1,
                    $heading,
                );
            }
        }

        foreach ($this->collection as $rowIndex => $row) {
            if ($this->export instanceof WithMapping) {
                $row = (object) array_merge((array) $row, $this->export->map($row));
            }

            foreach ($fields as $colIndex => $field) {
                $stringBefore = Str::before($field, '.');
                $stringAfter = Str::after($field, '.');

                $relationship = $stringBefore === $stringAfter ? null : $stringBefore;
                $column = $stringBefore === $stringAfter ? $field : $stringAfter;

                $value = !is_null($relationship) && isset($row->$relationship) ? $row->$relationship->$column : (!is_null($relationship) ? '' : ($row->$column ?? ''));

                $sheet->setCellValueByColumnAndRow(
                    $colIndex + 1,
                    $rowIndex + 2,
                    $this->presentValue($value, $column)
                );
            }
        }

        $ext = $this->getExtension() ?? $this->getDefaultExtension();

        $writer = $ext === 'xls' ? new Xls($spreadsheet) : new Xlsx($spreadsheet);

        $dateFormat = ExcelConfig::get('exports.date_format', 'Ymd_Hi');
        $dateFormat = is_string($dateFormat) && $dateFormat !== '' ? $dateFormat : 'Ymd_Hi';

        $filename = Str::of(class_basename($model))->lower()->toString() . '_' . now()->format($dateFormat) . '.' . $ext;

        $directory = ExcelConfig::get('exports.temporary_path', sys_get_temp_dir());
        $directory = is_string($directory) && is_dir($directory) ? $directory : sys_get_temp_dir();

        $tempFile = tempnam($directory, $filename) . '.' . $ext;

        $writer->save($tempFile);

        ExportAuditContext::record($tempFile, $this->model, $this->collection->count(), $ext);

        return $tempFile;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @return void
     */
    private function applyProperties(Spreadsheet $spreadsheet): void
    {
        $properties = ExcelConfig::getArray('exports.properties', [
            'creator' => '',
            'title' => '',
            'company' => '',
        ]);

        if ($this->export instanceof WithProperties) {
            $properties = array_merge($properties, $this->export->properties());
        }

        $document = $spreadsheet->getProperties();

        foreach (['creator' => 'setCreator', 'title' => 'setTitle', 'company' => 'setCompany'] as $key => $setter) {
            if (!isset($properties[$key]) || !is_string($properties[$key]) || $properties[$key] === '') {
                continue;
            }

            $document->{$setter}($properties[$key]);
        }
    }

    /**
     * @param mixed $value
     * @param string $column
     * @return mixed
     */
    private function presentValue($value, string $column)
    {
        if ($value === null || $value === '') {
            return $this->emptyValue();
        }

        $format = $this->dateFormat($column);

        if ($format === null) {
            return $value;
        }

        try {
            return Date::parse($value)->format($format);
        } catch (\Throwable $exception) {
            return $value;
        }
    }

    /**
     * @param string $column
     * @return string|null
     */
    private function dateFormat(string $column): ?string
    {
        $column = strtolower($column);
        $formats = ExcelConfig::getArray('exports.formats.dates', [
            '_date' => 'j M Y',
        ]);
        $matched = null;
        $matchedLength = 0;

        foreach ($formats as $suffix => $format) {
            if (!is_string($suffix) || $suffix === '' || !is_string($format) || $format === '') {
                continue;
            }

            $suffix = strtolower($suffix);

            if (strlen($suffix) <= $matchedLength) {
                continue;
            }

            if (substr($column, -strlen($suffix)) === $suffix) {
                $matched = $format;
                $matchedLength = strlen($suffix);
            }
        }

        return $matched;
    }

    /**
     * @return string
     */
    private function emptyValue(): string
    {
        $empty = ExcelConfig::get('exports.empty_value', '');

        return is_string($empty) ? $empty : '';
    }
}
