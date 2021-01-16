<?php


namespace Utility;


class FileReader
{

    /**
     * @param $path
     * @return array|void
     */
    public static function readCSV($path)
    {
        $file = fopen($path, 'r');
        $csv = [];

//        $count = 0;
        while (($line = fgetcsv($file)) !== FALSE) {
//            if($count == 3)
//                break;

            $csv[] = $line;
//            $count++;
        }
        fclose($file);
        return $csv;
    }

}