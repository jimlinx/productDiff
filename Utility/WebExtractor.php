<?php


namespace Utility;


class WebExtractor
{

    /**
     * @param $saveDir "/category/subcategory/sku"
     * @param $url "http://aaronknightdev.com/ecommerce/wp-content/uploads/2020/12/1NGUR9594.png"
     */
    public static function saveUrlImage($saveDir, $url)
    {
        $saveDir = Util::createDir($saveDir);

        if($url == '') {
            echo "No image\n";
            return;
        }

        $regex = "!" . ".*\/(.*)" . "!";
        preg_match($regex, $url, $match);
        $saveDir = $saveDir . "/" . $match[1];
        file_put_contents($saveDir, file_get_contents($url));
        echo "    Saved: $saveDir\n";
    }


    public static function saveParsedData($parsed)
    {
        $total = count($parsed);
        $index = 1;
        foreach($parsed as $key => $entry) {
            echo "Processing [$index out of $total][" . $key . "]\n";
            foreach($entry['images'] as $imageUrl) {
                foreach($entry['saveDir'] as $dir) {
                    self::saveUrlImage($dir, trim($imageUrl));
                }
            }
            $index++;
        }
    }
}