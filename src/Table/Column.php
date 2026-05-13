<?php

namespace Esolutions\Datatable\Table;

use JsonSerializable;

/**
 * Clase para definir columnas de una tabla que serán visualizadas en el frontend.
 */
class Column implements JsonSerializable
{
    public string $name;

    public ?string $label = null;

    public ?string $align = 'left';

    public ?string $width = null;

    public bool $sortable = false;

    public ?string $sort_field = null;

    public bool $searchable = false;

    public bool $locked = false;

    public bool $visible = true;

    public bool $only_export = false;

    public bool $summable = false;

    public ?int $excel_width = null;

    public ?string $excel_format = null;

    public bool $excel_wrap = false;

    /**
     * Constructor principal, solo requiere el nombre de la columna.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Método de factoría para crear una nueva columna.
     */
    public static function make(string $name): self
    {
        return new self($name);
    }

    /**
     * Define el label (cabecera) de la columna.
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Define la alineación ('left', 'right', 'center') de la columna.
     */
    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    /**
     * Atajo para alinear a la derecha.
     */
    public function alignRight(): self
    {
        return $this->align('right');
    }

    /**
     * Atajo para alinear al centro.
     */
    public function alignCenter(): self
    {
        return $this->align('center');
    }

    /**
     * Define el ancho de la columna (ej: '120px', '10%').
     */
    public function width(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Indica si la columna es ordenable.
     */
    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Define el nombre real de la columna en BD para ORDER BY.
     * Usar cuando el nombre en el frontend difiere del de la BD.
     */
    public function sortField(string $field): self
    {
        $this->sort_field = $field;

        return $this;
    }

    /**
     * Indica si la columna es buscable.
     */
    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Indica si la columna está "bloqueada" (ej: no desplazable).
     */
    public function locked(bool $locked = true): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Indica si la columna es visible por defecto en la tabla.
     */
    public function visible(bool $visible = true): self
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Marca la columna como solo-exportación (no se muestra en la tabla del frontend).
     */
    public function onlyExport(bool $onlyExport = true): self
    {
        $this->only_export = $onlyExport;

        return $this;
    }

    /**
     * Marca la columna como sumable (se incluye en fila de totales al exportar).
     */
    public function summable(bool $summable = true): self
    {
        $this->summable = $summable;

        return $this;
    }

    /**
     * Define el ancho fijo de la columna en Excel (en caracteres).
     */
    public function excelWidth(int $width): self
    {
        $this->excel_width = $width;

        return $this;
    }

    /**
     * Define el formato numérico en Excel (ej: '#,##0.00', '0', 'dd/mm/yyyy').
     */
    public function excelFormat(string $format): self
    {
        $this->excel_format = $format;

        return $this;
    }

    /**
     * Activa wrap text en Excel para celdas con contenido multilínea.
     */
    public function excelWrap(bool $wrap = true): self
    {
        $this->excel_wrap = $wrap;

        return $this;
    }

    /**
     * Convierte la columna a un array asociativo para el frontend.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'align' => $this->align,
            'width' => $this->width,
            'sortable' => $this->sortable,
            'sort_field' => $this->sort_field,
            'searchable' => $this->searchable,
            'locked' => $this->locked,
            'visible' => $this->visible,
            'only_export' => $this->only_export,
            'summable' => $this->summable,
            'excel_width' => $this->excel_width,
            'excel_format' => $this->excel_format,
            'excel_wrap' => $this->excel_wrap,
        ];
    }

    /**
     * Permite serializar la columna directamente a JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Columna predefinida para marcar si es default.
     */
    public static function isDefault(): self
    {
        return self::make('is_default')
            ->label(__('¿Predeterminado?'))
            ->alignCenter()
            ->width('120px')
            ->locked()
            ->sortable(false);
    }

    /**
     * Columna predefinida para estado activo/inactivo.
     */
    public static function isActive(): self
    {
        return self::make('is_active')
            ->label(__('¿is active?'))
            ->alignCenter()
            ->width('120px')
            ->locked()
            ->sortable(false);
    }

    /**
     * Columna predefinida para acciones.
     */
    public static function actions(): self
    {
        return self::make('actions')
            ->label(__('actions'))
            ->alignRight()
            ->width('180px')
            ->locked()
            ->sortable(false);
    }
}
