from __future__ import annotations

import csv
import json
import re
import shutil
import ssl
import socket
import time
from collections import Counter, deque
from concurrent.futures import ThreadPoolExecutor, as_completed
from dataclasses import asdict, dataclass
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import quote, urldefrag, urljoin, urlparse
from urllib.request import Request, urlopen


START_URL = "https://alhikmakw.org.kw/donation/"
ORIGIN = "https://alhikmakw.org.kw"
SCOPE_PREFIX = "/donation"
MAX_PAGES = 120
WORKERS = 8
TIMEOUT = 18
OUT_DIR = Path("audit-assets/technical")
SCREENSHOT_SOURCE = Path(
    r"C:\Users\MSI GF63\AppData\Local\Temp\cursor\screenshots"
    r"\alhikma-donation-mobile-top.png"
)


def clean_url(value: str, base: str) -> str | None:
    value = value.strip()
    if not value or value.startswith(("javascript:", "mailto:", "tel:", "data:")):
        return None
    absolute = urldefrag(urljoin(base, value))[0]
    parsed = urlparse(absolute)
    if parsed.scheme not in {"http", "https"}:
        return None
    parsed = parsed._replace(path=quote(parsed.path, safe="/%:@"))
    # Query variants only change the media tab on project pages. Keeping them
    # would multiply identical HTML responses without finding additional pages.
    if parsed.netloc == urlparse(ORIGIN).netloc:
        absolute = parsed._replace(query="").geturl()
    else:
        absolute = parsed.geturl()
    return absolute.rstrip("/") or absolute


class PageParser(HTMLParser):
    def __init__(self, base_url: str) -> None:
        super().__init__(convert_charrefs=True)
        self.base_url = base_url
        self.links: list[dict[str, str]] = []
        self.images: list[dict[str, str | None]] = []
        self.scripts: list[str] = []
        self.styles: list[str] = []
        self.forms: list[dict[str, str]] = []
        self.inputs: list[dict[str, str]] = []
        self.meta: list[dict[str, str]] = []
        self.canonical: list[str] = []
        self.alternates: list[dict[str, str]] = []
        self.headings: list[dict[str, str]] = []
        self.title = ""
        self.lang = ""
        self.direction = ""
        self._capture: tuple[str, int] | None = None
        self._buffer: list[str] = []
        self._link_index: int | None = None

    @staticmethod
    def attrs_dict(attrs: list[tuple[str, str | None]]) -> dict[str, str]:
        return {key.lower(): value or "" for key, value in attrs}

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        tag = tag.lower()
        values = self.attrs_dict(attrs)
        if tag == "html":
            self.lang = values.get("lang", "")
            self.direction = values.get("dir", "")
        elif tag == "title":
            self._capture = ("title", 0)
            self._buffer = []
        elif tag in {"h1", "h2", "h3", "h4", "h5", "h6"}:
            self.headings.append({"level": tag, "text": ""})
            self._capture = ("heading", len(self.headings) - 1)
            self._buffer = []
        elif tag == "a":
            self.links.append(
                {
                    "href": values.get("href", ""),
                    "text": "",
                    "target": values.get("target", ""),
                    "rel": values.get("rel", ""),
                }
            )
            self._link_index = len(self.links) - 1
        elif tag == "img":
            self.images.append(
                {
                    "src": values.get("src"),
                    "alt": values.get("alt") if "alt" in values else None,
                    "loading": values.get("loading"),
                    "width": values.get("width"),
                    "height": values.get("height"),
                }
            )
        elif tag == "script" and values.get("src"):
            self.scripts.append(urljoin(self.base_url, values["src"]))
        elif tag == "link":
            rel = values.get("rel", "").lower()
            href = values.get("href", "")
            if "stylesheet" in rel and href:
                self.styles.append(urljoin(self.base_url, href))
            if "canonical" in rel and href:
                self.canonical.append(urljoin(self.base_url, href))
            if "alternate" in rel and href:
                self.alternates.append(
                    {
                        "href": urljoin(self.base_url, href),
                        "hreflang": values.get("hreflang", ""),
                    }
                )
        elif tag == "meta":
            self.meta.append(
                {
                    "name": values.get("name")
                    or values.get("property")
                    or values.get("http-equiv", ""),
                    "content": values.get("content", ""),
                }
            )
        elif tag == "form":
            self.forms.append(
                {
                    "action": urljoin(self.base_url, values.get("action", "")),
                    "method": values.get("method", "get").lower(),
                }
            )
        elif tag in {"input", "select", "textarea"}:
            self.inputs.append(
                {
                    "tag": tag,
                    "type": values.get("type", ""),
                    "name": values.get("name", ""),
                    "id": values.get("id", ""),
                    "aria-label": values.get("aria-label", ""),
                    "placeholder": values.get("placeholder", ""),
                    "required": "required" if "required" in values else "",
                    "autocomplete": values.get("autocomplete", ""),
                }
            )

    def handle_endtag(self, tag: str) -> None:
        tag = tag.lower()
        if tag == "a":
            self._link_index = None
        if self._capture and (
            (self._capture[0] == "title" and tag == "title")
            or (self._capture[0] == "heading" and tag.startswith("h"))
        ):
            text = re.sub(r"\s+", " ", " ".join(self._buffer)).strip()
            kind, index = self._capture
            if kind == "title":
                self.title = text
            else:
                self.headings[index]["text"] = text
            self._capture = None
            self._buffer = []

    def handle_data(self, data: str) -> None:
        if self._capture:
            self._buffer.append(data)
        if self._link_index is not None:
            self.links[self._link_index]["text"] += data


