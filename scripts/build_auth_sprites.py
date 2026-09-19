from __future__ import annotations

from pathlib import Path

import numpy as np
from PIL import Image

ASSETS = Path(r"C:\Users\MSI GF63\.cursor\projects\d-Androied-studio-bro-SofraGaza\assets")
OUT_DIR = Path(r"D:\Androied studio bro\SofraGaza\public\images\auth")

DENSE_SHEETS = ("chef-look", "chef-shy", "rider-look", "rider-shy")


def find_file(name: str) -> Path:
    exact = ASSETS / name
    if exact.exists():
        return exact
    matches = list(ASSETS.glob(f"*{name}"))
    if not matches:
        raise FileNotFoundError(name)
    return matches[0]


def chroma_key(rgb: np.ndarray) -> np.ndarray:
    pix = rgb.astype(np.float32)
    r, g, b = pix[:, :, 0], pix[:, :, 1], pix[:, :, 2]
    h, w = r.shape

    samples = np.concatenate([
        pix[2:8, 2:8].reshape(-1, 3),
        pix[2:8, w - 8:w - 2].reshape(-1, 3),
        pix[h - 8:h - 2, 2:8].reshape(-1, 3),
        pix[h - 8:h - 2, w - 8:w - 2].reshape(-1, 3),
    ], axis=0)
    bg = np.median(samples, axis=0)
    dist_bg = np.sqrt(((pix - bg) ** 2).sum(axis=2))

    chroma = (r + b) / 2.0 - g
    dist_mag = np.sqrt((r - 255.0) ** 2 + (g - 0.0) ** 2 + (b - 255.0) ** 2)

    alpha = np.clip((dist_bg - 38.0) / 40.0, 0.0, 1.0)
    alpha = np.where(dist_mag < 95.0, 0.0, alpha)
    alpha = np.where(chroma > 120.0, np.minimum(alpha, 0.08), alpha)
    alpha = np.where((r > 220) & (b > 220) & (g < 70), 0.0, alpha)

    out = np.zeros((h, w, 4), dtype=np.float32)
    out[:, :, 0] = r
    out[:, :, 1] = g
    out[:, :, 2] = b
    out[:, :, 3] = alpha * 255.0

    spill = np.maximum(0.0, np.minimum(out[:, :, 0], out[:, :, 2]) - out[:, :, 1])
    magenta_edge = spill > 16
    out[:, :, 0] = np.where(magenta_edge, np.clip(out[:, :, 0] - spill * 0.3, 0, 255), out[:, :, 0])
    out[:, :, 2] = np.where(magenta_edge, np.clip(out[:, :, 2] - spill * 0.92, 0, 255), out[:, :, 2])

    still_magenta = ((out[:, :, 0] + out[:, :, 2]) / 2.0 - out[:, :, 1] > 85) & (out[:, :, 1] < 90)
    out[:, :, 3] = np.where(still_magenta | (dist_bg < 28), 0, out[:, :, 3])

    empty = out[:, :, 3] < 10
    out[:, :, 0] = np.where(empty, 0, out[:, :, 0])
    out[:, :, 1] = np.where(empty, 0, out[:, :, 1])
    out[:, :, 2] = np.where(empty, 0, out[:, :, 2])
    out[:, :, 3] = np.where(empty, 0, out[:, :, 3])
    return out.astype(np.uint8)


def content_bands(density: np.ndarray, threshold: float = 0.018) -> list[tuple[int, int]]:
    active = density > threshold
    bands: list[tuple[int, int]] = []
    start = None
    for index, on in enumerate(active):
        if on and start is None:
            start = index
        elif not on and start is not None:
            if index - start >= 12:
                bands.append((start, index))
            start = None
    if start is not None and len(density) - start >= 12:
        bands.append((start, len(density)))
    return bands


def color_change_bands(seq: np.ndarray, min_size: int, dist_thresh: float = 42.0) -> list[tuple[int, int]]:
    bands: list[tuple[int, int]] = []
    start = 0
    acc = seq[0].astype(np.float64)
    count = 1.0
    for index in range(1, len(seq)):
        mean = acc / count
        if float(np.linalg.norm(seq[index] - mean)) > dist_thresh:
            if index - start >= min_size:
                bands.append((start, index))
                start = index
                acc = seq[index].astype(np.float64)
                count = 1.0
                continue
        acc += seq[index]
        count += 1.0
    if len(seq) - start >= min_size:
        bands.append((start, len(seq)))
    return bands


