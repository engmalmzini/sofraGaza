import json
import re
import socket
import ssl
import time
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import quote, urljoin, urlparse
from urllib.request import Request, urlopen
from urllib.error import HTTPError, URLError

OUT = Path(__file__).resolve().parent
TARGET = "https://alhikmakw.org.kw/donation/"
UA = "Mozilla/5.0 (compatible; ReadOnlyTechnicalAudit/1.0)"


def fetch(url, method="GET", timeout=25, read_body=True):
    safe_url = quote(url, safe=":/?&=%#[]@!$'()*+,;")
    req = Request(safe_url, headers={"User-Agent": UA}, method=method)
    started = time.perf_counter()
    try:
        with urlopen(req, timeout=timeout) as response:
            body = response.read() if read_body else b""
            return {
                "requested_url": url,
                "final_url": response.geturl(),
                "status": response.status,
                "reason": response.reason,
                "elapsed_ms": round((time.perf_counter() - started) * 1000, 1),
                "headers": dict(response.headers.items()),
                "set_cookie": response.headers.get_all("Set-Cookie") or [],
                "body_bytes": len(body),
                "_body": body,
            }
    except HTTPError as exc:
        body = exc.read() if read_body else b""
        return {
            "requested_url": url,
            "final_url": exc.geturl(),
            "status": exc.code,
            "reason": str(exc.reason),
            "elapsed_ms": round((time.perf_counter() - started) * 1000, 1),
            "headers": dict(exc.headers.items()),
            "set_cookie": exc.headers.get_all("Set-Cookie") or [],
            "body_bytes": len(body),
            "_body": body,
        }
    except (URLError, TimeoutError, OSError) as exc:
        return {
            "requested_url": url,
            "error": repr(exc),
            "elapsed_ms": round((time.perf_counter() - started) * 1000, 1),
            "_body": b"",
        }


class PageParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = []
        self.assets = []
        self.metas = []
        self.link_tags = []
        self.scripts = []
        self.forms = []
        self.headings = []
        self.images = []
        self.html_attrs = {}
        self.title = ""
        self._title = False
        self._heading = None
        self._form = None

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == "html":
            self.html_attrs = a
        elif tag == "title":
            self._title = True
        elif tag == "meta":
            self.metas.append(a)
        elif tag == "link":
            self.link_tags.append(a)
            if a.get("href"):
                self.assets.append(a["href"])
        elif tag == "script":
            self.scripts.append(a)
            if a.get("src"):
                self.assets.append(a["src"])
        elif tag == "a" and a.get("href"):
            self.links.append({"href": a["href"], "text": ""})
        elif tag == "img":
            self.images.append(a)
            if a.get("src"):
                self.assets.append(a["src"])
        elif tag in {"source", "video", "audio", "iframe"} and a.get("src"):
            self.assets.append(a["src"])
        elif tag == "form":
            self._form = {"attributes": a, "inputs": []}
            self.forms.append(self._form)
        elif tag in {"input", "select", "textarea", "button"} and self._form is not None:
            self._form["inputs"].append({"tag": tag, **a})
        elif tag in {"h1", "h2", "h3", "h4", "h5", "h6"}:
            self._heading = {"tag": tag, "text": ""}
            self.headings.append(self._heading)

    def handle_endtag(self, tag):
        if tag == "title":
            self._title = False
        elif tag == "form":
            self._form = None
        elif self._heading and tag == self._heading["tag"]:
            self._heading = None

    def handle_data(self, data):
        if self._title:
            self.title += data
        if self._heading is not None:
            self._heading["text"] += data
        if self.links:
            self.links[-1]["text"] += data


def public(record):
    return {k: v for k, v in record.items() if k != "_body"}


results = {
    "audit_started_utc": datetime.now(timezone.utc).isoformat(),
    "target": TARGET,
    "requests": {},
}

