# Changelog

All notable changes to this project will be documented in this file.

## [5.0.0] - 2025-01-12

### ⚠️ Breaking Changes
This is a complete rewrite of the package and is **NOT** backwards compatible with v4.x.

### Changed
- Complete architectural overhaul with improved organization and modern PHP features
- Requires PHP 8.2+ and Laravel 12.x
- New namespace structure for better code organization
- Enhanced type safety with strict typing throughout
- Improved repository pattern implementation

### Added
- Comprehensive filter system with date and financial period filtering
- Transaction support with automatic wrapping
- Advanced search capabilities
- Full method reference documentation

### Migration
Please refer to the comprehensive documentation in the [README](README.md) for the latest usage examples and API reference. The v4 API is no longer supported.

---

## Previous Versions

### v4.0
- PHP 8.2 Support
- Requires Laravel 11.x

### v3.0
- Dropped PHP 7.x, 8.0 Support
- Requires Laravel 10.x

### v2.0
- Added `searchable()` method accepting `SearchFilter`
- Deprecated `searchPaginate()` method
