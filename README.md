# esolutions/datatable

Infraestructura para tablas server-side con paginación, filtros, columnas, diálogos de confirmación y exportación Excel.

## Instalación

```bash
composer require esolutions/datatable
```

## Namespace

```
Esolutions\Datatable\
```

## Dependencias

- `esolutions/laravel` — para `ApiResponse` y `AuthHelper`
- `maatwebsite/excel` — para exportación Excel

---

## Uso completo — ejemplo de un módulo

Cada módulo define un DataTable como trait y lo usa en su controlador:

```php
// Modules/Plan/DataTables/PlanDataTable.php
use Esolutions\Datatable\Table\{Button, ButtonBuilder, Column, ColumnBuilder, Filter, FilterBuilder};
use Esolutions\Datatable\Traits\PaginationSystemTrait;

trait PlanDataTable
{
    use PaginationSystemTrait;

    protected function getTableConfig(): array
    {
        return [
            'page_title'  => __('plans title page'),
            'table_title' => __('plans title table'),
            'table_name'  => 'plans',
        ];
    }

    protected function getHeaderButtons(): array
    {
        return (new ButtonBuilder())
            ->addButton(Button::newButton())
            ->addButton(Button::refreshButton())
            ->addButtonGroup([Button::exportButton()])
            ->getButtons();
    }

    protected function getColumns(): array
    {
        return (new ColumnBuilder())
            ->addColumn(Column::make('name')->label('Nombre'))
            ->addColumn(Column::make('price')->label('Precio')->alignRight()->width('100px'))
            ->addColumn(Column::isActive())
            ->addColumn(Column::actions())
            ->getColumns();
    }

    protected function getFilters(): array
    {
        return (new FilterBuilder())
            ->addFilter(Filter::makeInput('search')->label('Buscar')->cssClass('col-12'))
            ->getFilters();
    }
}
```

```php
// Modules/Plan/Http/Controllers/PlanController.php
use Esolutions\Datatable\Dialog\DialogAction;
use Esolutions\Datatable\Dialog\Requests\ActionRequest;
use Esolutions\Laravel\Auth\AuthHelper;
use Esolutions\Laravel\Http\ApiResponse;

class PlanController extends Controller
{
    use PlanDataTable;

    // GET /plans/record/{id}
    public function record($id) { ... }

    // POST /plans/store
    public function store(PlanRequest $request) { ... }

    // GET /plans/record-active/{id}
    public function recordActive($id)
    {
        $record = Plan::findOrFail($id);
        return DialogAction::getActiveRecordActionData($record, 'name', 'plan');
    }

    // POST /plans/store-active
    public function storeActive(ActionRequest $request)
    {
        $password = $request->input('password');
        if ($password && !AuthHelper::checkPassword($password)) {
            return ApiResponse::error('La contraseña es incorrecta', 403);
        }
        $record = Plan::findOrFail($request->input('id'));
        $record->update(['is_active' => !$record->is_active]);
        return ApiResponse::success($record->is_active ? 'Activado' : 'Desactivado');
    }

    // GET /plans/record-delete/{id}
    public function recordDelete($id)
    {
        $record = Plan::findOrFail($id);
        return DialogAction::getDeleteRecordActionData($record, 'name', 'plan');
    }

    // POST /plans/delete
    public function storeDelete(ActionRequest $request)
    {
        $password = $request->input('password');
        if ($password && !AuthHelper::checkPassword($password)) {
            return ApiResponse::error('La contraseña es incorrecta', 403);
        }
        Plan::findOrFail($request->input('id'))->delete();
        return ApiResponse::success('Plan eliminado');
    }
}
```

---

## Dialog — `DialogAction`

Genera la configuración del diálogo de confirmación que el frontend renderiza.

### `getDeleteRecordActionData`

```php
DialogAction::getDeleteRecordActionData(
    record: $record,
    nameField: 'name',       // campo que muestra el nombre en el diálogo
    type: 'plan',            // texto del tipo de registro
    verifyPassword: false    // true = el frontend muestra campo de contraseña
);
```

Retorna:
```json
{
    "title": "Eliminar plan",
    "description": "¿Está seguro que desea eliminar el plan <strong>Básico</strong>?",
    "button_label_submit": "Eliminar",
    "button_color": "red",
    "icon": "triangle-exclamation",
    "icon_color": "red",
    "verify_password": false
}
```

