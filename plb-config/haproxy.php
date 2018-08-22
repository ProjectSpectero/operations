<?php

require_once "blocks.php";

$mappingFileName = 'mapping';
$simpleMappingFileName = "mapping.simple";
$extrasFileName = 'extras.txt';

$start = <<<EOF
global
        user haproxy
        group haproxy
        daemon
        nbproc 1
        cpu-map 1 0
        cpu-map 2 1
        maxconn 10000
defaults
        mode    tcp
        balance leastconn
        timeout client      30000ms
        timeout server      30000ms
        timeout connect      3000ms
        retries 3
EOF;

function println ($template, ...$data)
{
    printf($template . "\n", $data);
}

$proxyBase = '23.155.192.2';
$portStart = 12500; // Start well away from privileged ports
$portUpperBound = 65534;

$tiers = [
    12500 => 3000,
    32500 => 100,
];

$proxyPort = 10240;
$counter = 1;

echo $start . "\n";

$lbPort = $portStart;

foreach ($tiers as $start => $count)
{
    $localMappingFileName = $mappingFileName . '-' . $count . '.txt';
    $localSimpleMappingFileName = $simpleMappingFileName . '-' . $count . '.txt';

    if (file_exists($localMappingFileName))
        unlink($localMappingFileName);

    if (file_exists($localSimpleMappingFileName))
        unlink($localSimpleMappingFileName);
}

if (file_exists($extrasFileName))
    unlink($extrasFileName);

$fullListOfProxies = [];

printf ("# Proxy range order: %s\n\n", implode(", ", $proxyRanges));
foreach ($proxyRanges as $proxyRange)
{
    for ($i = 1; $i < 255; $i++)
    {
        $proxy = sprintf($proxyRange . ':%d', $i, $proxyPort);
        $fullListOfProxies[] = $proxy;
    }
}

foreach ($tiers as $startPort => $count)
{
    $lbPort = $startPort;

    $localMappingFileName = $mappingFileName . '-' . $count . '.txt';
    $localSimpleMappingFileName = $simpleMappingFileName . '-' . $count . '.txt';

    for ($i = 0; $i < $count; $i++)
    {
        $random = array_rand($fullListOfProxies);
        $proxy = $fullListOfProxies[$random];

        $listener = $proxyBase . ':' . $lbPort;
        println('frontend managed_proxy_' . $counter);
        println('    bind ' . $listener);
        println('    default_backend bk_proxy_' . $counter);
        printf("\n");
        println('backend bk_proxy_' . $counter);
        println('   server proxy_server_' . $counter . ' ' . $proxy . ' maxconn 2048');
        printf("\n");
        $counter++;
        $lbPort++;

        file_put_contents($localMappingFileName, $listener . ' -> ' . $proxy . "\n", FILE_APPEND);
        file_put_contents($localSimpleMappingFileName, $listener . "\n", FILE_APPEND);

        if ($lbPort > $portUpperBound)
            throw new Exception("Ran out of ports on $proxyBase while mapping $proxy to $listener");

        // Remove it, since been chosen once
        unset($fullListOfProxies[$random]);
    }
}

foreach ($fullListOfProxies as $proxy)
{
    file_put_contents($extrasFileName, $proxy . "\n", FILE_APPEND);
}

