from pathlib import Path
import subprocess
import sys

root = Path(__file__).resolve().parents[1]
src = root / "public" / "fonts" / "material-symbols-outlined.woff2"
names = root / "hostinger-dist" / "icon-names.txt"
out = root / "public" / "fonts" / "material-symbols-outlined.woff2"

if not src.exists():
    sys.exit("font missing: " + str(src))
if not names.exists():
    sys.exit("icon names missing")

text = names.read_text(encoding="utf-8").strip().replace(",", " ")
tmp = root / "hostinger-dist" / "material-symbols-outlined.subset.woff2"

cmd = [
    sys.executable,
    "-m",
    "fontTools.subset",
    str(src),
    f"--text={text}",
    "--layout-features=liga,kern",
    "--flavor=woff2",
    f"--output-file={tmp}",
]
print(" ".join(cmd[:6]), "...")
subprocess.check_call(cmd)

before = src.stat().st_size
after = tmp.stat().st_size
if after < 1024:
    sys.exit("subset too small")
tmp.replace(out)
print(f"font {before/1024:.0f}KB -> {after/1024:.0f}KB")
