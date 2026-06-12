"""Record a looping hero animation of iWea for the README.

Visits Home → Compare → Analytics → Diff (2 sources selected) → Home.
Requires Docker running with real weather data.

Usage:
    source /mnt/d/work/projects/parts-diagram-ocr/.venv/bin/activate
    docker compose up -d   (from the iwea project root)
    python scripts/make_hero.py http://localhost:8080 assets/hero.webp
"""
import sys
import time
from io import BytesIO
from pathlib import Path

from PIL import Image
from playwright.sync_api import sync_playwright

W, H     = 1280, 720
OUT_W    = 880
FPS_MS   = 60
HOLD_MS  = 800

CURSOR_SVG = (
    "data:image/svg+xml;utf8,"
    "<svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 28 28'>"
    "<path d='M2 2 L2 22 L8 16 L12 25 L16 23 L12 14 L20 14 Z' "
    "fill='white' stroke='black' stroke-width='1.5' stroke-linejoin='round'/></svg>"
)


def install_cursor(page):
    page.evaluate(
        """(svg) => {
            const c = document.createElement('img');
            c.id = '__cur';
            c.src = svg;
            Object.assign(c.style, {
                position: 'fixed', left: '0px', top: '0px', width: '28px',
                height: '28px', zIndex: 99999, pointerEvents: 'none',
                filter: 'drop-shadow(0 1px 2px rgba(0,0,0,.4))', transition: 'none',
            });
            document.body.appendChild(c);
        }""",
        CURSOR_SVG,
    )


class Recorder:
    def __init__(self, page):
        self.page      = page
        self.frames    = []
        self.durations = []
        self.cx, self.cy = W / 2, H / 2

    def _place_cursor(self, x, y):
        self.page.mouse.move(x, y)
        self.page.evaluate(
            "([x,y]) => { const c=document.getElementById('__cur');"
            "if(c){c.style.left=x+'px'; c.style.top=y+'px';} }",
            [x, y],
        )
        self.cx, self.cy = x, y

    def snap(self, dur=FPS_MS):
        png = self.page.screenshot()
        self.frames.append(Image.open(BytesIO(png)).convert("RGB"))
        self.durations.append(dur)

    def hold(self, ms=HOLD_MS):
        if self.frames:
            self.durations[-1] += ms
        else:
            self.snap(ms)

    def glide(self, x, y, steps=14, settle=12):
        x0, y0 = self.cx, self.cy
        for i in range(1, steps + 1):
            t    = i / steps
            ease = t * t * (3 - 2 * t)
            self._place_cursor(x0 + (x - x0) * ease, y0 + (y - y0) * ease)
            self.page.wait_for_timeout(settle)
            self.snap()

    def multi_snap(self, count, delay_ms=80):
        """Capture count frames with delay — shows chart animation in motion."""
        for _ in range(count):
            self.snap(delay_ms)
            self.page.wait_for_timeout(delay_ms)


def wait_chart(page, timeout=12000):
    """Wait for Highcharts SVG root then let animation finish."""
    page.wait_for_selector(".highcharts-root", timeout=timeout)
    page.wait_for_timeout(1000)


def goto(page, url, rec):
    """Navigate, reinstall cursor, park it off-screen-ish."""
    page.goto(url)
    install_cursor(page)
    rec._place_cursor(W - 60, H - 60)


def record(base_url, out_path):
    out_path = Path(out_path)
    out_path.parent.mkdir(parents=True, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page    = browser.new_page(
            viewport={"width": W, "height": H},
            device_scale_factor=1,
        )
        rec = Recorder(page)

        # ── Act 1: Home ────────────────────────────────────────────────────────
        goto(page, base_url + "/", rec)
        wait_chart(page)
        # Capture chart animation rendering in
        rec.multi_snap(12, 80)
        rec.snap(); rec.hold(2200)

        # ── Act 2: Compare ─────────────────────────────────────────────────────
        goto(page, base_url + "/compare", rec)
        wait_chart(page)
        # Wait for second chart (initChartMax called after initChartMin)
        page.wait_for_timeout(600)
        rec.multi_snap(10, 80)
        rec.snap(); rec.hold(2200)

        # ── Act 3: Analytics ───────────────────────────────────────────────────
        goto(page, base_url + "/analytics", rec)
        # Distance matrix + group charts populate via JS on load
        page.wait_for_selector("#table-result-distance table", timeout=12000)
        page.wait_for_selector(".highcharts-root", timeout=8000)
        page.wait_for_timeout(800)
        # Glide cursor over the distance matrix to give it life
        rec.snap(); rec.hold(600)
        try:
            mx, my = page.evaluate(
                """() => { const e = document.querySelector('#table-result-distance');
                    const b = e.getBoundingClientRect();
                    return [b.x + b.width * 0.4, b.y + b.height * 0.5]; }"""
            )
            rec.glide(mx, my, steps=16)
        except Exception:
            pass
        rec.snap(); rec.hold(2400)

        # ── Act 4: Diff ────────────────────────────────────────────────────────
        goto(page, base_url + "/diff", rec)
        wait_chart(page)
        rec.snap(); rec.hold(600)

        # Click first two source checkboxes to show a diff
        checkboxes = page.query_selector_all("#source-list-sites input[type='checkbox']")
        if len(checkboxes) >= 2:
            cx0, cy0 = page.evaluate(
                """() => { const e=document.querySelector('#source-list-sites input');
                    const b=e.getBoundingClientRect();
                    return [b.x+b.width/2, b.y+b.height/2]; }"""
            )
            rec.glide(cx0, cy0, steps=12)
            checkboxes[0].click()
            page.wait_for_timeout(200)
            rec.snap()

            cx1, cy1 = page.evaluate(
                """() => { const items=document.querySelectorAll('#source-list-sites input');
                    const b=items[1].getBoundingClientRect();
                    return [b.x+b.width/2, b.y+b.height/2]; }"""
            )
            rec.glide(cx1, cy1, steps=12)
            checkboxes[1].click()
            page.wait_for_timeout(600)
            rec.multi_snap(8, 80)
        rec.snap(); rec.hold(2000)

        # ── Close loop: back to Home ───────────────────────────────────────────
        goto(page, base_url + "/", rec)
        wait_chart(page)
        rec._place_cursor(W - 60, H - 60)
        rec.snap(); rec.hold(600)

        browser.close()

    # Downscale and write looping animated WebP
    scale  = OUT_W / W
    size   = (OUT_W, round(H * scale))
    frames = [f.resize(size, Image.LANCZOS) for f in rec.frames]
    frames[0].save(
        out_path,
        save_all=True, append_images=frames[1:],
        duration=rec.durations, loop=0, quality=84, method=6,
    )
    kb      = out_path.stat().st_size / 1024
    total_s = sum(rec.durations) / 1000
    print(f"wrote {out_path} — {len(frames)} frames, {total_s:.1f}s, {kb:.0f} KB")
    return 0


def main(argv):
    if len(argv) != 2:
        print("usage: make_hero.py <base-url> <out.webp>", file=sys.stderr)
        return 1
    return record(argv[0], argv[1])


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
