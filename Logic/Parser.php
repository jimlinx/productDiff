<?php


namespace Logic;


use Utility\FileReader;
use Utility\Util;

class Parser
{
    static $MIL_INDEX_ID = 6;
    static $MIL_INDEX_IMG = 35;

    static $OLD_INDEX_ID = 0;
    static $OLD_INDEX_IMG = 29;

    static $LIVE_INDEX_ID = 0;
    static $LIVE_INDEX_TYPE = 1;
    static $LIVE_INDEX_SKU = 2;
    static $LIVE_INDEX_CATEGORIES = 26;
    static $LIVE_INDEX_IMG = 29;
    static $LIVE_INDEX_PARENT = 32;


    /**
     * STEP1: Read CSV, store [ID | TYPE | SKU | CATEGORY | PARENT | IMAGES]
     *
     * STEP2: Identify Parents - find all 'variable' type and save categories
     * STEP3: Process 'variation' types
     *      - If SKU is empty, it is the parent. i.e.: /category/subcategory/parent/image
     *      - Else /category/subcategory/[parent]sku/image
     *
     * STEP4: Process 'simple' types. i.e.: /category/subcategory/sku/image
     *
     * For multiple categories, save multiple times prefixing [M]. i.e.: /category/subcategory/[M][parent]sku/image
     *
     * @param $path
     * @return array
     */
    public static function parseDownloadData($path)
    {
        $csv = FileReader::readCSV($path);

        $csvRaw = [];
        foreach($csv as $row) {
            $csvRaw[$row[self::$LIVE_INDEX_ID]] = [
                'id' => $row[self::$LIVE_INDEX_ID],
                'type' => $row[self::$LIVE_INDEX_TYPE],
                'sku' => $row[self::$LIVE_INDEX_SKU],
                'categories' => $row[self::$LIVE_INDEX_CATEGORIES],
                'parent' => $row[self::$LIVE_INDEX_PARENT],
                'images' => explode(',', $row[self::$LIVE_INDEX_IMG])
            ];
        }

        # Identify Parents
        $parents = [];
        foreach($csvRaw as $row) {
            if($row['type'] == 'variable')
                $parents[$row['sku']] = self::parseCategories($row['categories']);
        }

        # Process Variations
        $skuImages = [];
        $parsed = [];
        foreach($csvRaw as $row) {
            if($row['type'] != 'variation')
                continue;

            $escapedSku = str_replace('/', '_', $row['sku']);
            $escapedParent = str_replace('/', '_', $row['parent']);

            # Parent
            if($row['sku'] == '') {
                $saveDirs = [];
                foreach($parents[$row['parent']] as $category) {
                    $saveDirs[] = $category . "[" . $escapedParent . "]" . $escapedParent;
                }

                $parsed[$row['id']] = [
                    'saveDir' => $saveDirs,
                    'images' => $row['images']
                ];
            }

            # Non-Parent
            if($row['sku'] != '') {
                $saveDirs = [];
                foreach($parents[$row['parent']] as $category) {
                    $saveDirs[] = $category . "[" . $escapedParent . "]" . $escapedSku;
                }

                $parsed[$row['id']] = [
                    'saveDir' => $saveDirs,
                    'images' => $row['images']
                ];

                if(array_key_exists($escapedSku, $skuImages)) {
                    array_push($skuImages[$escapedSku], Util::trimImagesArray($row['images']));
                } else
                    $skuImages[$escapedSku] = Util::trimImagesArray($row['images']);
            }
        }

        # Process Simples
        foreach($csvRaw as $row) {
            if($row['type'] != 'simple')
                continue;

            $escapedSku = str_replace('/', '_', $row['sku']);
            $categories = self::parseCategories($row['categories']);
            $saveDirs = [];
            foreach($categories as $category) {
                $saveDirs[] = $category . $escapedSku;
            }

            $parsed[$row['id']] = [
                'saveDir' => $saveDirs,
                'images' => $row['images']
            ];

            if(array_key_exists($escapedSku, $skuImages)) {
                array_push($skuImages[$escapedSku], Util::trimImagesArray($row['images']));
            } else
                $skuImages[$escapedSku] = Util::trimImagesArray($row['images']);

        }

        return [
            'parsed' => $parsed,
            'skuImages' => $skuImages
        ];
    }


