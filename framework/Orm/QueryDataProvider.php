<?php

namespace T4\Orm;

use T4\Core\Collection;
use T4\Core\IProvider;
use T4\Core\Std;
use T4\Dbal\Query;
use T4\Dbal\QueryBuilder;

/**
 * Class QueryDataProvider
 * @package T4\Orm
 *
 * @property \T4\Orm\Model $class
 * @property string $query
 * @property string $queryForCount
 * @property array $params
 *
 * @property int $total
 * @property int $pageSize
 * @property \Generator $pages
 */
class QueryDataProvider
    extends Std
    implements IProvider
{

    public function __construct($query, $params = [], $class = null)
    {
        if (null !== $class && !(is_subclass_of($class, Model::class, true))) {
            throw new Exception('Invalid model class given');
        }
        $this->class = $class;

        $this->query = $query;
        $this->params = $params;
    }


    /**
     * @return \T4\Dbal\Connection
     */
    protected function getConnection()
    {
        if (!empty($this->class)) {
            return $this->class::getDbConnection();
        } else {
            if ('cli' == PHP_SAPI) {
                $app = \T4\Console\Application::instance();
            } else {
                $app = \T4\Mvc\Application::instance();
            }
            return $app->db->default;
        }
    }

    protected function sanitizeQuery($query)
    {
        if ($query instanceof QueryBuilder) {
            $query =  $query->getQuery($this->getConnection()->getDriverName());
        }
        if ($query instanceof Query) {
            $query =  $this->getConnection()->getDriver()->makeQueryString($query);
        }
        $this->queryForCount = preg_replace('~^[\s]*SELECT([\s\S]+)FROM~iU', 'SELECT COUNT(*) FROM', $query);
        return $query;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setPageSize(int $size = 0) : IProvider
    {
        $this->pageSize = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize() : int
    {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getTotal() : int
    {
        return (int)$this->getConnection()->query($this->queryForCount, $this->params)->fetchScalar();
    }

    /**
     * @return \Generator
     */
    public function getPages() : \Generator
    {
        if (0 != $this->pageSize) {
            $pages = ceil($this->getTotal() / $this->pageSize);
            $query = $this->query . ' LIMIT ' . $this->pageSize;
        } else {
            $pages = 1;
            $query = $this->query;
        }

        for ($i = 1; $i <= $pages; $i++) {
            if ($pages > 1) {
                $query = $query . ' OFFSET ' . ( ($i - 1) * $this->pageSize );
                if (empty($this->class)) {
                    yield $i => new Collection( $this->getConnection()->query($query, $this->params)->fetchAllObjects(Std::class) );
                } else {
                    yield $i => $this->class::findAllByQuery($query, $this->params);
                }
            }
        }
    }

    /**
     * @param int $n
     * @return \T4\Core\Collection
     */
    public function getPage(int $n) : Collection
    {
        if ($this->pageSize != 0) {
            $query = $this->query . ' LIMIT ' . $this->pageSize . ' OFFSET ' . ( ($n - 1) * $this->pageSize );
        } else {
            $query = $this->query;
        }

        if (empty($this->class)) {
            return new Collection( $this->getConnection()->query($query, $this->params)->fetchAllObjects(Std::class) );
        } else {
            return $this->class::findAllByQuery($query, $this->params);
        }
    }

    public function getAll() : Collection
    {
        $query = $this->query;
        if (empty($this->class)) {
            return new Collection( $this->getConnection()->query($query, $this->params)->fetchAllObjects(Std::class) );
        } else {
            return $this->class::findAllByQuery($query, $this->params);
        }
    }

}