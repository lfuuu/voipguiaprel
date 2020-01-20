<?php

return [
    'adminEmail' => 'admin@example.com',
    'NnpCalculationApi' => '',
    'prefixListTypeSevenOpenLink' => 'http://reg10.mcntelecom.ru:8032/test/nnpcalc?cmd=showTypeSeven',
    'prefixListTypeSevenGenerateLink' => 'http://reg10.mcntelecom.ru:8032/test/nnpcalc?cmd=fillNNPPrefixList&type={type}&id={id}&ndc_token={token}',
    'commentMaxLength' => 2048,
    'loggingEnabled' => true,
    'logReadMethods' => true,
    's3' => [
        'access_key' => '0FGDZKFU070KVOGACRUE',
        'secret_key' => 'UpikFKTR7pmhnenKE1lRWGkhgiCtBDdkTsB30EwT',
        'host' => 'rados.mcn.ru',
        'bucket_name' => 'autocaller',
        'use_ssl' => true,
    ],
];
