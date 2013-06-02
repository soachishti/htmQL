<?php
/*
 * htmSQL (HTML parser using SQL)
 * Created January 2, 2013
 * Version 2
 * Location: https://github.com/soachishti/htmSQL
 * 
 * Copyright (c) 2013, SOAChishti (soachishti@outlook.com).
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
 * DISCLAIMED. IN NO EVENT SHALL SOAChishti BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
    
 --------------------------------------------------------------------

 CHANGELOG:

 1 -> 2 (April 08, 2013):
	- Converted to Object Oriented
	- Support different  encodings.
	- Support  malformed HTML.
	- Added SimpleXML support
	- Added Error Reporting
*/

ini_set("display_errors", "on"); # Report all Errors
error_reporting(E_ALL ^ E_NOTICE); # Report all Errors
//error_reporting(0); # No Error Reporting

class htmql
{	
	public $rel2abs = 0; # Convert Relative Urls to Absolute Url 
	public $baseUrl;  # Base Url for converting Relative url to Absolute url
	public $urlAttribute = Array('href','src'); # Key of Attribute which contains URL 
	public $htmlEncode = true; # Key of Attribute which contains URL (For regex Version)
	public $removeHtml = true; # Remove html from tag text. (For regex Version)
	public $removeSpecialChars = true; # Remove special characters from html if query is "SELECT html2text FROM *"
	public $parseMethod = "SIMPLEXML"; # Convert Relative Urls to Absolute Url 
	public $NoMultiDimenArray = 1; # Return result in Multi dimensional Array or Single Array.
	
	public $error; # Return Error
	
	private $num_rows = 0;
	private $html = null;
	private $result = null;
	
/*======================================================================*\
	Function:	connect
	Purpose:	Parse SQL syntax and output result
	Input:		$type(host,string) and $string(Html or Web url)
	Output:		Tag Data
\*======================================================================*/	
	
	public function connect($type, $string)
	{
		if($type == "string")
		{
			$this->rel2abs = 0;
			$this->html = $string;
		}
		else if($type == "host")
		{
			$html = file_get_contents($string);
			$this->html = $html;
			$this->baseUrl = $url;
		}
		else
		{
			$this->error = "Invalid Type";
			return false;
		}
		unset($string,$type);
		return true;
	}
	
/*======================================================================*\
	END OF Function htmql_query
\*======================================================================*/

/*======================================================================*\
	Function:	htmql_query
	Purpose:	Parse SQL syntax and output result
	Input:		HTML content, SQL query
	Output:		Tag Data
\*======================================================================*/

