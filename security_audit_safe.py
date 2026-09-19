from __future__ import annotations

import json
import socket
import ssl
import time
import warnings
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urlparse
from urllib.request import Request, urlopen


ROOT = Path(__file__).resolve().parent
BASE = "https://alhikmakw.org.kw"
TARGET = f"{BASE}/donation/"
OUTPUT = ROOT / "audit-assets" / "technical" / "security-safe-scan.json"
PAGE_HTML = ROOT / "audit-assets" / "technical" / "page.html"
USER_AGENT = "KAT-Security-Audit/1.0 (non-destructive; contact: info@katkw.com)"
TIMEOUT = 20
DELAY = 0.6

SECURITY_HEADERS = (
    "Strict-Transport-Security",
    "Content-Security-Policy",
    "X-Frame-Options",
    "X-Content-Type-Options",
    "Referrer-Policy",
    "Permissions-Policy",
    "Cross-Origin-Opener-Policy",
    "Cross-Origin-Resource-Policy",
    "Access-Control-Allow-Origin",
    "Access-Control-Allow-Credentials",
    "Allow",
    "Server",
    "X-Powered-By",
    "platform",
    "panel",
)

# HEAD only: no sensitive response bodies are downloaded or stored.
LIMITED_PATH_CHECKS = (
    "/.env",
    "/.git/HEAD",
    "/composer.json",
    "/composer.lock",
    "/phpinfo.php",
    "/server-status",
    "/storage/logs/laravel.log",
    "/vendor/",
    "/telescope",
    "/_ignition/health-check",
    "/backup.zip",
    "/database.sql",
    "/donation/uploads/",
    "/.well-known/security.txt",
)


class ResourceParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.resources: list[dict[str, object]] = []
        self.forms: list[dict[str, object]] = []
        self._form: dict[str, object] | None = None

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        data = dict(attrs)
        if tag == "script" and data.get("src"):
            self.resources.append(
                {
                    "type": "script",
                    "url": data["src"],
                    "integrity": data.get("integrity"),
                    "crossorigin": data.get("crossorigin"),
                }
            )
        elif tag == "link" and data.get("href") and "stylesheet" in (data.get("rel") or ""):
            self.resources.append(
                {
                    "type": "stylesheet",
                    "url": data["href"],
                    "integrity": data.get("integrity"),
                    "crossorigin": data.get("crossorigin"),
                }
            )
        elif tag == "form":
            self._form = {
                "method": (data.get("method") or "GET").upper(),
                "action": data.get("action"),
                "csrf_token_present": False,
            }
            self.forms.append(self._form)
        elif tag == "input" and self._form is not None and data.get("name") == "_token":
            self._form["csrf_token_present"] = True

    def handle_endtag(self, tag: str) -> None:
        if tag == "form":
            self._form = None


def selected_headers(headers: object) -> dict[str, str]:
    return {
        name: value
        for name in SECURITY_HEADERS
        if (value := headers.get(name)) is not None  # type: ignore[attr-defined]
    }


def request_result(
    url: str,
    method: str = "HEAD",
    extra_headers: dict[str, str] | None = None,
) -> dict[str, object]:
    headers = {"User-Agent": USER_AGENT, **(extra_headers or {})}
    request = Request(url, method=method, headers=headers)
    try:
        with urlopen(request, timeout=TIMEOUT) as response:
            return {
                "url": url,
                "final_url": response.geturl(),
                "method": method,
                "status": response.status,
                "headers": selected_headers(response.headers),
                "content_type": response.headers.get("Content-Type"),
                "content_length": response.headers.get("Content-Length"),
            }
    except HTTPError as error:
        return {
            "url": url,
            "final_url": error.geturl(),
            "method": method,
            "status": error.code,
            "headers": selected_headers(error.headers),
            "content_type": error.headers.get("Content-Type"),
            "content_length": error.headers.get("Content-Length"),
        }
    except (URLError, TimeoutError, OSError) as error:
        return {"url": url, "method": method, "error": str(error)}


def tls_protocols(host: str) -> list[dict[str, object]]:
    protocols = (
        ("TLS 1.0", ssl.TLSVersion.TLSv1),
        ("TLS 1.1", ssl.TLSVersion.TLSv1_1),
        ("TLS 1.2", ssl.TLSVersion.TLSv1_2),
        ("TLS 1.3", ssl.TLSVersion.TLSv1_3),
    )
    results: list[dict[str, object]] = []
    warnings.filterwarnings("ignore", category=DeprecationWarning)
    for label, version in protocols:
        context = ssl.SSLContext(ssl.PROTOCOL_TLS_CLIENT)
        context.check_hostname = False
        context.verify_mode = ssl.CERT_NONE
        context.minimum_version = version
        context.maximum_version = version
        try:
            context.set_ciphers("ALL:@SECLEVEL=0")
        except ssl.SSLError:
            pass
        try:
            with socket.create_connection((host, 443), timeout=TIMEOUT) as raw:
                with context.wrap_socket(raw, server_hostname=host) as wrapped:
                    results.append(
                        {
                            "protocol": label,
                            "accepted": True,
                            "negotiated": wrapped.version(),
                            "cipher": wrapped.cipher(),
                        }
                    )
        except (OSError, ssl.SSLError) as error:
            results.append({"protocol": label, "accepted": False, "error": str(error)})
    return results


def offline_html_analysis() -> dict[str, object]:
    parser = ResourceParser()
    parser.feed(PAGE_HTML.read_text(encoding="utf-8", errors="replace"))
    external = []
    for resource in parser.resources:
        hostname = urlparse(str(resource["url"])).hostname
        if hostname and hostname != "alhikmakw.org.kw":
            external.append(resource)
    without_sri = [resource for resource in external if not resource["integrity"]]
    return {
        "external_resources": external,
        "external_resource_count": len(external),
        "external_resources_without_sri": without_sri,
        "external_without_sri_count": len(without_sri),
        "forms": parser.forms,
    }


def main() -> None:
    homepage = request_result(TARGET, "HEAD")
    time.sleep(DELAY)
    cors_preflight = request_result(
        TARGET,
        "OPTIONS",
        {
            "Origin": "https://security-audit.invalid",
            "Access-Control-Request-Method": "POST",
            "Access-Control-Request-Headers": "content-type,x-xsrf-token",
        },
    )
    time.sleep(DELAY)

    paths = []
    for path in LIMITED_PATH_CHECKS:
        paths.append(request_result(f"{BASE}{path}", "HEAD"))
        time.sleep(DELAY)

    result = {
        "scope": "Non-destructive external verification; no exploits, login attempts, form submissions, or sensitive bodies.",
        "target": TARGET,
        "homepage": homepage,
        "cors_preflight": cors_preflight,
        "limited_head_checks": paths,
        "tls_protocols": tls_protocols("alhikmakw.org.kw"),
        "offline_html_analysis": offline_html_analysis(),
    }
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"WROTE {OUTPUT}")


if __name__ == "__main__":
    main()
