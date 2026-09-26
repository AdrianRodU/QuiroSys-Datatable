<?php

namespace Quirosys\Datatable\Traits;

use Illuminate\Http\Request;

trait PaginationBaseTrait
{
    protected string $pageTitle;

    protected string $tableName;

    protected string $tableTitle;

    protected array $visibleColumns = [];

    /** Columnas elegidas por el usuario para exportar (persistidas por tabla). */
    protected array $exportColumns = [];

    protected array $columns = [];

    protected array $filters = [];

    protected ?string $sortBy = 'id';

    protected bool $descending = true;

    protected string $direction = 'asc';

    protected int $perPage = 10;

    protected array $metaAdditional = [];

    public function initTableBase($model): array
    {
        $config = $this->getTableConfig();

        $this->pageTitle = __($config['page_title']);
        $this->tableTitle = __($config['table_title']);
        $this->tableName = $config['table_name'];
        $this->columns = $this->getColumns();
        $this->filters = $this->getFilters();

        $this->getConfigurationDataTableBase($model);

        $result = [
            'pageTitle' => $this->pageTitle,
            'tableName' => $this->tableName,
            'tableTitle' => $this->tableTitle,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'visibleColumns' => $this->visibleColumns,
            'exportColumns' => $this->exportColumns,
            'pagination' => $this->initPagination(),
            'headerButtons' => $this->getHeaderButtons(),
            'bulkActions' => method_exists($this, 'getBulkActions') ? $this->getBulkActions() : [],
            'mobileConfig' => method_exists($this, 'getMobileConfig') ? $this->getMobileConfig() : null,
        ];

        if (method_exists($this, 'getTableBadge')) {
            $result['tableBadge'] = $this->getTableBadge();
        }

        return $result;
    }

    public function initPagination(): array
    {
        $defaultSortable = collect($this->columns)->first(function ($col) {
            return isset($col['sortable']) ? (bool) $col['sortable'] : false;
        });

        $pageSizes = array_values(array_unique([5, 10, 20, 50, 3, $this->perPage]));

        return [
            'sortBy' => $defaultSortable['field'] ?? $this->sortBy,
            'descending' => $this->descending,
            'perPage' => $this->perPage,
            'pageSizes' => $pageSizes,
        ];
    }

    /** Carga/crea preferencia del usuario/tabla */
    protected function getConfigurationDataTableBase($model): void
    {
        $record = $model
            ->where('user_id', auth()->id())
            ->where('table', $this->tableName)
            ->first();

        if (! $record) {
            $this->visibleColumns = $this->extractVisibleColumns();
            $modelClass = get_class($model->getModel()); // obtiene la clase del modelo
            $model = new $modelClass;
            $model->user_id = auth()->id();
            $model->table = $this->tableName;
            $model->visible_columns = $this->visibleColumns;
            if ($this->supportsKnownColumns($model)) {
                $model->known_columns = $this->allColumnNames();
            }
            $model->records_per_page = 10;
            $model->sort_by = 'id';
            $model->descending = true;
            $model->save();
        } else {
            $visibleFromDb = $record->visible_columns ?? [];
            $this->visibleColumns = ! empty($visibleFromDb) ? $visibleFromDb : $this->extractVisibleColumns();
            $this->revealNewColumns($record);
            $this->exportColumns = $record->export_columns ?? [];
            $this->perPage = (int) ($record->records_per_page ?? 10);
            $this->sortBy = (string) ($record->sort_by ?: 'id');
            $this->descending = (bool) ($record->descending ?? true);
            $this->direction = $this->descending ? 'desc' : 'asc';
        }
    }

    /**
     * Persiste las columnas de exportación elegidas por el usuario para esta
     * tabla (para que no tenga que re-seleccionarlas la próxima vez).
     */
    public function persistExportColumnsBase($model, array $columns): void
    {
        $record = $model
            ->where('user_id', auth()->id())
            ->where('table', $this->tableName)
            ->first();

        if ($record) {
            $record->export_columns = array_values($columns);
            $record->save();
        }
    }

