/*
 * Plaudit embed widget. Usage:
 *   <div data-plaudit="your-project" data-layout="grid|carousel" data-theme="auto|light|dark" data-limit="9"></div>
 *   <script src="https://your-plaudit-site/widget.js" async></script>
 * Renders inside a Shadow DOM so the host page's CSS can't break it (and ours can't leak out).
 * All customer text is set with textContent: never innerHTML, since strangers wrote it.
 */
(function () {
    var script = document.currentScript;
    if (!script) return;
    var origin = new URL(script.src).origin;

    var CSS = [
        ':host{display:block;--brand:#4f46e5;--card:#fff;--text:#1d1d27;--muted:#6b6b7b;--line:#e3e3ea;--star:#f5a524}',
        // The host page can still style the host element itself, so text styles go on our own wrapper.
        '.plaudit{all:initial;display:block;font:15px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--text);text-align:left}',
        ':host([data-theme=dark]){--card:#1c1c25;--text:#ececf1;--muted:#9d9daf;--line:#2e2e3a}',
        '@media (prefers-color-scheme:dark){:host([data-theme=auto]){--card:#1c1c25;--text:#ececf1;--muted:#9d9daf;--line:#2e2e3a}}',
        '*{box-sizing:border-box}',
        '.grid{columns:3 260px;column-gap:16px}',
        '.grid .card{break-inside:avoid;margin:0 0 16px}',
        '.track{display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;padding:2px 2px 8px;scrollbar-width:none}',
        '.track::-webkit-scrollbar{display:none}',
        '.track .card{flex:0 0 min(320px,85%);scroll-snap-align:start;margin:0}',
        '.card{background:var(--card);border:1px solid var(--line);border-top:3px solid var(--brand);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px}',
        '.stars{color:var(--star);letter-spacing:.1em}',
        'blockquote{margin:0;white-space:pre-line;overflow-wrap:anywhere;flex:1}',
        'figcaption{display:flex;align-items:center;gap:10px}',
        '.avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;flex:none;display:grid;place-items:center;font-weight:700;color:var(--brand);background:color-mix(in srgb,var(--brand) 16%,var(--card))}',
        '.title{color:var(--muted);font-size:.85em}',
        '.nav{display:flex;justify-content:flex-end;gap:8px;margin-top:8px}',
        'button{font:inherit;width:36px;height:36px;border-radius:50%;border:1px solid var(--line);background:var(--card);color:var(--text);cursor:pointer}',
        'button:focus-visible,.track:focus-visible{outline:3px solid color-mix(in srgb,var(--brand) 50%,transparent);outline-offset:2px}',
        '.foot{margin-top:8px;font-size:.8em;color:var(--muted);text-align:right}',
        '.foot a{color:inherit}'
    ].join('');

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text != null) node.textContent = text;
        return node;
    }

    function card(t) {
        var fig = el('figure', 'card');
        fig.style.margin = '0';
        var rating = Math.max(0, Math.min(5, parseInt(t.rating, 10) || 0));
        if (rating) {
            var stars = el('div', 'stars', '★★★★★'.slice(0, rating));
            stars.setAttribute('role', 'img');
            stars.setAttribute('aria-label', rating + ' out of 5 stars');
            fig.appendChild(stars);
        }
        fig.appendChild(el('blockquote', null, t.body));

        var cap = el('figcaption');
        if (t.avatar_url && /^https?:\/\//.test(t.avatar_url)) {
            var img = el('img', 'avatar');
            img.src = t.avatar_url;
            img.alt = '';
            img.loading = 'lazy';
            cap.appendChild(img);
        } else {
            var initial = el('span', 'avatar', (t.name || '?').charAt(0));
            initial.setAttribute('aria-hidden', 'true');
            cap.appendChild(initial);
        }
        var who = el('span');
        who.appendChild(el('strong', null, t.name));
        if (t.title) {
            who.appendChild(document.createElement('br'));
            who.appendChild(el('span', 'title', t.title));
        }
        cap.appendChild(who);
        fig.appendChild(cap);
        return fig;
    }

    function render(host, data) {
        var layout = host.getAttribute('data-layout') === 'carousel' ? 'carousel' : 'grid';
        var limit = parseInt(host.getAttribute('data-limit'), 10) || 9;
        var theme = host.getAttribute('data-theme');
        host.setAttribute('data-theme', theme === 'light' || theme === 'dark' ? theme : 'auto');

        var root = host.shadowRoot || host.attachShadow({ mode: 'open' });
        root.textContent = '';
        var style = el('style', null, CSS);
        root.appendChild(style);
        if (/^#[0-9a-f]{6}$/i.test(data.brand_color)) host.style.setProperty('--brand', data.brand_color);

        var items = (data.testimonials || []).slice(0, limit);
        if (!items.length) return;

        var wrap = el('section', 'plaudit');
        wrap.setAttribute('aria-label', 'Testimonials for ' + data.name);
        var list = el('div', layout === 'carousel' ? 'track' : 'grid');
        items.forEach(function (t) { list.appendChild(card(t)); });
        wrap.appendChild(list);

        if (layout === 'carousel' && items.length > 1) {
            list.tabIndex = 0;
            var nav = el('div', 'nav');
            [['‹', 'Previous testimonials', -1], ['›', 'Next testimonials', 1]].forEach(function (b) {
                var btn = el('button', null, b[0]);
                btn.type = 'button';
                btn.setAttribute('aria-label', b[1]);
                btn.addEventListener('click', function () { list.scrollBy({ left: b[2] * list.clientWidth * 0.9 }); });
                nav.appendChild(btn);
            });
            wrap.appendChild(nav);
        }

        var foot = el('div', 'foot');
        var link = el('a', null, 'See all on our wall of love');
        if (/^https?:\/\//.test(data.wall_url)) link.href = data.wall_url;
        link.target = '_blank';
        link.rel = 'noopener';
        foot.appendChild(link);
        wrap.appendChild(foot);
        root.appendChild(wrap);
    }

    function load(host) {
        if (host.__plaudit) return;
        host.__plaudit = true;
        var slug = host.getAttribute('data-plaudit');
        fetch(origin + '/api/walls/' + encodeURIComponent(slug))
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (data) { render(host, data); })
            .catch(function (e) { console.warn('Plaudit: could not load testimonials for "' + slug + '"', e); });
    }

    function init() { document.querySelectorAll('[data-plaudit]').forEach(load); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
