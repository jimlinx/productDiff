<?php

use Logic\Parser;
use Utility\Util;
use Utility\WebExtractor;

include 'Common.php';

# JSON_UNESCAPED_SLASHES
main();


function main() {

    Parser::compareData();

//    $x = ['a','b','c'];
//    $y = ['b','c','d'];
//    $diff = array_intersect($x, $y);
//    echo json_encode($diff) . "\n";


}


function downloadImages()
{
    $startTime = new DateTime();
    $parsed = Parser::parseDownloadData('csv/Beforelive.csv');
//    echo "Parsed: \n" . json_encode($parsed, JSON_UNESCAPED_SLASHES) . "\n";
    WebExtractor::saveParsedData($parsed);
    echo "\nOperation completed after " . Util::duration($startTime) . "\n";
}


function testDownload()
{
    $url = "http://aaronknightdev.com/ecommerce/wp-content/uploads/2020/12/1NGUR9594.png";
    $savePath = "zzzzz/product/test.png";
    WebExtractor::saveUrlImage($savePath, $url);
}