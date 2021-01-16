<?php

use Logic\Parser;
use Utility\Util;
use Utility\WebExtractor;

include 'Common.php';

main();


function main() {
    Parser::compareData();
}


function downloadImages()
{
    $startTime = new DateTime();
    $parsed = Parser::parseDownloadData('csv/Beforelive.csv');
//    echo "Parsed: \n" . json_encode($parsed, JSON_UNESCAPED_SLASHES) . "\n";
    WebExtractor::saveParsedData($parsed);
    echo "\nOperation completed after " . Util::duration($startTime) . "\n";
}
