<?php

namespace T4\Dbal;


use T4\Core\Config;

class Connection {

    /**
     * Конфигурация соединения
     * @var \T4\Core\Config
     */
    protected $config;

    /**
     * Объект соединения с базой данных
     * @var \PDO
     */
    protected $pdo;

    public function __construct (Config $config) {
        $this->config = $config;
        try {
            $dsn = $config->driver.':host='.$config->host.';dbname='.$config->dbname;
            $this->pdo = new \PDO($dsn, $config->user, $config->password);
        } catch ( \PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getDriverName() {
        return (string) $this->config->driver;
    }

    /**
     * @param $sql
     * @return \PDOStatement
     */
    protected function prepare($sql) {
        $statement = $this->pdo->prepare($sql);
        return $statement;
    }

    /**
     * @param $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function execute($sql, array $params=[]) {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

}