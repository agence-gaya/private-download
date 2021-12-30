<?php
$EM_CONF['private_download'] = array(
    'title' => 'Private download',
    'description' => 'Alternative to dumpFile core eID. Allow downloading private files with an extbase controller.',
    'category' => 'plugin',
    'author' => 'Benoit Chenu',
    'author_email' => 'contact@gaya.fr',
    'author_company' => 'GAYA Manufacture digitale',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.1.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '10.4.22-10.4.99',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);