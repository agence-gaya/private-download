<?php

declare(strict_types=1);

namespace GAYA\PrivateDownload\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

#[AsEventListener]
class GeneratePublicUrlForResource
{
    public function __construct(private readonly ConfigurationManagerInterface $configurationManager, private readonly HashService $hashService) {}

    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        //si on est en mode "backend" il ne faut pas modifier l'url publique
        if ((($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface) && (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend())) {
            return;
        }

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
                            'file' => $event->getResource()->getUid(),
                        ],
                    ];
                    if ($event->getResource() instanceof File) {
                        $queryParameterArray['tx_privatedownload_download']['action'] = 'getFile';
                    } elseif ($event->getResource() instanceof ProcessedFile) {
                        $queryParameterArray['tx_privatedownload_download']['action'] = 'getProcessedFile';
                    }

                    $queryStringSeparator = str_contains((string) $typoScript['baseURL'], '?') ? '&' : '?';

                    $queryParameterArray['tx_privatedownload_download']['token'] = $this->hashService->hmac(json_encode($queryParameterArray), 'privateDownload');
                    $publicUrl = GeneralUtility::locationHeaderUrl(PathUtility::getAbsoluteWebPath(Environment::getPublicPath() . $typoScript['baseURL']));
                    $publicUrl .= $queryStringSeparator . http_build_query($queryParameterArray, '', '&', PHP_QUERY_RFC3986);

                    $event->setPublicUrl($publicUrl);
                }
            } catch (Exception) {

            }
        }
    }
}
