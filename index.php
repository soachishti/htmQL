<?php
include('htmql.php');

$content = file_get_contents('http://www.google.com/'); # Fetching HTML from Url

$rel2abs = true; # Convert Relative Urls to Absolute Url 
$base_url = 'http://www.google.com/'; # Base Url for converting Relative url to Absolute url
$url_attrib = Array('href','src'); # Key of Attribute which contains URL 

$sql = "SELECT * FROM a";  # Querying HTML

$result = html_query($content,$sql);

echo '<pre>';
	print_r($result); # Output Result
echo '</pre>';
?>