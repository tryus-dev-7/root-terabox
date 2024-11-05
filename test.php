<?php

function extractVideoId($url)
{
    if (preg_match('/\/s\/1(.+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

$id = extractVideoId("https://terabox.com/s/19M07bES1zTp9G9b8Civn_w");
print_r($id);