    public static function parseCategories($line)
    {
        $categories = explode(',', $line);
        foreach($categories as $key => $value) {
            $categories[$key] = str_replace('&amp;', '&', trim($value));
            $categories[$key] = '/' . str_replace(' > ', '/', $categories[$key]) . '/';

            if(count($categories) > 1)
                $categories[$key] = $categories[$key] . "[M]";
        }
        return $categories;
    }


    public static function parseMil($path)
    {
        return self::parseCompareData($path, self::$MIL_INDEX_ID, self::$MIL_INDEX_IMG);
    }


    public static function parseOld($path)
    {
        return self::parseCompareData($path, self::$OLD_INDEX_ID, self::$OLD_INDEX_IMG);
    }


    public static function parseLive($path)
    {
        return self::parseCompareData($path, self::$LIVE_INDEX_ID, self::$LIVE_INDEX_IMG);
    }


    public static function parseCompareData($path, $index_id, $index_img)
    {
        $csv = FileReader::readCSV($path);

        $parsed = [];
        foreach($csv as $row) {
            $img = $row[$index_img];
            $split_img = preg_split('/[\ \n\,]+/', $img);
            foreach($split_img as $key => $url) {
                $trimmed = Util::trimImgUrl($url);
                if($trimmed == '')
                    unset($split_img[$key]);
                else
                    $split_img[$key] = $trimmed;
            }
            $parsed[$row[$index_id]] = $split_img;
        }
        unset($parsed['ID']);
        return $parsed;
    }


    public static function compareData()
    {
        $mil = self::parseMil('csv/mil.csv');
        $old = self::parseOld('csv/old.csv');
        $current = self::parseLive('csv/Afterlive.csv');

        $toAdd = self::findToAdd($mil, $old);
        $toRemove = self::findToRemove($mil, $old);
        echo "To Add: " . json_encode($toAdd) . "\n\n\n\n\n";
        echo "To Remove: " . json_encode($toRemove) . "\n\n\n\n\n";

        self::validateCurrent($current, $toAdd, $toRemove);
    }


    public static function findToAdd($mil, $old)
    {
        $result = [];
        foreach($mil as $key => $milImages) {
            $oldImages = $old[$key];
            $toAdd = array_values(array_diff($milImages, $oldImages));
            $result[$key] = $toAdd;
        }
        return $result;
    }


    public static function findToRemove($mil, $old)
    {
        $result = [];
        foreach($mil as $key => $milImages) {
            $oldImages = $old[$key];
            $toRemove = array_values(array_diff($oldImages, $milImages));
            $result[$key] = $toRemove;
        }
        return $result;
    }


    public static function validateCurrent($current, $toAdd, $toRemove)
    {
        $missingEntries = [];
        $dontIncludeToAdd = [];
        $includeToRemove = [];

        foreach($current as $key => $currentImages) {

            # Check if entry exists
            if(!array_key_exists($key, $toAdd)) {
                $missingEntries[] = $key;
                continue;
            }

            # Check missing toAdd
            $dontIncludeToAdd[$key] = array_values(array_diff($toAdd[$key], $currentImages));

            # Check toRemove still exist
            $includeToRemove[$key] = array_values(array_intersect($currentImages, $toRemove[$key]));
        }

        echo "Missing from original => " . json_encode($missingEntries) . "\n";
        echo "\n\n\n";

        foreach($dontIncludeToAdd as $key => $entry) {
            if(!empty($entry))
                echo "To Add [$key] => " . json_encode($entry) . "\n";
        }
        echo "\n\n\n";

        foreach($includeToRemove as $key => $entry) {
            if(!empty($entry))
                echo "To Remove [$key] => " . json_encode($entry) . "\n";
        }
    }


    public static function findMissingSkuImage($skuImages, $csvPath)
    {
        $csv = FileReader::readCSV($csvPath, true);

        $csvImages = [];
        foreach($csv as $row) {
            $sku = $row[2];
            if(array_key_exists($sku, $csvImages)) {
                array_push($csvImages[$sku], $row[0]);
            } else
                $csvImages[$sku] = [$row[0]];
        }

        unset($csvImages['Folder Path']);
        echo "\nImages to check\n";
        echo "\n" . json_encode($csvImages, JSON_UNESCAPED_SLASHES) . "\n";


        foreach($csvImages as $sku => $images) {
            if(!array_key_exists($sku, $skuImages)) {
                echo "SKU not found [$sku]\n";
                continue;
            }

            foreach($images as $image) {
                if(!in_array($image, $skuImages[$sku])) {
                    echo "Missing Image [SKU: $sku][$image]\n";
                }
            }
        }
    }
}