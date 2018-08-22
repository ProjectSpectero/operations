<?php

$dataFile = file('data.txt');
$mapFile = file('map.txt');

$data = [];
foreach ($dataFile as $lineNumber => $line)
{
    list($endpoint, $rate) = explode(',', $line);

    $data[$endpoint] = rtrim($rate);
}

$map = [];

foreach ($mapFile as $lineNumber => $line)
{
    list($endpoint, $outgoingIP) = explode(' -> ', $line);

    $map[$endpoint] = rtrim($outgoingIP);
}


foreach ($data as $endpoint => $rate)
{
    echo "$endpoint -> " . $map[$endpoint] . " ($rate% failure)" . PHP_EOL;
}