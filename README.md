# quirosys/datatable

Infraestructura para tablas server-side con paginación, filtros, columnas, diálogos de confirmación y exportación Excel.

## Instalación

Desde este repositorio (VCS), agregando en el `composer.json` del proyecto:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/AdrianRodU/QuiroSys-Datatable.git" }
]
```

```bash
composer require quirosys/datatable
```

## Namespace

```
Quirosys\Datatable\
```

## Dependencias

- `quirosys/laravel` — para `ApiResponse` y `AuthHelper`
- `maatwebsite/excel` — para exportación Excel

---

## Uso completo — ejemplo de un módulo

Cada módulo define un DataTable como trait y lo usa en su controlador:

```php
// Modules/Plan/DataTables/PlanDataTable.php
use Quirosys\Datatable\Table\{Button, ButtonBuilder, Column, ColumnBuilder, Filter, FilterBuilder};
use Quirosys\Datatable\Traits\PaginationSystemTrait;

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
use Quirosys\Datatable\Dialog\DialogAction;
use Quirosys\Datatable\Dialog\Requests\ActionRequest;
use Quirosys\Laravel\Auth\AuthHelper;
use Quirosys\Laravel\Http\ApiResponse;

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
// v2.2.0: qué pasa al desactivarlo, en una línea aparte debajo de la pregunta
DialogAction::getActiveRecordActionData($record, 'name', 'medio', false, 'La ficha deja de ofrecerlo.');
```

Detecta el estado actual (`is_active`) y retorna el texto correspondiente (Activar / Desactivar). Desde la
v2.2.0 el nombre va escapado (el frontend pinta la descripción con `v-html`).

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
use Quirosys\Datatable\Table\Column;

Column::make('name')->label('Nombre')
Column::make('price')->label('Precio')->alignRight()->width('100px')
Column::make('date')->label('Fecha')->alignCenter()
Column::isActive()    // columna "Activo" (v2.2.0): su celda es Cell::activeToggle()
Column::photo()       // columna "Foto" (v2.3.0): su celda es Cell::avatar()
Column::actions()     // columna estándar de botones de acción
```

### `ColumnBuilder`

```php
use Quirosys\Datatable\Table\ColumnBuilder;

$columns = (new ColumnBuilder())
    ->addColumn(Column::make('name')->label('Nombre'))
    ->addColumn(Column::isActive())
    ->addColumn(Column::actions())
    ->getColumns();
```

### Columna "Activo" (v2.2.0)

La convención de todas las tablas que se activan y desactivan: la columna `Column::isActive()` con un
interruptor verde. Ya no se usan `Button::activeButton*` ni `Cell::badgeIsActive` (quedan como obsoletos).

```php
'is_active' => Cell::activeToggle($row),                     // cualquiera que vea la tabla lo cambia
'is_active' => Cell::activeToggle($row, $puedeCambiar),      // sin permiso: bloqueado
'is_active' => Cell::activeToggle($row, true, 'toggle-active'), // un diálogo propio de la página
```

- Apagar pide confirmación con el diálogo de la tabla: `GET {resource}/record-active/{id}` y
  `POST {resource}/active` con `{ id, is_active: false }`.
- Encender va directo: `POST {resource}/active` con `{ id, is_active: true }`.
- El backend guarda el valor pedido (no invierte el actual): así una tabla desactualizada no lo deja al revés.
- Requiere `@quirosys/x-components` 2.25.0 (XTableServer, XCellColumnRenderer y XDialogAction).
- Con `@quirosys/x-components` 2.27.0 el interruptor queda centrado en su columna (antes, a la izquierda).

### Columna "Foto" (v2.3.0)

La foto de la persona de cada fila (personal, usuarios), en círculo y antes del nombre. Sin foto, sus iniciales
sobre el primario suave; sin iniciales, un ícono de persona. No sale en el Excel.

```php
->addColumn(Column::photo())
->addColumn(Column::make('name')->label('Nombre')->sortable())

'photo' => Cell::avatar($fotoUrl, $row->name, '32px', 'JN'),   // $fotoUrl null = sin foto: iniciales
```

- Requiere `@quirosys/x-components` 2.27.0 para la fila sin foto (antes `$src` era obligatorio).

---

## Table — Filtros

### `Filter`

```php
use Quirosys\Datatable\Table\Filter;

Filter::makeInput('search')->label('Buscar')->cssClass('col-12')
Filter::isActive('col-8 col-md-4')   // v2.2.0: Todos (por defecto) / Activos / Inactivos
Filter::isActive('col-12 col-md-3')->value('active')->default('active')   // arranca en Activos
Filter::makeSelect('status')->label('Estado')->options([
    ['id' => 'all', 'name' => 'Todos'],
    ['id' => 1,     'name' => 'Activo'],
    ['id' => 0,     'name' => 'Inactivo'],
])
```

La tabla lo aplica en su consulta:

```php
$query->tap(fn ($q) => Filter::applyIsActive($q, $filters));                 // is_active
$query->tap(fn ($q) => Filter::applyIsActive($q, $filters, 'persons.is_active')); // con joins
```

### `FilterBuilder`

```php
use Quirosys\Datatable\Table\FilterBuilder;

$filters = (new FilterBuilder())
    ->addFilter(Filter::makeInput('search')->label('Buscar')->cssClass('col-24 col-sm-12'))
    ->getFilters();
```

---

## Table — Botones de cabecera

```php
use Quirosys\Datatable\Table\{Button, ButtonBuilder};

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
use Quirosys\Datatable\Traits\PaginationSystemTrait;
// o
use Quirosys\Datatable\Traits\PaginationTenantTrait;
```

Ambos proveen: `updatePagination()`, `$perPage`, `$sortBy`, `$direction`, `$metaAdditional`.

---

## Exports — `GenericReportExport`

Exportador Excel reutilizable con título, encabezados estilizados y datos.

```php
use Quirosys\Datatable\Exports\GenericReportExport;
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
