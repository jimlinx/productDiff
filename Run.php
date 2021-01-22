<?php

use Logic\Parser;
use Utility\Util;
use Utility\WebExtractor;

include 'Common.php';

main();


function main() {
//    Parser::compareData();
    downloadImages();
}


function downloadImages()
{
    $startTime = new DateTime();
    $data = Parser::parseDownloadData('csv/Beforelive.csv');
//    echo "\n" . json_encode($data['parsed'], JSON_UNESCAPED_SLASHES) . "\n";
//    echo "\n" . json_encode($data['skuImages'], JSON_UNESCAPED_SLASHES) . "\n";
//    WebExtractor::saveParsedData($data['parsed']);

    Parser::findMissingSkuImage($data['skuImages'], 'csv/new-images-to-add-pre-mess.csv');

    echo "\nOperation completed after " . Util::duration($startTime) . "\n";
}
