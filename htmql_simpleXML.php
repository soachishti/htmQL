<?php
/*
 * htmSQL (HTML parser using SQL)
 * Created January 2, 2013
 * Version 1.1
 * 
 * Copyright (c) 2013, soacWAY (soacway@gmail.com).
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 * 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL soacWAY BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

ini_set("display_errors", "on"); # Report all Errors
error_reporting(E_ALL ^ E_NOTICE); # Report all Errors
//error_reporting(0); # No Error Reporting

/*======================================================================*\
	Function:	htmql_query
	Purpose:	Parse SQL syntax and output result
	Input:		HTML content, SQL query
	Output:		Tag Data
\*======================================================================*/

function htmql_query($content,$sql)
{
	global $removeSpecialChars,$rel2abs,$baseUrl,$urlAttribute;
	preg_match("#^SELECT\s*(.*?)\s*FROM\s*(.*?)\s*(|WHERE\s*(.*?)\s*)$#",$sql,$out); #

	$where = !empty($out[4]) ? $out[4] : null ;
	$select = !empty($out[1]) ? array_flip(explode(",",strtolower($out[1]))) : null;
	$requiredTags = !empty($out[2]) ? array_flip(explode(",",$out[2])) : null;

	if($select == null)
	{
		die("Attributes doen't exit");
	}
	else if($requiredTags  == null)
	{
		die("Tag deosn't exist");
	}
	
	if(isset($select["html2text"]))
	{
		$removeSpecialChars = !empty($removeSpecialChars) ? true : false; 
		$data["html2text"] = html2txt($content,$removeSpecialChars);
	}
	else
	{	
			
		if($where != null)
		{
			preg_match_all("#(|OR|AND)\s*([a-z0-9\-]+)\s*(LIKE|\!=|=|<|>)+\s*[\"\'](.*?)[\"\']\s*(|OR|AND)*#is",$where,$out);

			$count = count($out[2])-1;
			$keys = $out[2];
			$condition = $out[1];
			$values = $out[4];
			$comparison = $out[3];
		}
		
		$tidy = new tidy();
		$tidy = $tidy->repairString($content);
		
		$doc = new DOMDocument();
		@$doc->loadHTML($tidy);

		$html = simplexml_import_dom($doc); 
		$data = Array();
		
		
		foreach($html as $k1 => $v1)
		{
			$i = 0;
			foreach($v1 as $k2 => $v2)
			{	
				$data[$k2][$i]['text'] = trim((string)$v2[0]);
				$attribute = $v2->attributes();			
				foreach($attribute as $k3 => $v3)
				{
					if($rel2abs == true && isset($baseUrl) && in_array($k3, $urlAttribute)) 
					{
						$data[$k2][$i][$k3] = trim(rel2abs($baseUrl,(string)$v3));
					}
					else
					{
						$data[$k2][$i][$k3] = trim(utf8_decode((string)$v3));
					}
				}
				if(empty($data[$k2][$i]['text']))
				{
					unset($data[$k2][$i]['text']);
				}
				sort($data[$k2]);
				$i++;
			}
			$data[$k1][] = $data;
		}
		
		if($where != null)
		{
			foreach($data as $k1 => $v1)
			{
				foreach($v1 as $k2 => $v2)
				{
					$datas = !empty($data[$k1][$k2]) ? $data[$k1][$k2] : Array() ;	
					
					if(found($datas,$keys,$values,$condition,$count,$comparison) != 1)
					{
						unset($data[$k1][$k2]);
					}
					else
					{
						foreach($datas as $k3 => $v3)
						{
							if(!isset($select[$k3]) && !isset($requiredTags["*"]))
							{	
								unset($data[$k1][$k2][$k3]);
							}
						}
					}
					if(empty($data[$k1]))
					{
						unset($data[$k1]);
					}
				}
			}
		}
		
		if(!isset($requiredTags["*"]))
		{
			foreach($data as $key => $value)
			{
				if(!isset($requiredTags[$key]))
				{
					unset($data[$key]);
				}
			}
		}
		$data = sorting($data);
	}
	unset($content,$tags);	
	return (!empty($data)) ? $data : Array();
}

/*======================================================================*\
	END OF Function htmql_query
\*======================================================================*/

/*======================================================================*\
	Function:	found
	Purpose:	It is main function used for matching WHERE caluses with the data. 
	Input:		Data of single tag, Keys of where clause, Values of Where Clause, Operator of Where clause eg '=,!=,<,>,LIKE', no of WHERE Clauses 
	Output:		True if WHERE Clause match the data
\*======================================================================*/

