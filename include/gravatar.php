<?php
class gravatar
{
	private static $size = 14;
	private static $default = "mm";
	   //mm, identicon, monsterid, wavatar, retro
	/**
	 *
	 * @param int $size
	 */
	public static function setDefaultSize($size)
	{
		$size = (int)$size;
		if ($size > 0)
			self::$size = $size;
	}
	/**
	 *
	 * @param string $default
	 */
	public static function setDefaultImage($default)
	{
		self::$default = urlencode($default);
	}
	/**
	 *
	 * @param string $email
	 * @param int $size
	 * @param string $default
	 * @return string HTML image
	 */
	public static function showImage($email,$size=14,$default="mm")
	{
		$url = self::getGravatarURL($email,$size,$default);
		return "<img src=\"{$url}\" alt=\"Gravatar\" width=\"{$size}\" height=\"{$size}\" />";
	}
	/**
	 *
	 * @param string $email
	 * @param int $size
	 * @param string $default
	 * @return string URL
	 */
	public static function getGravatarURL($email,$size=14,$default="mm")
	{
		global $SET,$STR;
		$portrait=$SET['base']."images/gravatar";
		$path = pathconvert($SET['cur'],$portrait).'/';
		$filename = md5($email)."?s={$size}&d={$default}";
		$filenama = md5($email)."s{$size}";
		if (file_exists($path.$filenama) && filesize($path.$filenama) > 0)
			$url = $path.$filenama;
		else {
			$web = $SET['gravatar_server'].$filename;
			$url = $path.$filenama;
			file_put_contents($url, file_get_contents($web));
        }
		return $url;
	}
}
?>
