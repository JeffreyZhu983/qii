<?php
 namespace Qii\Library;
/**
 * Class ip
 *
 * @package Library
 */
class IP
{
	var $StartIP = 0;
	var $EndIP = 0;
	var $Country = '';
	var $Local = '';

	var $CountryFlag = 0; // 标识 Country位置
	// 0x01,随后3字节为Country偏移,没有Local
	// 0x02,随后3字节为Country偏移,接着是Local
	// 其他,Country,Local,Local有类似的压缩。可能多重引用。

	var $fp;

	var $FirstStartIp = 0;
	var $LastStartIp = 0;
	var $EndIpOff = 0;

	/**
	 * 获取访问者的ip地址
	 *
	 * @return mixed
	 */
	public function getIP()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	public function getStartIp($RecNo)
	{
		$offset = $this->FirstStartIp + $RecNo * 7;
		@fseek($this->fp, $offset, SEEK_SET);
		$buf = fread($this->fp, 7);
		$this->EndIpOff = ord($buf[4]) + (ord($buf[5]) * 256) + (ord($buf[6]) * 256 * 256);
		$this->StartIp = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
		return $this->StartIp;
	}

	public function getEndIp()
	{
		@fseek($this->fp, $this->EndIpOff, SEEK_SET);
		$buf = fread($this->fp, 5);
		$this->EndIp = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
		$this->CountryFlag = ord($buf[4]);
		return $this->EndIp;
	}

	public function getCountry()
	{
		switch ($this->CountryFlag) {
			case 1:
			case 2:
				$this->Country = $this->getFlagStr($this->EndIpOff + 4);
				$this->Local = (1 == $this->CountryFlag) ? '' : $this->getFlagStr($this->EndIpOff + 8);
				break;
			default:
				$this->Country = $this->getFlagStr($this->EndIpOff + 4);
				$this->Local = $this->getFlagStr(ftell($this->fp));
		}
	}

	public function getFlagStr($offset)
	{
		$flag = 0;

		while (1) {
			@fseek($this->fp, $offset, SEEK_SET);
			$flag = ord(fgetc($this->fp));

			if ($flag == 1 || $flag == 2) {
				$buf = fread($this->fp, 3);

				if ($flag == 2) {
					$this->CountryFlag = 2;
					$this->EndIpOff = $offset - 4;
				}

				$offset = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256);
			} else
				break;
		}

		if ($offset < 12) return '';

		@fseek($this->fp, $offset, SEEK_SET);

		return $this->getStr();
	}

	public function getStr()
	{
		$str = '';

		while (1) {
			$c = fgetc($this->fp);

			if (ord($c[0]) == 0) break;

			$str .= $c;
		}

		return $str;
	}

	public function QQwry($dotip = '')
	{
		if (!$dotip) return;

		if (preg_match("/^(127)/", $dotip)) {
			$this->Country = '本地网络';
			return $this;
		} else if (preg_match("/^(192)/", $dotip)) {
			$this->Country = '局域网';
			return $this;
		}
		$ip = $this->IpToInt($dotip);
		$this->fp = fopen(__QQWRY__, "rb");

		if ($this->fp == NULL) {
			$szLocal = "OpenFileError";
			return 1;
		}

		@fseek($this->fp, 0, SEEK_SET);
		$buf = fread($this->fp, 8);
		$this->FirstStartIp = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
		$this->LastStartIp = ord($buf[4]) + (ord($buf[5]) * 256) + (ord($buf[6]) * 256 * 256) + (ord($buf[7]) * 256 * 256 * 256);

		$RecordCount = floor(($this->LastStartIp - $this->FirstStartIp) / 7);

		if ($RecordCount <= 1) {
			$this->Country = "FileDataError";
			fclose($this->fp);
			return 2;
		}

		$RangB = 0;
		$RangE = $RecordCount;

		// Match ...
		while ($RangB < $RangE - 1) {
			$RecNo = floor(($RangB + $RangE) / 2);
			$this->getStartIp($RecNo);

			if ($ip == $this->StartIp) {
				$RangB = $RecNo;
				break;
			}

			if ($ip > $this->StartIp) $RangB = $RecNo;
			else $RangE = $RecNo;
		}

		$this->getStartIp($RangB);
		$this->getEndIp();

		if (($this->StartIp <= $ip) && ($this->EndIp >= $ip)) {
			$this->getCountry();
		} else {
			$this->Country = '未知';
			$this->Local = '';
		}

		fclose($this->fp);
              return $this;
	}

	public function IpToInt($Ip)
	{
		$array = explode('.', $Ip, 4);
		$Int = ($array[0] * 256 * 256 * 256) + ($array[1] * 256 * 256) + ($array[2] * 256) + $array[3];

		return $Int;
	}

	/**
	 * 将ip转换成整形
	 *
	 * @param string $ip
	 * @return string
	 */
	public function ip2Long($ip = null)
	{
		if (!$ip) $ip = $this->getIP();
		return sprintf("%u", ip2long($ip));
	}

	/**
	 * 将长整形的数转换成ip地址
	 *
	 * @param $longIP
	 * @return string
	 */
	public function long2Ip($longIP)
	{
		if(!$longIP || intval($longIP) == 0) return 'Unknow';
		$ip1 = ($longIP >> 24) & 0xff; // 跟0xff做与运算的目的是取低8位
		$ip2 = ($longIP >> 16) & 0xff;
		$ip3 = ($longIP >> 8) & 0xff;
		$ip4 = $longIP & 0xff;
		return $ip1 . '.' . $ip2 . '.' . $ip3 . '.' . $ip4;
	}
}