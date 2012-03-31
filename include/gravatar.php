<?php
class gravatar
{
	private static $size = 16;
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
	public static function showImage($email,$size=0,$default="")
	{
		$url = self::getGravatarURL($email,$size,$default);
		return "<img src=\"{$url}\" alt=\"Gravatar\" border=0 />";
	}
	/**
	 *
	 * @param string $email
	 * @param int $size
	 * @param string $default
	 * @return string URL
	 */
	public static function getGravatarURL($email,$size=0,$default="")
	{
		global $SETTINGS,$STR;
		$portrait=$SETTINGS['base']."images/gravatar";
		$path = pathconvert($SETTINGS['cur'],$portrait).'/';
		if ($size == 0)
			$size = self::$size;
		if ($default == "")
			$default = self::$default;
		$filename = md5($email)."?s={$size}&d={$default}";
		$filenama = md5($email)."s{$size}";
		if (file_exists($path.$filenama))
			$url = $path.$filenama;
		else {
			$web = "http://www.gravatar.com/avatar/".$filename;
			$url = $path.$filenama;
			file_put_contents($url, file_get_contents($web));
        }
		return $url;
	}
}
