<?php
/**
 * Xss 过滤
 * 
 * $xss = new \Qii\Library\Xss();
 * $xss->xss_clean('需要过滤的内容');
 */
namespace Qii\Library;
class Xss
{
	protected $_xss_hash = '';

	//过滤的脚本
	protected $_never_allowed_str = array(
		'document.cookie' => '[Qii]',
		'document.write' => '[Qii]',
		'.parentNode' => '[Qii]',
		'.innerHTML' => '[Qii]',
		'window.location' => '[Qii]',
		'-moz-binding' => '[Qii]',
		'<!--' => '&lt;!--',
		'-->' => '--&gt;',
		'<![CDATA[' => '&lt;![CDATA[',
		'<comment>' => '&lt;comment&gt;',
	);

	//过滤的标签
	protected $_never_allowed_regex = array(
		'javascript\s*:',
		'expression\s*(\(|&\#40;)', // CSS and IE
		'vbscript\s*:', // IE, surprise!
		'Redirect\s+302',
		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
	);

	/**
	 * 过滤内容
	 * @param string $str string
	 * @param book $isImage 是否是图片
	 * @return string
	 */
	public function xss_clean($str, $isImage = false)
	{
		if (is_array($str)) {
			while (list($key) = each($str)) {
				$str[$key] = mb_convert_encoding($str[$key], 'gbk');
				$str[$key] = self::xss_clean(htmlspecialchars(strip_tags($str[$key])));

			}
			return $str;
		}


		// ɾ��һЩ���ɼ����ַ���

		$str = $this->remove_invisible_characters($str);

		//��֤ʵ���е�URL
		$str = $this->_validate_entities($str);

		//URL Decode
		$str = rawurldecode($str);

		//ת���ַ�ʵ��ΪASCII
		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $str);

