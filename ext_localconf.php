<?php

use GAYA\PrivateDownload\Controller\DownloadController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::configurePlugin(
    'PrivateDownload',
    'Download',
    [
        DownloadController::class => 'getFile,getProcessedFile',
    ],
    // non-cacheable actions
    [
        DownloadController::class => 'getFile,getProcessedFile',
    ]
);
