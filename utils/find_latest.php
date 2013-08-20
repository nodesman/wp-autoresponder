<?php

$svn_tag_list = "https://core.svn.wordpress.org/tags/";
$cache_filepath = "cache/latest";
if (is_file($cache_filepath) && (86400 > ( time() - filemtime($cache_filepath) ) ) ) {
    $version = file_get_contents($cache_filepath);
} else {
	$content = file_get_contents($svn_tag_list);
	$doc = new DOMDocument();
	$doc->loadHTML($content);
	$listElements = $doc->getElementsByTagName("li");
	$lastOne = $listElements->item($listElements->length-1);
	$version = trim($lastOne->textContent,"/");
	file_put_contents($cache_filepath, $version);
}

echo $version;

