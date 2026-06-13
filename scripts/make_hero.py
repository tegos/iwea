"""Record a looping hero animation of iWea for the README.

Visits Home → Compare → Diff (interactive checkboxes) → Home.
Requires Docker running with real weather data.

Usage:
    source /mnt/d/work/projects/parts-diagram-ocr/.venv/bin/activate
    docker compose up -d   (from the iwea project root)
    python scripts/make_hero.py http://localhost:8080 assets/hero.webp
"""
import sys
import urllib.request
from io import BytesIO
from pathlib import Path

from PIL import Image
from playwright.sync_api import sync_playwright

HIGHCHARTS_CDN = "https://cdn.jsdelivr.net/npm/highcharts@12/highcharts.js"

W, H   = 1280, 720
OUT_W  = 720
FPS_MS = 80

CURSOR_SVG = (
    "data:image/svg+xml;utf8,"
    "<svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 28 28'>"
    "<path d='M2 2 L2 22 L8 16 L12 25 L16 23 L12 14 L20 14 Z' "
    "fill='white' stroke='black' stroke-width='1.5' stroke-linejoin='round'/></svg>"
)


def install_cursor(page):
    page.evaluate(
        """(svg) => {
            document.getElementById('__cur')?.remove();
            const c = document.createElement('img');
            c.id = '__cur';
            c.src = svg;
            Object.assign(c.style, {
                position: 'fixed', left: '0px', top: '0px', width: '28px',
                height: '28px', zIndex: 99999, pointerEvents: 'none',
                filter: 'drop-shadow(0 1px 2px rgba(0,0,0,.5))', transition: 'none',
            });
            document.body.appendChild(c);
        }""",
        CURSOR_SVG,
    )


class Recorder:
    def __init__(self, page):
        self.page = page
        self.frames = []
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

    def hold(self, ms):
        if self.frames:
            self.durations[-1] += ms

    def glide(self, x, y, steps=16, settle=14):
        x0, y0 = self.cx, self.cy
        for i in range(1, steps + 1):
            t = i / steps
            ease = t * t * (3 - 2 * t)
            self._place_cursor(x0 + (x - x0) * ease, y0 + (y - y0) * ease)
            self.page.wait_for_timeout(settle)
            self.snap()


def scroll_to(page, selector):
    page.evaluate(
        "(sel) => { const e = document.querySelector(sel);"
        "if (e) e.scrollIntoView({block:'center', behavior:'instant'}); }",
        selector,
    )
    page.wait_for_timeout(80)


def scroll_top(page):
    page.evaluate("window.scrollTo(0, 0)")
    page.wait_for_timeout(80)


def trace_chart(page, rec, container_id, steps=18, y_frac=0.45):
    """Glide cursor L→R across a Highcharts chart to trigger crosshair + tooltip."""
    scroll_to(page, f"#{container_id}")
    page.wait_for_timeout(200)
    bbox = page.locator(f"#{container_id}").bounding_box()
    if not bbox:
        return
    # Target the plot area (avoid axes on edges)
    x0 = bbox["x"] + bbox["width"] * 0.10
    x1 = bbox["x"] + bbox["width"] * 0.88
    y  = bbox["y"] + bbox["height"] * y_frac
    rec.glide(x0, y, steps=5)          # approach from current position
    rec.hold(200)
    x_cur = x0
    dx = (x1 - x0) / steps
    for _ in range(steps):
        x_cur += dx
        rec._place_cursor(x_cur, y)
        page.wait_for_timeout(22)
        rec.snap()
    rec.hold(500)
    # Sweep back quickly
    rec.glide(x0 + (x1 - x0) * 0.3, y, steps=7, settle=10)


def wait_chart(page, timeout=14000):
    page.wait_for_selector(".highcharts-root", timeout=timeout)
    page.wait_for_timeout(1200)


def goto(page, url, rec, scroll_reset=True):
    page.goto(url)
    if scroll_reset:
        scroll_top(page)
    install_cursor(page)
    rec._place_cursor(W * 0.75, H * 0.12)


def record(base_url, out_path):
    out_path = Path(out_path)
    out_path.parent.mkdir(parents=True, exist_ok=True)

    print("Fetching Highcharts …")
    with urllib.request.urlopen(HIGHCHARTS_CDN, timeout=30) as resp:
        hc_js = resp.read()
    print(f"Highcharts fetched ({len(hc_js)//1024} KB)")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": W, "height": H}, device_scale_factor=1)

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

        # ── Act 1: Home — forecast widget then chart hover ─────────────────────
        goto(page, base_url + "/", rec)
        wait_chart(page)
        # Show header + forecast widget
        scroll_top(page)
        rec.snap(); rec.hold(1000)
        # Glide cursor over the 7-day forecast cards
        card_y = page.evaluate(
            "() => { const e = document.querySelector('.forecast-container');"
            "return e ? e.getBoundingClientRect().top + 60 : 380; }"
        )
        rec.glide(W * 0.25, card_y, steps=8)
        rec.hold(200)
        rec.glide(W * 0.72, card_y, steps=12, settle=18)
        rec.hold(300)
        # Scroll to chart and hover for tooltip
        trace_chart(page, rec, "container-chart-min")
        rec.hold(1000)

        # ── Act 2: Compare — two charts, hover both ────────────────────────────
        goto(page, base_url + "/compare", rec)
        wait_chart(page)
        page.wait_for_timeout(400)   # let initChartMax fire
        scroll_to(page, "#container-chart-min")
        rec.snap(); rec.hold(600)
        trace_chart(page, rec, "container-chart-min", steps=18)
        rec.hold(200)
        trace_chart(page, rec, "container-chart-max", steps=16)
        rec.hold(1000)

        # ── Act 3: Diff — click two checkboxes, watch chart animate in ─────────
        goto(page, base_url + "/diff", rec)
        wait_chart(page)
        scroll_top(page)
        rec.snap(); rec.hold(800)

        labels = page.locator("#source-list-sites label")
        if labels.count() >= 2:
            lab0 = labels.nth(0)
            lab0.scroll_into_view_if_needed()
            b0 = lab0.bounding_box()
            if b0:
                rec.glide(b0["x"] + b0["width"] / 2, b0["y"] + b0["height"] / 2, steps=10)
            rec.hold(200)
            lab0.click()
            page.wait_for_timeout(120)
            rec.snap(); rec.hold(250)

            lab1 = labels.nth(1)
            b1 = lab1.bounding_box()
            if b1:
                rec.glide(b1["x"] + b1["width"] / 2, b1["y"] + b1["height"] / 2, steps=10)
            rec.hold(150)
            lab1.click()
            # Capture chart drawing animation
            page.wait_for_timeout(180)
            for _ in range(10):
                rec.snap(65)
                page.wait_for_timeout(65)
            rec.hold(300)
            # Hover over diff chart
            trace_chart(page, rec, "container-chart-diff", steps=20)
        rec.hold(1400)

        # ── Close loop: Home again ─────────────────────────────────────────────
        goto(page, base_url + "/", rec)
        wait_chart(page)
        scroll_top(page)
        rec.snap(); rec.hold(600)

        browser.close()

    scale  = OUT_W / W
    size   = (OUT_W, round(H * scale))
    frames = [f.resize(size, Image.LANCZOS) for f in rec.frames]
    frames[0].save(
        out_path,
        save_all=True, append_images=frames[1:],
        duration=rec.durations, loop=0, quality=75, method=6,
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
