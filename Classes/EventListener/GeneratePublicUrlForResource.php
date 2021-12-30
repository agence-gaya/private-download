<?php
declare(strict_types = 1);
namespace GAYA\PrivateDownload\EventListener;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class GeneratePublicUrlForResource
{
    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    public function __construct()
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
    }

    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        if ($event->getDriver() instanceof LocalDriver
            && ($event->getResource() instanceof File || $event->getResource() instanceof ProcessedFile)
        ) {
            $typoScript = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'privatedownload'
            );

            try {
                $publicUrl = $event->getDriver()->getPublicUrl($event->getResource()->getIdentifier());

                if ($publicUrl === null && $event->getResource() instanceof FileInterface) {
                    $queryParameterArray = [
                        'type' => $typoScript['typeNum'],
                        'tx_privatedownload_download' => [
                            'controller' => 'Download',
                            'file' => $event->getResource()->getUid()
                        ]
                    ];
                    if ($event->getResource() instanceof File) {
                        $queryParameterArray['tx_privatedownload_download']['action'] = 'getFile';
                    } elseif ($event->getResource() instanceof ProcessedFile) {
                        $queryParameterArray['tx_privatedownload_download']['action'] = 'getProcessedFile';
                    }

                    $queryStringSeparator = strpos($typoScript['baseURL'], '?') !== null ? '&' : '?';

                    $queryParameterArray['tx_privatedownload_download']['token'] = GeneralUtility::hmac(implode('|', $queryParameterArray), 'privateDownload');
                    $publicUrl = GeneralUtility::locationHeaderUrl(PathUtility::getAbsoluteWebPath(Environment::getPublicPath() . $typoScript['baseURL']));
                    $publicUrl .= $queryStringSeparator . http_build_query($queryParameterArray, '', '&', PHP_QUERY_RFC3986);

                    $event->setPublicUrl($publicUrl);
                }
            } catch (Exception $exception) {

            }
        }
    }
}
