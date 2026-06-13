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
import urllib.request
from io import BytesIO
from pathlib import Path

from PIL import Image
from playwright.sync_api import sync_playwright

HIGHCHARTS_CDN = "https://cdn.jsdelivr.net/npm/highcharts@12/highcharts.js"

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

    print("Fetching Highcharts …")
    with urllib.request.urlopen(HIGHCHARTS_CDN, timeout=30) as resp:
        hc_js = resp.read()
    print(f"Highcharts fetched ({len(hc_js)//1024} KB)")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page    = browser.new_page(
            viewport={"width": W, "height": H},
            device_scale_factor=1,
        )
        # Serve locally-cached Highcharts — code.highcharts.com blocked in WSL
        page.route(
            "**/code.highcharts.com/**",
            lambda route: route.fulfill(
                status=200,
                content_type="application/javascript; charset=utf-8",
                body=hc_js,
            ),
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
        # Distance matrix populates via JS; charts may not render with empty data
        page.wait_for_selector("#table-result-distance table", timeout=12000)
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

        # Click first two source labels to check checkboxes
        labels = page.locator("#source-list-sites label")
        n_labels = labels.count()
        if n_labels >= 2:
            lab0 = labels.nth(0)
            lab0.scroll_into_view_if_needed()
            b0 = lab0.bounding_box()
            if b0:
                rec.glide(b0["x"] + b0["width"] / 2, b0["y"] + b0["height"] / 2, steps=12)
            lab0.click()
            page.wait_for_timeout(200)
            rec.snap()

            lab1 = labels.nth(1)
            b1 = lab1.bounding_box()
            if b1:
                rec.glide(b1["x"] + b1["width"] / 2, b1["y"] + b1["height"] / 2, steps=12)
            lab1.click()
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
