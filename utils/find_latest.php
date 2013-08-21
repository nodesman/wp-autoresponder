<?php

$url = "https://core.svn.wordpress.org/tags/";
$output = file_get_contents($url);

$doc = new DOMDocument();
$doc->loadHTML($output);
$listElements = $doc->getElementsByTagName("li");
$lastNode = $listElements->item($listElements->length-1);
echo trim($lastNode->textContent,'/');

