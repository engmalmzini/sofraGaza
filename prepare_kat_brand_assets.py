from __future__ import annotations

from pathlib import Path

import pymupdf
from PIL import Image


ROOT = Path(__file__).resolve().parent
SOURCE = Path(r"C:\Users\MSI GF63\Downloads\KAT VISUAL IDENTITY FINAL  (1).pdf")
OUTPUT = ROOT / "reports" / "brand"
REPORT = ROOT / "reports" / "alhikma-website-technical-audit-2026.pdf"
REPORT_COVER_PREVIEW = ROOT / "reports" / "alhikma-report-content-cover-preview.png"


def main() -> None:
    OUTPUT.mkdir(parents=True, exist_ok=True)
    document = pymupdf.open(SOURCE)
    print(f"PAGES {document.page_count}")
    for index in range(min(4, document.page_count)):
        page = document[index]
        scale = 2.5 if index == 0 else 1.7
        pixmap = page.get_pixmap(matrix=pymupdf.Matrix(scale, scale), alpha=False)
        destination = OUTPUT / f"kat-identity-page-{index + 1}.png"
        pixmap.save(destination)
        print(
            f"PAGE {index + 1} {page.rect.width:.1f}x{page.rect.height:.1f} "
            f"IMAGES {len(page.get_images(full=True))} -> {destination.name}"
        )

    first_page = document[0]
    clip = pymupdf.Rect(
        first_page.rect.width * 0.16,
        first_page.rect.height * 0.40,
        first_page.rect.width * 0.86,
        first_page.rect.height * 0.63,
    )
    logo_pixmap = first_page.get_pixmap(
        matrix=pymupdf.Matrix(4, 4),
        clip=clip,
        alpha=False,
    )
    logo = Image.frombytes(
        "RGB",
        (logo_pixmap.width, logo_pixmap.height),
        logo_pixmap.samples,
    ).convert("RGBA")
    background = logo.getpixel((0, 0))[:3]
    pixels = []
    for red, green, blue, _ in logo.getdata():
        distance = max(
            abs(red - background[0]),
            abs(green - background[1]),
            abs(blue - background[2]),
        )
        if distance <= 12:
            alpha = 0
        elif distance >= 42:
            alpha = 255
        else:
            alpha = round((distance - 12) / 30 * 255)
        pixels.append((red, green, blue, alpha))
    logo.putdata(pixels)
    bounds = logo.getbbox()
    if bounds:
        margin = 24
        left, top, right, bottom = bounds
        logo = logo.crop(
            (
                max(0, left - margin),
                max(0, top - margin),
                min(logo.width, right + margin),
                min(logo.height, bottom + margin),
            )
        )
    logo_destination = OUTPUT / "kat-logo-color-horizontal.png"
    logo.save(logo_destination, "PNG", optimize=True)
    print(f"LOGO {logo.size} -> {logo_destination.name}")

    if REPORT.exists():
        with pymupdf.open(REPORT) as report:
            pixmap = report[1].get_pixmap(
                matrix=pymupdf.Matrix(1.5, 1.5),
                alpha=False,
            )
            pixmap.save(REPORT_COVER_PREVIEW)
            print(f"REPORT_COVER -> {REPORT_COVER_PREVIEW.name}")


if __name__ == "__main__":
    main()
