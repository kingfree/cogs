<?php
/**
 * 邮件、排名和个人页展示头像时优先使用站内缓存。未配置远程头像服务时显示站点图标。
 * 例：echo gravatar::showImage('student@example.org', 32);
 */
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
        if (is_file($portrait.'/'.$filenama) && filesize($portrait.'/'.$filenama) > 0) return h($path.$filenama);
        if (empty($SET['gravatar_server'])) return h(路径('style/cogs.png'));
        return h($SET['gravatar_server'].$filename);
    }
}
