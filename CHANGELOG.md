# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Applied Rector refactorings (`RepeatedOrEqualToInArrayRector`, `RepeatedAndNotEqualToNotInArrayRector`, `instanceof` checks, early return); no behaviour change.
- Rector configuration: `StrictArrayParamDimFetchRector` skipped for `TurboSchemaCommand` (YAML input may be a scalar or null).
- GitHub Actions: `actions/checkout` bumped to v5, `softprops/action-gh-release` bumped to v2.

## [v2.4.0] - 2026-09-17

### Added
- Laravel 13 support (`illuminate/support: ^12.0|^13.0`).
- Symfony 8 support for `symfony/yaml` (`^7.3|^8.0`).

### Changed
- `grazulex/laravel-modelschema` constraint bumped to `^1.2` (Laravel 13 compatible).
- Minimum PHP version is now 8.3.
- Development dependencies modernised: Orchestra Testbench `^10.0|^11.0`, Pest `^3.8|^4.0`, Pest Laravel plugin `^3.2|^4.0`, Pint `^1.24`.
- Rector configuration updated for Rector 2.x (`strictBooleans` prepared set removed).
- CI matrix now runs PHP 8.3 / 8.4 against Laravel 12 and 13 (Testbench 10 / 11), with `prefer-lowest` and `prefer-stable`.
- Release workflow now validates against Laravel 13 / Testbench 11.

### Removed
- Laravel 11 support (end of life).

### Fixed
- Removed null-coalescing on non-nullable `Field` properties in `ModelSchemaGenerationAdapter` (reported by PHPStan 2.x).

## [v2.3.0] - 2026-02-02

### Added
- Configurable view file extension support.

## [v2.2.0] - 2026-01-28

### Fixed
- CRUD views generation with the `--views` option.

## [v2.1.0] - 2025-08-04

### Changed
- Schema handling now relies on `grazulex/laravel-modelschema`.

## [v2.0.0] - 2025-08-02

### Added
- YAML schema option for module generation.

## [v1.0.0] - 2025-07-31

### Added
- Initial release.

[v2.4.0]: https://github.com/Grazulex/laravel-turbomaker/compare/v2.3.0...v2.4.0
[v2.3.0]: https://github.com/Grazulex/laravel-turbomaker/compare/V2.2.0...v2.3.0
[v2.2.0]: https://github.com/Grazulex/laravel-turbomaker/compare/v2.1.0...V2.2.0
[v2.1.0]: https://github.com/Grazulex/laravel-turbomaker/compare/v2.0.0...v2.1.0
[v2.0.0]: https://github.com/Grazulex/laravel-turbomaker/compare/v1.0.0...v2.0.0
[v1.0.0]: https://github.com/Grazulex/laravel-turbomaker/releases/tag/v1.0.0
