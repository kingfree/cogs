"""HTTP regression suite for a disposable installation created by integration.py."""
from pathlib import Path
import http.cookiejar
import io
import gzip
import base64
import os
import re
import secrets
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
import zipfile

base = os.environ['COGS_TEST_WEB_URL']
root = Path(os.environ['COGS_TEST_ROOT'])
requests_made = 0


class Visitor:
    def __init__(self):
        self.cookies = http.cookiejar.CookieJar()
        self.client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies))
        self.token = ''

    def visit(self, path, fields=None, files=None, *, status=200, contains=None, csrf=True):
        global requests_made
        headers = {'User-Agent': 'COGS integration'}
        body = None
        if fields is not None or files:
            fields = dict(fields or {})
            if csrf:
                fields['form_token'] = self.token
            if files:
                tag = secrets.token_hex(16)
                parts = []
                for key, value in fields.items():
                    parts.append(f'--{tag}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
                for key, (name, data) in files.items():
                    parts.append(f'--{tag}\r\nContent-Disposition: form-data; name="{key}"; filename="{name}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode() + data + b'\r\n')
                body = b''.join(parts) + f'--{tag}--\r\n'.encode()
                headers['Content-Type'] = 'multipart/form-data; boundary=' + tag
            else:
                body = urllib.parse.urlencode(fields).encode()
        req = urllib.request.Request(base + path, body, headers)
        try:
            response = self.client.open(req, timeout=35)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            payload = response.read()
            assert response.status == status, (path, response.status, status, payload[-500:])
        token = re.search(rb'(?:name="form_token" value|name="cogs-token" content)="([0-9a-f]{64})"', payload)
        if token:
            self.token = token[1].decode()
        if contains is not None:
            assert contains.encode() in payload, (path, contains, payload[-550:])
        requests_made += 1
        return payload

    def login(self, username):
        self.visit('/user/login.php', contains='password')
        self.visit('/user/dologin.php', {'username': username, 'password': os.environ['COGS_TEST_LOGIN_PASSWORD'], 'savepwd': 1}, contains='登录成功')
        self.visit('/user/panel.php', contains='editpwd')


guest, admin, user = Visitor(), Visitor(), Visitor()
for path in ['/', '/problem/index.php', '/contest/index.php', '/submit/index.php', '/user/index.php',
             '/user/login.php', '/page/index.php', '/docs/map.php', '/user/grouplist.php', '/problem/comments.php']:
    guest.visit(path, contains='</html>')
for path in ['/user/privilege.php', '/mail/index.php', '/problem/testdata.php', '/contest/export.php?ctid=1']:
    guest.visit(path, status=403)
guest.visit('/user/dologin.php', {'username': 'root', 'password': os.environ['COGS_TEST_LOGIN_PASSWORD']}, csrf=False, status=403)
admin.login('root')
user.login('student')
user.visit('/user/privilege.php', {'way':'ins','pri':42,'uid':2}, status=403)
admin.visit('/user/privilege.php', {'way':'ins','pri':42,'uid':2}, contains='已添加权限')
user.visit('/problem/editcate.php?action=add', contains='</html>')
admin.visit('/user/privilege.php', {'way':'del','pri':42,'uid':2}, contains='已删除权限')
user.visit('/problem/editcate.php?action=add', status=403)
admin.visit('/user/doeditgroup.php?action=add', {'gname':'练习队','memo':'组','adminuid':1,'parent':1}, contains='添加分组')
admin.visit('/user/grouplist.php', contains='练习队')
admin.visit('/page/doeditpage.php?action=add', {'title':"O'Reilly 公告",'text':'<p>正文</p>','force':0,'group':1}, contains='页面已保存')
user.visit('/page/page.php?aid=1', contains='正文')
user.visit('/problem/sendcomments.php', {'aid':1,'detail':'新评论','showcode':0}, contains='发表评论成功')
user.visit('/page/page.php?aid=1', contains='新评论')
user.visit('/mail/send.php', {'toid':1,'title':"O'Reilly",'msg':'私人回信'}, contains='发送邮件成功')
admin.visit('/mail/inbox.php?mid=1', contains='私人回信')
guest.visit('/mail/inbox.php?mid=1', status=403)
data = io.BytesIO()
with zipfile.ZipFile(data, 'w') as archive:
    archive.writestr('sum1.in', '12 30\n')
    archive.writestr('sum1.ans', '42\n')
problem = {'probname':"求和 O'Reilly",'filename':'sum','datacnt':1,'timelimit':2000,'memorylimit':128,
           'readforce':0,'submitable':1,'difficulty':2,'plugin':1,'group':1,'detail':'<p>计算和</p>'}
admin.visit('/problem/doeditprob.php?action=add', problem, {'datafile':('tests.zip',data.getvalue())}, contains='题目已保存')
user.visit('/problem/problem.php?pid=1', contains='计算和')
user.visit('/problem/doeditprob.php?action=edit&pid=1', problem, status=403)
user.visit('/problem/addcate.php', {'pid':1,'cname':'算术','memo':'基础'}, contains='成功')
user.visit('/problem/index.php?caid=1', contains='算术')
admin.visit('/problem/testdata.php?problem=sum', contains='sum1.ans')
assert admin.visit('/problem/testdata.php?problem=sum&download=sum1.in') == b'12 30\n'
user.visit('/submit/testdata.php', {'filename':'sum','point':1,'io':'ans'}, status=403)
admin.visit('/problem/testdata.php?problem=sum', {'action':'save','file':'note.txt','contents':'notes'}, contains='note.txt')
admin.visit('/problem/testdata.php?problem=sum', {'action':'move','file':'note.txt','target_problem':'sum','target':'moved.txt'}, contains='moved.txt')
admin.visit('/problem/testdata.php?problem=sum', {'action':'delete','file':'moved.txt'}, contains='sum1.in')
fps = admin.visit('/problem/problem_export_xml.php', {'in':'1','submit':'Export'})
doc = ET.fromstring(fps)
assert doc.findtext('item/test_output') == '42\n'
doc.find('item/title').text = '导入求和'
admin.visit('/problem/problem_import_xml.php', {}, {'fps':('set.xml', ET.tostring(doc,encoding='utf-8'))}, contains='已导入 1')
admin.visit('/problem/checkprobname.php?name='+urllib.parse.quote('导入求和'), contains='1')
src = b'#include <stdio.h>\nint main(){int a,b;FILE*i=fopen("sum.in","r");fscanf(i,"%d%d",&a,&b);FILE*o=fopen("sum.out","w");fprintf(o,"%d\\n",a+b);return 0;}'
user.visit('/submit/run.php', {'pid':1,'lang':'c','judger':1}, {'file':('sum.c',src)}, contains='通过了全部测试点')
user.visit('/submit/code.php?id=1', contains='fprintf')
user.visit('/user/detail.php?uid=2', contains='student')
with zipfile.ZipFile(io.BytesIO(user.visit('/user/export.php?uid=2'))) as archive:
    assert src in [archive.read(f) for f in archive.namelist()]
user.visit('/submit/run.php', {'pid':1,'lang':'c','rejudge':1,'sid':1}, status=403)
admin.visit('/contest/doeditcompbase.php?action=add', {'cname':'练习赛','cons[]':1}, contains='添加比赛')
from datetime import datetime, timedelta
from zoneinfo import ZoneInfo
now = datetime.now(ZoneInfo('Asia/Shanghai'))
def round_fields(start,end):
    fields = {'cbid':1,'intro':'比赛简介','showscore':1,'group':1}
    for key,d in [('st',start),('et',end)]:
        fields.update({key+'_'+k:v for k,v in zip('ymdhis',(d.year,d.month,d.day,d.hour,d.minute,d.second))})
    return fields
admin.visit('/contest/doeditcomptime.php?action=add', round_fields(now-timedelta(hours=1),now+timedelta(hours=1)), contains='添加比赛场次')
user.visit('/contest/problem.php?ctid=1&pid=1', contains='计算和')
user.visit('/contest/submit.php', {'ctid':1,'pid':1,'lang':'c'}, {'file':('sum.c',src)}, contains='提交代码成功')
admin.visit('/contest/compile.php', {'csid':1,'judger':1}, contains='100!A')
admin.visit('/contest/report.php?ctid=1', contains='100')
admin.visit('/contest/comptime.php?ctid=1', contains='求和')
admin.visit('/contest/export.php?ctid=1', contains='sum.c')
admin.visit('/contest/doeditcomptime.php?action=edit&ctid=1', round_fields(now-timedelta(days=2),now-timedelta(days=1)), contains='修改比赛场次')
user.visit('/contest/submit.php', {'ctid':1,'pid':1,'lang':'c','endtime':'9999999999'}, {'file':('sum.c',src)}, status=403)
admin.visit('/admin/gen_acrank.php', {}, contains='计算完成')
admin.visit('/admin/dobackup.php', {'backtype':'usercode','filename':'daily'}, contains='备份已保存')
admin.visit('/admin/dobackup.php', {'backtype':'showback'}, contains='.tar.gz')
for path in ['/contest/compbase.php','/admin/index.php','/admin/settings.php','/submit/graderlist.php',
             '/problem/editprob.php?action=edit&pid=1','/user/loginlog.php?uid=1','/user/edituser.php?uid=2',
             '/contest/editcompbase.php?action=edit&cbid=1','/contest/editcomptime.php?action=edit&ctid=1',
             '/page/editpage.php?action=edit&aid=1','/problem/comments.php?aid=1','/user/grouplist.php']:
    admin.visit(path,contains='</html>')
# Restricted resources must have the same audience in detail pages, search, maps and discussions.
admin.visit('/page/doeditpage.php?action=add', {'title':'组内集训讲义','text':'组内文章正文','force':0,'group':2}, contains='页面已保存')
admin.visit('/problem/sendcomments.php', {'aid':2,'detail':'组内讲义留言'}, contains='发表评论成功')
admin.visit('/page/page.php?aid=2', contains='组内讲义留言')
user.visit('/page/page.php?aid=2', status=404)
user.visit('/problem/sendcomments.php', {'aid':2,'detail':'未读文章'}, status=404)
user.visit('/problem/sendcomments.php', {'cid':2,'detail':'其他作者'}, status=403)
for route in ['/','/page/index.php','/docs/map.php','/problem/comments.php?show=yes','/problem/comments.php?aid=2']:
    body = user.visit(route)
    assert '组内集训讲义'.encode() not in body and '组内讲义留言'.encode() not in body, route
private = dict(problem, filename='private-case', probname='组内练习题', group=2)
admin.visit('/problem/doeditprob.php?action=add', private, contains='题目已保存')
user.visit('/problem/problem.php?pid=3', status=404)
user.visit('/submit/run.php', {'pid':3,'lang':'c','judger':1}, {'file':('sum.c',src)}, status=404)
for route in ['/problem/index.php','/docs/map.php','/']:
    assert '组内练习题'.encode() not in user.visit(route), route
# A synthetic image and Pascal checker must survive export and re-import with their original mode.
sample_png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WD1+sAAAAAASUVORK5CYII='
item = doc.find('item')
item.find('title').text = '带特判题包'
item.find('description').text = '<p><img src="fps-pixel.png"></p>'
item.find('cogs_plugin').text = '0'
ET.SubElement(item,'spj',{'language':'Pascal'}).text = 'begin halt(100) end.'
picture = ET.SubElement(item,'img')
ET.SubElement(picture,'src').text = 'fps-pixel.png'
ET.SubElement(picture,'base64').text = sample_png
admin.visit('/problem/problem_import_xml.php', {}, {'fps':('checker.xml',ET.tostring(doc,encoding='utf-8'))}, contains='已导入 1')
restored = ET.fromstring(admin.visit('/problem/problem_export_xml.php', {'in':'4','submit':'Export'})).find('item')
assert restored.findtext('cogs_plugin') == '0' and restored.find('spj').attrib['filename'] == 'spj.pas'
assert base64.b64decode(restored.findtext('img/base64')) == base64.b64decode(sample_png)
for backup_type in ('compcode','testdata','database'):
    admin.visit('/admin/dobackup.php', {'backtype':backup_type,'filename':'fixture'}, contains='备份已保存')
with gzip.open(next((root/'backup/database').glob('*.sql.gz')), 'rt') as dump:
    assert 'CREATE TABLE `userinfo`' in dump.read()
# Only the disposable test installation exposes its own local CAPTCHA fixture to this process.
new_user = Visitor()
new_user.visit('/user/register.php?accept=1', contains='VerifyCode')
assert new_user.visit('/include/verifycode.php').startswith(b'\x89PNG')
session_id = next(cookie.value for cookie in new_user.cookies if cookie.name=='PHPSESSID')
captcha = re.search(rb'IMGCODE\|s:4:"([a-z0-9]+)"', (root/'sessions'/('sess_'+session_id)).read_bytes())[1].decode()
credentials = {'usr':'new_student','nickname':'新同学','realname':'','email':'new@example.org','pwd':'fresh-cogs',
               'repwd':'fresh-cogs','passwordtip':'颜色','passwordtipans':'绿色','VerifyCode':captcha}
new_user.visit('/user/doreg.php', credentials, contains='注册成功')
new_user.visit('/user/panel.php', contains='editpwd')
new_user.visit('/user/doedit.php', {'action':'editpwd','opwd':'fresh-cogs','npwd1':'new-pass','npwd2':'new-pass'}, contains='密码已修改')
new_user.visit('/user/dologout.php', {}, contains='退出')
new_user.visit('/user/login.php', contains='password')
new_user.visit('/user/dologin.php', {'username':'new_student','password':'new-pass'}, contains='登录成功')
recover = Visitor()
recover.visit('/user/lost.php', contains='用户名')
recover.visit('/user/lost.php', {'User':'new_student'}, contains='颜色')
recover.visit('/user/lost.php', {'User':'new_student','ans':'绿色'}, contains='临时密码')
new_user.visit('/user/panel.php', status=403)
user.visit('/user/dologout.php', {}, contains='退出')
user.visit('/user/panel.php', status=403)
print(f'网页通过 {requests_made} 次 HTTP 请求')
