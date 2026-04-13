<?php

declare(strict_types=1);

namespace GAYA\PrivateDownload\Resource\Event;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;

final readonly class CheckFileAccessEvent
{
    public function __construct(private ?File $file, private ?ProcessedFile $processedFile) {}

    public function getFile(): File
    {
        return $this->file;
    }

    public function getProcessedFile(): ProcessedFile
    {
        return $this->processedFile;
    }
}
