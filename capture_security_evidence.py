from __future__ import annotations

import shutil
import subprocess
import tempfile
from pathlib import Path


ROOT = Path(__file__).resolve().parent
SECURITY = ROOT / "audit-assets" / "security"
SCREENSHOTS = ROOT / "audit-assets" / "screenshots"


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


def capture(source: Path, destination: Path, virtual_time_ms: int = 5000) -> None:
    destination.unlink(missing_ok=True)
    profile = Path(tempfile.mkdtemp(prefix="kat-security-evidence-"))
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
        "--hide-scrollbars",
        "--window-size=1280,1000",
        f"--virtual-time-budget={virtual_time_ms}",
        f"--screenshot={destination.resolve()}",
        source.resolve().as_uri(),
    ]
    try:
        result = subprocess.run(
            command,
            cwd=ROOT,
            timeout=150,
            check=False,
            capture_output=True,
            text=True,
        )
        if result.returncode != 0 or not destination.exists():
            details = (result.stderr or result.stdout).strip()
            raise RuntimeError(f"Screenshot failed ({result.returncode}): {details}")
    finally:
        shutil.rmtree(profile, ignore_errors=True)


def main() -> None:
    SCREENSHOTS.mkdir(parents=True, exist_ok=True)
    capture(
        SECURITY / "security-headers-evidence.html",
        SCREENSHOTS / "security-headers-evidence.png",
    )
    capture(
        SECURITY / "clickjacking-proof.html",
        SCREENSHOTS / "clickjacking-proof.png",
        virtual_time_ms=25000,
    )
    print("SECURITY_EVIDENCE_OK")


if __name__ == "__main__":
    main()
