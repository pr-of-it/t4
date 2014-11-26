<?php

namespace T4\Dbal;

use T4\Core\Std;

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

    /**
     * @param Std $config
     * @throws Exception
     */
    public function __construct(Std $config)
    {
        $this->config = $config;
        try {
            $dsn = $config->driver . ':host=' . $config->host . ';dbname=' . $config->dbname;
            $options = [];
            if (!empty($config->options)) {
                $options = $config->options->toArray();
            }
            $this->pdo = new \PDO($dsn, $config->user, $config->password, $options);
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
     * @param $query
     * @return Statement
     */
    public function prepare($query)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        $statement = $this->pdo->prepare($query);
        return $statement;
    }

    /**
     * @param $query
     * @param array $params
     * @return bool
     */
    public function execute($query, array $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = $query->getParams();
            $query = $query->getQuery();
        }
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    /**
     * @param $query
     * @param array $params
     * @return Statement
     */
    public function query($query, array $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = $query->getParams();
            $query = $query->makeQuery($this->getDriver());
        }
        $statement = $this->pdo->prepare($query);
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