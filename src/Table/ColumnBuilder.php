<?php

namespace Esolutions\Datatable\Table;

/**
 * Clase para construir una colección de columnas para la tabla.
 */
class ColumnBuilder implements \JsonSerializable
{
    /**
     * @var Column[] Colección de columnas agregadas.
     */
    protected array $columns = [];

    /**
     * Agrega una columna a la colección.
     */
    public function addColumn(Column $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * Permite agregar varias columnas a la vez.
     *
     * @param  Column[]  $columns
     */
    public function addColumns(array $columns): self
    {
        foreach ($columns as $column) {
            if ($column instanceof Column) {
                $this->addColumn($column);
            }
        }

        return $this;
    }

    /**
     * Retorna la configuración de todas las columnas como array.
     */
    public function getColumns(): array
    {
        return array_map(fn ($column) => $column->toArray(), $this->columns);
    }

    /**
     * Permite serializar directamente la colección de columnas a JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->getColumns();
    }
}
