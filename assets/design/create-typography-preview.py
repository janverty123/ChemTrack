from pathlib import Path
from PIL import Image, ImageDraw, ImageFont
import json

ROOT = Path(__file__).resolve().parent
COLORS = {
    'page': '#F7FAF8', 'surface': '#FFFFFF', 'ink': '#173C35',
    'primary': '#115E59', 'mint': '#E6F2EC', 'muted': '#526760',
    'line': '#D7E4DD', 'control': '#7A9087',
    'pending_ink': '#8A5A16', 'pending_bg': '#FFF2D4',
    'error_ink': '#B42318', 'error_bg': '#FFF0EE',
}
SCALE = 2
canvas = Image.new('RGB', (1440 * SCALE, 1290 * SCALE), COLORS['page'])
draw = ImageDraw.Draw(canvas)


def font(size, weight=400, family='inter'):
    face = ImageFont.truetype(str(ROOT / 'fonts' / f'{family}-variable.ttf'), round(size * SCALE))
    face.set_variation_by_axes([weight] if family == 'manrope' else [min(32, max(14, size)), weight])
    return face


def text(x, y, value, size=16, weight=400, family='inter', color='ink'):
    draw.text((x * SCALE, y * SCALE), value, font=font(size, weight, family), fill=COLORS.get(color, color), anchor='lt')


def rect(box, color='surface', radius=0, outline=None):
    scaled = tuple(round(v * SCALE) for v in box)
    fill = COLORS.get(color, color)
    border = COLORS.get(outline, outline) if outline else None
    draw.rounded_rectangle(scaled, radius=round(radius * SCALE), fill=fill, outline=border, width=SCALE)


def line(x1, y1, x2, y2, color='line'):
    draw.line((x1*SCALE, y1*SCALE, x2*SCALE, y2*SCALE), fill=COLORS[color], width=SCALE)


rect((72, 36, 114, 78), 'primary', 10)
text(83, 45, 'C', 24, 700, 'manrope', 'surface')
text(128, 42, 'ChemTrack', 28, 700, 'manrope')
text(820, 51, 'Typeface and colour proposal', 14, 500, color='muted')
rect((1216, 36, 1368, 84), 'surface', 24, 'line')
text(1242, 51, 'Menu', 15, 600)
line(1324, 54, 1344, 54, 'primary')
line(1324, 64, 1344, 64, 'primary')
line(72, 111, 1368, 111)

text(72, 155, 'Main headline · Manrope 700 · 64 px', 14, 500, color='muted')
text(72, 200, 'Chemistry, one', 64, 700, 'manrope')
text(72, 274, 'step at a time.', 64, 700, 'manrope')
text(76, 370, 'Work through your modules, check your understanding,', 18, color='muted')
text(76, 399, 'and see what you have accomplished.', 18, color='muted')
rect((76, 458, 260, 514), 'primary', 12)
text(108, 477, 'Start learning', 15, 600, color='surface')
text(282, 479, 'See how it works', 15, 600, color='primary')

rect((858, 157, 1368, 538), 'surface', 20, 'line')
text(890, 191, 'Your learning path', 28, 700, 'manrope')
rect((890, 239, 1002, 274), 'mint', 17)
text(913, 249, 'ST2-G', 15, 600, color='primary')
text(1240, 250, 'Cycle 1', 14, 500, color='muted')
text(890, 306, 'Inside the atom', 24, 600, 'manrope')
text(890, 351, 'Continue your next chemistry module.', 16, color='muted')
text(890, 393, '3 of 5 activities completed', 14, 500, color='muted')
rect((890, 422, 1336, 430), 'mint', 4)
rect((890, 422, 1158, 430), 'primary', 4)
rect((890, 456, 1054, 504), 'primary', 10)
text(918, 472, 'Continue', 15, 600, color='surface')
text(1124, 473, 'Sample content', 13, color='muted')

line(72, 582, 1368, 582)
text(72, 618, 'A clear hierarchy for every screen', 32, 700, 'manrope')
rows = [
    (694, 'Section heading', 'Understand your progress.', 32, 700, 'manrope', '32–40 px'),
    (767, 'Card title', 'Pick up where you left off', 22, 600, 'manrope', '20–24 px'),
    (826, 'Body and lesson text', 'Read a little, check your understanding, then continue.', 18, 400, 'inter', '16–18 px'),
    (882, 'Buttons and navigation', 'Continue module', 15, 600, 'inter', '15–16 px'),
    (936, 'Codes and result tables', 'ST2-G    75%    3 / 5', 18, 600, 'inter', '16–18 px'),
]
for y, role, example, size, weight, family, dimensions in rows:
    text(76, y + 7, role, 14, 500, color='muted')
    text(340, y, example, size, weight, family)
    text(1260, y + 7, dimensions, 13, color='muted')
    line(76, y + 47, 1364, y + 47)

text(72, 1031, 'Light theme palette', 28, 700, 'manrope')
swatches = [
    ('Background', 'page'), ('Cards', 'surface'), ('Headings', 'ink'),
    ('Actions', 'primary'), ('Progress tint', 'mint'), ('Secondary text', 'muted'),
]
for i, (label, token) in enumerate(swatches):
    x = 72 + i * 219
    rect((x, 1091, x + 199, 1160), token, 10, 'line')
    text(x, 1175, label, 14, 500)
    text(x, 1201, COLORS[token], 13, color='muted')

text(72, 1254, 'Design proposal only. The application’s existing styles have not been changed.', 13, color='muted')


def luminance(color):
    values = [int(color[i:i+2], 16) / 255 for i in (1, 3, 5)]
    channels = [v / 12.92 if v <= .04045 else ((v + .055) / 1.055) ** 2.4 for v in values]
    return sum(a*b for a, b in zip(channels, (.2126, .7152, .0722)))


def ratio(foreground, background):
    a, b = sorted((luminance(COLORS[foreground]), luminance(COLORS[background])))
    return (b + .05) / (a + .05)


pairs = [('ink', 'page'), ('ink', 'surface'), ('muted', 'page'), ('muted', 'surface'),
         ('surface', 'primary'), ('primary', 'mint'), ('pending_ink', 'pending_bg'),
         ('error_ink', 'error_bg'), ('control', 'surface')]
checks = []
for foreground, background in pairs:
    contrast = ratio(foreground, background)
    minimum = 3 if foreground == 'control' else 4.5
    if contrast < minimum:
        raise RuntimeError(f'Insufficient contrast for {foreground}/{background}: {contrast:.2f}')
    checks.append({'foreground': foreground, 'background': background, 'ratio': round(contrast, 2), 'minimum': minimum})

canvas.resize((1440, 1290), Image.Resampling.LANCZOS).save(ROOT / 'typography-theme-preview.png')
(ROOT / 'typography-theme-contrast.json').write_text(json.dumps(checks, indent=2) + '\n', encoding='utf-8')
print('Created typography/theme preview using actual Manrope and Inter fonts.')
print(json.dumps(checks))
