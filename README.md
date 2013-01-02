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

      $sql = "SELECT * FROM a";
      $result = html_query($content,$sql);
      
**Example Usage 2**

      $sql = "SELECT src FROM img WHERE src = '/'";
      $result = html_query($content,$sql);
    
**Example Usage 3**

      $sql = "SELECT name,content FROM meta WHERE name LIKE '^description$'";
      $result = html_query($content,$sql);


Original idea is from [Jonas John](http://www.jonasjohn.de/old-projects.htm)
