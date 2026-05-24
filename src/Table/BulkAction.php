<?php

namespace Esolutions\Datatable\Table;

use JsonSerializable;

/**
 * Configuracion de una accion masiva (bulk action) para tablas server-side.
 *
 * Aparece dentro del dropdown "X seleccionados" del XTableServer
 * cuando hay al menos 1 fila seleccionada.
 *
 * Ejemplo de uso en un DataTable trait:
 *
 *   protected function getBulkActions(): array
 *   {
 *       return [
 *           BulkAction::make()
 *               ->action('deactivate')
 *               ->label('Desactivar')
 *               ->icon('fa-light fa-ban')
 *               ->confirm('¿Desactivar {count} usuario(s)?'),
 *
 *           BulkAction::make()
 *               ->action('delete')
 *               ->label('Eliminar')
 *               ->icon('fa-light fa-trash')
 *               ->color('negative')
 *               ->divider()  // separa visualmente del item anterior
 *               ->confirm('¿Eliminar PERMANENTEMENTE {count} usuario(s)?', 'Si, eliminar'),
 *       ];
 *   }
 *
 * El frontend (XTableServer) emite @bulk-action con { action, ids }
 * cuando el usuario hace clic en una opcion del dropdown.
 */
class BulkAction implements JsonSerializable
{
    protected ?string $action = null;
    protected ?string $label = null;
    protected ?string $icon = null;
    protected ?string $color = null;       // 'negative', 'positive', 'warning', etc.
    protected ?string $confirm = null;     // mensaje de confirmacion, soporta {count}
    protected ?string $confirmLabel = null;
    protected ?string $confirmTitle = null;
    protected bool $divider = false;       // muestra separador visual antes de este item
    protected bool $disable = false;

    public static function make(): self
    {
        return new self;
    }

    /**
     * Identificador de la accion. El frontend lo enviara en el evento @bulk-action.
     * Ejemplo: 'deactivate', 'delete', 'export', 'change-plan'
     */
    public function action(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Texto visible en el dropdown.
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Icono opcional (Quasar / FontAwesome).
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Color del item: 'default', 'negative' (rojo), 'positive', 'warning', 'info'.
     * Se mapea a las variantes del XDropdownItem.
     */
    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Si se define, el frontend muestra un dialogo de confirmacion ANTES de emitir el evento.
     * Soporta el placeholder {count} que se reemplaza con la cantidad de seleccionados.
     *
     * Ejemplo: '¿Eliminar {count} usuario(s)?'
     */
    public function confirm(string $message, ?string $confirmLabel = null, ?string $title = null): self
    {
        $this->confirm = $message;
        $this->confirmLabel = $confirmLabel;
        $this->confirmTitle = $title;
        return $this;
    }

    /**
     * Muestra un separador (linea) ANTES de este item en el dropdown.
     * Util para separar acciones destructivas (eliminar).
     */
    public function divider(bool $value = true): self
    {
        $this->divider = $value;
        return $this;
    }

    /**
     * Deshabilita la accion (aparece grayed-out en el dropdown).
     */
    public function disable(bool $value = true): self
    {
        $this->disable = $value;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'action'       => $this->action,
            'label'        => $this->label,
            'icon'         => $this->icon,
            'color'        => $this->color,
            'confirm'      => $this->confirm,
            'confirmLabel' => $this->confirmLabel,
            'confirmTitle' => $this->confirmTitle,
            'divider'      => $this->divider,
            'disable'      => $this->disable,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
