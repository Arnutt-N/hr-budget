// Rendered-page checks for the SPA design-audit pass.
// Contrast: ground truth from computed styles (never eyeballed).
import { existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { createRequire } from 'node:module';

/** Resolve the local axe-core bundle (offline gate — no CDN fallback). */
export function resolveAxe() {
  const require = createRequire(import.meta.url);
  const entry = require.resolve('axe-core');
  const min = join(dirname(entry), 'axe.min.js');
  if (existsSync(min)) return min;
  if (existsSync(entry)) return entry;
  throw new Error('axe-core not found — run `npm i` (devDependency axe-core) first.');
}

/** Run axe WCAG 2.x A/AA rules; returns slim violation rows. */
export async function axeViolations(page, axePath) {
  await page.addScriptTag({ path: axePath });
  const res = await page.evaluate(async () => {
    // eslint-disable-next-line no-undef
    return await axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'] } });
  });
  return (res.violations || []).map((v) => ({
    id: v.id,
    impact: v.impact,
    nodes: v.nodes.length,
    targets: v.nodes.slice(0, 3).map((n) => n.target.join(' ')),
  }));
}

/** Every visible text node vs its alpha-composited background (WCAG 1.4.3). */
export function auditContrast(page, max = 10) {
  return page.evaluate((cap) => {
    const toRGBA = (s) => {
      if (!s) return null;
      let m = s.match(/color\(srgb\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)(?:\s*\/\s*([\d.]+))?\)/);
      if (m) return [+m[1] * 255, +m[2] * 255, +m[3] * 255, m[4] !== undefined ? +m[4] : 1];
      m = s.match(/[\d.]+/g);
      return m ? [+m[0], +m[1], +m[2], m[3] !== undefined ? +m[3] : 1] : null;
    };
    const lin = (c) => { c /= 255; return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4; };
    const lum = ([r, g, b]) => 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b);
    const ratio = (a, b) => { const x = lum(a), y = lum(b); return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05); };
    const comp = (fg, bg) => {
      const a = fg[3] + bg[3] * (1 - fg[3]);
      if (!a) return [0, 0, 0, 0];
      return [(fg[0] * fg[3] + bg[0] * bg[3] * (1 - fg[3])) / a, (fg[1] * fg[3] + bg[1] * bg[3] * (1 - fg[3])) / a, (fg[2] * fg[3] + bg[2] * bg[3] * (1 - fg[3])) / a, a];
    };
    function rootBg() {
      let bg = [255, 255, 255, 1];
      const html = toRGBA(getComputedStyle(document.documentElement).backgroundColor);
      const body = toRGBA(getComputedStyle(document.body).backgroundColor);
      if (html) bg = comp(html, bg);
      if (body) bg = comp(body, bg);
      return bg;
    }
    function bgOf(el) {
      // Accumulate from transparent so the early-exit only fires on a real
      // opaque layer; composite over the page background at the end.
      let bg = [0, 0, 0, 0], n = el;
      while (n && n !== document.body && n !== document.documentElement) {
        const c = toRGBA(getComputedStyle(n).backgroundColor);
        if (c) bg = comp(c, bg);
        if (bg[3] >= 1) break;
        n = n.parentElement;
      }
      return comp(bg, rootBg());
    }
    const fails = [];
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    const seen = new Set();
    let n;
    while ((n = walker.nextNode())) {
      const text = n.nodeValue.trim();
      if (!text || text.length < 2) continue;
      const el = n.parentElement;
      if (!el || seen.has(el)) continue;
      seen.add(el);
      const r = el.getBoundingClientRect();
      if (r.width < 2 || r.height < 2 || r.bottom < 0 || r.top > innerHeight + 400) continue;
      const st = getComputedStyle(el);
      if (st.visibility === 'hidden' || st.display === 'none' || st.opacity === '0') continue;
      const fg = toRGBA(st.color);
      if (!fg || fg[3] < 0.5) continue;
      const bg = bgOf(el);
      const rt = ratio([fg[0], fg[1], fg[2]], [bg[0], bg[1], bg[2]]);
      const fs = parseFloat(st.fontSize);
      const bold = parseInt(st.fontWeight) >= 700 || st.fontWeight === 'bold';
      const need = fs >= 24 || (fs >= 18.66 && bold) ? 3.0 : 4.5;
      if (rt < need) {
        const cls = typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/).slice(0, 3).join('.') : '';
        fails.push({ text: text.slice(0, 60), sel: (el.tagName.toLowerCase() + cls).slice(0, 90), ratio: +rt.toFixed(2), need, fg: st.color, fs });
      }
      if (fails.length >= cap) break;
    }
    return fails;
  }, max);
}

/** Focus a sample of controls; report those with no outline/box-shadow change. */
export function auditFocus(page, max = 40) {
  return page.evaluate((cap) => {
    const els = [...document.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])')]
      .filter((e) => { const r = e.getBoundingClientRect(); return r.width > 1 && r.height > 1 && getComputedStyle(e).visibility !== 'hidden'; });
    const bad = [];
    for (const el of els.slice(0, cap)) {
      el.focus({ preventScroll: true });
      const st = getComputedStyle(el);
      const ring = st.outlineStyle !== 'none' && parseFloat(st.outlineWidth) > 0;
      if (!ring && st.boxShadow === 'none') {
        const label = (el.getAttribute('aria-label') || el.textContent || el.name || el.type || el.tagName).trim().slice(0, 40);
        bad.push(`${el.tagName.toLowerCase()}${el.id ? '#' + el.id : ''} "${label}"`);
      }
      el.blur();
    }
    // NOTE: PrimeVue renders focus rings on wrapper boxes, not the sr-only input —
    // verify any checkbox/radio hit manually before filing.
    return { checked: Math.min(els.length, cap), total: els.length, noIndicator: [...new Set(bad)].slice(0, 12) };
  }, max);
}

/** Horizontal overflow in px at the CURRENT viewport (caller sets 360 first). */
export function overflowX(page) {
  return page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
}
