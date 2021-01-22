<?php


namespace Utility;


use DateTime;

class Util
{
    public static function trimImgUrl($url)
    {
        $regex = "!" . ".*\/(.*)" . "!";
        preg_match($regex, $url, $match);
        if (empty($match))
            return $url;
        return $match[1];
    }


    public static function createDir($dirPath)
    {
        $dir = getcwd() . "/images" . $dirPath;
        is_dir($dir) ? null : mkdir($dir, 0777, true);
        return $dir;
    }


    public static function duration($startTime)
    {
        $duration = $startTime->diff(new DateTime());
        return $duration->i . "min " . $duration->s . "sec";
    }


    public static function trimImagesArray($imagesArray)
    {
        foreach($imagesArray as $key => $url) {
            $trimmed = Util::trimImgUrl($url);
            if($trimmed == '')
                unset($imagesArray[$key]);
            else
                $imagesArray[$key] = $trimmed;
        }
        return $imagesArray;
    }
}