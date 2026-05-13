<?php

namespace Esolutions\Datatable\Table;

use JsonSerializable;

/**
 * Clase para construir una colección de filtros para la tabla.
 */
class FilterBuilder implements JsonSerializable
{
    /**
     * @var Filter[]
     */
    protected array $filters = [];

    /**
     * Agrega un filtro a la colección.
     */
    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Permite agregar varios filtros a la vez.
     *
     * @param  Filter[]  $filters
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            if ($filter instanceof Filter) {
                $this->addFilter($filter);
            }
        }

        return $this;
    }

    /**
     * Retorna la configuración de los filtros como array.
     */
    public function getFilters(): array
    {
        return array_map(fn ($filter) => $filter->toArray(), $this->filters);
    }

    /**
     * Para serialización directa a JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->getFilters();
    }
}
