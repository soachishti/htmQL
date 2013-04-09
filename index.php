<?php
require('htmql.php'); # Including functions file

$htmql = new htmql();
//Settings
//$htmql->rel2abs = false; # Convert Relative Urls to Absolute Url 
//$htmql->baseUrl;  # Base Url for converting Relative url to Absolute url
//$htmql->urlAttribute = Array('href','src'); # Key of Attribute which contains URL 
//$htmql->htmlEncode = true; # Key of Attribute which contains URL (For regex Version)
//$htmql->removeHtml = true; # Remove html from tag text. (For regex Version)
//$htmql->removeSpecialChars = true; # Remove special characters from html if query is "SELECT html2text FROM *"
$htmql->parseMethod = "SIMPLEXML"; # Convert Relative Urls to Absolute Url 
$htmql->NoMultiDimenArray = 0; # Return result in Multi dimensional Array or Single Array.

	$connect = $htmql->connect("string",file_get_contents('foo.html'));
//	$connect = $htmql->connect("host","http://www.google.com/");

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

$result = $htmql->query($sql); //Executing Query
if($connect == false || $result == false)
{
	die($htmql->error);
}

echo "<pre>";

	echo "<b>No of results:</b>" . $htmql->num_rows() . "<br />"; // No of Results
	foreach($htmql->fetch_array() as $row)
	{
		print_r($row);
	}
//
//	foreach($htmql->fetch_object() as $row)
//	{
//		print_r($row);
//	} 

echo '</pre>';
$htmql->close(); // Deleting variables
?>
