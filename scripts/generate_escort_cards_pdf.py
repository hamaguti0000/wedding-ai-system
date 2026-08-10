#!/usr/bin/env python3
import json
import math
import sys
from functools import lru_cache
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont, ImageOps

DPI = 200
PX_PER_MM = DPI / 25.4

def mm(value):
    return int(round(value * PX_PER_MM))

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

# フォントファイル(NotoのCJK .ttcは十数MB)を解決するのは1回だけにする。
# 実測でカードごとにPathを検索・truetype()で再パースしていると、
# 200名規模でPDF生成がPHP側の120秒タイムアウトを超えてしまう。
SERIF_PATH = first_font_path(SERIF)
SERIF_BOLD_PATH = first_font_path(SERIF_BOLD)
SANS_PATH = first_font_path(SANS)

_MEASURE_DRAW = ImageDraw.Draw(Image.new("RGB", (1, 1)))

@lru_cache(maxsize=None)
def font(path, size):
    try:
        return ImageFont.truetype(path, size=size)
    except OSError:
        return ImageFont.load_default()

def fit_font(path, text, size, max_width):
    f = font(path, size)
    while size > 10 and _MEASURE_DRAW.textlength(text, font=f) > max_width:
        size -= 1
        f = font(path, size)
    return f

def draw_fit(draw, pos, text, path, size, fill, max_width, anchor=None):
    if not text:
        return
    draw.text(pos, text, font=fit_font(path, text, size, max_width), fill=fill, anchor=anchor)

def make_card(base_portrait, guest, couple, date):
    portrait_w, portrait_h = mm(55), mm(91)
    card_w, card_h = mm(91), mm(55)

    portrait = base_portrait.copy()
    draw = ImageDraw.Draw(portrait)

    # couple/date欄の下地はテンプレートの背景色そのものに合わせないと、四角い
    # 「貼り付けたテキストボックス」のような境界線が見えてしまう。色を決め打ちすると
    # テンプレート画像を差し替えたときにまたズレるため、そのカードの角(文字も花柄も
    # かからない場所)から都度サンプリングする。
    paper = base_portrait.getpixel((mm(0.5), mm(0.5)))
    navy = (37, 58, 92)
    gold = (169, 131, 72)

    draw.rectangle([mm(4.6), mm(4.3), mm(30), mm(16.2)], fill=paper)
    date_font = font(SERIF_PATH, mm(2.45))
    draw_fit(draw, (mm(5.2), mm(7.9)), couple, SERIF_PATH, mm(2.8), gold, mm(24))
    if date:
        draw.text((mm(5.2), mm(13.0)), date, font=date_font, fill=gold)

    table_font = font(SERIF_PATH, mm(18.5))
    draw.text((mm(44.4), mm(39.0)), guest.get("table", ""), font=table_font, fill=navy, anchor="mm")

    # ローマ字(名/姓を2段組み、名を大きく)を優先し、ローマ字が無いアカウント(テスト用等)は
    # 漢字氏名1行にフォールバックする。
    first_name = (guest.get("first_name") or "").strip()
    last_name = (guest.get("last_name") or "").strip()
    if first_name or last_name:
        draw_fit(draw, (mm(6.0), mm(49.0)), first_name, SERIF_PATH, mm(7.4), navy, mm(40))
        draw_fit(draw, (mm(6.2), mm(61.5)), last_name, SERIF_PATH, mm(4.6), navy, mm(40))
    else:
        name = guest.get("name") or ""
        draw_fit(draw, (mm(6.0), mm(53.4)), name, SERIF_PATH, mm(5.6), navy, mm(38))

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
    portrait_w, portrait_h = mm(55), mm(91)
    base_portrait = ImageOps.fit(
        template.convert("RGB"), (portrait_w, portrait_h),
        method=Image.Resampling.LANCZOS, centering=(0.5, 0.5),
    )

    pages = []
    for page_index in range(max(1, math.ceil(len(guests) / 10))):
        page = Image.new("RGB", (page_w, page_h), "white")
        for i, guest in enumerate(guests[page_index * 10:(page_index + 1) * 10]):
            card = make_card(base_portrait, guest, payload.get("couple", ""), payload.get("date", ""))
            page.paste(card, (xs[i % 2], ys[i // 2]))
        pages.append(page)

    out_path.parent.mkdir(parents=True, exist_ok=True)
    first, rest = pages[0], pages[1:]
    # 300DPI・JPEGデフォルト品質のままだと大人数でPDFが十数MBになり、モバイル回線での
    # プレビュー表示が完了しない/固まる原因になっていた。200DPI・quality=80だと
    # 205名でも約8MBに収まりつつ、150DPI・quality=70より花柄の線がくっきり見える。
    first.save(out_path, "PDF", resolution=DPI, save_all=True, append_images=rest, quality=80)

if __name__ == "__main__":
    main()
