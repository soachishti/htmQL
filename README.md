htmQL (HTML parser using SQL)
=============================

htmQL PHP library with the help of which
you to parse HTML by using SQL syntax.

Requirements:
------------
* PHP > 5

Features:
------------
* Better recognition of Tags
* Extract html content using SQL
* Support SELECT and WHERE clause.
* Convert HTML to Text
* Convert Relatvie Urls to Absolute Urls
* No use of eval function as compare to [htmlSQL](https://github.com/hxseven/htmlSQL).

**htmQL query look like**

    SELECT text,href,title FROM a WHERE class = "link" AND id LIKE '/'
           ^ Attributes or      ^       ^ attribute value           ^ 
             "*" = all Attribs  ^         must be                   ^ Regex Expression eg "^html$"
             to return          ^
                                ^
                                ^ HTML tag to search in
                                  "*" = all tags

**Example Usage 1**

Extract all attribute and text from tag a

      $sql = "SELECT * FROM a";
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
      
**Example Usage 2**

Extract src attribute values from img tag where src not equal to '/'

      $sql = "SELECT src FROM img WHERE src != '/'";
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
    
**Example Usage 3**

Extract name and content attribute values from meta tag where name like description

      $sql = "SELECT name,content FROM meta WHERE name LIKE '^description$'";
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
      
**Example Usage 4**

Convert html to text

      $sql = "SELECT html2txt FROM *";
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
      
**Example Usage 5**

Convert relative urls in href and src attribute to absolute urls

      $url = "'http://www.google.com/'";
      $rel2abs = true;
      $base_url = $url;
      $url_attib = Array('href','src');
      $sql = "SELECT href,src FROM img,a";
      $content = file_get_contents($url);
      $result = html_query($content,$sql);      


Original idea is by [Jonas John](http://www.jonasjohn.de/old-projects.htm)
