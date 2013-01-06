<?php
require('htmql.php'); # Including functions file

$content = file_get_contents('http://www.google.com/'); # Fetching HTML from Url

//Extract all attribute and text from tag a
	$sql = "SELECT href FROM a";  # Query 1

//Extract src attribute values from img tag where src not equal to '/'
	//$sql = "SELECT src FROM img WHERE src != '/'";  # Query 2

//Extract name and content attribute values from meta tag where name like description
	//$sql = "SELECT name,content FROM meta WHERE name LIKE '^description$'";  # Query 3

//Convert html to text
	//$sql = "SELECT html2txt FROM *";  # Query 4
	
//Extract src FROM img tag where src equal to '/intl/en_ALL/images/srpr/logo1w.png'
	//$sql = "SELECT src FROM img WHERE src = '/intl/en_ALL/images/srpr/logo1w.png'";  # Query 5

//Convert relative urls in href and src attribute to absolute urls
	//$rel2abs = false; # Convert Relative Urls to Absolute Url 
	//$base_url = 'http://www.google.com/'; # Base Url for converting Relative url to Absolute url
	//$url_attrib = Array('href','src'); # Key of Attribute which contains URL 

$result = htmql_query($content,$sql);

echo '<pre>';
	print_r($result); # Output Result
echo '</pre>';
?>