		$str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, '_decode_entity'), $str);

		//�ٴ�ɾ��һЩ���ɼ����ַ���
		$str = $this->remove_invisible_characters($str);

		if (strpos($str, "\t") !== false) {
			$str = str_replace("\t", ' ', $str);
		}

		$converted_string = $str;

		//ɾ�����������ַ���
		$str = $this->_do_never_allowed($str);

		//ת��PHP��ʼ�������Ϊʵ��HTML
		$str = preg_replace('/<\?(php)/i', "&lt;?\\1", $str);
		$str = str_replace(array('<?', '?' . '>'), array('&lt;?', '?&gt;'), $str);

		//��������j a v a s c r i p t������ʽ���ַ����ı������������
		$words = array(
			'javascript', 'expression', 'vbscript', 'script', 'base64',
			'applet', 'alert', 'document', 'write', 'cookie', 'window'
		);
		foreach ($words as $word) {
			$temp = '';
			for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++) {
				$temp .= substr($word, $i, 1) . "\s*";
			}
			$str = preg_replace_callback('#(' . substr($temp, 0, -3) . ')(\W)#is', array($this, '_compact_exploded_words'), $str);
		}

		//����
		do {
			$original = $str;
			if (preg_match("/<a/i", $str)) {
				$str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", array($this, '_js_link_removal'), $str);
			}

			if (preg_match("/<img/i", $str)) {
				$str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, '_js_img_removal'), $str);
			}

			if (preg_match("/script/i", $str) or preg_match("/xss/i", $str)) {
				$str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
			}
		} while ($original != $str);
		unset($original);

		//����һЩ���ԵĶ���
		$str = $this->_remove_evil_attributes($str, $isImage);

		//����������HTML
		$naughty = 'object|alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$str = preg_replace_callback('#<(/*\s*)(' . $naughty . ')([^><]*)([><]*)#is', array($this, '_sanitize_naughty_html'), $str);

		//�ɽű��Ķ����ɵ�
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

		//����ٸ��
		$str = $this->_do_never_allowed($str);

		return $str;
	}


	/*
	 * ɾ��HTML�е�һЩ����
	 * @param string $str �ַ���
	 * @pram bool $isImage �Ƿ���ͼƬ
	 * @return string
	 */
	protected function _remove_evil_attributes($str, $isImage)
	{
		$evil_attributes = array('on\w*', 'xmlns', 'formaction');    //��style����ȥ����ԭ��������html�ύʱ�Ὣ���е���Ϣ���˵�
		if ($isImage === true) {
			unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
		}

		do {
			$count = 0;
			$attribs = array();
			preg_match_all('/(' . implode('|', $evil_attributes) . ')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is', $str, $matches, PREG_SET_ORDER);

			foreach ($matches as $attr) {
				$attribs[] = preg_quote($attr[0], '/');
			}
			preg_match_all('/(' . implode('|', $evil_attributes) . ')\s*=\s*([^\s>]*)/is', $str, $matches, PREG_SET_ORDER);

			foreach ($matches as $attr) {
				$attribs[] = preg_quote($attr[0], '/');
			}

			if (count($attribs) > 0) {
				$str = preg_replace('/(<?)(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(' . implode('|', $attribs) . ')(.*?)([\s><]?)([><]*)/i', '$1$2 $4$6$7$8', $str, -1, $count);
			}

		} while ($count);

		return $str;
	}

	/*
	 * ɾ��һЩ���ɼ����ַ���
	 * @param string $str �ַ���
	 * @param bool $urlEncoded �Ƿ񾭹�url����
	 * @return string
	 *
	 */
	private function remove_invisible_characters($str, $urlEncoded = false)
	{
		$non_displayables = array();
		if ($urlEncoded) {
			$non_displayables[] = '/%0[0-8bcef]/';    // url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';    // url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

		do {
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		} while ($count);

		return $str;
	}

	/*
	 * ��֤ʵ���е�URL
	 * @param string $str �ַ���
	 */
	protected function _validate_entities($str)
	{
		$str = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', $this->xss_hash() . "\\1=\\2", $str);
		$str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i', "\\1\\2;", $str);
		$str = str_replace($this->xss_hash(), '&', $str);
		return $str;
	}

	/*
	 * ����һ�����Hash����URL��ַ
	 *
	 */
	public function xss_hash()
	{
		if ($this->_xss_hash == '') {
			mt_srand();
			$this->_xss_hash = md5(time() + mt_rand(0, 1999999999));
		}

		return $this->_xss_hash;
	}

	/*
	 * ɾ���ַ�����������ַ�
	 *
	 * @param string $str ��Ҫ������ַ���
	 * @return string
	 */
	protected function _do_never_allowed($str)
	{
		$str = str_replace(array_keys($this->_never_allowed_str), $this->_never_allowed_str, $str);

		foreach ($this->_never_allowed_regex as $regex) {
			$str = preg_replace('#' . $regex . '#is', '[iReader]', $str);
		}

		return $str;
	}

	/*
	 * ������һЩ����֪��Ļص�����
	 * @param array $matches
	 */
	protected function _compact_exploded_words($matches)
	{
		return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
	}

	/**
	 * ת��<>����
	 * @param array $match
	 */
	protected function _convert_attribute($match)
	{
		return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
	}

	/**
	 * ת���ַ���
	 * @param array $match
	 */
	protected function _decode_entity($match)
	{
		return $this->entity_decode($match[0], 'UTF-8');
	}

	/**
	 * �Ƴ�js�ַ���
	 * @param array $match
	 * @return string
	 */
	protected function _js_link_removal($match)
	{
		return str_replace(
			$match[1],
			preg_replace(
				'#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
				'',
				$this->_filter_attributes(str_replace(array('<', '>'), '', $match[1]))
			),
			$match[0]
		);
	}

	/**
	 * �Ƴ��ַ�����ͼƬ��ַ
	 * @param array $match
	 * @return string
	 */
	protected function _js_img_removal($match)
	{
		return str_replace(
			$match[1],
			preg_replace(
				'#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
				'',
				$this->_filter_attributes(str_replace(array('<', '>'), '', $match[1]))
			),
			$match[0]
		);
	}

	/**
	 * ��ԭ<>�ַ���
	 * @param array $matches
	 * @return string
	 */
	protected function _sanitize_naughty_html($matches)
	{
		$str = '&lt;' . $matches[1] . $matches[2] . $matches[3];
		$str .= str_replace(array('>', '<'), array('&gt;', '&lt;'),
			$matches[4]);
		return $str;
	}

	/**
	 * ת���ַ�������
	 * @param string $str �ַ���
	 * @return string $charset �ַ����ı���
	 * @return string
	 */
	protected function entity_decode($str, $charset = 'UTF-8')
	{
		if (stristr($str, '&') === false) {
			return $str;
		}

		$str = html_entity_decode($str, ENT_COMPAT, $charset);
		$str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
	}

	/**
	 * �����ַ����е�html�����ַ�
	 * @param string $str �ַ���
	 * @return string
	 */
	protected function _filter_attributes($str)
	{
		$out = '';

		if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
			foreach ($matches[0] as $match) {
				$out .= preg_replace("#/\*.*?\*/#s", '', $match);
			}
		}

		return $out;
	}
}