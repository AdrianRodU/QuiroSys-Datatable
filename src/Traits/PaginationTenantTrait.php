<?php

namespace Esolutions\Datatable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Configuration\Models\ConfigurationDataTable;

trait PaginationTenantTrait
{
    use PaginationBaseTrait;

    protected function defaultModelQuery(): Builder
    {
        return ConfigurationDataTable::query();
    }

    public function initTable(): array
    {
        return $this->initTableBase($this->defaultModelQuery());
    }

    public function updatePagination(Request $request): void
    {
        $this->updateConfigurationDataTableBase($this->defaultModelQuery(), $request);
    }

    public function updateVisibleColumns(Request $request): array
    {
        $this->updateVisibleColumnsWithDataBase($this->defaultModelQuery(), $request->all());

        return [
            'success' => true,
            'message' => 'Actualización satisfactoria',
        ];
    }
}
