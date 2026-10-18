from pathlib import Path
import re
import urllib.request

root = Path(__file__).resolve().parents[1]
names = (root / "hostinger-dist" / "icon-names.txt").read_text(encoding="utf-8").strip().replace(" ", ",")
# Drop HTML false positives that are not icons.
skip = {"div", "p", "summary", "rating"}
icons = [n for n in names.split(",") if n and n not in skip]
query = (
    "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    ":opsz,wght,FILL,GRAD@24,400,0..1,0&icon_names=" + ",".join(icons)
)

req = urllib.request.Request(
    query,
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"},
)
with urllib.request.urlopen(req, timeout=60) as res:
    css = res.read().decode("utf-8")

match = re.search(r"url\((https://fonts.gstatic.com/[^)]+)\)", css)
if not match:
    raise SystemExit("No font URL in Google CSS:\n" + css[:500])

font_url = match.group(1)
req = urllib.request.Request(font_url, headers={"User-Agent": "Mozilla/5.0"})
with urllib.request.urlopen(req, timeout=60) as res:
    data = res.read()

out = root / "public" / "fonts" / "material-symbols-outlined.woff2"
out.write_bytes(data)
print(f"downloaded {len(data)/1024:.1f} KB from Google icon subset ({len(icons)} icons)")
print(font_url[:120])
