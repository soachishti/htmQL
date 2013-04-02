<?php
require('htmql_simpleXML.php'); # Including functions file
//require('htmql_regex.php'); # Including functions file

$url = "http://www.google.com/";
$content = file_get_contents($url); # Fetching HTML from Url


//Extract all attribute and text from tag a
	$sql = "SELECT * FROM *";  # Query 1

//Extract src attribute values from img tag where src not equal to '/'
	//$sql = "SELECT src FROM img WHERE src != '/'";  # Query 2

//Extract name and content attribute values from meta tag where name like description
	//$sql = "SELECT name,content FROM meta WHERE name LIKE '^description$'";  # Query 3

//Convert html to text
	//$sql = "SELECT html2text FROM *";  # Query 4 This is fixed query you cannot add special tag.
	
//Extract href attributes value where id, class and style are given as supplied.
	//$sql = "SELECT href FROM a WHERE id = 'something' AND class = 'something' OR style = 'beautiful'";  # Query 5
		
//Convert relative urls in href and src attribute to absolute urls
$rel2abs = true; # Convert Relative Urls to Absolute Url 
$baseUrl = $url; # Base Url for converting Relative url to Absolute url
$urlAttribute = Array('href','src','content'); # Key of Attribute which contains URL 
$htmlEncode = true; # Key of Attribute which contains URL 
//$removeSpecialChars = true; # Remove special characters from html if query is "SELECT html2text FROM *"
//$removeHtml = true; # Remove html from tag text.

$start = microtime();

$result = htmql_query($content,$sql); //Executing Query

$end = microtime();
$time = $end-$start;
echo "<br />Query perform in $time seconds<br />";
echo '<pre>';
	print_r($result); # Output Result		
echo '</pre>';

?>
