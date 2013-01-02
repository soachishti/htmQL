htmQL (HTML parser using SQL)
=============================

htmQL PHP library with the help of which
you to parse HTML by using SQL syntax.

Requirements:
------------
* PHP > 5

**htmQL query look like**

    SELECT text,href,title FROM a WHERE class = "link" AND id LIKE '/'
           ^ Attributes or      ^       ^ attribute value           ^ 
             "*" = all Attribs  ^         must be                   ^ Regex Expression eg "^html$"
             to return          ^
                                ^
                                ^ HTML tag to search in
                                  "*" = all tags

**Example Usage 1**

      $sql = "SELECT * FROM a"; # Extract all attribute and text from tag a
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
      
**Example Usage 2**

      $sql = "SELECT src FROM img WHERE src != '/'"; # Extract src attribute values from img tag where src not equal to '/'
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
    
**Example Usage 3**

      $sql = "SELECT name,content FROM meta WHERE name LIKE '^description$'"; # Extract name and content attribute values from meta tag where name like description
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);
      
**Example Usage 4**

      $sql = "SELECT html2txt FROM *"; # Convert html to text
      $content = file_get_contents('http://www.google.com/');
      $result = html_query($content,$sql);


Original idea is from [Jonas John](http://www.jonasjohn.de/old-projects.htm)
