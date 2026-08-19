"""Footer PNGs + PulseLine/ImpactIcon ia (NYXERIN) + favicon."""
import base64
import re
import shutil
from io import BytesIO
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont
from fontTools.misc.transform import Transform
from fontTools.pens.boundsPen import ControlBoundsPen
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parent
NYXERIN_WOFF = ROOT / "assets" / "fonts" / "nyxerin.woff"
NYXERIN_WOFF_SRC = Path(r"c:\Users\olga-\OneDrive\Desktop\СОХРАНЕНО\NYXERIN (1) (1) (1).woff")
NYXERIN_TTF = ROOT / "assets" / "fonts" / "nyxerin.ttf"
FAVICON_SRC = Path(
    r"C:\Users\olga-\.cursor\projects\c-Users-olga-OneDrive-Desktop-public-html\assets"
    r"\c__Users_olga-_AppData_Roaming_Cursor_User_workspaceStorage_f9809fc3c3f36583cebe27e3a8b98aa8_images_"
    r"22__4_-dd175276-c908-4028-9e07-655b79c2e9b1.png"
)
FAVICON_SVG_SRC = Path(r"c:\Users\olga-\OneDrive\Desktop\СОХРАНЕНО\22.svg")
MEDIA = ROOT / "_next" / "static" / "media"
PULSE_JS = ROOT / "_next" / "static" / "chunks" / "8e1e9e7a85fc0466.js"
ICON_JS = ROOT / "_next" / "static" / "chunks" / "fe86549c3883d530.js"
BRAND_RED = (255, 0, 36, 255)


def ensure_nyxerin_ttf() -> None:
    NYXERIN_WOFF.parent.mkdir(parents=True, exist_ok=True)
    if not NYXERIN_WOFF.exists() and NYXERIN_WOFF_SRC.exists():
        shutil.copy2(NYXERIN_WOFF_SRC, NYXERIN_WOFF)
    if not NYXERIN_TTF.exists():
        TTFont(NYXERIN_WOFF).save(NYXERIN_TTF)


def render_footer_png(width: int, height: int, out: Path) -> None:
    ensure_nyxerin_ttf()
    img = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    text = "IMPACT"

    target_w = width * 0.96
    size = 10
    best_size = 10
    best_bbox = (0, 0, 0, 0)
    while size <= 4000:
        font = ImageFont.truetype(str(NYXERIN_TTF), size)
        bbox = draw.textbbox((0, 0), text, font=font)
        tw = bbox[2] - bbox[0]
        if tw > target_w:
            break
        best_size = size
        best_bbox = bbox
        size += 2

    font = ImageFont.truetype(str(NYXERIN_TTF), best_size)
    bbox = best_bbox
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    x = (width - tw) // 2 - bbox[0]
    pad_bottom = max(12, int(height * 0.04))
    y = height - th - pad_bottom - bbox[1]

    draw.text((x, y), text, fill=(255, 255, 255, 64), font=font)
    img.save(out, optimize=True)
    print(f"footer png: {out} ({width}x{height}, font={best_size}px)")


def get_ia_paths() -> tuple[list[str], float, float, float, float]:
    ensure_nyxerin_ttf()
    font = TTFont(NYXERIN_TTF)
    glyph_set = font.getGlyphSet()
    cmap = font.getBestCmap()
    hmtx = font["hmtx"].metrics

    paths: list[str] = []
    x = 0.0
    bounds_pen = ControlBoundsPen(glyph_set)
    for ch in "ia":
        gname = cmap[ord(ch)]
        inner = SVGPathPen(glyph_set)
        glyph_set[gname].draw(TransformPen(inner, Transform(1, 0, 0, 1, x, 0)))
        paths.append(inner.getCommands())
        glyph_set[gname].draw(TransformPen(bounds_pen, Transform(1, 0, 0, 1, x, 0)))
        x += hmtx[gname][0]

    min_x, min_y, max_x, max_y = bounds_pen.bounds

    svg_path = ROOT / "assets" / "ia-logo.svg"
    svg_body = "\n".join(f'  <path d="{d}" fill="#FF0024"/>' for d in paths)
    svg_path.write_text(
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{min_x:.0f} {min_y:.0f} '
        f'{max_x - min_x:.0f} {max_y - min_y:.0f}">\n{svg_body}\n</svg>\n',
        encoding="utf-8",
    )
    return paths, min_x, min_y, max_x, max_y


def scale_transform(
    min_x: float,
    min_y: float,
    max_x: float,
    max_y: float,
    box_x: float,
    box_y: float,
    box_w: float,
    box_h: float,
    pad: float = 0.10,
) -> str:
    src_w = max(max_x - min_x, 1e-6)
    src_h = max(max_y - min_y, 1e-6)
    inner_w = box_w * (1.0 - pad)
    inner_h = box_h * (1.0 - pad)
    scale = min(inner_w / src_w, inner_h / src_h)
    tx = box_x + (box_w - src_w * scale) / 2
    ty = box_y + (box_h - src_h * scale) / 2
    return f"translate({tx:.4f},{ty:.4f}) scale({scale:.6f}) translate({-min_x:.4f},{-min_y:.4f})"


def js_path_elements(paths: list[str], jsx_fn: str = "t") -> str:
    chunks = []
    for d in paths:
        chunks.append(f'(0,{jsx_fn}.jsx)("path",{{d:"{d}",fill:"currentColor"}})')
    return ",".join(chunks)


