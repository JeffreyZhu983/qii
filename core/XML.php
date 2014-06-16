<?php
###################################################################################
# XMLParse class: utility class to be used with PHP's XMLParse handling functions
###################################################################################
if(!function_exists('count_numeric_items'))
{
	function count_numeric_items(&$array)
	{
		return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
	}
}
if(class_exists('XMLParse'))
{
	return;
}
class XMLParse
{
	public $version = '1.1.0';
	public $parser;   #a reference to the XMLParse parser
	public $document; #the entire XMLParse structure built up so far
	public $parent;   #a pointer to the current parent - the parent will be an array
	public $stack;    #a stack of the most recent parent at each nesting level
	public $last_opened_tag; #keeps track of the last tag opened.
	
	public function XMLParse()
	{
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}
	public function parse(&$data)
	{
		$this->document = array();
		$this->stack    = array();
		$this->parent   = &$this->document;
		return xml_parse($this->parser, $data, true) ? $this->document : NULL;
	}
	public function open(&$parser, $tag, $attributes)
	{
		$this->data = ''; #stores temporary cdata
		$this->last_opened_tag = $tag;
		if(is_array($this->parent) and array_key_exists($tag,$this->parent))
		{ #if you've seen this tag before
			if(is_array($this->parent[$tag]) and array_key_exists(0,$this->parent[$tag]))
			{ #if the keys are numeric
				#this is the third or later instance of $tag we've come across
				$key = count_numeric_items($this->parent[$tag]);
			}
			else
			{
				#this is the second instance of $tag that we've seen. shift around
				if(array_key_exists("$tag attr",$this->parent))
				{
					$arr = array('0 attr'=>&$this->parent["$tag attr"], &$this->parent[$tag]);
					unset($this->parent["$tag attr"]);
				}
				else
				{
					$arr = array(&$this->parent[$tag]);
				}
				$this->parent[$tag] = &$arr;
				$key = 1;
			}
			$this->parent = &$this->parent[$tag];
		}
		else
		{
			$key = $tag;
		}
		if($attributes) $this->parent["$key attr"] = $attributes;
		$this->parent  = &$this->parent[$key];
		$this->stack[] = &$this->parent;
	}
	public function data(&$parser, $data)
	{
		if($this->last_opened_tag != NULL) #you don't need to store whitespace in between tags
		$this->data .= $data;
	}
	public function close(&$parser, $tag)
	{
		if($this->last_opened_tag == $tag)
		{
			$this->parent = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) $this->parent = &$this->stack[count($this->stack)-1];
	}
	public function destruct(){ xml_parser_free($this->parser); }
}
/**
 * XML Parse Class
 *
 * Usage
 * $xmlClass = &new XML();
 * $xmlClass->setXml('configure/site.config.php');
 * $data = $xmlClass->XML2Array();
 * print_r($data);
 * echo ($xmlClass->array2XML($data));
 * 
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: XML.php,v 1.1 2010/04/23 06:02:12 Jinhui.Zhu Exp $
 */
class XML
{
	public $version = '1.1.0';
	private $_data;
	public function __construct()
	{
		
	}
	/**
	 * 
	 * 设置解析的数据
	 * @param String $data
	 */
	public function setData($data)
	{
		$this->_data = $data;
	}
	/**
	 * 设置XML文件
	 *
	 * @param String $fileName
	 */
	public function setXml($fileName)
	{
		if(substr($fileName, -4) == '.xml')
		{
			$this->_data = file_get_contents($fileName);
		}
		else 
		{
			$data = Qii::loadFile($fileName);
			$this->_data = $data[0];
		}
	}
	public function parseTags($xml, $tag = '')
	{
		preg_match_all ("/(<([\w]+)[^>]*>)(.*)(<\/\\2>)/", $xml, $htmlTags);
		$returnArray = array();
		if(is_array($htmlTags))
		{
			foreach($htmlTags[2] AS $i => $v)
			{
				if($tag != '')
				{
					if($v == $tag && (!$returnArray || !in_array($htmlTags[3][$i], $returnArray)))
					{
						$returnArray[] = $htmlTags[3][$i];
					}
				}
				else
				{
					if(!$returnArray || !in_array($htmlTags[3][$i], $returnArray))
					{
						$returnArray[$v][] = $htmlTags[3][$i];
					}
				}
			}
		}
		return  $returnArray;
	}
	/**
	 * Xml 转换成Array
	 *
	 * @return Array
	 */
	public function XML2Array()
	{
		$xml_parser = new XMLParse();
		$data = $xml_parser->parse($this->_data);
		$xml_parser->destruct();
		return $data;
	}
	/**
	 * Array 转换成XML文件
	 *
	 * @param Array $data
	 * @param Int $level
	 * @param String $prior_key
	 * @return XML
	 */
	public function array2XML(&$data, $level = 0, $prior_key = NULL)
	{
		if($level == 0){ ob_start(); echo '<?xml version="1.0" ?>',"\n"; }
		while(list($key, $value) = each($data))
		if(!strpos($key, ' attr')) #if it's not an attribute
		#we don't treat attributes by themselves, so for an empty element
		# that has attributes you still need to set the element to NULL
	
		if(is_array($value) and array_key_exists(0, $value)){
			$this->array2XML($value, $level, $key);
		}else{
			$tag = $prior_key ? $prior_key : $key;
			echo str_repeat("\t", $level),'<',$tag;
			if(array_key_exists("$key attr", $data)){ #if there's an attribute for this element
				while(list($attr_name, $attr_value) = each($data["$key attr"]))
				echo ' ',$attr_name,'="',htmlspecialchars($attr_value),'"';
				reset($data["$key attr"]);
			}
	
			if(is_null($value)) echo " />\n";
			elseif(!is_array($value)) echo '>',htmlspecialchars($value),"</$tag>\n";
			else echo ">\n",$this->array2XML($value, $level+1),str_repeat("\t", $level),"</$tag>\n";
		}
		reset($data);
		if($level == 0){ $str = &ob_get_contents(); ob_end_clean(); return $str; }
	}
}
?>