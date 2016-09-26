# CHANGELOG

### 0.11.4 (2016-09-22)

- Added `NullType` for special cases whenever the query contains an `select null as test`. Very specific support.

### 0.11.2 (2016-05-15)

- Reactivate install of latest zend-db in composer, v1.8.1 fix the bug :
  [zend-db #100](https://github.com/zendframework/zend-db/pull/100).

### 0.11.1 (2016-05-13)

- Disallow install of zend-db 1.8.0 in composer deps due to this bug
  [zend-db #105](https://github.com/zendframework/zend-db/issues/105)