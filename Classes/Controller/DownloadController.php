<?php
declare(strict_types = 1);
namespace GAYA\PrivateDownload\Controller;

use GAYA\PrivateDownload\Resource\Event\CheckFileAccessEvent;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;

class DownloadController extends ActionController
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    public function callActionMethod()
    {
        if (!$this->isTokenValid($this->buildParametersFromRequest($this->request), $this->request)) {
            throw new PageNotFoundException();
        }
        parent::callActionMethod();
    }

    /**
     * @param int $file
     * @throws ImmediateResponseException
     * @throws PageNotFoundException
     */
    public function getFileAction(int $file)
    {
        try {
            $fileObject = $this->resourceFactory->getFileObject($file);
            if ($fileObject->isDeleted() || $fileObject->isMissing()) {
                $fileObject = null;
            }
            if (!$this->isFileValid($fileObject)) {
                $fileObject = null;
            }
        } catch (\Exception $e) {
            $fileObject = null;
        }

        if ($fileObject === null) {
            throw new PageNotFoundException();
        }

        // Dispatch an event for additional file checks
        $event = new CheckFileAccessEvent($fileObject, null);
        $this->eventDispatcher->dispatch($event);

        throw new ImmediateResponseException($fileObject->getStorage()->streamFile($fileObject), 200);
    }

    /**
     * @param int $file
     * @throws ImmediateResponseException
     * @throws PageNotFoundException
     */
    public function getProcessedFileAction(int $file)
    {
        try {
            $processedFileRepository = GeneralUtility::makeInstance(ProcessedFileRepository::class);
            /** @var ProcessedFile|null $fileObject */
            $fileObject = $processedFileRepository->findByUid($file);
            if (!$fileObject || $fileObject->isDeleted()) {
                $fileObject = null;
            }
            if (!$this->isFileValid($fileObject->getOriginalFile())) {
                $fileObject = null;
            }
        } catch (\Exception $e) {
            $fileObject = null;
        }

        if ($fileObject === null) {
            throw new PageNotFoundException();
        }

        // Dispatch an event for additional file checks
        $event = new CheckFileAccessEvent(null, $fileObject);
        $this->eventDispatcher->dispatch($event);

        throw new ImmediateResponseException($fileObject->getStorage()->streamFile($fileObject), 200);
    }

    protected function buildParametersFromRequest(Request $request): array
    {
        $queryParams = $request->getArguments();

        $parameters = [
            'type' => $this->settings['typeNum'],
            'tx_privatedownload_download' => [
                'controller' => 'Download',
                'file' => (int)$queryParams['file'],
                'action' => $queryParams['action']
            ]
        ];

        return $parameters;
    }

    protected function isTokenValid(array $parameters, Request $request): bool
    {
        return hash_equals(
            GeneralUtility::hmac(json_encode($parameters), 'privateDownload'),
            $request->getArguments()['token'] ?? ''
        );
    }

    protected function isFileValid(FileInterface $file): bool
    {
        return $file->getStorage()->getDriverType() !== 'Local'
            || GeneralUtility::makeInstance(FileNameValidator::class)
                ->isValid(basename($file->getIdentifier()));
    }
}