<form method="post" action="doedit.php?uid=<?php echo $_SESSION['ID'] ?>" class='form-inline'>
<div id='editpwd' class='modal hide fade in'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<span class='label label-info'>修改密码</span>
</div>
<div class='modal-body alert'>
<input name="action" type="hidden" value="editpwd" />
<table class='table-form'>
<tr>
<td width='100px'>原密码</td>
<td><input name="opwd" type="password" /></td>
</tr>
<tr>
<td>新密码</td>
<td><input name="npwd1" type="password" /></td>
</tr>
<tr>
<td>重复输入新密码</td>
<td><input name="npwd2" type="password" /></td>
</tr>
</table>
</div>
<div class='modal-footer'>
<button data-dismiss='modal' class='btn'>取消</button>
<button type="submit" class='btn btn-primary'>修改密码</button>
</div>
</div>
</form>

