<?php

namespace Quirosys\Datatable\Dialog;

use Illuminate\Database\Eloquent\Model;

class DialogAction
{
    public static function getDeleteRecordActionData(Model $record, string $nameField = 'name', string $type = 'registro', bool $verifyPassword = false): array
    {
        $name = $record->{$nameField};
        return [
            'title'               => "Eliminar $type",
            'description'         => "¿Está seguro que desea eliminar $type <strong>$name</strong>? Esta acción no puede ser deshecha.",
            'button_label_submit' => __('delete'),
            'button_color'        => 'red',
            'icon'                => 'triangle-exclamation',
            'icon_color'          => 'red',
            'verify_password'     => $verifyPassword,
        ];
    }

    public static function getActiveRecordActionData(Model $record, string $nameField = 'name', string $type = 'registro', bool $verifyPassword = false): array
    {
        $isActive = (bool) $record->is_active;
        $name     = $record->{$nameField};
        return [
            'title'               => $isActive ? "Desactivar el $type" : "Activar el $type",
            'description'         => $isActive
                ? "¿Está seguro que desea desactivar el $type <strong>$name</strong>?"
                : "¿Está seguro que desea activar el $type <strong>$name</strong>?",
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
