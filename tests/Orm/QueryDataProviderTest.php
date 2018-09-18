<?php

namespace T4\Tests\Orm\Models {

    use T4\Dbal\Connection;

    class TestConnection extends Connection {
        public function __construct()
        {
        }
        public function getDriverName()
        {
            return 'mysql';
        }
    }

    class Model extends \T4\Orm\Model {
        public static function getDbConnection()
        {
            return new TestConnection();
        }
    }

}
namespace T4\Tests\Orm {

    use T4\Dbal\Query;
    use T4\Orm\QueryDataProvider;
    use T4\Tests\Orm\Models\Model;

    require_once realpath(__DIR__ . '/../../framework/boot.php');


    class QueryDataProviderTest
        extends \PHPUnit\Framework\TestCase
    {

        public function testQueryForCount()
        {
            $provider = new QueryDataProvider('SELECT * FROM test', []);
            $this->assertEquals('SELECT COUNT(*) FROM test', $provider->queryForCount);
            
            $provider = new QueryDataProvider('SELECT foo, bar FROM test WHERE foo=:foo', [':foo'=>42]);
            $this->assertEquals('SELECT COUNT(*) FROM test WHERE foo=:foo', $provider->queryForCount);
            
            $provider = new QueryDataProvider(
                (new Query())->select()->from('test')->where('bar=:bar'),
                [],
                Model::class
            );
            $this->assertEquals("SELECT COUNT(*) FROM `test` AS t1\nWHERE bar=:bar", $provider->queryForCount);
        }

    }

}