def patch_pulse_line(paths: list[str], min_x, min_y, max_x, max_y) -> None:
    xf = scale_transform(min_x, min_y, max_x, max_y, box_x=3.0, box_y=178.5, box_w=30.0, box_h=32.0)
    new_fragment = (
        f'"logo"===m?(0,t.jsx)("g",{{transform:"{xf}",style:{{filter:"drop-shadow(0 0 5px rgba(255, 0, 39, 0.68))"}},children:['
        + js_path_elements(paths)
        + "]})" + ":null"
    )
    text = PULSE_JS.read_text(encoding="utf-8")
    start = text.find('"logo"===m?')
    if start < 0:
        raise RuntimeError("PulseLine logo fragment not found")
    end_marker = ":null})]})}"
    end = text.find(end_marker, start)
    if end < 0:
        raise RuntimeError("PulseLine logo fragment end not found")
    end += len(":null")
    PULSE_JS.write_text(text[:start] + new_fragment + text[end:], encoding="utf-8")
    print("patched PulseLine in", PULSE_JS.name)


def patch_impact_icon(paths: list[str], min_x, min_y, max_x, max_y) -> None:
    xf = scale_transform(min_x, min_y, max_x, max_y, box_x=0, box_y=0, box_w=100, box_h=100)
    path_js = js_path_elements(paths, jsx_fn="s")
    end_marker = "})],57815),"
    new_icon = (
        f'e.s(["ImpactIcon",0,e=>(0,s.jsxs)("svg",{{viewBox:"0 0 100 100",fill:"none",...e,children:[(0,s.jsx)("g",{{transform:"{xf}",children:['
        + path_js
        + "]})]})],57815),"
    )
    text = ICON_JS.read_text(encoding="utf-8")
    start = text.find('e.s(["ImpactIcon",0,e=>(0,s.jsxs)("svg",{')
    if start < 0:
        raise RuntimeError("ImpactIcon block not found")
    end = text.find(end_marker, start)
    if end < 0:
        raise RuntimeError("ImpactIcon block end not found")
    end += len(end_marker)
    ICON_JS.write_text(text[:start] + new_icon + text[end:], encoding="utf-8")
    print("patched ImpactIcon in", ICON_JS.name)


def _favicon_from_svg() -> Image.Image | None:
    if not FAVICON_SVG_SRC.exists():
        return None
    raw = FAVICON_SVG_SRC.read_text(encoding="utf-8")
    matches = re.findall(r"data:image/png;base64,([^\"]+)", raw)
    if len(matches) < 2:
        return None
    return Image.open(BytesIO(base64.b64decode(matches[1]))).convert("RGBA")


def _favicon_from_user() -> Image.Image | None:
    if FAVICON_SRC.exists():
        return Image.open(FAVICON_SRC).convert("RGBA")
    return None


def _favicon_rendered() -> Image.Image:
    ensure_nyxerin_ttf()
    size = 256
    img = Image.new("RGBA", (size, size), BRAND_RED)
    draw = ImageDraw.Draw(img)
    font_size = 10
    best = 10
    while font_size <= 400:
        font = ImageFont.truetype(str(NYXERIN_TTF), font_size)
        bbox = draw.textbbox((0, 0), "ia", font=font)
        tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
        if tw > size * 0.72 or th > size * 0.72:
            break
        best = font_size
        font_size += 2
    font = ImageFont.truetype(str(NYXERIN_TTF), best)
    bbox = draw.textbbox((0, 0), "ia", font=font)
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    x = (size - tw) // 2 - bbox[0]
    y = (size - th) // 2 - bbox[1]
    draw.text((x, y), "ia", fill=(255, 255, 255, 255), font=font)
    return img


def render_favicon() -> None:
    src = _favicon_from_user() or _favicon_from_svg() or _favicon_rendered()
    src_path = ROOT / "assets" / "favicon-source.png"
    src.save(src_path)

    sizes = (48, 32, 16)
    icons: list[Image.Image] = []
    for size in sizes:
        canvas = src.resize((size, size), Image.Resampling.LANCZOS)
        icons.append(canvas)

    icons[0].save(ROOT / "impact-icon.ico", format="ICO", sizes=[(s, s) for s in sizes])
    shutil.copy2(ROOT / "impact-icon.ico", ROOT / "favicon.ico")

    png256 = src.resize((256, 256), Image.Resampling.LANCZOS)
    png256.save(ROOT / "favicon.png")
    png256.save(ROOT / "apple-touch-icon.png")
    png256.save(ROOT / "assets" / "favicon.png")
    png256.save(ROOT / "assets" / "apple-touch-icon.png")
    print("favicon: favicon.ico + favicon.png + apple-touch-icon.png")


def main() -> None:
    MEDIA.mkdir(parents=True, exist_ok=True)
    render_footer_png(3680, 601, MEDIA / "logo.890d0c79.png")
    render_footer_png(1840, 300, MEDIA / "logo-mobile.74ce8933.png")

    paths, min_x, min_y, max_x, max_y = get_ia_paths()
    print(f"ia paths bbox: {min_x} {min_y} {max_x} {max_y}")
    patch_pulse_line(paths, min_x, min_y, max_x, max_y)
    patch_impact_icon(paths, min_x, min_y, max_x, max_y)
    render_favicon()
    print("done")


if __name__ == "__main__":
    main()
