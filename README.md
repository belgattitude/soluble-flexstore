# soluble/flexstore

[![PHP Version](http://img.shields.io/badge/php-5.3+-ff69b4.svg)](https://packagist.org/packages/soluble/flexstore)
[![HHVM Status](http://hhvm.h4cc.de/badge/soluble/flexstore.png?style=flat)](http://hhvm.h4cc.de/package/soluble/flexstore)
[![Build Status](https://travis-ci.org/belgattitude/soluble-flexstore.png?branch=master)](https://travis-ci.org/belgattitude/soluble-flexstore)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore)
[![Latest Stable Version](https://poser.pugx.org/soluble/flexstore/v/stable.svg)](https://packagist.org/packages/soluble/flexstore)
[![Total Downloads](https://poser.pugx.org/soluble/flexstore/downloads.png)](https://packagist.org/packages/soluble/flexstore)
[![License](https://poser.pugx.org/soluble/flexstore/license.png)](https://packagist.org/packages/soluble/flexstore)

## Introduction



## Features


## Requirements

- PHP engine 5.4+, 7.0+

## Documentation

 - [Manual](http://docs.soluble.io/soluble-flexstore/manual/) in progress and [API documentation](http://docs.soluble.io/soluble-flexstore/api/) available.

## Installation

Instant installation via [composer](http://getcomposer.org/).

```console
php composer require soluble/flexstore:0.*
```
Most modern frameworks will include Composer out of the box, but ensure the following file is included:

```php
<?php
// include the Composer autoloader
require 'vendor/autoload.php';
```

## Quick start

### Connection





```php
<?php

use Soluble\Metadata\Reader;

$conn = new \mysqli($hostname,$username,$password,$database);
$conn->set_charset($charset);

// Alternatively you can create a PDO_mysql connection
// $conn = new \PDO("mysql:host=$hostname", $username, $password, [
//            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
// ]);

$reader = new Reader\MysqliMetadataReader($conn);

$sql = "select id, name from my_table";

$meta = $reader->getColumnsMetadata($sql);

// The resulting ArrayObject look like

// The resulting array looks like
[
 ["id"] => <Column\Definition\IntegerColumn>
 ["name"] => <Column\Definition\StringColumn>
]

```

## API

### AbstractMetadataReader

The `Soluble\Metadata\Reader\AbstractMetadataReader` offers

| Methods                      | Return        | Description                                         |
|------------------------------|---------------|-----------------------------------------------------|
| `getColumnsMetadata($sql)`   | `ArrayObject` | Metadata information indexed by column name/alias   |

### AbstractColumnDefinition

Metadata information is stored as an `Soluble\Datatype\Column\Definition\AbstractColumnDefinition` object on which :


| General methods              | Return        | Description                                         |
|------------------------------|---------------|-----------------------------------------------------|
| `getName()`                  | `string`      | Return column name (unaliased)                      |
| `getAlias()`                 | `string`      | Return column alias                                 |
| `getTableName()`             | `string`      | Return origin table                                 |
| `getSchemaName()`            | `string`      | Originating schema for the column/table             |

| Type related methods         | Return        | Description                                         |
|------------------------------|---------------|-----------------------------------------------------|
| `getDataType()`              | `string`      | Column datatype (see Column\Type)                   |
| `getNativeDataType()`        | `string`      | Return native datatype                              |
| `isText()`                   | `boolean`     | Whether the column is textual (string, blog...)     |
| `isNumeric()`                | `boolean`     | Whether the column is numeric (decimal, int...)     |
| `isDate()`                   | `boolean`     | Is a date type                                      |

| Extra information methods    | Return        | Description                                         |
|------------------------------|---------------|-----------------------------------------------------|
| `isComputed()`               | `boolean`     | Whether the column is computed, i.e. '1+1, sum()    |
| `isGroup()`                  | `boolean`     | Grouped operation sum(), min(), max()               |


| Source infos                 | Return        | Description                                         |
|------------------------------|---------------|-----------------------------------------------------|
| `isPrimary()`                | `boolean`     | Whether the column is (part of) primary key         |
| `isNullable()`               | `boolean`     | Whether the column is nullable                      |
| `getColumnDefault()`         | `string`      | Return default value for column                     |
| `getOrdinalPosition()`       | `integer`     | Return position in the select                       |


Concrete implementations of `Soluble\Datatype\Column\Definition\AbstractColumnDefinition` are

| Drivers              | Interface                 | Description                   |
|----------------------|---------------------------|-------------------------------|
| `BitColumn`          |                           |                               |
| `BlobColumn`         |                           |                               |
| `BooleanColumn`      |                           |                               |
| `DateColumn`         | `DateColumnInterface`     |                               |
| `DateTimeColumn`     | `DatetimeColumnInterface` |                               |
| `DecimalColumn`      | `NumericColumnInterface`  |                               |
| `FloatColumn`        | `NumericColumnInterface`  |                               |
| `GeometryColumn`     |                           |                               |
| `IntegerColumn`      | `NumericColumnInterface`  |                               |
| `StringColumn`       | `TextColumnInterface`     |                               |
| `TimeColumn`         |                           |                               |




## Supported drivers

Currently only pdo_mysql and mysqli drivers  are supported. 

| Drivers            | Adapter interface implementation                     |
|--------------------|------------------------------------------------------|
| pdo_mysql          | `Soluble\DbAdapter\Adapter\MysqlAdapter`             |
| mysqli             | `Soluble\DbAdapter\Adapter\MysqlAdapter`             |


## Contributing

Contribution are welcome see [contribution guide](./CONTRIBUTING.md)

## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)





