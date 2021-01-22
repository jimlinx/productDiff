<?php


namespace Utility;


class FileReader
{

    /**
     * @param $path
     * @return array|void
     */
    public static function readCSV($path, $trimSlash = false)
    {
        $file = fopen($path, 'r');
        $csv = [];

        while (($line = fgetcsv($file)) !== FALSE) {
            if($trimSlash)
                $line = str_replace("\\", '', $line);
            $csv[] = $line;
        }
        fclose($file);
        return $csv;
    }

}