function found($data,$key,$values,$condition,$count,$operator)
{
	for($i = 0;$i <= $count;$i++)
	{
		if(compare($data[$key[$i]],$values[$i],$operator[$i]))
		{
			$res[] = 1;
		}
		else
		{
			$res[] = 0;
		}
	}
	for($i = 1;$i <= $count;$i++)
	{
		if($condition[$i] == "AND")
		{
			$result = (isset($result)) ? $result * $res[$i] : $res[$i] * $res[$i-1];			
		}
		if($condition[$i] == "OR")
		{
			$result = (isset($result)) ? $result + $res[$i] : $res[$i] + $res[$i-1];
		}
	}
	unset($data);
	if(!isset($result)){
		return $res[0];
	}
	if($result == 0){
		return 0;
	}
	else{
		return 1;
	}
}

/*======================================================================*\
	END OF Function found
\*======================================================================*/

/*======================================================================*\
	Function:	compare
	Purpose:	This function will compare the two value with given operator.
	Input:		value1, $value2 and operator.
	Output:		True if Match else False
\*======================================================================*/

function compare($k1,$k2,$operator)
{
	switch($operator)
	{
		case "LIKE":
			return (preg_match("#".addslashes($k2)."#is",$k1)) ? true : false;
		break;
		case "=":
			return ($k1 == $k2) ? true : false ;
		break;
		case "!=":
			return ($k1 !== $k2) ? true : false ;
		break;
		case "<":
			return ($k1 < $k2) ? true : false ;
		break;
		case ">":
			return ($k1 > $k2) ? true : false ;
		break;
	}
}

/*======================================================================*\
	END OF Function compare
\*======================================================================*/

/*======================================================================*\
	Function:	html2txt
	Purpose:	This function will remove all tags from html
	Input:		html as string or true and false if you want to remove special character.
	Output:		Text of HTML
\*======================================================================*/

function html2txt($html,$special = false)
{
	/* regex for removing html tags,comment,spaces, script and link tag  */
	$search = array (
	"#<script[^>]*?>.*?</script>#si","#<style[^>]*?>.*?</style>#si",
	"#<link rel[^<>]*>#i",
	"#<!--.*?-->#si",
	"#&nbsp;#",
	"#<.*?>#"
	);
	$txt = preg_replace($search, " ", $html); # replacing html tags, comments, spaces, script and link tags
	/* Removing Special Characters  */
	if($special == true)
	{
		$txt = preg_replace("/(\,|\.|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\_|\+|\=|\-|\[|\]|\;|\'|\\|\/|\|\,|\{|\}|\:|\"|\||\?|\>|\<|\>|\\|\||\/)/","", $txt);
		$txt = preg_replace("/[^(\x20-\x7F)]*/","", $txt);
	}
	$txt = preg_replace("#\s+#"," ", $txt); # Removing repaeated spaces.
	return trim($txt); # return text
}

/*======================================================================*\
	END OF Function html2txt
\*======================================================================*/

/*======================================================================*\
	Function:	rel2abs
	Purpose:	Convert Relative URL to Absolute Url
	Input:		Relative Url and Base Url
	Output:		Absolute Url
\*======================================================================*/

function rel2abs($base,$rel)
{
	if (parse_url($rel, PHP_URL_SCHEME) != "") # return if already absolute URL 
	{
		return $rel;
	}
	
	if ($rel == "#" || $rel == "?") # queries and anchors 
	{
		return $base.$rel;
	}
	
	extract(parse_url($base)); # parse base URL and convert to local variables: $scheme, $host, $path
	$path = (isset($path)) ? $path : "/";
	$path = preg_replace("#/[^/]*$#", "", $path); # remove non-directory element from path
	
	if ($rel == "/") 
	{
		$path = "";
	}
	$abs = $host . $path . "/" . $rel;
	$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'); # replace '//' or '/./' or '/foo/../' with '/'
	for($n = 1; $n > 0; $abs=preg_replace($re, "/", $abs, -1, $n)) {}

	return $scheme."://".$abs; #absolute URL is ready!
}

/*======================================================================*\
	END OF Function rel2abs
\*======================================================================*/

/*======================================================================*\
	Function:	sorting
	Purpose:	sort final variable from 0th key
	Input:		array
	Output:		array with 0th key
\*======================================================================*/

function sorting($array) {
	
	if(is_array($array))
	{
		foreach($array as $key => $value)
		{
			$count = count($array[$key])-1;
			$i = 0;
			$a[$key] = null;
			foreach($value as $v)
			{
				$a[$key][$i] = $v;
				$i++;
			}
		}
	}
	unset($array);
    return $a;
}

/*======================================================================*\
	END OF Function sorting
\*======================================================================*/
?>