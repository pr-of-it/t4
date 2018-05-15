<?php

namespace T4\Dbal;

use T4\Core\Config;

/**
 * Class Connection
 * @package T4\Dbal
 */
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
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param \T4\Core\Config $config
     * @return \PDO
     * @throws \PDOException
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
        $pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [Statement::class]);
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
     * @param string $string
     * @param int $parameter_type
     * @return string
     */
    public function quote(string $string, $parameter_type = \PDO::PARAM_STR)
    {
        return $this->pdo->quote($string, $parameter_type);
    }

    /**
     * @param \T4\Dbal\Query $query
     * @return \T4\Dbal\Statement
     */
    public function prepare(Query $query)
    {
        $sql = $this->getDriver()->makeQueryString($query);
        $statement = $this->pdo->prepare($sql);
        return $statement;
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder|\T4\Dbal\Query $query
     * @param array $params
     * @return bool
     */
    public function execute($query, array $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = array_merge($params, $query->getParams());
            $query = $query->getQuery($this->getDriver());
        }
        if ($query instanceof Query) {
            $params = array_merge($params, $query->params);
            $query = $this->getDriver()->makeQueryString($query);
        }
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder|\T4\Dbal\Query $query
     * @param array $params
     * @return \T4\Dbal\Statement
     */
    public function query($query, array $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = array_merge($params, $query->getParams());
            $query = $query->getQuery($this->getDriver());
        }
        if ($query instanceof Query) {
            $params = array_merge($params, $query->params);
            $query = $this->getDriver()->makeQueryString($query);
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
        return $this->pdo->rollBack();
    }

    public function commitTransaction()
    {
        return $this->pdo->commit();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