### `getActiveRecordActionData`

```php
DialogAction::getActiveRecordActionData($record, 'name', 'plan');
```

Detecta el estado actual (`is_active`) y retorna el texto correspondiente (Activar / Desactivar).

### `getActionData`

Para acciones personalizadas:

```php
DialogAction::getActionData(
    title: 'Sincronizar datos',
    description: '¿Desea sincronizar con SUNAT?',
    options: ['button_color' => 'primary', 'verify_password' => true]
);
```

---

## Dialog — `ActionRequest`

FormRequest estándar para los endpoints `storeDelete` y `storeActive`.

**Reglas de validación:**

| Campo | Regla |
|---|---|
| `id` | `required` |
| `password` | `nullable`, `required_if:verify_password,true` |

---

## Table — Columnas

### `Column`

```php
use Esolutions\Datatable\Table\Column;

Column::make('name')->label('Nombre')
Column::make('price')->label('Precio')->alignRight()->width('100px')
Column::make('date')->label('Fecha')->alignCenter()
Column::isActive()    // columna estándar de estado activo/inactivo
Column::actions()     // columna estándar de botones de acción
```

### `ColumnBuilder`

```php
use Esolutions\Datatable\Table\ColumnBuilder;

$columns = (new ColumnBuilder())
    ->addColumn(Column::make('name')->label('Nombre'))
    ->addColumn(Column::isActive())
    ->addColumn(Column::actions())
    ->getColumns();
```

---

## Table — Filtros

### `Filter`

```php
use Esolutions\Datatable\Table\Filter;

Filter::makeInput('search')->label('Buscar')->cssClass('col-12')
Filter::makeSelect('status')->label('Estado')->options([
    ['id' => 'all', 'name' => 'Todos'],
    ['id' => 1,     'name' => 'Activo'],
    ['id' => 0,     'name' => 'Inactivo'],
])
```

### `FilterBuilder`

```php
use Esolutions\Datatable\Table\FilterBuilder;

$filters = (new FilterBuilder())
    ->addFilter(Filter::makeInput('search')->label('Buscar')->cssClass('col-24 col-sm-12'))
    ->getFilters();
```

---

## Table — Botones de cabecera

```php
use Esolutions\Datatable\Table\{Button, ButtonBuilder};

$buttons = (new ButtonBuilder())
    ->addButton(Button::newButton())
    ->addButton(Button::refreshButton())
    ->addButtonGroup([Button::exportButton()])
    ->getButtons();
```

| Botón | Descripción |
|---|---|
| `Button::newButton()` | Abre el formulario de creación |
| `Button::refreshButton()` | Recarga la tabla |
| `Button::exportButton()` | Exporta a Excel |

---

## Traits de paginación

### `PaginationSystemTrait`

Para tablas en el contexto del sistema central (base de datos `central`).

### `PaginationTenantTrait`

Para tablas dentro del contexto de un tenant.

```php
use Esolutions\Datatable\Traits\PaginationSystemTrait;
// o
use Esolutions\Datatable\Traits\PaginationTenantTrait;
```

Ambos proveen: `updatePagination()`, `$perPage`, `$sortBy`, `$direction`, `$metaAdditional`.

---

## Exports — `GenericReportExport`

Exportador Excel reutilizable con título, encabezados estilizados y datos.

```php
use Esolutions\Datatable\Exports\GenericReportExport;
use Maatwebsite\Excel\Facades\Excel;

public function exportRecords(Request $request)
{
    $records = Plan::all()->map(fn($r) => [$r->name, $r->price])->toArray();

    return Excel::download(
        new GenericReportExport(
            data: $records,
            headings: ['Nombre', 'Precio'],
            title: 'Reporte de Planes'
        ),
        'reporte_planes.xlsx'
    );
}
```

**Formato del Excel generado:**
- **Fila 1:** Título centrado, negrita, tamaño 18, combinado en todas las columnas
- **Fila 2:** Encabezados con fondo azul `#1976d2`, texto blanco, negrita
- **Filas de datos:** Bordes grises, texto envuelto, columnas auto-dimensionadas
