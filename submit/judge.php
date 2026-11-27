<?php
// 兼容旧表单地址，普通提交和重判执行同一条流程。
// 例：管理员从旧表单向 submit/judge.php 提交 pid、sid、rejudge=1 和当前表单令牌，以已有源码重判。
require __DIR__.'/run.php';
