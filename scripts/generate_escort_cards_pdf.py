#!/usr/bin/env python3
import json
import math
import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont, ImageOps

DPI = 300
PX_PER_MM = DPI / 25.4

def mm(value):
    return int(round(value * PX_PER_MM))

def font(paths, size, index=0):
    for path in paths:
        if Path(path).exists():
            return ImageFont.truetype(path, size=size, index=index)
    return ImageFont.load_default()

SERIF = [
    "/usr/share/fonts/opentype/noto/NotoSerifCJK-Regular.ttc",
    "/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc",
    "/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf",
]
SERIF_BOLD = [
    "/usr/share/fonts/opentype/noto/NotoSerifCJK-Bold.ttc",
    "/usr/share/fonts/opentype/noto/NotoSansCJK-Bold.ttc",
    "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf",
]
SANS = [
    "/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc",
    "/usr/share/fonts/opentype/noto/NotoSerifCJK-Regular.ttc",
    "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
]

def first_font_path(paths):
    return next((p for p in paths if Path(p).exists()), paths[0])

def fit_font(paths, text, size, max_width):
    path = first_font_path(paths)
    f = ImageFont.truetype(path, size=size)
    dummy = ImageDraw.Draw(Image.new("RGB", (10, 10)))
    while size > 10 and dummy.textlength(text, font=f) > max_width:
        size -= 1
        f = ImageFont.truetype(path, size=size)
    return f

def draw_fit(draw, pos, text, paths, size, fill, max_width, anchor=None):
    if not text:
        return
    draw.text(pos, text, font=fit_font(paths, text, size, max_width), fill=fill, anchor=anchor)

def make_card(template, guest, couple, date):
    portrait_w, portrait_h = mm(55), mm(91)
    card_w, card_h = mm(91), mm(55)

    portrait = ImageOps.fit(template.convert("RGB"), (portrait_w, portrait_h), method=Image.Resampling.LANCZOS, centering=(0.5, 0.5))
    draw = ImageDraw.Draw(portrait)

    paper = (247, 244, 237)
    navy = (37, 58, 92)
    gold = (169, 131, 72)
    muted = (131, 119, 103)

    draw.rectangle([mm(4.6), mm(4.3), mm(30), mm(16.2)], fill=paper)
    date_font = font(SERIF, mm(2.45))
    draw_fit(draw, (mm(5.2), mm(7.9)), couple, SERIF, mm(2.8), gold, mm(24))
    if date:
        draw.text((mm(5.2), mm(13.0)), date, font=date_font, fill=gold)

    table_font = font(SERIF, mm(18.5))
    draw.text((mm(44.4), mm(39.0)), guest.get("table", ""), font=table_font, fill=navy, anchor="mm")

    kana = guest.get("furigana") or ""
    name = guest.get("name") or ""
    kana_font = font(SANS, mm(1.9))
    name_font = fit_font(SERIF, name, mm(5.6), mm(38))
    if kana:
        draw_fit(draw, (mm(6.0), mm(48.0)), kana, SANS, mm(1.9), muted, mm(38))
        draw.text((mm(6.0), mm(56.2)), name, font=name_font, fill=navy)
    else:
        draw.text((mm(6.0), mm(53.4)), name, font=name_font, fill=navy)

    return portrait.rotate(-90, expand=True).resize((card_w, card_h), Image.Resampling.LANCZOS)

def main():
    payload_path = Path(sys.argv[1])
    out_path = Path(sys.argv[2])
    payload = json.loads(payload_path.read_text(encoding="utf-8-sig"))
    guests = payload.get("guests", [])

    page_w, page_h = mm(210), mm(297)
    card_w, card_h = mm(91), mm(55)
    xs = [mm(14), mm(105)]
    ys = [mm(11 + i * 55) for i in range(5)]

    template = Image.open(payload["template"])
    pages = []
    for page_index in range(max(1, math.ceil(len(guests) / 10))):
        page = Image.new("RGB", (page_w, page_h), "white")
        for i, guest in enumerate(guests[page_index * 10:(page_index + 1) * 10]):
            card = make_card(template, guest, payload.get("couple", ""), payload.get("date", ""))
            page.paste(card, (xs[i % 2], ys[i // 2]))
        pages.append(page)

    out_path.parent.mkdir(parents=True, exist_ok=True)
    first, rest = pages[0], pages[1:]
    first.save(out_path, "PDF", resolution=DPI, save_all=True, append_images=rest)

if __name__ == "__main__":
    main()
