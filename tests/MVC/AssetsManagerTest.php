<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

const ROOT_PATH_PROTECTED = './';

class AssetsManagerTest extends \PHPUnit\Framework\TestCase
{

    public function testGetRealPathInvalidPathWithoutSlash()
    {
        $reflector = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'getRealPath');
        $reflector->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $this->expectExceptionMessage("Path 'foo' for asset is invalid");
        $reflector->invoke($manager, 'foo');
    }

    public function testGetRealPathInvalidPathWith3Slashes()
    {
        $reflector = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'getRealPath');
        $reflector->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $this->expectExceptionMessage("Path '///foo' for asset is invalid");
        $reflector->invoke($manager, '///foo');
    }

    public function testGetRealPathUnexisting1()
    {
        $reflector = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'getRealPath');
        $reflector->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $this->expectExceptionMessage("Path '/foo' for asset is not found");
        $reflector->invoke($manager, '/foo');
    }

    public function testGetRealPathUnexisting2()
    {
        $reflector = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'getRealPath');
        $reflector->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $this->expectExceptionMessage("Path '//foo' for asset is not found");
        $reflector->invoke($manager, '//foo');
    }

    public function testGetRealPositive1()
    {
        $reflector = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'getRealPath');
        $reflector->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $path = $reflector->invoke($manager, '/' . basename(__FILE__));
        $this->assertSame(__FILE__, $path);
    }

    public function testGetRealPositive2()
    {
        $reflector = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'getRealPath');
        $reflector->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $path = $reflector->invoke($manager, '//boot.php');
        $this->assertSame(T4\ROOT_PATH . DS . 'boot.php', $path);
    }

    public function testSavedAssetsDirs()
    {
        $addAssetsDirToPublished = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'addAssetsDirToPublished');
        $addAssetsDirToPublished->setAccessible(true);
        $isInsidePublishedAssetsDir = new ReflectionMethod(\T4\Mvc\AssetsManager::class, 'isInsidePublishedAssetsDir');
        $isInsidePublishedAssetsDir->setAccessible(true);

        $manager = \T4\Mvc\AssetsManager::instance();

        $this->assertFalse($isInsidePublishedAssetsDir->invoke($manager, '/foo'));

        $addAssetsDirToPublished->invoke($manager, '/app/protected/foo', '/foo');

        $this->assertFalse($isInsidePublishedAssetsDir->invoke($manager, '/foo'));
        $this->assertFalse($isInsidePublishedAssetsDir->invoke($manager, '/app/protected'));
        $this->assertFalse($isInsidePublishedAssetsDir->invoke($manager, 'protected'));
        $this->assertFalse($isInsidePublishedAssetsDir->invoke($manager, '/some/app/protected/foo/bar'));

        $this->assertSame('/foo', $isInsidePublishedAssetsDir->invoke($manager, '/app/protected/foo'));
        $this->assertSame('/foo/bar', $isInsidePublishedAssetsDir->invoke($manager, '/app/protected/foo/bar'));
    }

}
