# Private Download

## Introduction

This module is an alternative to native TYPO3 dumpFile eID : if a file is in a private storage, this will generate an publicUrl to an extbase wrapper instead of an eID wrapper.

The disadvantage of the TYPO3 dumpFile eID is the position of this middleware : the hook "checkFileAccess" don't allow us to check if a user is authenticated for example.

This extbase version solves the problem.

## Configuration

Include the plugin setup in your root template.

You can customize the typeNum if needed :

```
plugin.tx_privatedownload_download.settings.typeNum = 12013
```

## Events

Before sending the file to the client, this PSR-14 event is dispatched :

```
GAYA\PrivateDownload\Resource\Event\CheckFileAccessEvent
```

The event will provide you one of the tow properties, dependending on file type :

- File $file : if the file is not processed
- ProcessedFile $processedFile : if the file is processed

You can refer this documentation to register a listener :

https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Hooks/EventDispatcher/Index.html

## Credits

© 2020 GAYA Manufacture Digitale [https://www.gaya.fr/]