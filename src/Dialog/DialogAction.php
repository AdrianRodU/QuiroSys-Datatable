<?php

namespace Quirosys\Datatable\Dialog;

use Illuminate\Database\Eloquent\Model;

class DialogAction
{
    public static function getDeleteRecordActionData(Model $record, string $nameField = 'name', string $type = 'registro', bool $verifyPassword = false): array
    {
        // Escapado (v2.2.0): XDialogAction pinta la descripción con v-html.
        $name = e((string) $record->{$nameField});
        return [
            'title'               => "Eliminar $type",
            // Tuteo (2026-10-03): antes "¿Está seguro que desea…? Esta acción no puede ser deshecha.".
            'description'         => "¿Seguro que quieres eliminar $type <strong>$name</strong>? No se puede deshacer.",
            'button_label_submit' => __('delete'),
            'button_color'        => 'red',
            'icon'                => 'triangle-exclamation',
            'icon_color'          => 'red',
            'verify_password'     => $verifyPassword,
        ];
    }

    /**
     * @param  string|null  $deactivateHint  Qué pasa al desactivarlo (v2.2.0), en una línea aparte debajo de la
     *                                       pregunta; p. ej. "La ficha deja de ofrecerlo".
     */
    public static function getActiveRecordActionData(Model $record, string $nameField = 'name', string $type = 'registro', bool $verifyPassword = false, ?string $deactivateHint = null): array
    {
        $isActive = (bool) $record->is_active;
        // Escapado (v2.2.0): XDialogAction pinta la descripción con v-html.
        $name     = e((string) $record->{$nameField});
        $hint     = $isActive && $deactivateHint ? '<div class="q-mt-sm text-grey-7">' . e($deactivateHint) . '</div>' : '';
        return [
            // Sin artículo, como "Eliminar $type": "el $type" salía "el sala", "el categoría"… en los tipos femeninos.
            'title'               => $isActive ? "Desactivar $type" : "Activar $type",
            'description'         => $isActive
                ? "¿Seguro que quieres desactivar $type <strong>$name</strong>?$hint"
                : "¿Seguro que quieres activar $type <strong>$name</strong>?",
            'button_label_submit' => $isActive ? __('deactivate') : __('activate'),
            'button_color'        => $isActive ? 'red' : 'green',
            'icon'                => $isActive ? 'shield-xmark' : 'shield-check',
            'icon_color'          => $isActive ? 'red' : 'green',
            'verify_password'     => $verifyPassword,
        ];
    }

    public static function getActionData(string $title, string $description, array $options = []): array
    {
        return array_merge([
            'title'               => $title,
            'description'         => $description,
            'button_label_submit' => __('confirm'),
            'button_color'        => 'primary',
            'icon'                => 'circle-info',
            'icon_color'          => 'primary',
            'verify_password'     => false,
        ], $options);
    }
}
