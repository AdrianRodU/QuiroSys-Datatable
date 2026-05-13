<?php

namespace Esolutions\Datatable\Table;

use JsonSerializable;

/**
 * CellComponent: Represents an interactive cell rendered as a frontend component
 * (XToggle, XCheckbox, XInput, XSelect, etc.) with an optional action definition.
 *
 * Output contract (array) is consumed by the frontend XCellRenderer:
 * - type_input: 'component'
 * - component: string (e.g. 'XToggle')
 * - modelValue: mixed
 * - props: array
 * - action: array|null
 */
class CellComponent implements JsonSerializable
{
    protected string $component;

    protected $modelValue;

    protected array $props = [];

    protected ?array $action = null;

    public function __construct(string $component, $modelValue = null, array $props = [])
    {
        $this->component = $component;
        $this->modelValue = $modelValue;
        $this->props = $props;
    }

    /**
     * Generic factory.
     */
    public static function make(string $component, $modelValue = null, array $props = []): self
    {
        return new self($component, $modelValue, $props);
    }

    // -------------------------
    // Component shortcuts
    // -------------------------

    public static function toggle(bool $checked, array $props = []): self
    {
        return self::make('XToggle', $checked, $props);
    }

    public static function checkbox(bool $checked, array $props = []): self
    {
        return self::make('XCheckbox', $checked, $props);
    }

    public static function input($value, array $props = []): self
    {
        return self::make('XInput', $value, $props);
    }

    public static function select($value, array $options, array $props = []): self
    {
        $props['options'] = $options;

        return self::make('XSelect', $value, $props);
    }

    // -------------------------
    // Props
    // -------------------------

    public function modelValue($value): self
    {
        $this->modelValue = $value;

        return $this;
    }

    public function prop(string $key, $value): self
    {
        $this->props[$key] = $value;

        return $this;
    }

    public function props(array $props): self
    {
        $this->props = array_merge($this->props, $props);

        return $this;
    }

    // -------------------------
    // Actions
    // -------------------------

    /**
     * Define a generic API action that should run when the component value changes.
     * Frontend will replace '{id}' using row.id and replace '$value' with the new value.
     */
    public function api(
        string $method,
        string $url,
        array $data = [],
        bool $refresh = true,
        bool $optimistic = true
    ): self {
        $this->action = [
            'type' => 'api',
            'method' => strtolower($method),
            'url' => $url,
            'data' => $data,
            'refresh' => $refresh,
            'optimistic' => $optimistic,
        ];

        return $this;
    }

    public function apiPatch(string $url, array $data = [], bool $refresh = true, bool $optimistic = true): self
    {
        return $this->api('patch', $url, $data, $refresh, $optimistic);
    }

    public function apiPost(string $url, array $data = [], bool $refresh = true, bool $optimistic = true): self
    {
        return $this->api('post', $url, $data, $refresh, $optimistic);
    }

    /**
     * Optional confirmation before executing action.
     * Frontend can show a dialog if present.
     */
    public function confirm(string $title, string $message): self
    {
        if (! $this->action) {
            $this->action = ['type' => 'api']; // minimal; will be overwritten by api() later if needed
        }

        $this->action['confirm'] = [
            'title' => $title,
            'message' => $message,
        ];

        return $this;
    }

    /**
     * Optional debounce (ms) for inputs/selects (frontend responsibility).
     */
    public function debounce(int $ms): self
    {
        if (! $this->action) {
            $this->action = ['type' => 'api'];
        }
        $this->action['debounce'] = $ms;

        return $this;
    }

    public function when(bool $condition, \Closure $cb): self
    {
        if ($condition) {
            $cb($this);
        }

        return $this;
    }

    // -------------------------
    // Array / JSON
    // -------------------------

    public function toArray(): array
    {
        $arr = [
            'type_input' => 'component',
            'component' => $this->component,
            'modelValue' => $this->modelValue,
            'props' => $this->props,
        ];

        if ($this->action) {
            $arr['action'] = $this->action;
        }

        return $arr;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
