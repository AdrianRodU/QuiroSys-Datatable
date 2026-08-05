<?php

namespace Quirosys\Datatable\Table;

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
     * Pesos por tipo de filtro para auto-distribucion proporcional.
     * Mayor peso = mas espacio. Input/date son mas anchos que select.
     */
    protected const TYPE_WEIGHTS = [
        'input'       => 2,
        'select'      => 1,
        'tree-select' => 1,
        'date'        => 2,
    ];

    /**
     * Retorna la configuración de los filtros como array.
     *
     * AUTO-LAYOUT: filtros sin class explicito se distribuyen automaticamente
     * para llenar el row (col-24 grid). Pesos varian por tipo.
     *
     * Ejemplos:
     *  - 1 filtro auto         -> col-24
     *  - 2 selects auto        -> col-12 + col-12
     *  - 1 input + 1 select    -> col-16 + col-8 (input pesa mas)
     *  - 3 selects auto        -> col-8 + col-8 + col-8
     *  - 1 input + 2 selects   -> col-12 + col-6 + col-6
     *
     * Filtros con cssClass() manual mantienen su valor; el auto reparte solo
     * el espacio sobrante.
     *
     * Las clases generadas son responsivas: 'col-24 col-sm-N' (mobile = 100%,
     * desktop = N de 24).
     */
    public function getFilters(): array
    {
        $this->autoLayout();
        return array_map(fn ($filter) => $filter->toArray(), $this->filters);
    }

    /**
     * Calcula y asigna classes a filtros que no tengan class explicito.
     */
    protected function autoLayout(): void
    {
        if (empty($this->filters)) return;

        // Separar filtros con/sin class manual + calcular cols ocupados por los manuales
        $autoIndices = [];
        $manualCols = 0;

        foreach ($this->filters as $i => $filter) {
            $class = $filter->getClass();
            if ($class === null || $class === '') {
                $autoIndices[] = $i;
            } else {
                // Extraer "col-N" para descontar del total (acepta col-N o col-sm-N etc.)
                if (preg_match('/(?:^|\s)col-(?:sm-|md-|lg-|xl-)?(\d+)\b/', $class, $m)) {
                    $manualCols += (int) $m[1];
                }
            }
        }

        if (empty($autoIndices)) return; // Todos manuales, nada que distribuir

        $remaining = max(0, 24 - $manualCols);
        if ($remaining <= 0) {
            // No hay espacio, los auto van a col-24 como fallback (wrap a nueva fila)
            foreach ($autoIndices as $i) {
                $this->filters[$i]->setClassInternal('col-24');
            }
            return;
        }

        // Calcular pesos por tipo
        $weights = [];
        $totalWeight = 0;
        foreach ($autoIndices as $i) {
            $w = self::TYPE_WEIGHTS[$this->filters[$i]->getType()] ?? 1;
            $weights[$i] = $w;
            $totalWeight += $w;
        }

        // Distribuir proporcional + ajustar el ultimo con el sobrante (rounding)
        $assigned = 0;
        $lastAutoIndex = end($autoIndices);
        foreach ($autoIndices as $i) {
            if ($i === $lastAutoIndex) {
                $col = $remaining - $assigned;
            } else {
                $col = (int) floor($remaining * $weights[$i] / $totalWeight);
                $col = max(1, $col); // minimo col-1
                $assigned += $col;
            }
            $this->filters[$i]->setClassInternal("col-24 col-sm-{$col}");
        }
    }

    /**
     * Para serialización directa a JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->getFilters();
    }
}