	public function query($sql)
	{
		if(!isset($sql))
		{
			$this->error = "Invalid Query";
			return false;
		}
		preg_match("#^SELECT\s*(.*?)\s*FROM\s*(.*?)\s*(|WHERE\s*(.*?)\s*)$#is",$sql,$out); #

		$where = !empty($out[4]) ? $out[4] : null ;
		$select = !empty($out[1]) ? array_flip(explode(",",strtolower($out[1]))) : null;
		$requiredTags = !empty($out[2]) ? array_flip(explode(",",$out[2])) : null;
		unset($out);
		
		if($select == null)
		{
			$this->error = "Attributes doen't exit";
			return false;
		}
		else if($requiredTags  == null)
		{
			$this->error = "Tag deosn't exist";
			return false;
		}
		
		if(isset($select["html2text"]))
		{
			$this->result["html2text"] = html2txt($this->html,$this->removeSpecialChars);
		}
		else
		{	
			$this->html = mb_convert_encoding($this->html,'UTF-8',mb_detect_encoding($this->html, "auto"));
			$tidy = new tidy();
			$options  = Array(
							'output-xhtml' => 1,
							'doctype' => 'auto'
						);
			$this->html = $tidy->repairString($this->html,$options);		
			
			if($where != null)
			{
				preg_match_all("#(|OR|AND)\s*([a-z0-9\-]+)\s*(LIKE|\!=|=|<|>)+\s*[\"\'](.*?)[\"\']\s*(|OR|AND)*#is",$where,$out);
				$count = count($out[2])-1;
				$keys = $out[2];
				$condition = $out[1];
				$values = $out[4];
				$comparison = $out[3];
			}
			
			$this->result = Array();
					
			if($this->parseMethod == "REGEX")
			{
				preg_match_all("#<\s*([a-z0-9\-]+)\s*#is", $this->html, $out);
				$tags = array_flip(array_unique($out[1])); 
				$toSelect = isset($keys) ? array_merge(array_flip($keys),$select) : $select;
				foreach($tags as $key => $value)
				{
					$extractedTags = $this->tag_extract($key,$this->html,$toSelect);
					$this->result = array_merge($extractedTags,$this->result);
				}
			}
			else if($this->parseMethod == "SIMPLEXML")
			{
				$doc = new DOMDocument();
				@$doc->loadHTML($this->html);
				$html = simplexml_import_dom($doc); 
			
				$this->result = $this->toArray($html);
			}
			else
			{
				$this->error = "Invalid Method";
				return false;
			}
			
			if($where != null)
			{
				foreach($this->result as $k1 => $v1)
				{
					foreach($v1 as $k2 => $v2)
					{
						$datas = !empty($this->result[$k1][$k2]) ? $this->result[$k1][$k2] : Array() ;	
						
						if($this->found($datas,$keys,$values,$condition,$count,$comparison) != 1)
						{
							unset($this->result[$k1][$k2]);
						}
						else
						{
							foreach($datas as $k3 => $v3)
							{
								if(!isset($select[$k3]) && !isset($select["*"]))
								{	
									unset($this->result[$k1][$k2][$k3]);
								}
							}
						}
						if(empty($this->result[$k1]))
						{
							unset($this->result[$k1]);
						}
					}
				}
			}
			
			if(!isset($requiredTags["*"]))
			{
				foreach($this->result as $key => $value)
				{
					if(!isset($requiredTags[$key]))
					{
						unset($this->result[$key]);
					}
				}
			}
			$this->result = $this->sorting($this->result,$this->NoMultiDimenArray);
		}
		$this->result = (!empty($this->result)) ? $this->result : Array();
		return true;
	}

/*======================================================================*\
	END OF Function htmql_query
\*======================================================================*/

/*======================================================================*\
	Function:	tag_extract
	Purpose:	fetch the contents of tags such as text and its attributes 
	Input:		Tags, Fetched HTML (content) and Attributes to Select(select)
	Output:		Tags content such as text and its attributes if exits
\*======================================================================*/

