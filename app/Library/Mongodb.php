<?php
namespace App\Library;

use MongoDB\Database;
use phpDocumentor\Reflection\Types\Self_;

class Mongodb
{
    private $db = null;
    public static $instances = null;

    private function __construct($host, $port, $database)
    {
        $mongodb = new \MongoDB\Client("mongodb://{$host}:{$port}");
        $this->db = $mongodb->selectDatabase($database);
    }

    /**
     * @return Mongodb|null
     */
    public static function getInstance()
    {
        if (self::$instances === null || !(self::$instances instanceof Mongodb)) {
            $host = config('mongodb.host');
            $port = config('mongodb.port');
            $database = config('mongodb.database');
            self::$instances = new self($host, $port, $database);
        }

        return self::$instances;
    }

    public function __call($name, $arguments)
    {
        if (method_exists(Database::class, $name)) {
            return call_user_func_array([$this->db, $name], $arguments);
        } else {
            throw new \BadMethodCallException("方法未定义");
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(Database::class, $name)) {
            return  call_user_func_array([Database::class, $name], $arguments);
        } else {
            throw new \BadMethodCallException("方法未定义");
        }
    }
}