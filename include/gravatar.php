<?php
/**
 * 邮件、排名和个人页调用 gravatar::showImage('student@example.org', 32) 显示头像。
 * 查找顺序为站内缓存、配置的头像服务、站点图标。
 */
class gravatar
{
    /** 返回带尺寸的 img 标签，URL 已按 HTML 属性转义。 */
    public static function showImage($email, $size = 14, $default = 'mm')
    {
        $size = (int)$size;                                          // 同一尺寸同时用于缓存文件名和显示尺寸。
        $url = self::getGravatarURL($email, $size, $default);
        return "<img src=\"$url\" alt=\"Gravatar\" width=\"$size\" height=\"$size\" />";
    }

    /** 优先使用该尺寸的站内头像；返回可放入 HTML 的 URL。 */
    public static function getGravatarURL($email, $size = 14, $default = 'mm')
    {
        global $SET;
        $digest = md5($email);                                       // 已有缓存和远端服务都使用邮件地址的 MD5。
        $relative = 'images/gravatar/'.$digest.'s'.(int)$size;        // 保留旧缓存名，迁出代码目录也能命中。
        $file = publicFilePath($relative);                           // 磁盘位置可配置，公开 URL 保持站点相对路径。
        if (is_file($file) && filesize($file) > 0) return h(路径($relative));
        if (empty($SET['gravatar_server'])) return h(路径('style/cogs.png')); // 无外部服务时用站标。
        return h($SET['gravatar_server'].$digest.'?'.http_build_query(['s'=>(int)$size, 'd'=>$default]));
    }
}
