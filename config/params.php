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
        'access_key' => 'CBXZ0LX7FS8NIWFX2L4M',
        'secret_key' => 'XvrACDB6lhoQWhnU5MxphgOqzXrCEYKDi5KA0wIu',
        'host' => 's3.mcnloc.ru',
        'bucket_name' => 'autocaller',
        'use_ssl' => true,
    ],
];
