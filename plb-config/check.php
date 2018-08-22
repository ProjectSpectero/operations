<?php
require_once "blocks.php";

if ($argc <3)
    die("provide: username password uri (optional)\n");

$username = $argv[1];
$password = $argv[2];

if (isset($argv[3]))
    $uri = $argv[3];
else
    $uri = "https://www.google.com/";

$auth = base64_encode($username . ':' . $password);

foreach ($proxyRanges as $range)
{
    $host = sprintf($range . ':%d', mt_rand(1, 254), 10240);

    $ctxOptions = [
        'http' => [
            'proxy' => sprintf('tcp://%s', $host),
            'request_fulluri' => true,
            'header' => "Proxy-Authorization: Basic $auth",
        ]
    ];

    $ctx = stream_context_create($ctxOptions);
    $headers = @get_headers($uri, 1 , $ctx);

    if ($headers == false)
        echo "Request to $uri via $host failed, you might wanna check up on it.\n";
    else
    {
        // "HTTP/1.0 301 Moved Permanently"
        $code = null;
        $message = null;
        $version = null;

        sscanf($headers[0], "HTTP/%f %d %s", $version, $code, $message);

        switch ($code)
        {
            case 200:
                echo "Proxy $host worked fine, $uri returned 200/OK\n";
                break;
            case 301:
            case 302:
                echo "Proxy $host made $uri redirect to " . $headers['Location'] . " ($code)\n";
                break;

            default:
                echo "Proxy $host made $uri return $code, further details: \n";
                var_dump($headers);
                break;
        }
    }
}