def detect_color_grid(rgb: np.ndarray) -> tuple[list[tuple[int, int]], list[tuple[int, int]]] | None:
    h, w = rgb.shape[:2]
    left = np.median(rgb[:, 2:12], axis=1)
    top = np.median(rgb[2:12, :], axis=0)
    row_bands = color_change_bands(left, min_size=max(36, h // 10))
    col_bands = color_change_bands(top, min_size=max(36, w // 10))
    if len(row_bands) in (2, 3, 4) and len(col_bands) in (2, 4):
        return row_bands, col_bands
    return None


def take_row_bands(row_bands: list[tuple[int, int]]) -> list[tuple[int, int]]:
    if len(row_bands) == 4:
        return row_bands[1:3]
    if len(row_bands) >= 3:
        return row_bands[:2]
    return row_bands


def drop_saturated_backdrop(arr: np.ndarray) -> np.ndarray:
    r = arr[:, :, 0].astype(np.float32)
    g = arr[:, :, 1].astype(np.float32)
    b = arr[:, :, 2].astype(np.float32)
    magenta = (r > 140) & (b > 140) & (g < 150) & ((r + b) / 2.0 - g > 35)
    blue = (b > 145) & (b > r + 18) & (b > g + 8)
    arr[:, :, 3] = np.where(magenta | blue, 0, arr[:, :, 3])
    empty = arr[:, :, 3] < 10
    arr[:, :, 0] = np.where(empty, 0, arr[:, :, 0])
    arr[:, :, 1] = np.where(empty, 0, arr[:, :, 1])
    arr[:, :, 2] = np.where(empty, 0, arr[:, :, 2])
    return arr


def ensure_single_figure(cell: np.ndarray) -> np.ndarray:
    alpha = cell[:, :, 3] > 22
    row_bands = content_bands(alpha.mean(axis=1), threshold=0.04)
    col_bands = content_bands(alpha.mean(axis=0), threshold=0.04)
    if len(row_bands) >= 2:
        y0, y1 = max(row_bands, key=lambda band: int((cell[band[0]:band[1], :, 3] > 22).sum()))
        cell = cell[y0:y1]
    if len(col_bands) >= 2:
        x0, x1 = max(col_bands, key=lambda band: int((cell[:, band[0]:band[1], 3] > 22).sum()))
        cell = cell[:, x0:x1]
    return crop_content(cell)


def extract_cells(rgb: np.ndarray) -> list[np.ndarray]:
    grid = detect_color_grid(rgb)
    if grid is not None:
        row_bands, col_bands = grid
        row_bands = take_row_bands(row_bands)
        col_bands = col_bands[:4]
        print(f"    color-grid rows={len(row_bands)} cols={len(col_bands)}")
        cells = []
        for y0, y1 in row_bands:
            for x0, x1 in col_bands:
                pad = 8
                tile = chroma_key(rgb[y0 + pad : max(y0 + pad + 1, y1 - pad), x0 + pad : max(x0 + pad + 1, x1 - pad)])
                tile = drop_saturated_backdrop(tile)
                cells.append(ensure_single_figure(tile))
        while len(cells) < 8:
            cells.append(cells[-1] if cells else rgb)
        return cells[:8]

    keyed = chroma_key(rgb)
    keyed = drop_saturated_backdrop(keyed)
    return [ensure_single_figure(cell) for cell in split_figures(keyed)]


def split_figures(arr: np.ndarray, inset: int = 4) -> list[np.ndarray]:
    alpha = arr[:, :, 3] > 22
    row_bands = content_bands(alpha.mean(axis=1))
    col_bands = content_bands(alpha.mean(axis=0))

    print(f"    bands rows={len(row_bands)} cols={len(col_bands)} shape={arr.shape[1]}x{arr.shape[0]}")

    if len(row_bands) >= 3:
        row_bands = row_bands[:2]
    elif len(row_bands) < 2:
        return split_2x4(arr, 1)

    if len(col_bands) >= 4:
        col_bands = col_bands[:4]
    else:
        return split_2x4(arr, 1)

    cells = []
    for y0, y1 in row_bands:
        for x0, x1 in col_bands:
            ya, yb = y0 + inset, y1 - inset
            xa, xb = x0 + inset, x1 - inset
            if yb <= ya or xb <= xa:
                cells.append(arr[y0:y1, x0:x1].copy())
            else:
                cells.append(arr[ya:yb, xa:xb].copy())

    while len(cells) < 8:
        cells.append(cells[-1] if cells else arr)
    return cells[:8]


def split_2x4(arr: np.ndarray, inset: int = 1) -> list[np.ndarray]:
    h, w = arr.shape[:2]
    cells = []
    for row in range(2):
        for col in range(4):
            y0 = row * h // 2 + inset
            y1 = (row + 1) * h // 2 - inset
            x0 = col * w // 4 + inset
            x1 = (col + 1) * w // 4 - inset
            cells.append(arr[y0:y1, x0:x1].copy())
    return cells


def content_bbox(cell: np.ndarray, threshold: int = 18) -> tuple[int, int, int, int] | None:
    ys, xs = np.where(cell[:, :, 3] > threshold)
    if len(xs) == 0:
        return None
    return int(xs.min()), int(ys.min()), int(xs.max()) + 1, int(ys.max()) + 1


def crop_content(cell: np.ndarray) -> np.ndarray:
    box = content_bbox(cell)
    if box is None:
        return cell
    x0, y0, x1, y1 = box
    pad = 8
    x0 = max(0, x0 - pad)
    y0 = max(0, y0 - pad)
    x1 = min(cell.shape[1], x1 + pad)
    y1 = min(cell.shape[0], y1 + pad)
    return cell[y0:y1, x0:x1]


def fit_cell(cell: np.ndarray, width: int, height: int) -> np.ndarray:
    canvas = np.zeros((height, width, 4), dtype=np.uint8)
    if cell.size == 0:
        return canvas

    inner_w = max(1, int(width * 0.9))
    inner_h = max(1, int(height * 0.9))
    scale = min(inner_w / cell.shape[1], inner_h / cell.shape[0])
    new_w = max(1, int(round(cell.shape[1] * scale)))
    new_h = max(1, int(round(cell.shape[0] * scale)))
    resized = np.array(
        Image.fromarray(cell, "RGBA").resize((new_w, new_h), Image.Resampling.LANCZOS),
        dtype=np.uint8,
    )
    x = (width - new_w) // 2
    y = height - new_h - max(8, (height - inner_h) // 2)
    y = max(0, y)
    canvas[y : y + new_h, x : x + new_w] = resized
    return canvas


def stitch_grid(cells: list[np.ndarray], cols: int, rows: int, cell_w: int, cell_h: int) -> Image.Image:
    sheet = np.zeros((cell_h * rows, cell_w * cols, 4), dtype=np.uint8)
    for index, cell in enumerate(cells):
        row, col = divmod(index, cols)
        fitted = fit_cell(cell, cell_w, cell_h)
        sheet[row * cell_h : (row + 1) * cell_h, col * cell_w : (col + 1) * cell_w] = fitted
    return Image.fromarray(sheet, "RGBA")


def build_dense_sheet(name: str) -> None:
    grid: list[list[np.ndarray | None]] = [[None] * 8 for _ in range(8)]
    for index in range(8):
        filename = f"{name}-{index + 1:02d}.png"
        print(f"  {filename}")
        image = Image.open(find_file(filename)).convert("RGB")
        cells = extract_cells(np.array(image, dtype=np.uint8))
        block_row = (index // 2) * 2
        block_col = (index % 2) * 4
        for cell_index, cell in enumerate(cells):
            local_row, local_col = divmod(cell_index, 4)
            grid[block_row + local_row][block_col + local_col] = cell

    ordered = [cell if cell is not None else np.zeros((8, 8, 4), dtype=np.uint8) for row in grid for cell in row]
    if name.startswith("rider"):
        cell_w, cell_h = 400, 480
    else:
        cell_w, cell_h = 320, 540

    sheet = stitch_grid(ordered, 8, 8, cell_w, cell_h)
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    dest = OUT_DIR / f"{name}.png"
    sheet.save(dest, "PNG", optimize=True)
    print(f"{name}: {sheet.size[0]}x{sheet.size[1]} cell={cell_w}x{cell_h} -> {dest}")


def main() -> None:
    names = DENSE_SHEETS
    if __import__("sys").argv[1:]:
        names = tuple(__import__("sys").argv[1:])
    for name in names:
        build_dense_sheet(name)


if __name__ == "__main__":
    main()
