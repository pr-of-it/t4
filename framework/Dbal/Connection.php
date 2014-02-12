<?php

namespace T4\Dbal;


use T4\Core\Config;

class Connection
{

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

    public function __construct(Config $config)
    {
        $this->config = $config;
        try {
            $dsn = $config->driver . ':host=' . $config->host . ';dbname=' . $config->dbname;
            $this->pdo = new \PDO($dsn, $config->user, $config->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [__NAMESPACE__.'\\Statement']);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getDriverName()
    {
        return (string)$this->config->driver;
    }

    /**
     * @return IDriver
     */
    public function getDriver()
    {
        return DriverFactory::getDriver($this->getDriverName());
    }

    /**
     * @param $sql
     * @return Statement
     */
    protected function prepare($sql)
    {
        $statement = $this->pdo->prepare($sql);
        return $statement;
    }

    /**
     * @param $sql
     * @param array $params
     * @return Statement
     */
    public function execute($sql, array $params = [])
    {
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($params);
    }

    /**
     * @param $sql
     * @param array $params
     * @return Statement
     */
    public function query($sql, array $params = [])
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @return array
     */
    public function getErrorInfo() {
        return $this->pdo->errorInfo();
    }

}