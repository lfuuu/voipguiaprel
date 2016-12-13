<?php
namespace app\classes;

class PrefixExpander
{
    public static function expand($sourcePrefixList)
    {
        $resultPrefixes = [];

        foreach ($sourcePrefixList as $sourcePrefix) {
            $prefixes = [];
            preg_match_all('/(^(\d*)|\[(\d+)\])/', $sourcePrefix, $result, PREG_SET_ORDER);
            foreach($result as $res) {
                if (count($res) == 3) {
                    $prefixes[] = $res[0];
                } elseif (count($res) == 4) {
                    $newPrefixes = [];
                    foreach ($prefixes as $prefix) {
                        for ($pos = 0; $pos < strlen($res[3]); $pos++) {
                            $newPrefixes[] = $prefix . $res[3][$pos];
                        }
                    }
                    $prefixes = $newPrefixes;
                }
            }

            $resultPrefixes = array_merge(
                $resultPrefixes,
                $prefixes
            );
        }

        return array_unique($resultPrefixes);
    }
}