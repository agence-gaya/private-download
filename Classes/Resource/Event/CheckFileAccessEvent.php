<?php
declare(strict_types = 1);
namespace GAYA\PrivateDownload\Resource\Event;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;

final class CheckFileAccessEvent
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @var ProcessedFile
     */
    protected $processedFile;

    public function __construct(?File $file, ?ProcessedFile $processedFile)
    {
        $this->file = $file;
        $this->processedFile = $processedFile;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return ProcessedFile
     */
    public function getProcessedFile(): ProcessedFile
    {
        return $this->processedFile;
    }
}