test_urls = {
    "donation": TARGET,
    "root": "https://alhikmakw.org.kw/",
    "http_donation": "http://alhikmakw.org.kw/donation/",
    "robots": "https://alhikmakw.org.kw/robots.txt",
    "sitemap": "https://alhikmakw.org.kw/sitemap.xml",
    "404": f"https://alhikmakw.org.kw/__audit_nonexistent_{int(time.time())}__",
}
raw = {}
for name, url in test_urls.items():
    raw[name] = fetch(url)
    results["requests"][name] = public(raw[name])
    suffix = {"donation": "page.html", "robots": "robots.txt", "sitemap": "sitemap.xml"}.get(name)
    if suffix and raw[name].get("_body"):
        (OUT / suffix).write_bytes(raw[name]["_body"])

html = raw["donation"].get("_body", b"").decode("utf-8", "replace")
parser = PageParser()
parser.feed(html)

json_ld = re.findall(
    r'<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>',
    html,
    flags=re.I | re.S,
)
seo = {
    "html_attributes": parser.html_attrs,
    "title": parser.title.strip(),
    "metas": parser.metas,
    "link_tags": parser.link_tags,
    "headings": [{**h, "text": " ".join(h["text"].split())} for h in parser.headings],
    "images_total": len(parser.images),
    "images_missing_alt": sum(1 for i in parser.images if not i.get("alt")),
    "images_empty_alt": sum(1 for i in parser.images if i.get("alt") == ""),
    "json_ld_raw": json_ld,
}
(OUT / "seo.json").write_text(json.dumps(seo, ensure_ascii=False, indent=2), encoding="utf-8")
(OUT / "forms.json").write_text(
    json.dumps(parser.forms, ensure_ascii=False, indent=2), encoding="utf-8"
)

base_host = urlparse(TARGET).hostname
link_urls = []
for item in parser.links:
    href = item["href"].strip()
    if not href or href.startswith(("#", "javascript:", "mailto:", "tel:", "data:")):
        continue
    absolute = urljoin(TARGET, href)
    if absolute not in link_urls:
        link_urls.append(absolute)

link_checks = []
for url in link_urls[:150]:
    rec = fetch(url, method="HEAD", read_body=False)
    if rec.get("status") in {400, 403, 405, 501}:
        rec = fetch(url, method="GET", read_body=False)
    link_checks.append({
        **public(rec),
        "scope": "internal" if urlparse(url).hostname == base_host else "external",
    })
(OUT / "links-check.json").write_text(
    json.dumps(link_checks, ensure_ascii=False, indent=2), encoding="utf-8"
)

asset_urls = []
for value in parser.assets:
    if not value or value.startswith(("data:", "blob:")):
        continue
    absolute = urljoin(TARGET, value)
    if absolute not in asset_urls:
        asset_urls.append(absolute)

assets = []
for url in asset_urls[:200]:
    rec = fetch(url, method="HEAD", read_body=False)
    headers = rec.get("headers", {})
    assets.append({
        "url": url,
        "status": rec.get("status"),
        "error": rec.get("error"),
        "content_type": headers.get("Content-Type"),
        "content_length": headers.get("Content-Length"),
        "cache_control": headers.get("Cache-Control"),
        "content_encoding": headers.get("Content-Encoding"),
        "elapsed_ms": rec.get("elapsed_ms"),
    })
(OUT / "assets.json").write_text(
    json.dumps(assets, ensure_ascii=False, indent=2), encoding="utf-8"
)

host = base_host
tls = {"host": host, "port": 443}
try:
    context = ssl.create_default_context()
    with socket.create_connection((host, 443), timeout=15) as sock:
        with context.wrap_socket(sock, server_hostname=host) as ssock:
            tls.update({
                "protocol": ssock.version(),
                "cipher": ssock.cipher(),
                "peer_certificate": ssock.getpeercert(),
            })
except Exception as exc:
    tls["error"] = repr(exc)
(OUT / "tls.json").write_text(json.dumps(tls, ensure_ascii=False, indent=2), encoding="utf-8")

results["counts"] = {
    "links_discovered": len(link_urls),
    "links_checked": len(link_checks),
    "assets_discovered": len(asset_urls),
    "assets_checked": len(assets),
    "forms": len(parser.forms),
}
results["audit_finished_utc"] = datetime.now(timezone.utc).isoformat()
(OUT / "summary.json").write_text(
    json.dumps(results, ensure_ascii=False, indent=2), encoding="utf-8"
)
print(json.dumps(results, ensure_ascii=False, indent=2))
