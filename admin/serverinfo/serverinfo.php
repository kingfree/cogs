<p>服务器信息：</p>
<table width="100%" border="1">
  <tr>
    <td scope="row">服务器IP:端口</td>
    <td><?php echo "$_SERVER[SERVER_ADDR]:$_SERVER[SERVER_PORT]";?></td>
  </tr>
  <tr>
    <td scope="row">本地IP:端口</td>
    <td><?php echo "$_SERVER[REMOTE_ADDR]:$_SERVER[REMOTE_PORT]";?></td>
  </tr>
  <tr>
    <td scope="row">服务器软件</td>
    <td><?php echo $_SERVER[SERVER_SOFTWARE];?></td>
  </tr>
  <tr>
    <td scope="row">站点根目录</td>
    <td><?php echo $_SERVER[DOCUMENT_ROOT];?></td>
  </tr>
  <tr>
    <td width="13%" scope="row">工作目录</td>
    <td width="87%"><?php echo posix_getcwd();?></td>
  </tr>
  <tr>
    <td scope="row">控制终端</td>
    <td><?php echo posix_ctermid();?></td>
  </tr>
  <tr>
    <td scope="row">组</td>
    <td><?php echotablearray(posix_getgrgid(posix_getegid()));?></td>
  </tr>
  <tr>
    <td scope="row">用户</td>
    <td><?php echotablearray(posix_getpwuid(posix_getegid()));?></td>
  </tr>
  <tr>
    <td scope="row">资源信息</td>
    <td><?php echotablearray(posix_getrlimit());?></td>
  </tr>
  <tr>
    <td scope="row">系统信息</td>
    <td><?php echotablearray(posix_uname());?></td>
  </tr>
  <tr>
    <td scope="row">系统时间</td>
    <td><?php echotablearray(posix_times());?></td>
  </tr>
</table>