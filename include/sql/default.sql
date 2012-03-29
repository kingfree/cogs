<?

//表：settings
$sql="delete from settings";
$p->dosql($sql);
$sql="insert into settings values('1','global_sitename','CmYkRgB123 Online Grading System')";
$p->dosql($sql);
$sql="insert into settings values('2','global_adminname','CmYkRgB123')";
$p->dosql($sql);
$sql="insert into settings values('3','global_adminaddress','http://www.cmykrgb123.com')";
$p->dosql($sql);
$sql="insert into settings values('4','global_constructiontime','1213849886')";
$p->dosql($sql);
$sql="insert into settings values('5','global_root','cojs/')";
$p->dosql($sql);
$sql="insert into settings values('6','style_profile','2.css')";
$p->dosql($sql);
$sql="insert into settings values('7','style_jumptime','1')";
$p->dosql($sql);
$sql="insert into settings values('8','style_pagesize','10')";
$p->dosql($sql);
$sql="insert into settings values('9','style_ranksize','20')";
$p->dosql($sql);
$sql="insert into settings values('10','style_defstring','chinese.php')";
$p->dosql($sql);
$sql="insert into settings values('11','reg_defgroup','1')";
$p->dosql($sql);
$sql="insert into settings values('12','reg_readfroce','1')";
$p->dosql($sql);
$sql="insert into settings values('13','limit_regallow','1')";
$p->dosql($sql);
$sql="insert into settings values('14','limit_siteopen','1')";
$p->dosql($sql);
$sql="insert into settings values('15','limit_checker','0')";
$p->dosql($sql);
$sql="insert into settings values('16','prob_deftimelimit','1000')";
$p->dosql($sql);
$sql="insert into settings values('17','prob_defdifficulty','1')";
$p->dosql($sql);
$sql="insert into settings values('18','global_index','<h1 align=\"center\"><strong>河南省实验中学</strong></h1>
<h1 align=\"center\"><strong>信息学(计算机)奥林匹克竞赛在线评测系统</strong></h1>
<p style=\"font-size: 16px;\">全国青少年信息学奥林匹克竞赛(National Olympiad in Informatics, 简称NOI)是一项面向全国青少年的信息学竞赛和普及活动，旨在向那些在中学阶段学习的青少年普及计算机科学知识；给学校的信息技术教育课程提供动力和新的思路；给那些有才华的学生提供相互交流和学习的机会；通过竞赛和相关的活动培养和选拔优秀的计算机人才。<br />
竞赛的目的是为了在更高层次上推动普及。本竞赛及其相关活动遵循开放性原则，任何有条件和有兴趣的学校和个人，都可以在业余时间自愿参加。本活动不和现行的学校教学相冲突，也不列入教学计划，是课外性质的因材施教活动。参加者可为初中或高中的学生。</p>
<p style=\"font-size: 16px;\">该系统为在线提交评测系统</p>
<p>开发者:<a href=\"http://www.cmykrgb123.com/\" target=\"_blank\">CmYkRgB123</a> 指导老师:常庆卫</p>
<p>链接 <a href=\"http://192.168.1.252/os/\" target=\"_blank\">河南省实验中学信息学(计算机)奥林匹克竞赛在线学习系统 </a></p>')";
$p->dosql($sql);
$sql="insert into settings values('23','global_head','<div align=\"center\" class=\"Title\">%global_sitename%</div>
<hr class=\"Spliter\" />')";
$p->dosql($sql);
$sql="insert into settings values('19','style_portrait','91')";
$p->dosql($sql);
$sql="insert into settings values('20','dir_databackup','/home/cojs/dbbackup')";
$p->dosql($sql);
$sql="insert into settings values('21','dir_source','/home/cojs/source')";
$p->dosql($sql);
$sql="insert into settings values('22','dir_competition','/home/cojs/competition')";
$p->dosql($sql);
$sql="insert into settings values('24','global_tail','<hr class=\"Spliter\" />
<p><span class=\"Tail\">
<li>请谨慎你的行为，如果你提交破坏评测系统的恶意代码，你的行为会被系统记录。我们将保留证据并采取措施来制裁非法的破坏者。</li>
</span><span align=\"center\" class=\"Tail\">Using style %style_profile%. Processd in %processtime% s, %querytimes% database queries. Copyright ◎<a target=\"_blank\" href=\"http://www.cmykrgb123.com\">CmYkRgB123</a> ,All rights reserverd.</span></p>')";
$p->dosql($sql);
$sql="insert into settings values('25','global_about','<p>%global_sitename%</p>
<p>管理员 <a href=\"%global_adminaddress%\">%global_adminname%</a></p>
<p>建站时间 %constructiontime%</p>')";
$p->dosql($sql);
$sql="insert into settings values('26','global_help','<table width=\"100%\" border=\"1\">
      <tr>
        <td><li>评测结果说明</li>
          <p>对于每个测试点，仅使用一个英文大写字母来表示该测试点的评测结果。</p>
          <table border=\"0\">
            <tr>
              <td>A</td>
              <td>正确</td>
            </tr>
            <tr>
              <td>W</td>
              <td>错误</td>
            </tr>
            <tr>
              <td>T</td>
              <td>超过时间限制</td>
            </tr>
            <tr>
              <td>E</td>
              <td>运行时出错</td>
            </tr>
            <tr>
              <td>R</td>
              <td>没有输出文件</td>
            </tr>
            <tr>
              <td>C</td>
              <td>编译失败</td>
            </tr>
            <tr>
              <td>N</td>
              <td>没有找到源文件</td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td><li>为什么程序在我的电脑上能够正常运行，而在评测系统上不能?</li>
		<ol>
          <li>评测系统建立在Ubuntu Linux 7.10下，编译器采用gcc,g++,freepascal.评测系统在比较你的输出时默认采用忽略一切无效字符(空格,回车等)
          的策略。</li>
		  <li>评测系统对你的程序内存的使用进行限制，默认为256MB，同时也对你的程序堆栈的使用进行限制。如果你的程序使用递归多大100,000层(甚至更多)，那么你的程序很可能运行时出错。</li>
		  <li>对于C和C++语言，主函数一定要定义为int main()而不是void main()。如果你的程序运行正常结束，应向系统返回一个整型值0，而不是其他的东西。</li>
		  <li>评测系统和你的电脑使用的内存安排方式可能不同。某些在你的电脑上没有经过初始化，理应为0的变量在评测系统上有可能并不如你所想的那样。</li>
		  <li>Linux对内存的访问控制更为严格，因此在Windows上可能正常运行的无效指针或数组下标访问越界，在评测系统上无法运行。</li>
		  <li>内存泄露的问题很可能会引起系统的保护模块杀死你的进程。因此，凡是使用malloc(或calloc,realloc,new)分配而得的内存空间，请使用free(或delete)完全释放。</li>
		  <li>在极少数情况下，你的程序运行错误(或编译失败)是因为你使用的某些变量与编译系统的变量名或函数名重复(例如:mmap,qsort)。对于这种问题，你只好尝试替换某些可能与系统变量名重复的变量名。</li>
		  <li>注意浮点运算，二进制浮点数运算的时候很有可能会造成意想不到的差异。例如a=0.00001+0.000001;</li>
		  <li>还有一种方法，可以让你在评测系统上直接调试程序。你可以使用assert宏(C/C++)，或者把想要观察的变量输出到stderr(C)或cerr(C++)。(Pascal似乎不行)</li>
		  <li>如果你会两种以上的语言，不妨将你的代码“翻译”成另一种语言然后提交，或许在翻译的时候你会发现你的程序的错误。如果翻译以后能够正常通过，那么请仔细检查你原来的程序。</li>
		  <li>如果以上都无法解决问题，请与管理员联系。或者联系作者：<a href=\"http://www.cmykrgb123.com\" target=\"_blank\">CmYkRgB123</a>。</li>
		  </ol>
		  </td>
      </tr>
      <tr>
        <td><li>系统规则</li>
		  <ol>
			<li>已经结束并且不公布成绩的比赛自动隐藏。</li>
			<li>删除用户时，将删除该用户的所有提交记录、提交文件、评论、比赛记录。该用户创建的题目及比赛的所有权会转移到根管理员用户。</li>
			<li>删除比赛时，将删除所有关联该比赛的场次。</li>
			<li>删除比赛场次时，将删除所有关联该场次的提交记录和提交文件。</li>
		</ol>
		</td>
      </tr>
    </table>')";
$p->dosql($sql);

?>