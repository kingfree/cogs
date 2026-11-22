"""Exercise a disposable signed worker using real compilers and problem data."""
import base64
import hashlib
import hmac
import io
import os
from pathlib import Path
import secrets
import time
import urllib.parse
import urllib.request
import zipfile

url = os.environ["COGS_TEST_GRADER_URL"]
data = Path(os.environ["COGS_TEST_ROOT"]) / "data" / "sum"
data.mkdir(exist_ok=True)
(data / "sum1.in").write_text("12 30\n")
(data / "sum1.ans").write_text("42\n")
key = os.environ["COGS_GRADER_KEY"].encode()


def send(action, **fields):
    parts = []
    for name, value in {"action": action, **fields}.items():
        parts.extend([base64.b64encode(name.encode()), base64.b64encode(value if isinstance(value, bytes) else str(value).encode())])
    packet = base64.b64encode(b"?".join(parts))
    timestamp = str(int(time.time()))
    sig = hmac.new(key, timestamp.encode() + b"\n" + packet, hashlib.sha256).hexdigest()
    form = urllib.parse.urlencode({"time": timestamp, "signature": sig, "query": packet}).encode()
    with urllib.request.urlopen(url, form, timeout=45) as response:
        body = response.read()
    assert body.startswith(b"<return>"), body[:100]
    parts = [base64.b64decode(p).decode() for p in base64.b64decode(body[8:]).split(b"?")]
    return dict(zip(parts[::2], parts[1::2]))


def assess(extension, source, checker=1, expected=1.0, limit=2000, verdict=None):
    language = ["pas", "c", "cpp", "zip"].index(extension)
    job = secrets.token_hex(16)
    params = dict(job=job, uid=7, pname="sum", language=language)
    assert send("lock", job=job)["state"] == "successful"
    assert send("lock", job=secrets.token_hex(16))["state"] == "busy"
    try:
        build = send("compile", **params, src="sum." + extension, code=source)
        if verdict == "compile":
            assert build["compilesucc"] == "0", build
            return
        assert build["compilesucc"] == "1", build
        result = send("grade", **params, grade=1, memorylimit=128, timelimit=limit, plugin=checker)
        assert float(result["score"]) == expected, result
        if verdict:
            assert result[verdict] == "1", result
    finally:
        assert send("unlock", job=job, uid=7)["state"] == "successful"


assert send("state")["state"] == "free"
assert send("shutdown")["state"] == "successful"
assert send("state")["state"] == "closed"
assert send("lock", job=secrets.token_hex(16))["state"] == "busy"
assert send("start")["state"] == "successful"
c = '#include <stdio.h>\nint main(){int a,b;FILE*i=fopen("sum.in","r");fscanf(i,"%d%d",&a,&b);FILE*o=fopen("sum.out","w");fprintf(o,"%d\\n",a+b);return 0;}'
assess("c", c)
assess("cpp", '#include <fstream>\nint main(){int a,b;std::ifstream("sum.in")>>a>>b;std::ofstream("sum.out")<<a+b<<"\\n";}')
assess("pas", "program sum; var a,b:longint;begin assign(input,'sum.in');reset(input);assign(output,'sum.out');rewrite(output);readln(a,b);writeln(a+b);close(output);end.")
answers = io.BytesIO()
with zipfile.ZipFile(answers, "w") as archive:
    archive.writestr("sum1.out", "42\n")
assess("zip", answers.getvalue(), checker=4)
assess("c", c.replace("a+b", "a-b"), expected=0)
assess("c", c.replace("%d\\n", "%d "), checker=2, expected=0)
assess("c", "int main(){return 1;}", expected=0, verdict="runerr")
assess("c", "int main(){return 0;}", expected=0, verdict="noreport")
assess("c", "int main(){for(;;){} }", limit=100, expected=0, verdict="timeout")
assess("c", "int main(", verdict="compile")
(data / "spj.c").write_text("int main(){return 75;}")
assess("c", c, checker=0, expected=.75)
(data / "spj.c").unlink()
(data / "cena.cpp").write_text('#include <fstream>\nint main(){std::ofstream("score.log")<<50;}')
assess("zip", answers.getvalue(), checker=3, expected=.5)
(data / "cena.cpp").unlink()
(data / "plugin.php").write_text('<?php function plugin_compare($input,$output,$answer){return trim(fgets($output))===trim(fgets($answer))?1:0;}')
assess("c", c, checker=0)
(data / "plugin.php").unlink()
(data / "interactor.c").write_text('#include <stdio.h>\nint main(){int n;puts("21");fflush(stdout);return scanf("%d",&n)==1&&n==42?100:0;}')
assess("c", '#include <stdio.h>\nint main(){int n;if(scanf("%d",&n)!=1)return 1;printf("%d\\n",2*n);return 0;}', checker=-1)
(data / "interactor.c").unlink()
assert send("state")["state"] == "free"
print("评测机通过 C、C++、Pascal、答案 ZIP、三种特判、异常结果和租约检查")
