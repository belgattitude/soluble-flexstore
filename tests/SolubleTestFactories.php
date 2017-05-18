<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 2016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

use Zend\Db\Adapter\Adapter;

class SolubleTestFactories
{
    /**
     * @var array
     */
    protected static $_adapter_instances = [];

    /**
     * @var array
     */
    protected static $_cache_instances = [];

    /**
     * @return array
     */
    public static function getLibXLLicense()
    {
        return [
                    'name' => $_SERVER['LIBXL_LICENSE_NAME'],
                    'key' => $_SERVER['LIBXL_LICENSE_KEY']
            ];
    }

    /**
     * @param array  $mysql_config (driver,hostname,username,password,database)
     * @param string $driver       force driver to be Pdo_Mysql, Mysqli
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    public static function getDbAdapter(array $mysql_config = null, $driver = null)
    {
        if ($mysql_config === null) {
            if ($mysql_config === null) {
                /**
                 * Those values must be defined in phpunit.xml configuration file.
                 */
                $mysql_config = self::getDatabaseConfig();
            }
            if ($driver !== null) {
                $mysql_config['driver'] = $driver;
            } else {
                $mysql_config['driver'] = $_SERVER['MYSQL_DRIVER'];
            }
        }

        $key = md5(serialize($mysql_config));
        if (!array_key_exists($key, self::$_adapter_instances)) {
            self::$_adapter_instances[$key] = new Adapter($mysql_config);
        }

        return self::$_adapter_instances[$key];
    }

    public static function getDatabaseConfig()
    {
        $mysql_config = [];
        $mysql_config['hostname'] = $_SERVER['MYSQL_HOSTNAME'];
        $mysql_config['username'] = $_SERVER['MYSQL_USERNAME'];
        $mysql_config['password'] = $_SERVER['MYSQL_PASSWORD'];
        $mysql_config['database'] = $_SERVER['MYSQL_DATABASE'];
        $mysql_config['driver_options'] = [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            ];
        $mysql_config['options'] = [
            'buffer_results' => true
        ];
        $mysql_config['charset'] = 'UTF8';

        return $mysql_config;
    }

    /**
     * @return string
     */
    public static function getCachePath()
    {
        $cache_dir = $_SERVER['PHPUNIT_CACHE_DIR'];
        if (!preg_match('/^\//', $cache_dir)) {
            $cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cache_dir;
        }

        return $cache_dir;
    }
}
