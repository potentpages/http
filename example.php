<?php
require_once("vendor/autoload.php");

use PotentHTTP\Client;

$httpObj = new Client();
$httpObj->add_get("https://potentpages.com/",
    "",
    null,
    "cookies.txt",
    false,
    null,
    null,
    10,
    15,
    "./artifacts/example.html",
    null);


$httpObj->add_get("https://potentpages.com/test.txt", "", null, "cookies.txt", false, null, null, 10, 15, null, null);
$response = $httpObj->run();
print_r($response);

$httpObj = null;
