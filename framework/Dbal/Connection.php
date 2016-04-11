<?php

namespace T4\Dbal;

use T4\Core\Config;

class Connection
{

    /**
     * @var \T4\Core\Config
     */
    protected $config;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param \T4\Core\Config $config
     * @throws \T4\Dbal\Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        try {
            $this->pdo = $this->getPdoObject($this->config);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param \T4\Core\Config $config
     * @return \PDO
     */
    protected function getPdoObject(Config $config)
    {
        $dsn = $config->driver . ':host=' . $config->host . ';dbname=' . $config->dbname;
        if (!empty($config->port)) {
            $dsn .= ';port=' . $config->port;
        }
        $options = [];
        if (!empty($config->options)) {
            $options = $config->options->toArray();
        }
        $pdo = new \PDO($dsn, $config->user, $config->password, $options);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [__NAMESPACE__ . '\\Statement']);
        return $pdo;
    }

    /**
     * @return string
     */
    public function getDriverName()
    {
        return (string)$this->config->driver;
    }

    /**
     * @return \T4\Dbal\IDriver
     */
    public function getDriver()
    {
        return DriverFactory::getDriver($this->getDriverName());
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @return \T4\Dbal\Statement
     */
    public function prepare($query)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery($this->getDriver());
        }
        $statement = $this->pdo->prepare($query);
        return $statement;
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @param array $params
     * @return bool
     */
    public function execute($query, array $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = array_merge($params, $query->getParams());
            $query = $query->getQuery($this->getDriver());
        }
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @param array $params
     * @return \T4\Dbal\Statement
     */
    public function query($query, array $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = array_merge($params, $query->getParams());
            $query = $query->getQuery($this->getDriver());
        }
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    /**
     * @param string $name [optional] Name of the sequence object from which the ID should be returned.
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @return array
     */
    public function getErrorInfo()
    {
        return $this->pdo->errorInfo();
    }

    public function __sleep()
    {
        return ['config'];
    }

    public function __wakeup()
    {
        $this->pdo = $this->getPdoObject($this->config);
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function rollbackTransaction()
    {
        return $this->pdo->rollback();
    }

    public function commitTransaction()
    {
        return $this->pdo->commit();
    }
}