# Laraigniter Excel

Excel exports for Laraigniter. An export class collects the rows. The writer stores an `xlsx` or `xls` file through PhpSpreadsheet.

## Installation

```powershell
composer require lara-igniter/laraigniter-excel
```

`lara-igniter/activitylog` is optional. When that package is installed, `download()` writes an activity entry using `activity` and `filters` in this config. Without the package those keys are ignored and the file is still downloaded.

Register the provider in `config/hooks.php`:

```php
$hook = Hooks::autoload([
    'providers' => [
        Laraigniter\Excel\ExcelServiceProvider::class,
    ],
]);
```

The provider loads `config/excel.php` from the package. Publish a copy when the application should replace those defaults:

```powershell
php artisan vendor:publish --tag=excel-config
```

The copy lands in the application `config/excel.php` and replaces the package values. `setExtension()` still wins over `exports.extension` for that call.

`exports.formats.dates` maps a column suffix to its own display format. The default is `_date` with `j M Y`. Add `_at` with another format for columns such as `created_at`. The longest matching suffix wins. An empty cell uses `exports.empty_value`. `exports.properties` sets the workbook creator, title, and company when the value is not empty. An export implements `Laraigniter\Excel\Contracts\WithProperties` and returns `properties()` to replace only the keys it declares.

## Export a collection

```php
use Laraigniter\Excel\Managers\ExportManager;

$path = ExportManager::make('excel')
    ->setData($rows)
    ->setModel(Member::class)
    ->store();
```

`store()` returns the path of a temporary file under `storage/framework/cache/excel`. Pass `xls` with `setExtension('xls')` when the workbook should use the older format. The default extension is `xlsx`. `temporary_files.local_permissions` sets the directory mode when that folder is created and the file mode after the workbook is saved.

`setColumns()` adds fields on top of the model exportables. `store(false)` exports only those columns.

A model exposes its columns with the `HasExport` trait. `getExportables()` returns `$exportable` when that property is set, and `$fillable` otherwise.

```php
use Laraigniter\Excel\Concerns\HasExport;

class Member extends Model
{
    use HasExport;

    protected array $exportable = [
        'last_name',
        'email',
    ];
}
```

## Download

Use the `Exportable` trait on the export class. `download()` calls `export()`, sends the file, and deletes it afterwards.

```php
use Laraigniter\Excel\Concerns\Exportable;
use Laraigniter\Excel\Managers\ExportManager;

class MemberExport
{
    use Exportable;

    public function export(): string
    {
        return ExportManager::make('excel')
            ->setData($rows)
            ->setModel(Member::class)
            ->store();
    }
}

return MemberExport::download();
```

## Headings and mapped values

Implement `WithHeadings` to replace the generated heading row. Implement `WithMapping` to replace or add values before they are written. Pass the export instance as the second argument of `ExportManager::make()`.

```php
use Laraigniter\Excel\Concerns\Exportable;
use Laraigniter\Excel\Contracts\WithHeadings;
use Laraigniter\Excel\Contracts\WithMapping;
use Laraigniter\Excel\Managers\ExportManager;

class MemberExport implements WithHeadings, WithMapping
{
    use Exportable;

    public function export(): string
    {
        return ExportManager::make('excel', $this)
            ->setData($rows)
            ->setModel(Member::class)
            ->store();
    }

    public function headings(): array
    {
        return ['Last name', 'Email'];
    }

    public function map(object $row): array
    {
        return [
            'email' => $row->email,
        ];
    }
}
```

A heading list replaces the whole heading row. Mapped keys overwrite the matching field on that row. A field such as `address.city` reads `$row->address->city`.


## License

MIT — see [LICENSE.md](LICENSE.md).