@dataclass
class PageResult:
    requested_url: str
    final_url: str
    status: int
    elapsed_ms: int
    content_type: str
    content_length: int
    title: str
    lang: str
    direction: str
    description: str
    canonical: list[str]
    og_tags: dict[str, str]
    headings: list[dict[str, str]]
    images_total: int
    images_missing_alt: int
    images_empty_alt: int
    images_lazy: int
    forms: list[dict[str, str]]
    inputs_without_accessible_name: int
    scripts: list[str]
    styles: list[str]
    internal_links: list[str]
    external_links: list[str]
    empty_links: int
    placeholder_hits: list[str]
    soft_404: bool
    error: str


def fetch_page(url: str) -> tuple[PageResult, list[str]]:
    started = time.perf_counter()
    request = Request(
        url,
        headers={
            "User-Agent": (
                "Mozilla/5.0 (compatible; AlHikmaSiteAudit/1.0; "
                "+read-only-quality-review)"
            ),
            "Accept": "text/html,application/xhtml+xml,application/pdf;q=0.8,*/*;q=0.5",
        },
    )
    try:
        with urlopen(request, timeout=TIMEOUT) as response:
            status = response.status
            final_url = response.geturl()
            content_type = response.headers.get("Content-Type", "")
            raw = response.read(4_000_000)
    except HTTPError as exc:
        status = exc.code
        final_url = exc.geturl()
        content_type = exc.headers.get("Content-Type", "") if exc.headers else ""
        raw = exc.read(500_000)
    except (URLError, TimeoutError, ssl.SSLError, OSError, ValueError) as exc:
        elapsed = round((time.perf_counter() - started) * 1000)
        result = PageResult(
            requested_url=url,
            final_url="",
            status=0,
            elapsed_ms=elapsed,
            content_type="",
            content_length=0,
            title="",
            lang="",
            direction="",
            description="",
            canonical=[],
            og_tags={},
            headings=[],
            images_total=0,
            images_missing_alt=0,
            images_empty_alt=0,
            images_lazy=0,
            forms=[],
            inputs_without_accessible_name=0,
            scripts=[],
            styles=[],
            internal_links=[],
            external_links=[],
            empty_links=0,
            placeholder_hits=[],
            soft_404=False,
            error=f"{type(exc).__name__}: {exc}",
        )
        return result, []

    elapsed = round((time.perf_counter() - started) * 1000)
    is_html = "html" in content_type.lower() or raw.lstrip().startswith(b"<!DOCTYPE")
    if not is_html:
        result = PageResult(
            requested_url=url,
            final_url=final_url,
            status=status,
            elapsed_ms=elapsed,
            content_type=content_type,
            content_length=len(raw),
            title="",
            lang="",
            direction="",
            description="",
            canonical=[],
            og_tags={},
            headings=[],
            images_total=0,
            images_missing_alt=0,
            images_empty_alt=0,
            images_lazy=0,
            forms=[],
            inputs_without_accessible_name=0,
            scripts=[],
            styles=[],
            internal_links=[],
            external_links=[],
            empty_links=0,
            placeholder_hits=[],
            soft_404=False,
            error="",
        )
        return result, []

    charset_match = re.search(r"charset=([\w-]+)", content_type, re.I)
    charset = charset_match.group(1) if charset_match else "utf-8"
    try:
        html = raw.decode(charset, errors="replace")
    except LookupError:
        html = raw.decode("utf-8", errors="replace")

    parser = PageParser(final_url)
    try:
        parser.feed(html)
    except Exception:
        pass

    meta_map = {
        item["name"].strip().lower(): item["content"].strip()
        for item in parser.meta
        if item["name"]
    }
    description = meta_map.get("description", "")
    og_tags = {key: value for key, value in meta_map.items() if key.startswith("og:")}
    internal: set[str] = set()
    external: set[str] = set()
    empty_links = 0
    for link in parser.links:
        normalized = clean_url(link["href"], final_url)
        text = re.sub(r"\s+", " ", link["text"]).strip()
        if not normalized:
            if not link["href"] or link["href"].strip() in {"#", "javascript:void(0);"}:
                empty_links += 1
            continue
        if urlparse(normalized).netloc == urlparse(ORIGIN).netloc:
            internal.add(normalized)
        else:
            external.add(normalized)
        if not text:
            empty_links += 1

    lower_html = html.lower()
    placeholders = []
    patterns = {
        "لوريم إيبسوم": "لوريم",
        "Question (AR)": "question (ar)",
        "Answer (AR)": "answer (ar)",
        "translation key": "translation.",
        "test/gibberish content": "sdf",
        "English placeholder Pay": ">pay<",
        "Project Name placeholder": "project name",
    }
    for label, token in patterns.items():
        if token in lower_html:
            placeholders.append(label)
    title_lower = parser.title.lower()
    soft_404 = (
        "does not exist" in title_lower
        or "page not found" in lower_html
        or "الصفحة غير موجودة" in lower_html
    )
    discover = [
        link
        for link in sorted(internal)
        if urlparse(link).path.startswith(SCOPE_PREFIX)
        and not re.search(
            r"\.(?:avif|gif|jpe?g|png|svg|webp|ico|mp4|webm|mp3|wav|pdf|docx?|xlsx?|zip)$",
            urlparse(link).path,
            re.I,
        )
    ]
    result = PageResult(
        requested_url=url,
        final_url=final_url,
        status=status,
        elapsed_ms=elapsed,
        content_type=content_type,
        content_length=len(raw),
        title=parser.title,
        lang=parser.lang,
        direction=parser.direction,
        description=description,
        canonical=parser.canonical,
        og_tags=og_tags,
        headings=parser.headings,
        images_total=len(parser.images),
        images_missing_alt=sum(image["alt"] is None for image in parser.images),
        images_empty_alt=sum(image["alt"] == "" for image in parser.images),
        images_lazy=sum(image["loading"] == "lazy" for image in parser.images),
        forms=parser.forms,
        inputs_without_accessible_name=sum(
            not field["aria-label"]
            and not field["placeholder"]
            and field["type"] != "hidden"
            for field in parser.inputs
        ),
        scripts=parser.scripts,
        styles=parser.styles,
        internal_links=sorted(internal),
        external_links=sorted(external),
        empty_links=empty_links,
        placeholder_hits=placeholders,
        soft_404=soft_404,
        error="",
    )
    return result, discover


