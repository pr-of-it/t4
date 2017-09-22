<?php

// Получаем абсолютный путь в ФС до ресурса и узнаем тип ресурса
$assetRealPath = $this->getRealPath($path);

if (is_dir($assetRealPath)) {
    $type = 'dir';
    $dirToPublish = $assetRealPath;
} else {
    $type = 'file';
    $dirToPublish = dirname($assetRealPath);
}

$dirToPublishHash = $this->getPathHash($dirToPublish);

$assetBasePath = self::ASSETS_BASE_PATH . DS . $dirToPublishHash;
$assetBaseUrl = self::ASSETS_BASE_URL . '/' . $dirToPublishHash;

if (!file_exists($assetBasePath)) {
    Helpers::mkDir($assetBasePath);
}

if ('dir' == $type) {
    Helpers::copyDir($dirToPublish, $assetBasePath);
} else {
    Helpers::copyFile($assetRealPath, $assetBasePath, 0666);
}
