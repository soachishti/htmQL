htmQL (HTML parser using SQL) v2
=============================

htmQL PHP library with the help of which
you to parse HTML by using SQL syntax.

Requirements:
------------
* PHP > 5
* SimpleXML and DOM (libXML)(needed for simpleXML version)
* HTML Tidy

Features:
------------
* Better recognition of Tags.
* Support Invalid HTML (simpleXML version support this)
* Faster than other HTML parser classes  (simpleXML version support this)
* Extract html tags using SQL syntax.
* Support SELECT and WHERE clause.
* Convert HTML to Text.
* Convert Relatvie Urls to Absolute Urls.
* No use of eval function as compare to [htmlSQL](https://github.com/hxseven/htmlSQL).

**htmQL query look like**

		SELECT text,href,title FROM a WHERE class = "link" AND id LIKE '^/$'
			   ^ Attributes or      ^       ^ attribute value           ^ 
				 "*" = all Attribs  ^         must be                   ^ Regex Expression eg "^html$"
				 to return          ^
									^
									^ HTML tag to search in
									  "*" = all tags

**Example Usage 1**

Extract all attribute and text from tag a

		  $htmql = new htmql();
		  $connect = $htmql->connect("host",$url);
		  $result = $htmql->query("SELECT * FROM a");

**Example Usage 2**

Extract src attribute values from img tag where src not equal to '/'

		  $htmql = new htmql();
		  $connect = $htmql->connect("host",$url);
		  $result = $htmql->query("SELECT src FROM img WHERE src != '/'");
		
**Example Usage 3**

Extract name and content attribute values from meta tag where name like description

		  $htmql = new htmql();
		  $connect = $htmql->connect("host",$url);
		  $result = $htmql->query("SELECT name,content FROM meta WHERE name LIKE '^description$'");
      
**Example Usage 4**

Convert html to text

		  $htmql = new htmql();
		  $connect = $htmql->connect("host",$url);
		  $result = $htmql->query("SELECT html2txt FROM *");
      
**Example Usage 5**

Convert relative urls in href and src attribute to absolute urls

		  $htmql = new htmql();
		  $htmql->url = "http://www.google.com/";
		  $htmql->rel2abs = true;
		  $htmql->baseUrl = $url;
		  $htmql->urlAttribute = Array('href','src');
		  $connect = $htmql->connect("host",$url);
		  $result = $htmql->query("SELECT href,src FROM img,a");
    
**Example Output MultiDimentional**	  
	
		Array
		(
			[html] => Array
				(
					[0] => Array
						(
							[itemscope] => Lorem Ipsum
							[itemtype] => Lorem Ipsum
							[xmlns] => Lorem Ipsum
						)

				)

		)
		Array
		(
			[meta] => Array
				(
					[0] => Array
						(
							[itemprop] => Lorem Ipsum 1
							[content] => Lorem Ipsum 1
						)

					[1] => Array
						(
							[id] => Lorem Ipsum 2
							[name] => Lorem Ipsum 2
							[content] => Lorem Ipsum 2
						)

				)

		)
		Array
		(
			[title] => Array
				(
					[0] => Array
						(
							[text] => Lorem Ipsum
						)

				)

		)
		 

**Example Output Single Array**

		Array
		(
			[itemscope] => Lorem Ipsum
			[itemtype] => Lorem Ipsum
			[xmlns] => Lorem Ipsum
			[tagname] => html
		)
		Array
		(
			[itemprop] => Lorem Ipsum
			[content] => Lorem Ipsum
			[tagname] => meta
		)
		Array
		(
			[id] => Lorem Ipsum
			[name] => Lorem Ipsum
			[content] => Lorem Ipsum
			[tagname] => meta
		)
		 
License:
------------
htmQL uses BSD 2-Clause License.
	  
Original idea is by [Jonas John](http://www.jonasjohn.de/old-projects.htm)
