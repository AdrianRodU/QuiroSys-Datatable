<?php

namespace Quirosys\Datatable\Table;

use JsonSerializable;

/**
 * Builder para gestionar y agrupar botones que serán enviados al frontend.
 * Permite agregar botones sueltos y grupos de botones (por ejemplo, dropdowns o split buttons).
 */
class ButtonBuilder implements JsonSerializable
{
    /**
     * @var array Colección de botones o grupos de botones.
     *            Cada elemento puede ser una instancia de Button (individual)
     *            o un array de tipo ['type' => 'group', 'label' => ..., 'icon' => ..., 'buttons' => [...]]
     */
    protected array $buttons = [];

    /**
     * Agrega un botón individual a la colección.
     *
     * @param  Button  $button  Instancia de Button.
     * @return $this
     */
    public function addButton(Button $button): self
    {
        $this->buttons[] = $button;

        return $this;
    }

    /**
     * Agrega un grupo de botones como una sola opción en la colección.
     * Útil para representar button-groups, dropdowns, etc. en el frontend.
     *
     * @param  Button[]  $buttons  Array de instancias Button.
     * @param  string|null  $label  Texto opcional para mostrar como título del grupo.
     * @param  string|null  $icon  Icono opcional para el grupo.
     * @return $this
     */
    public function addButtonGroup(array $buttons, ?string $label = null, ?string $icon = 'fal fa-ellipsis-vertical'): self
    {
        // 1) eliminar nulls y valores falsy que no sean botones
        $buttons = array_values(array_filter($buttons, fn ($b) => $b !== null));

        if (empty($buttons)) {
            return $this;
        }

        // 2) opcional: si quieres ser más estricto, deja solo Button o arrays
        // $buttons = array_values(array_filter($buttons, fn ($b) => $b instanceof Button || is_array($b)));

        $group = [
            'type' => 'group',
            'label' => $label,
            'icon' => $icon,
            'size' => '14px',
            'buttons' => array_map(fn ($b) => $b instanceof Button ? $b->toArray() : $b, $buttons),
        ];
        $this->buttons[] = $group;

        return $this;
    }

    /**
     * Agrega múltiples botones individuales de una vez.
     * Ignora elementos que no sean instancias de Button.
     *
     * @param  Button[]  $buttons
     * @return $this
     */
    public function addButtons(array $buttons): self
    {
        foreach ($buttons as $button) {
            if ($button instanceof Button) {
                $this->addButton($button);
            }
        }

        return $this;
    }

    /**
     * Devuelve la colección de botones lista para enviar al frontend.
     * Los grupos de botones se mantienen como arrays con 'type' => 'group'.
     */
    public function getButtons(): array
    {
        return array_map(function ($item) {
            if ($item instanceof Button) {
                return $item->toArray();
            }

            // Si ya es un grupo, se devuelve tal cual.
            return $item;
        }, $this->buttons);
    }

    /**
     * Permite serializar el builder directamente a JSON (por ej. en API Resource).
     */
    public function jsonSerialize(): array
    {
        return $this->getButtons();
    }
}