	private function tag_extract($tag,$content,$select)
	{
		$tags = Array();
		preg_match_all("/<\s*{$tag}\b\s*([ \t].*?|)\s*>((.*?)<\s*\/\s*{$tag}\s*>)?/is", $content, $out);

		for($i=0;$i<=count($out[0])-1;$i++)
		{
			if(isset($select["text"]) || isset($select['*']) && !empty($out[3][$i]))
			{
				if(!empty($this->removeHtml))
				{
					$out[3][$i] = strip_tags($out[3][$i]);
				}
				if(!empty($this->htmlEncode))
				{
					$tags[$tag][$i]['text'] = htmlspecialchars(trim($out[3][$i]));
				}
				else
				{
					$tags[$tag][$i]['text'] = trim($out[3][$i]);
				}
			}
			else
			{
				$tags[$tag][$i] = null;
			}
			preg_match_all("#\s*(.*?)\s*=\s*[\"\']?((?:.(?![\"\']?\s+(?:\S+)=|[>\"\']))+.)[\"\']?#is", $out[1][$i], $out1);
			foreach($out1[1] as $key => $value)
			{
				$out1[1][$key] = trim($out1[1][$key]);
				if(isset($select[$out1[1][$key]]) || isset($select["*"]))
				{
					if($this->rel2abs == true && isset($this->baseUrl) && in_array($out1[1][$key], $this->urlAttribute)) 
					{
						$tags[$tag][$i][$out1[1][$key]] = $this->rel2abs($this->baseUrl,$out1[2][$key]);
					}
					else
					{
						$tags[$tag][$i][$out1[1][$key]] = $out1[2][$key];
					}
				}
			}
		}
		unset($content);	
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

	private function found($data,$key,$values,$condition,$count,$operator)
	{
		for($i = 0;$i <= $count;$i++)
		{
			if($this->compare($data[$key[$i]],$values[$i],$operator[$i]))
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

	private function compare($k1,$k2,$operator)
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

	private function html2txt($html,$special = false)
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
		unset($html);
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
/*======================================================================*/

	private function rel2abs($base,$rel)
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

	private function sorting($array,$NoMultiDimenArray = false) {
		if(is_array($array) == true){
			if($NoMultiDimenArray == true)
			{
				
				foreach($array as $key => $value)
				{
					foreach($value as $k => $v){
						$v['tagname'] = $key; 
						$a[] = $v;
					}
				}
				$this->num_rows = count($a);
			}
			else
			{
				$count = 0;
				$c = 0;
				foreach($array as $key => $value)
				{
					$i = 0;
					foreach($value as $v)
					{
						$a[$c][$key][$i] = $v;
						$i++;
					}
					$count = $count + $i;
					$c++;
				}
				$this->num_rows = $count;
			}
		}
		unset($array);
		return $a;
	}

/*======================================================================*\
	END OF Function sorting
\*======================================================================*/

/*======================================================================*\
	Function:	toArray
	Purpose:	Convert SimpleXML objects to array to make easy for further processing
	Input:		SimpleXml Object
	Output:		Array of object.
\*======================================================================*/
	private function toArray($html,$i = 0)
	{	
		if($i == 0)
		{
			foreach($html->attributes() as $k3 => $v3)
			{
				$this->result['html'][$i][$k3] = (string)$v3;
			}
		}
		foreach($html as $k1 => $v1)
		{
			$attributes = isset($v1['@attributes']) ? $v1['@attributes'] : $v1->attributes();
			if(!empty($attributes))
			{
				foreach($attributes as $k2 => $v2)
				{
					$k2 = (string)$k2;
					$v2 = (string)$v2;
					if($this->rel2abs == true && isset($this->baseUrl) && in_array($k2, $this->urlAttribute)) 
					{
						$this->result[$k1][$i][$k2] = $this->rel2abs($this->baseUrl,$v2);
					}
					else
					{
						$this->result[$k1][$i][$k2] = $v2;
					}
				}
			}
			if(trim($v1) != '')
			{
				$this->result[$k1][$i]['text'] = (string)$v1;
			}
			$i++;
			if($v1->count() != 0)
			{
				$this->toArray($v1,$i);
			}
		}
		unset($html);
		return $this->result;
	}
	
/*======================================================================*\
	END OF Function toArray
\*======================================================================*/

/*======================================================================*\
	Function:	fetch_array
	Purpose:	Return Result as Array
	Input:		result from $this variable
	Output:		Array
\*======================================================================*/
	
	public function fetch_array()
	{
		return $this->result;
	}
	
/*======================================================================*\
	END OF Function fetch_array
\*======================================================================*/
	
/*======================================================================*\
	Function:	fetch_object
	Purpose:	Return Result as Object
	Input:		result from $this variable
	Output:		Object
\*======================================================================*/

	public function fetch_object()
	{
		$obj = new stdClass();

		if($this->NoMultiDimenArray == true)
		{
			foreach ($this->result as $k1 => $v1) {
				foreach ($v1 as $k2 => $v2){        
					$obj->$k2 = (object)$v2;
				}
			}
			unset($k1,$v1);
		}
		else
		{
			foreach($this->result as $key => $value)
			{
				foreach($value as $k1 => $v1)
				{	
					foreach($v1 as $k2 => $v2)
					{
						$obj->$key->$k1->$k2 = (object)$v2;
					}
				}
			}
			unset($key,$value,$k1,$v1);
		}
		return $obj;
	}
	
/*======================================================================*\
	END OF Function fetch_object
\*======================================================================*/
	
/*======================================================================*\
	Function:	num_rows
	Purpose:	Return number of result
	Input:		num_rows from $this variable
	Output:		Interger(Number of Result)
\*======================================================================*/

	public function num_rows()
	{
		return $this->num_rows;
	}
	
/*======================================================================*\
	END OF Function num_rows
\*======================================================================*/

/*======================================================================*\
	Function:	close
	Purpose:	Delete existing variables
	Input:		null
	Output:		Return true
\*======================================================================*/

	public function close()
	{
		unset($this->result);
		unset($this->error);
		unset($this->num_rows);
		return true;
	}
	
/*======================================================================*\
	END OF Function close
\*======================================================================*/
}
?>