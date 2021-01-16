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

        while (($line = fgetcsv($file)) !== FALSE) {
            $csv[] = $line;
        }
        fclose($file);
        return $csv;
    }

}