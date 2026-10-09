# Changelog

## [v2.2.0] - 2026-10-09
### Added
- `Cell::activeToggle($row, $canChange = true, $action = 'active')`: el interruptor verde de la columna Activo.
  Apagar confirma con el diálogo de la tabla (record-active → active) y encender va directo; sin permiso se ve
  bloqueado; una acción propia va a la página. Requiere `@quirosys/x-components` 2.25.0.
- `Filter::isActive()` (Todos / Activos / Inactivos) y `Filter::applyIsActive($query, $filters, $column)`.
- `DialogAction::getActiveRecordActionData(..., $deactivateHint)`: una línea que explica qué pasa al desactivar.
### Changed
- `Column::isActive()`: título "Activo" (antes "¿Activo?") y 100 px.
- `Cell::badgeIsActive` / `Cell::badgeBoolean`: "Sí" con tilde por defecto.
### Fixed
- `DialogAction`: el nombre del registro va escapado (la descripción se pinta con v-html).
### Deprecated
- `Button::activeButton`, `Button::activeButtonOnlyIcon` y `Cell::badgeIsActive`: la columna Activo los reemplaza.

## [v1.2.2] - 2026-06-11
### Fixed
- Removed hardcoded `"version"` field from `composer.json` (caused Packagist to skip tags)
- Published to Packagist — `repositories` block no longer needed in consumer projects

## [v1.2.1] - 2025-xx-xx
### Changed
- Internal improvements

## [v1.2.0] - 2025-xx-xx
### Added
- `Column`: added `visible()`, `sortField()`, `onlyExport()`, `summable()`, `excelWidth()`, `excelFormat()`, `excelWrap()`
- `Filter`: added `clearable()`, `filterable()`, `searchUrl()`, `makeSearch()`, `$class` param in `makePeriod()`

## [v1.1.0] - 2025-xx-xx
### Added
- `PaginationTenantTrait` and `PaginationSystemTrait`
- `GenericReportExport` for Excel exports with styled headers
- `DialogAction` for delete/active confirmation dialogs
- `ActionRequest` FormRequest for dialog endpoints

## [v1.0.0] - 2025-xx-xx
### Added
- Initial release: `Column`, `ColumnBuilder`, `Filter`, `FilterBuilder`, `Button`, `ButtonBuilder`
- `PaginationBaseTrait`, `ExcelTrait`, `FilterTrait`
