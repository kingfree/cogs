<?php
/**
 * 页头显示题库、比赛、私信和账号入口。普通浏览器和旧 IE 共用菜单、权限与计数。
 * 例：Navigation(false); 输出完整导航；Navigation(true); 输出旧 IE 的简化版。
 */

/** 显示非零的比赛或信件数。分类由调用模板选定，实际数量来自对应主体。 */
function navigationCount(int $count, string $class): string
{
    return $count ? "<span class='$class'>($count)</span>" : '';
}

/** 当前目录选择高亮项；题目评论虽然位于 problem/，仍属于导航里的“评论”。 */
function Navigation(bool $simple = false): void
{
    global $SET;
    $section = basename(dirname($SET['cur']));                         // 一级目录对应题目、比赛、账号等入口。
    if (str_contains(basename($SET['cur']), 'comment')) $section = 'comment';
    $rounds = ContestRound::counts(time());                           // 两种导航都显示进行中和即将开始的场次。
    $contest = '比赛'.navigationCount($rounds['active'], 'doing').navigationCount($rounds['upcoming'], 'todo');
    $links = ['problem'=>['problem/index.php','题目'], 'submit'=>['submit/index.php','记录'],
        'contest'=>['contest/index.php',$contest], 'page'=>['page/index.php','页面'],
        'user'=>['user/index.php','用户'], 'comment'=>['problem/comments.php','评论']];
    $uid = (int)$_SESSION['ID'];                                      // 游客不查询私信，也没有个人菜单。
    $mails = '';
    if ($uid) {
        $unread = Message::unreadCounts($uid);                       // 收件未读与已发送未读分别显示。
        $mails = navigationCount($unread['received'], 'doing').navigationCount($unread['sent'], 'todo');
    }
    $nickname = $uid ? h($_SESSION['nickname']) : '登录';              // 昵称来自账号资料，只能作为普通文字输出。
    ?>
<div id="nav" class="navbar <?= $_SESSION['style'] ? 'navbar-inverse' : '' ?>"><div class="navbar-inner"><div class="container-fluid">
<a href="<?=路径('index.php')?>" class="brand">COGS</a>
<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a>
<div class="nav-collapse"><ul class="nav">
<?php if (!$simple): // 旧 IE 使用普通链接，省略依赖下拉菜单的题目分类与搜索框。 ?>
<li class="dropdown"><a id="fenleibar" href="#" class="dropdown-toggle" data-toggle="dropdown"><b class="caret"></b></a>
<ul class="dropdown-menu span11" id="catebar">
<?php foreach (Category::all() as $category): ?>
<li style="width: 100px"><a href="<?=路径('problem/index.php?caid='.(int)$category['caid'])?>" title="<?=h($category['memo'])?>"><?=h($category['cname'])?></a></li>
<?php endforeach; ?>
</ul></li>
<?php endif; foreach ($links as $key => [$url, $label]): ?>
<li class="<?=$key === $section ? 'active' : ''?>"><a href="<?=路径($url)?>"><?=$label?></a></li>
<?php endforeach; if (!$simple): ?>
<form class="navbar-search" method="get" action="<?=路径('problem/index.php')?>"><input name="key" type="text" id="key" class="search-query span2" placeholder="搜索题目" /></form>
<?php endif; ?>
</ul><ul class="nav pull-right">
<li class="<?=$section === 'docs' ? 'active' : ''?>"><a href="<?=路径('docs/index.php')?>">帮助</a></li>
<?php if (Privilege::allows('可以管理')): // 菜单和管理入口各自校验权限，隐藏链接不会代替访问检查。 ?>
<li class="<?=$section === 'admin' ? 'active' : ''?>"><a href="<?=路径('admin/index.php')?>">管理</a></li>
<?php endif; ?>
<li class="divider-vertical"></li>
<?php if ($simple && $uid): ?>
<li><a href="<?=路径('user/detail.php?uid='.$uid)?>"><?=$nickname.$mails?></a></li>
<li><a href="<?=路径('user/dologout.php')?>">退出</a></li>
<?php elseif ($simple): ?>
<li><a href="<?=路径('user/login.php')?>">登录</a></li><li><a href="<?=路径('user/register.php')?>">注册</a></li>
<?php else: ?>
<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?=$nickname.$mails?><b class="caret"></b></a><ul class="dropdown-menu">
<?php if ($uid): ?>
<li class="span2"><a id="userbar" href="<?=路径('user/detail.php?uid='.$uid)?>"><span class="username"><?=$nickname?></span><span class="avatar"><?=gravatar::showImage($_SESSION['email'], 28)?></span></a></li>
<li class="span2"><a href="<?=路径('user/panel.php')?>"><span class="icon-cog"></span>设置</a></li>
<li class="span2"><a href="<?=路径('mail/index.php')?>"><span class="icon-envelope"></span>信件<?=$mails?></a></li>
<li class="span2"><a href="<?=路径('user/dologout.php')?>"><span class="icon-off"></span>退出</a></li>
<?php else: ?>
<li class="span3"><form method="post" action="<?=路径('user/dologin.php')?>" class="form-inline center"><?=formTokenField()?>
<input name="from" type="hidden" id="from" value="<?=h($SET['URI'])?>" />
<input name="username" type="text" class="input-small span2" placeholder="用户名" /><br />
<input name="password" type="password" class="input-small span2" placeholder="密码" /><br />
<label class="checkbox"><input name="savepwd" type="checkbox" value="1" />保存 Cookies</label><br />
<button class="btn btn-primary">登录</button></form></li><li class="divider"></li>
<li class="span2"><a href="<?=路径('user/register.php')?>"><span class="icon-user"></span>注册</a></li>
<li class="span2"><a href="<?=路径('user/lost.php')?>"><span class="icon-eye-open"></span>忘记密码</a></li>
<?php endif; ?>
</ul></li>
<?php endif; ?>
</ul></div></div></div></div>
<?php
}