    /**
     * Columnas agregadas a la tabla DESPUÉS de que el usuario guardó su preferencia nacen
     * visibles. La preferencia solo guarda las visibles, así que sin `known_columns` no se
     * distingue "la ocultó el usuario" de "no existía cuando guardó": por eso se sella la
     * lista de columnas conocidas. Una preferencia vieja (known_columns NULL) revela una
     * sola vez toda columna ausente y queda sellada con las columnas de hoy.
     */
    private function revealNewColumns($record): void
    {
        if (! $this->supportsKnownColumns($record)) {
            return;
        }

        $all = $this->allColumnNames();
        $known = $record->known_columns;
        $hiddenByDesign = collect($this->columns ?? [])
            ->filter(fn ($col) => isset($col['visible']) && $col['visible'] === false)
            ->pluck('name')
            ->all();

        $baseline = is_array($known) ? $known : $this->visibleColumns;
        $new = array_values(array_diff($all, $baseline, $hiddenByDesign));

        if (empty($new) && is_array($known)) {
            return;
        }

        if (! empty($new)) {
            // Orden de la definición de la tabla; descarta nombres que ya no existen.
            $this->visibleColumns = array_values(array_intersect($all, array_merge($this->visibleColumns, $new)));
            $record->visible_columns = $this->visibleColumns;
        }
        $record->known_columns = $all;
        $record->save();
    }

    private function allColumnNames(): array
    {
        return collect($this->columns ?? [])->pluck('name')->filter()->values()->all();
    }

    /** `known_columns` llega por migración de la app; sin la columna se conserva el comportamiento anterior. */
    private function supportsKnownColumns($model): bool
    {
        static $cache = [];
        $key = get_class($model);
        if (! array_key_exists($key, $cache)) {
            $cache[$key] = Schema::connection($model->getConnectionName())->hasColumn($model->getTable(), 'known_columns');
        }

        return $cache[$key];
    }

    private function extractVisibleColumns(): array
    {
        if (empty($this->columns) || ! is_array($this->columns)) {
            return [];
        }

        return collect($this->columns)
            ->filter(function ($col) {
                // Por defecto, todas las columnas son visibles excepto las bloqueadas (locked)
                // Solo se excluye si está explícitamente marcada como no visible
                if (isset($col['visible']) && $col['visible'] === false) {
                    return false;
                }

                return true;
            })
            ->pluck('name')
            ->all();
    }

    public function updateConfigurationDataTableBase($model, Request $request): void
    {
        $this->tableName = $request->input('tableName', $this->tableName ?? '');
        $this->visibleColumns = $request->input('visibleColumns', $this->visibleColumns);
        // Un sortBy VACÍO (el front lo manda como '' cuando no hay columna elegida)
        // no es un orden: cae al vigente o a 'id'. Antes se usaba tal cual y la
        // consulta moría con "Unknown column '' in order clause" (lista de
        // pacientes del ERP, 2026-09-23).
        $requestedSort = trim((string) $request->input('sortBy', ''));
        $this->sortBy = $requestedSort !== '' ? $requestedSort : (trim((string) $this->sortBy) ?: 'id');
        $this->descending = (bool) $request->input('descending', $this->descending);
        $this->direction = $this->descending ? 'desc' : 'asc';
        $this->perPage = (int) $request->input('rowsPerPage', $this->perPage);

        $this->metaAdditional = [
            'meta' => [
                'sort_by' => $this->sortBy,
                'descending' => $this->descending,
            ],
        ];

        $record = $model
            ->where('user_id', auth()->id())
            ->where('table', $this->tableName)
            ->first();

        if ($record) {
            $record->records_per_page = $this->perPage;
            $record->sort_by = (string) $this->sortBy;
            $record->descending = $this->descending;
            $record->save();
        }
    }

    public function updateVisibleColumnsWithDataBase($model, array $inputs): array
    {
        if (! isset($inputs['table_name'], $inputs['visible_columns'])) {
            return [
                'success' => false,
                'message' => 'No se realizó la actualización',
            ];
        }

        $record = $model
            ->where('user_id', auth()->id())
            ->where('table', $inputs['table_name'])
            ->first();

        if ($record) {
            $visible = $inputs['visible_columns'];
            if (is_array($visible)) {
                $visible = array_values($visible);
            }
            $record->visible_columns = $visible;
            // Sella las columnas que existen hoy: lo que el usuario deja fuera es decisión suya.
            if ($this->supportsKnownColumns($record) && method_exists($this, 'getColumns')) {
                $record->known_columns = collect($this->getColumns())->pluck('name')->filter()->values()->all();
            }
            $record->save();
        }

        return [
            'success' => true,
            'message' => 'Actualización satisfactoria',
        ];
    }
}
