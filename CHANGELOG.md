# Changelog

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
