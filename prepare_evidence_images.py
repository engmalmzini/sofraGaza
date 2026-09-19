from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageChops


SCREENSHOTS = Path(__file__).resolve().parent / "audit-assets" / "screenshots"

# Each pair limits the vertical crop to the part that contains the cited evidence.
# Horizontal whitespace is detected and removed automatically.
FOCUS = {
    "alhikma-statistics-mobile.png": (0.00, 0.62),
    "alhikma-statistics-conflicting-totals-mobile.png": (0.00, 0.42),
    "alhikma-faq-test-content-mobile.png": (0.00, 0.72),
    "alhikma-achievements-placeholder-mobile.png": (0.00, 0.77),
    "alhikma-donation-mobile-top.png": (0.00, 1.00),
    "alhikma-zakat-translation-key-mobile.png": (0.03, 0.90),
    "alhikma-project-request-personal-data-mobile.png": (0.02, 0.96),
}


def content_bounds(image: Image.Image) -> tuple[int, int, int, int]:
    rgb = image.convert("RGB")
    white = Image.new("RGB", rgb.size, "white")
    difference = ImageChops.difference(rgb, white).convert("L")
    visible = difference.point(lambda value: 255 if value > 8 else 0)
    return visible.getbbox() or (0, 0, image.width, image.height)


def crop_evidence(source: Path, top_ratio: float, bottom_ratio: float) -> Path:
    with Image.open(source) as original:
        image = original.convert("RGB")
        left, _, right, _ = content_bounds(image)
        margin = max(16, image.width // 100)
        left = max(0, left - margin)
        right = min(image.width, right + margin)
        top = max(0, int(image.height * top_ratio) - margin)
        bottom = min(image.height, int(image.height * bottom_ratio) + margin)
        cropped = image.crop((left, top, right, bottom))

        destination = source.with_name(f"{source.stem}-evidence.png")
        cropped.save(destination, "PNG", optimize=True)
        print(f"{source.name}: {image.size} -> {cropped.size}")
        return destination


def main() -> None:
    for filename, focus in FOCUS.items():
        crop_evidence(SCREENSHOTS / filename, *focus)


if __name__ == "__main__":
    main()
