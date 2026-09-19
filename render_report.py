from __future__ import annotations

import shutil
import subprocess
import tempfile
from pathlib import Path

import pymupdf


ROOT = Path(__file__).resolve().parent
HTML = ROOT / "reports" / "alhikma-website-technical-audit-2026.html"
BRAND_IDENTITY = Path(
    r"C:\Users\MSI GF63\Downloads\KAT VISUAL IDENTITY FINAL  (1).pdf"
)
CONTENT_PDF = ROOT / "reports" / "alhikma-website-technical-audit-content.pdf"
PDF = ROOT / "reports" / "alhikma-website-technical-audit-2026.pdf"
PREVIEW = ROOT / "reports" / "alhikma-report-preview.png"
CONTENT_COVER_PREVIEW = ROOT / "reports" / "alhikma-report-content-cover-preview.png"


def browser_path() -> Path:
    candidates = (
        Path(r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"),
        Path(r"C:\Program Files\Microsoft\Edge\Application\msedge.exe"),
        Path(r"C:\Program Files\Google\Chrome\Application\chrome.exe"),
    )
    for candidate in candidates:
        if candidate.exists():
            return candidate
    raise FileNotFoundError("Chrome or Edge was not found")


def render(extra_args: list[str], output: Path) -> None:
    output.unlink(missing_ok=True)
    profile = Path(tempfile.mkdtemp(prefix="alhikma-report-"))
    command = [
        str(browser_path()),
        "--headless",
        "--disable-gpu",
        "--disable-extensions",
        "--disable-background-networking",
        "--no-first-run",
        "--no-default-browser-check",
        f"--user-data-dir={profile}",
        "--allow-file-access-from-files",
        *extra_args,
        HTML.resolve().as_uri(),
    ]
    try:
        result = subprocess.run(
            command,
            cwd=ROOT,
            timeout=180,
            check=False,
            capture_output=True,
            text=True,
        )
        if result.returncode != 0 or not output.exists():
            details = (result.stderr or result.stdout).strip()
            raise RuntimeError(f"Rendering failed ({result.returncode}): {details}")
    finally:
        shutil.rmtree(profile, ignore_errors=True)


def merge_identity_cover() -> None:
    PDF.unlink(missing_ok=True)
    with (
        pymupdf.open(BRAND_IDENTITY) as identity,
        pymupdf.open(CONTENT_PDF) as content,
        pymupdf.open() as merged,
    ):
        merged.insert_pdf(identity, from_page=0, to_page=0)
        merged.insert_pdf(content)
        metadata = content.metadata
        metadata.update(
            {
                "title": "التدقيق التقني لموقع جمعية الحكمة الكويتية الخيرية",
                "author": "الكويت للتقدم التكنولوجي",
                "subject": "تقرير تقني وتصميمي وأمني",
            }
        )
        merged.set_metadata(metadata)
        merged.save(PDF, garbage=4, deflate=True)
    CONTENT_PDF.unlink(missing_ok=True)


def render_preview() -> None:
    PREVIEW.unlink(missing_ok=True)
    CONTENT_COVER_PREVIEW.unlink(missing_ok=True)
    with pymupdf.open(PDF) as document:
        identity_pixmap = document[0].get_pixmap(
            matrix=pymupdf.Matrix(1.5, 1.5),
            alpha=False,
        )
        identity_pixmap.save(PREVIEW)
        report_pixmap = document[1].get_pixmap(
            matrix=pymupdf.Matrix(1.5, 1.5),
            alpha=False,
        )
        report_pixmap.save(CONTENT_COVER_PREVIEW)


def main() -> None:
    render(
        [
            "--no-pdf-header-footer",
            "--print-to-pdf-no-header",
            f"--print-to-pdf={CONTENT_PDF.resolve()}",
        ],
        CONTENT_PDF,
    )
    merge_identity_cover()
    render_preview()
    print(f"PDF_OK {PDF.stat().st_size} bytes")
    print(f"PREVIEW_OK {PREVIEW.stat().st_size} bytes")


if __name__ == "__main__":
    main()
