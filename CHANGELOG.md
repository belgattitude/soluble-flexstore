# CHANGELOG

## 0.12.0 (2017-05-17)

### Removed

- Removed support for zend-paginator <= 2.4 due to invalid `Zend\Paginator\Adapter\Null` class name.

### Added
 
- License headers 

## 0.11.5 (2017-03-01)

### Fixes

- Typo in composer dependency version for soluble-metadata

## 0.11.4 (2016-09-22)

### Added

- Added `NullType` for special cases whenever the query contains an `select null as test`. Very specific support.

## 0.11.2 (2016-05-15)

## Fixes

- Reactivate install of latest zend-db in composer, v1.8.1 fix the bug :
  [zend-db #100](https://github.com/zendframework/zend-db/pull/100).

## 0.11.1 (2016-05-13)

### Fixes

- Disallow install of zend-db 1.8.0 in composer deps due to this bug
  [zend-db #105](https://github.com/zendframework/zend-db/issues/105)