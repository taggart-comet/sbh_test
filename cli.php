<?php

require_once __DIR__ . '/start.php';

ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');
//


function getAlphabet($words) {

    $ar = explode("\n", trim($words));

    $max_length = 0;
    foreach ($ar as $k => $row) {
        $ar[$k] = str_split($row);

        if (strlen($row) > $max_length) {
            $max_length = strlen($row);
        }
    }

    // putting there first letters
    $result = [];
    foreach ($ar as $row) {

        if (empty($row) || !isset($row[0])) {
            continue;
        }

        $result[] = $row[0];
    }

    $result = array_values(array_unique($result));

    foreach ($ar as $row) {
        for ($i = 1; $i <= $max_length; $i++) {

            if (!isset($row[$i])) {
                continue;
            }

            if (in_array($row[$i], $result)) {
                continue;
            }

            // if it's not already there it goes after the row where it was found
            $result = array_merge($result, [$i => $row[$i]]);
        }
    }

    return implode('', $result);
}



$words = "
ccc
ddd
aaa
";

print_r(getAlphabet($words));

