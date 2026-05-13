<?php

namespace Esolutions\Datatable\Table;

/**
 * Clase para definir un filtro configurable para tablas del frontend.
 */
class Filter implements \JsonSerializable
{
    protected ?string $label = null;

    protected ?string $name = null;

    protected ?string $type = null;

    protected ?array $options = null;

    protected ?string $default = null;

    /** @var mixed */
    public $value = null;

    protected bool $includeAllOption = false;

    protected ?string $class = null;

    protected string $dateStart = '';

    protected string $dateEnd = '';

    protected string $monthStart = '';

    protected string $monthEnd = '';

    protected bool $filterLocal = false;

    // --- XTreeSelect ---
    protected bool $withFilter = false;

    protected bool $multiple = false;

    protected bool $onlyLeafSelectable = false;

    protected string $optionValue = 'id';

    protected string $optionLabel = 'label';

    protected string $optionChildren = 'children';

    protected ?string $dependsOn = null;     // nombre del padre (ej: company_id)

    protected ?array $remote = null;         // { url, method, params }

    protected bool $resetOnParentChange = true;

    protected bool $disableWhenParentEmpty = true;

    protected bool $clearable = false;

    protected bool $filterable = false;

    protected ?string $searchUrl = null;

    protected function __construct() {}

    public static function make(string $name): self
    {
        $instance = new self;
        $instance->name = $name;

        return $instance;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function default(?string $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function value(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function dateStart(string $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function dateEnd(string $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function monthStart(string $monthStart): self
    {
        $this->monthStart = $monthStart;

        return $this;
    }

    public function monthEnd(string $monthEnd): self
    {
        $this->monthEnd = $monthEnd;

        return $this;
    }

    public function includeAllOption(bool $include = true): self
    {
        $this->includeAllOption = $include;
        if ($include && in_array($this->type, ['select', 'tree-select'], true)) {
            $this->value = 'all';
        }

        return $this;
    }

    public function filterLocal(bool $filterLocal = false): self
    {
        $this->filterLocal = $filterLocal;

        return $this;
    }

    // -------------------------
    // XTreeSelect helpers
    // -------------------------
    public function withFilter(bool $v = true): self
    {
        $this->withFilter = $v;

        return $this;
    }

    public function multiple(bool $v = true): self
    {
        $this->multiple = $v;

        return $this;
    }

    public function onlyLeafSelectable(bool $v = true): self
    {
        $this->onlyLeafSelectable = $v;

        return $this;
    }

    public function optionValue(string $key): self
    {
        $this->optionValue = $key;

        return $this;
    }

    public function optionLabel(string $key): self
    {
        $this->optionLabel = $key;

        return $this;
    }

    public function optionChildren(string $key): self
    {
        $this->optionChildren = $key;

        return $this;
    }

    public function clearable(bool $v = true): self
    {
        $this->clearable = $v;

        return $this;
    }

    public function filterable(bool $v = true): self
    {
        $this->filterable = $v;

        return $this;
    }

    public function searchUrl(string $url): self
    {
        $this->searchUrl = $url;
        $this->filterable = true;

        return $this;
    }

    public function cssClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function toArray(): array
    {
        $options = $this->options;

        // Para XTreeSelect no existe 'includeAllOption' nativo como en XSelect.
        // Si se solicita, envolvemos el árbol dentro de un nodo raíz "All".
        if ($this->includeAllOption && $this->type === 'tree-select') {
            $options = [
                [
                    $this->optionValue => 'all',
                    $this->optionLabel => __('all'),
                    $this->optionChildren => is_array($options) ? $options : [],
                    'selectable' => true,
                ],
            ];
        }

        return [
            'label' => $this->label,
            'name' => $this->name,
            'type' => $this->type,
            'options' => $options,
            'default' => $this->default,
            'value' => $this->value,
            'includeAllOption' => $this->includeAllOption,
            'class' => $this->class,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'monthStart' => $this->monthStart,
            'monthEnd' => $this->monthEnd,
            'filterLocal' => $this->filterLocal,

            // XTreeSelect props
            'withFilter' => $this->withFilter,
            'multiple' => $this->multiple,
            'onlyLeafSelectable' => $this->onlyLeafSelectable,
            'optionValue' => $this->optionValue,
            'optionLabel' => $this->optionLabel,
            'optionChildren' => $this->optionChildren,

            'clearable' => $this->clearable,
            'filterable' => $this->filterable,
            'searchUrl' => $this->searchUrl,
            'dependsOn' => $this->dependsOn,
            'remote' => $this->remote,
            'resetOnParentChange' => $this->resetOnParentChange,
            'disableWhenParentEmpty' => $this->disableWhenParentEmpty,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function makeInput(string $name, string $label = '', string $class = 'col-6'): self
    {
        return self::make($name)
            ->label($label)
            ->type('input')
            ->default('')
            ->cssClass($class);
    }

    public static function makeSelect(string $name, string $label = '', array $options = [], string $class = 'col-6'): self
    {
        return self::make($name)
            ->label($label)
            ->type('select')
            ->options($options)
            ->default('all')
            ->includeAllOption()
            ->cssClass($class);
    }

    /**
     * Filtro predefinido tipo tree-select (XTreeSelect).
     */
    public static function makeTreeSelect(string $name, string $label = '', array $options = [], string $class = 'col-6'): self
    {
        return self::make($name)
            ->label($label)
            ->type('tree-select')
            ->options($options)
            ->default('all')
            ->includeAllOption()
            ->withFilter(true)
            ->cssClass($class);
    }

    /**
     * Filtro select con búsqueda remota al backend (autocomplete).
     */
    public static function makeSearch(string $name, string $label, string $url, string $class = 'col-6'): self
    {
        return self::make($name)
            ->label($label)
            ->type('select')
            ->options([])
            ->optionValue('id')
            ->optionLabel('name')
            ->searchUrl($url)
            ->clearable()
            ->cssClass($class);
    }

    public static function makePeriod(string $name = 'period', ?string $label = null, ?string $class = null): self
    {
        $periodOptions = [
            ['id' => 'month', 'name' => __('by month')],
            ['id' => 'date', 'name' => __('by date')],
            ['id' => 'between_months', 'name' => __('between month')],
            ['id' => 'between_dates', 'name' => __('between date')],
        ];

        $instance = self::make($name)
            ->label($label ?? __('period'))
            ->type('date')
            ->options($periodOptions)
            ->value('month')
            ->dateStart(date('Y-m-d'))
            ->dateEnd(date('Y-m-d'))
            ->monthStart(date('Y-m'))
            ->monthEnd(date('Y-m'));

        if ($class) {
            $instance->cssClass($class);
        }

        return $instance;
    }

    public function dependsOn(string $parentName): self
    {
        $this->dependsOn = $parentName;

        return $this;
    }

    public function remoteOptions(string $url, string $method = 'get', array $params = []): self
    {
        $this->remote = [
            'url' => $url,
            'method' => strtolower($method),
            'params' => $params,
        ];

        return $this;
    }

    public function resetOnParentChange(bool $v = true): self
    {
        $this->resetOnParentChange = $v;

        return $this;
    }

    public function disableWhenParentEmpty(bool $v = true): self
    {
        $this->disableWhenParentEmpty = $v;

        return $this;
    }
}