def certificate_info(hostname: str) -> dict[str, object]:
    context = ssl.create_default_context()
    with socket.create_connection((hostname, 443), timeout=TIMEOUT) as sock:
        with context.wrap_socket(sock, server_hostname=hostname) as tls:
            cert = tls.getpeercert()
            return {
                "tls_version": tls.version(),
                "cipher": tls.cipher(),
                "subject": cert.get("subject"),
                "issuer": cert.get("issuer"),
                "not_before": cert.get("notBefore"),
                "not_after": cert.get("notAfter"),
                "subject_alt_names": cert.get("subjectAltName"),
            }


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    screenshot_target = Path("audit-assets/screenshots/alhikma-donation-mobile-top.png")
    if SCREENSHOT_SOURCE.exists():
        screenshot_target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(SCREENSHOT_SOURCE, screenshot_target)

    queued = deque([START_URL.rstrip("/")])
    seen: set[str] = set()
    pages: list[PageResult] = []
    link_sources: dict[str, list[str]] = {}

    with ThreadPoolExecutor(max_workers=WORKERS) as executor:
        while queued and len(seen) < MAX_PAGES:
            batch: list[str] = []
            while queued and len(batch) < WORKERS and len(seen) + len(batch) < MAX_PAGES:
                url = queued.popleft()
                if url not in seen:
                    seen.add(url)
                    batch.append(url)
            futures = {executor.submit(fetch_page, url): url for url in batch}
            for future in as_completed(futures):
                source = futures[future]
                result, discovered = future.result()
                pages.append(result)
                for target in discovered:
                    link_sources.setdefault(target, []).append(source)
                    if target not in seen:
                        queued.append(target)

    pages.sort(key=lambda page: page.requested_url)
    status_counts = Counter(page.status for page in pages)
    title_counts = Counter(page.title for page in pages if page.title)
    external_domains = Counter(
        urlparse(link).netloc
        for page in pages
        for link in page.external_links
        if urlparse(link).netloc
    )
    summary = {
        "generated_at_utc": datetime.now(timezone.utc).isoformat(),
        "start_url": START_URL,
        "pages_crawled": len(pages),
        "status_counts": dict(status_counts),
        "errors": sum(bool(page.error) for page in pages),
        "soft_404_pages": [
            page.requested_url for page in pages if page.soft_404
        ],
        "missing_descriptions": sum(
            page.status == 200 and "html" in page.content_type.lower() and not page.description
            for page in pages
        ),
        "missing_canonical": sum(
            page.status == 200 and "html" in page.content_type.lower() and not page.canonical
            for page in pages
        ),
        "missing_open_graph": sum(
            page.status == 200 and "html" in page.content_type.lower() and not page.og_tags
            for page in pages
        ),
        "duplicate_titles": {
            title: count for title, count in title_counts.items() if count > 1
        },
        "placeholder_pages": {
            page.requested_url: page.placeholder_hits
            for page in pages
            if page.placeholder_hits
        },
        "images_total": sum(page.images_total for page in pages),
        "images_missing_alt": sum(page.images_missing_alt for page in pages),
        "images_empty_alt": sum(page.images_empty_alt for page in pages),
        "images_lazy": sum(page.images_lazy for page in pages),
        "external_domains": dict(external_domains.most_common()),
        "slowest_pages": [
            {
                "url": page.requested_url,
                "elapsed_ms": page.elapsed_ms,
                "status": page.status,
            }
            for page in sorted(pages, key=lambda item: item.elapsed_ms, reverse=True)[:20]
        ],
        "certificate": certificate_info(urlparse(ORIGIN).hostname or ""),
        "mobile_screenshot_copied": screenshot_target.exists(),
    }

    payload = {
        "summary": summary,
        "pages": [asdict(page) for page in pages],
        "link_sources": link_sources,
    }
    (OUT_DIR / "crawl-results.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    with (OUT_DIR / "crawl-pages.csv").open("w", newline="", encoding="utf-8-sig") as handle:
        fieldnames = [
            "requested_url",
            "final_url",
            "status",
            "elapsed_ms",
            "content_type",
            "content_length",
            "title",
            "lang",
            "direction",
            "description",
            "h1_count",
            "images_total",
            "images_missing_alt",
            "images_empty_alt",
            "images_lazy",
            "empty_links",
            "placeholder_hits",
            "soft_404",
            "error",
        ]
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()
        for page in pages:
            writer.writerow(
                {
                    "requested_url": page.requested_url,
                    "final_url": page.final_url,
                    "status": page.status,
                    "elapsed_ms": page.elapsed_ms,
                    "content_type": page.content_type,
                    "content_length": page.content_length,
                    "title": page.title,
                    "lang": page.lang,
                    "direction": page.direction,
                    "description": page.description,
                    "h1_count": sum(h["level"] == "h1" for h in page.headings),
                    "images_total": page.images_total,
                    "images_missing_alt": page.images_missing_alt,
                    "images_empty_alt": page.images_empty_alt,
                    "images_lazy": page.images_lazy,
                    "empty_links": page.empty_links,
                    "placeholder_hits": " | ".join(page.placeholder_hits),
                    "soft_404": page.soft_404,
                    "error": page.error,
                }
            )
    print(json.dumps(summary, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
