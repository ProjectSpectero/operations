<?php

require_once "blocks.php";

$listeners = [];
$port = 10240;

foreach ($proxyRanges as $range)
{
    for ($i = 1; $i < 255; $i++)
    {
        $listeners[] = [
            'item1' => sprintf($range, $i),
            'item2' => 10240,
        ];
    }
}

echo json_encode($listeners) . PHP_EOL;