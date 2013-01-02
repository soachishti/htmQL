<?php
/*
 * htmSQL (HTML parser using SQL)
 * Created January 2, 2013
 * Version 1
 * 
 * (c) 2013 soacWAY (soacway@gmail.com).
 * 
 * htmSQL  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * htmSQL  is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with htmSQL .  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

ini_set('display_errors', 'on'); # Report all Errors
error_reporting(E_ALL ^ E_NOTICE); # Report all Errors
//error_reporting(0); # No Error Reporting

/*======================================================================*\
	Function:	tag_extract
	Purpose:	fetch the contents of tags such as text and its attributes 
	Input:		Tags, Fetched HTML (content) and Attributes to Select(select)
	Output:		Tags content such as text and its attributes if exits
\*======================================================================*/

function html_query($content,$sql)
{
	preg_match("#^SELECT\s*(.*?)\s*FROM\s*(.*?)\s*(|WHERE\s*(.*?)\s*)$#",$sql,$out); #

	$where = isset($out[4]) ? $out[4] : null ;
	$select = array_flip(explode(',',strtolower($out[1])));
	$tags = array_flip(explode(',',$out[2])) ;
		
	if(isset($select['html2text']))
	{
		echo 1;
		$data['html2txt'] = html2txt($content);
	}
	else
	{	
		if(isset($tags['*']))
		{	
			preg_match_all("/<([a-z0-9\-]+)\s+?/is", $content, $out);
			$tags = array_flip(array_unique($out[1])); 
		} 

		$data = Array();
		foreach($tags as $key => $value)
		{
			$extracted_tags = tag_extract($key,$content,$select);
			$data = array_merge($extracted_tags,$data);
		}


	
		if($where != null)
		{
			preg_match_all("#(|OR|AND)\s*([a-z0-9\-]+)\s*(LIKE|\!=|=|<|>)+\s*[\"\'](.*?)[\"\']\s*(|OR|AND)*#is",$where,$out);
		
			$count = count($out[2])-1;
			$keys = $out[2];
			$condition = $out[1];
			$values = $out[4];
			$comparison = $out[3];
		
			foreach($data as $k1 => $v1)
			{
				foreach($v1 as $k2 => $v3)
				{
					for($i=0;$i<=count($v3['attribute'])-1;$i++)
					{
						$datas = $data[$k1][$k2]['attribute'][$i];	
						if(found($datas,$keys,$values,$condition,$count,$comparison) != 1)
						{
							unset($data[$k1][$k2]);
						}
					}
				}
			}
		}
	}
	return $data;
}

/*======================================================================*\
	END OF Function html_query
\*======================================================================*/

/*======================================================================*\
	Function:	tag_extract
	Purpose:	fetch the contents of tags such as text and its attributes 
	Input:		Tags, Fetched HTML (content) and Attributes to Select(select)
	Output:		Tags content such as text and its attributes if exits
\*======================================================================*/

function tag_extract($tag,$content,$select)
{
	global $rel2abs,$base_url,$url_attrib;
	$tags = Array();
	preg_match_all("/<{$tag}([ \t].*?|)>((.*?)<\/{$tag}>)?/is", $content, $out);
	
	for($i=0;$i<=count($out[0])-1;$i++)
	{
		echo '1';
		if(isset($select['text']) || isset($select['*']))
		{
			$tags[$tag][$i]['text'] = htmlspecialchars(trim($out[3][$i]));
		}
		preg_match_all('/\s*(.*?)\s*=\s*[\"\']?((?:.(?![\"\']?\s+(?:\S+)=|[>\"\']))+.)[\"\']?/is', $out[1][$i], $out1);
		foreach($out1[1] as $key => $value)
		{
			$out1[1][$key] = trim($out1[1][$key]);
			if(isset($select[$out1[1][$key]]) || isset($select['*']))
			{
				if($rel2abs == true && isset($base_url) && in_array($out1[1][$key], $url_attrib)) 
				{
					$tags[$tag][$i]['attribute'][$key][$out1[1][$key]] = rel2abs($base_url,$out1[2][$key]);
				}
				else
				{
					$tags[$tag][$i]['attribute'][$key][$out1[1][$key]] = $out1[2][$key];
				}
			}
		}
	}	
	return $tags;
}

/*======================================================================*\
	END OF Function tag_extract
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
		if($condition[$i] == 'AND')
		{
			$result = (isset($result)) ? $result * $res[$i] : $res[$i] * $res[$i-1];			
		}
		if($condition[$i] == 'OR')
		{
			$result = (isset($result)) ? $result + $res[$i] : $res[$i] + $res[$i-1];
		}
	}
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
		case 'LIKE':
			return (preg_match("#".addslashes($k2)."#is",$k1)) ? true : false;
		break;
		case '=':
			return ($k1 == $k2) ? true : false ;
		break;
		case '!=':
			return ($k1 !== $k2) ? true : false ;
		break;
		case '<':
			return ($k1 < $k2) ? true : false ;
		break;
		case '>':
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
	$txt = preg_replace($search, " ", $str); # replacing html tags, comments, spaces, script and link tags
	
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
	if (parse_url($rel, PHP_URL_SCHEME) != '') # return if already absolute URL 
	{
		return $rel;
	}
	
	if ($rel=='#' || $rel=='?') # queries and anchors 
	{
		return $base.$rel;
	}
	
	extract(parse_url($base)); # parse base URL and convert to local variables: $scheme, $host, $path
	$path = (isset($path)) ? $path : '/';
	$path = preg_replace('#/[^/]*$#', '', $path); # remove non-directory element from path
	
	if ($rel == '/') 
	{
		$path = '';
	}
	$abs = $host . $path . "/" . $rel;
	$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'); # replace '//' or '/./' or '/foo/../' with '/'
	for($n = 1; $n > 0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

	return $scheme.'://'.$abs; #absolute URL is ready!
}

/*======================================================================*\
	END OF Function rel2abs
\*======================================================================*/
?>
