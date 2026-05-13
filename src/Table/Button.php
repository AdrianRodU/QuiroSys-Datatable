<?php

namespace Esolutions\Datatable\Table;

use JsonSerializable;

/**
 * Clase para construir la configuración de un botón para tablas.
 */
class Button implements JsonSerializable
{
    protected ?string $label;

    protected ?string $icon;

    protected ?string $action;

    protected ?string $color;

    protected bool $disable;

    protected ?string $url;

    protected string $size;

    protected ?string $tooltip;

    /**
     * Constructor: inicializa los valores por defecto.
     */
    public function __construct()
    {
        $this->label = null;
        $this->icon = null;
        $this->action = null;
        $this->color = 'default';
        $this->disable = false;
        $this->url = null;
        $this->size = '14px';
        $this->tooltip = null;
    }

    /**
     * Crea una nueva instancia de Button.
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Define el texto (etiqueta) del botón.
     */
    public function label(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Define el icono del botón (usa FontAwesome por defecto).
     */
    public function icon(string $icon): self
    {
        $this->icon = 'fal fa-'.$icon;

        return $this;
    }

    /**
     * Define la acción que representa el botón (por ejemplo: new, edit, delete).
     */
    public function action(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Define el color principal del botón (ejemplo: primary, red, green).
     */
    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Establece si el botón estará deshabilitado.
     */
    public function disable(bool $disable): self
    {
        $this->disable = $disable;

        return $this;
    }

    /**
     * Define la URL asociada al botón (puede ser null si no aplica).
     */
    public function url(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Define el tamaño del botón (por defecto: 14px).
     */
    public function size(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Define el texto del tooltip (ayuda) para el botón.
     */
    public function tooltip(?string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * Devuelve el botón como array asociativo (para APIs o frontend).
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'icon' => $this->icon,
            'action' => $this->action,
            'color' => $this->color,
            'disable' => $this->disable,
            'url' => $this->url,
            'size' => $this->size,
            'tooltip' => $this->tooltip,
        ];
    }

    /**
     * Permite serializar directamente el botón a JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Botón preconfigurado para agregar nuevo (con parámetros opcionales).
     */
    public static function newButton(?string $label = null, ?string $url = null, ?string $tooltip = 'Nuevo'): self
    {
        return self::make()
            ->label($label)
            ->icon('plus')
            ->action('new')
            ->color('primary')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón preconfigurado para editar (con parámetros opcionales).
     */
    public static function editButton(?string $label = 'Editar', ?string $url = null, ?string $tooltip = 'Editar'): self
    {
        return self::make()
            ->label($label)
            ->icon('pencil')
            ->action('edit')
            ->color('default')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón preconfigurado para duplicar (con parámetros opcionales).
     */
    public static function duplicateButton(?string $label = 'Duplicar', ?string $url = null, ?string $tooltip = 'Duplicar'): self
    {
        return self::make()
            ->label($label)
            ->icon('copy')
            ->action('duplicate')
            ->color('default')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón preconfigurado para refrescar (con parámetros opcionales).
     */
    public static function refreshButton(?string $url = null, ?string $tooltip = ''): self
    {
        $tooltip = $tooltip === '' ? __('refresh') : $tooltip;

        return self::make()
            ->icon('arrows-rotate')
            ->action('refresh')
            ->color('primary')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón preconfigurado para exportar (con parámetros opcionales).
     */
    public static function exportButton(?string $label = 'Exportar', ?string $url = null, ?string $tooltip = 'Exportar'): self
    {
        return self::make()
            ->label($label)
            ->icon('download')
            ->action('export')
            ->color('primary')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón preconfigurado para eliminar (con parámetros opcionales).
     */
    public static function deleteButton(?string $label = 'Eliminar', ?string $url = null, ?string $tooltip = 'Eliminar'): self
    {
        return self::make()
            ->label($label)
            ->icon('xmark')
            ->action('delete')
            ->color('red')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón preconfigurado para ver detalle (con parámetros opcionales).
     */
    public static function viewButton(?string $label = 'Ver', ?string $url = null, ?string $tooltip = 'Ver'): self
    {
        return self::make()
            ->label($label)
            ->icon('eye')
            ->action('view')
            ->color('default')
            ->url($url)
            ->tooltip($tooltip);
    }

    /**
     * Botón que cambia el estado activo/inactivo según la fila (row).
     */
    public static function activeButton($row, ?string $label = null, ?string $url = null, ?string $tooltip = null): self
    {
        $isActive = is_array($row) ? ($row['is_active'] ?? false) : ($row->is_active ?? false);
        $icon = $isActive ? 'shield-xmark' : 'shield-check';
        $color = $isActive ? 'red' : 'green';
        $defaultLabel = $isActive ? 'Desactivar' : 'Activar';

        return self::make()
            ->label($label ?? $defaultLabel)
            ->icon($icon)
            ->action('active')
            ->color($color)
            ->url($url)
            ->tooltip($tooltip ?? $defaultLabel);
    }

    /**
     * Botón de estado activo/inactivo mostrando solo el icono (sin label).
     */
    public static function activeButtonOnlyIcon($row, ?string $url = null, ?string $tooltip = null): self
    {
        $isActive = is_array($row) ? ($row['is_active'] ?? false) : ($row->is_active ?? false);
        $icon = $isActive ? 'shield-xmark' : 'shield-check';
        $color = $isActive ? 'red' : 'green';
        $defaultTooltip = $isActive ? 'Desactivar' : 'Activar';

        return self::make()
            ->icon($icon)
            ->action('active')
            ->color($color)
            ->url($url)
            ->tooltip($tooltip ?? $defaultTooltip);
    }

    public static function separator(): array
    {
        return ['type' => 'separator'];
    }
}
