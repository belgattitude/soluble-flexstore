# soluble/flexstore

[![PHP Version](http://img.shields.io/badge/php-5.4+-ff69b4.svg)](https://packagist.org/packages/soluble/flexstore)
[![HHVM Status](http://hhvm.h4cc.de/badge/soluble/flexstore.svg?style=flat)](http://hhvm.h4cc.de/package/soluble/flexstore)
[![Build Status](https://travis-ci.org/belgattitude/soluble-flexstore.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-flexstore)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore)
[![Latest Stable Version](https://poser.pugx.org/soluble/flexstore/v/stable.svg)](https://packagist.org/packages/soluble/flexstore)
[![Total Downloads](https://poser.pugx.org/soluble/flexstore/downloads.png)](https://packagist.org/packages/soluble/flexstore)
[![License](https://poser.pugx.org/soluble/flexstore/license.png)](https://packagist.org/packages/soluble/flexstore)

## Introduction


## Features

- Extensible datasource
- ColumModel alteration
- Renderers and formatters 
- Custom writers

## Requirements

- PHP engine 5.4+, 7.0+

## Documentation

 - [Manual](http://docs.soluble.io/soluble-flexstore/manual/) in progress and [API documentation](http://docs.soluble.io/soluble-flexstore/api/) available.

## Installation

Instant installation via [composer](http://getcomposer.org/).

```console
$ composer require soluble/flexstore:0.*
```
Most modern frameworks will include Composer out of the box, but ensure the following file is included:

```php
<?php
// include the Composer autoloader
require 'vendor/autoload.php';
```

## Quick start


## API

### Getting a store

Getting a store from a zend-db select.

```php
<?php

use Soluble\FlexStore\Store;
use Soluble\FlexStore\Source;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;

// 1. Database adapter

$adapter = new Adapter([
                'driver'    => 'mysqli',  // or PDO_Mysql
                'hostname'  => $hostname,
                'username'  => $username,
                'password'  => $password,
                'database'  => $database,
                'charset'   => 'UTF-8'
]);

// 2. Make a select

$select = new Select();
$select->from('product')
       ->where(['flag_archive' => 0]);

// 3. Create a datasource
$sqlSource = new Source\Zend\SqlSource($adapter, $select);

// 4. Get a Store
$store = new Store($sqlSource);

```

### Retrieving data on a store

```php
<?php

use Soluble\FlexStore\Store;
use Soluble\FlexStore\Options;

// 4. Get a Store
$store = new Store($sqlSource);


$options = new Options();
$options->setLimit(10);

$data = $store->getData($options);

foreach ($data as $idx => $row) {
    // The $row is an ArrayObject
    echo $idx . '-' . $row['column'] . PHP_EOL;
}

```

### Getting the ColumnModel

```php
<?php

use Soluble\FlexStore\Store;
use Soluble\FlexStore\Options;

// 4. Get a Store
$store = new Store($sqlSource);


$cm = $store->getColumnModel();

$columns = $cm->getColumns();

// Will look like
[
 ["col_1_name"] => (Soluble\FlexStore\Column\Column) 
 ["col_2_name"] => (Soluble\FlexStore\Column\Column) 
]

// Getting information about a column

$column = $cm->getColumn("col_1_name");

$properties = $column->getProperties();

$column->getName();
$column->getHeader();
$column->getType();

$column->getFormatter();

$column->getWidth();
$column->isEditable();
$column->isExcluded();
$column->isFilterable();
$column->isGroupable();
$column->isSortable();


```



## API

### Store

`Soluble\FlexStore\Store`
 
| Methods                                | Return                         | Comment                             |
|----------------------------------------|--------------------------------|-------------------------------------|
| `__construct(SourceInterface $source)` | `Resultset\ResultsetInterface` |  |
| `getData(Options $options=null)`       | `Resultset\ResultsetInterface` |  |
| `getColumnModel()`                     | `Column\ColumnModel`           |  |
| `getSource()`                          | `Source\SourceInterface`       |  |


### Options

`Soluble\FlexStore\Options` can be used to alter the data retrieval process.

| Methods                          | Return                 | Comment                  |
|----------------------------------|------------------------|--------------------------|
| `setLimit($limit, $offset=null)` | `Options`              | Fluent interface         |
| `getLimit()`                     | `integer`              |                          |
| `getOffset()`                    | `integer`              |                          |
| `hasLimit()`                     | `boolean`              |                          |
| `hasOffset()`                    | `boolean`              |                          |
| `getHydrationOptions()`          | `HydrationOptions`     |                          |


### Resultset

The `Store::getData()` method returns a resultset implementing the `Resultset\ResultsetInterface`.
This interface is traversable, countable and implements the `Iterator` interface (foreach...)

| Methods                    | Return                    | Comment                  |
|----------------------------|---------------------------|--------------------------|
| `count()`                  | `integer`                 | Number of results        |
| `getFieldCount()`          | `integer`                 | Number of fields/columns |
| `toArray()`                | `array`                   |                          |
| `current()`                | `ArrayObject`             | The current row          |
| `getDataSource()`          | `Source\SourceInterface`  | The underlying source    |


### ColumnModel

ColumnModel allows to alter the way columns will be retrieved or displayed.

It must be called before the `Store::getData()` method.

#### Basic information

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `getColumns($include_excluded=false)`    | `ArrayObject`        |                |
| `get(string $column)`                    | `Column`             |                |
| `exists(string $column)`                 | `boolean`            |                |


### Sorting columns

Changing the order of the retrieved columns.

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `sort(array $sorted_columns)             | `ColumnModel`        | Fluent         |


### Getting or setting exclusions

Excluded columns are not retrieved by the `Store::getData()` method.

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `exclude(array|string $columns)`         | `ColumnModel`        | Fluent         |
| `includeOnly(array|string $columns)`     | `ColumnModel`        | All others will be excluded             |
| `getExcluded()`                          | `array`              |                |

#### Adding virtual columns

Adding a column that does not exists in the underlying source. The value of this column is generally 
computed by a renderer.

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `add(Column $column, string $after_column=null) | `ColumnModel` | Fluent         |

#### Searching columns

You can search the column model for columns matching a specific pattern.

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `search()`         | `ColumnModel\Search`| see operations on the search object       |


#### Metadata

Metadata are currently retrieved automatically (this will probably change ...)

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `setMetadata(ColumnsMetadata $metadata)` | `ColumnModel`        |                   |


### Search on ColumnModel

You can search the column model for columns matching a specific pattern and apply 
actions on the result.

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `all()`                                  | `Search\Result`         |    |
| `findByType(string $type)`               | `Search\Result`         |    |
| `in(array $columns)`                     | `Search\Result`         |    |
| `notIn(array $columns)`                  | `Search\Result`         |    |
| `regexp(string $regexp)`                 | `Search\Result`         |    |
| `findByType(string $type)`               | `Search\Result`         |    |
| `findVirtual()`                          | `Search\Result`         |    |

With the associated `Search\Result` you can easily

### Search on ColumnModel

You can search the column model for columns matching a specific pattern and apply 
actions on the result.

| Methods                                  | Return               | Comment           |
|------------------------------------------|----------------------|-------------------|
| `setEditable(boolean $editable=true)`      | `Search\Result`         |    |
| `setExcluded(boolean $excluded=true)`      | `Search\Result`         |    |
| `setFilterable(boolean $filterable=true)`  | `Search\Result`         |    |
| `setGroupable(boolean $groupable=true)`    | `Search\Result`         |    |
| `setSortable(boolean $sortable=true)`      | `Search\Result`         |    |
| `setHidden(boolean $hidden=true)`          | `Search\Result`         |    |
| `setWidth(int|float|string $width)`        | `Search\Result`         |    |


## Supported drivers


## Contributing

Contribution are welcome see [contribution guide](./CONTRIBUTING.md)

## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)





