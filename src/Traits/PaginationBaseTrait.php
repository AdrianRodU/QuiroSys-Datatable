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
            $model->records_per_page = 10;
            $model->sort_by = 'id';
            $model->descending = true;
            $model->save();
        } else {
            $visibleFromDb = $record->visible_columns ?? [];
            $this->visibleColumns = ! empty($visibleFromDb) ? $visibleFromDb : $this->extractVisibleColumns();
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
        $this->sortBy = $request->input('sortBy', $this->sortBy);
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
            $record->save();
        }

        return [
            'success' => true,
            'message' => 'Actualización satisfactoria',
        ];
    }
}
