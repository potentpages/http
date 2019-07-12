<?php 
require_once("Http.class.php");

$httpObj = new Http();
$httpObj->add_get("https://potentpages.com/", "", null, "cookies.txt", false, null, null, 10, 15, "out.html", null);
$httpObj->add_get("https://potentpages.com/test.txt", "", null, "cookies.txt", false, null, null, 10, 15, null, null);
$response = $httpObj->run();
print_r($response);

$httpObj = null;
