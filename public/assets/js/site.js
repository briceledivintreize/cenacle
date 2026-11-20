/* ==========================================================================
   Le Cénacle — interactions et animations du site public
   GSAP + ScrollTrigger + SplitText, défilement doux (Lenis),
   scènes WebGL (Three.js, chargé à la demande).
   Respecte « prefers-reduced-motion » : tout le contenu reste visible sans JS.
   ========================================================================== */

const html = document.documentElement;
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(pointer: fine)').matches;
const isMobile = window.matchMedia('(max-width: 960px)').matches;
const { gsap, ScrollTrigger, SplitText } = window;
const PALETTE = { vert: '#50A028', jaune: '#F0F000', rouge: '#C81414', noir: '#000000' };

let lenis = null;

/* ---------------------------------------------------------------- utilitaires */
const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

function onVisible(el, cb, opts = { rootMargin: '200px' }) {
  const io = new IntersectionObserver((entries) => entries.forEach((e) => cb(e.isIntersecting)), opts);
  io.observe(el);
  return io;
}

/* ================================================================ Menu mobile */
function initMobileMenu() {
  const btn = $('.burger');
  const menu = $('#menu-mobile');
  if (!btn || !menu) return;
  const links = $$('a', menu);
  const open = () => {
    menu.hidden = false;
    btn.setAttribute('aria-expanded', 'true');
    btn.setAttribute('aria-label', 'Fermer le menu');
    document.body.classList.add('menu-open');
    lenis?.stop();
    if (!reduced && gsap) {
      gsap.fromTo(menu, { clipPath: 'circle(0% at 100% 0%)' }, { clipPath: 'circle(150% at 100% 0%)', duration: 0.8, ease: 'power3.inOut' });
      gsap.fromTo(links, { y: 40, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.05, delay: 0.25, duration: 0.6, ease: 'power3.out' });
    }
    links[0]?.focus();
  };
  const close = () => {
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Ouvrir le menu');
    document.body.classList.remove('menu-open');
    lenis?.start();
    if (!reduced && gsap) {
      gsap.to(menu, { clipPath: 'circle(0% at 100% 0%)', duration: 0.5, ease: 'power3.in', onComplete: () => { menu.hidden = true; } });
    } else {
      menu.hidden = true;
    }
  };
  btn.addEventListener('click', () => (btn.getAttribute('aria-expanded') === 'true' ? close() : open()));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') { close(); btn.focus(); }
  });
  window.addEventListener('resize', () => { if (window.innerWidth > 960 && !menu.hidden) close(); });
}

/* ================================================================ Visionneuse */
function initLightbox() {
  const box = $('.lightbox');
  const items = $$('[data-lightbox]');
  if (!box || !items.length) return;
  const stage = $('.lightbox__stage', box);
  const caption = $('.lightbox__caption', box);
  let index = 0;
  let lastFocus = null;

  const show = (i) => {
    index = (i + items.length) % items.length;
    const it = items[index];
    const type = it.dataset.lightbox;
    const src = it.dataset.src;
    stage.innerHTML = '';
    let node;
    if (type === 'image') {
      node = new Image();
      node.src = src;
      node.alt = it.dataset.caption || '';
    } else if (type === 'video') {
      node = document.createElement('video');
      node.src = src; node.controls = true; node.autoplay = true; node.playsInline = true;
    } else {
      node = document.createElement('iframe');
      node.src = src; node.allow = 'autoplay; fullscreen; picture-in-picture'; node.allowFullscreen = true;
      node.title = it.dataset.caption || 'Vidéo';
    }
    stage.appendChild(node);
    caption.textContent = it.dataset.caption || '';
    if (!reduced && gsap) gsap.fromTo(node, { opacity: 0, scale: 0.94 }, { opacity: 1, scale: 1, duration: 0.5, ease: 'power3.out' });
  };
  const open = (i) => {
    lastFocus = document.activeElement;
    box.hidden = false;
    lenis?.stop();
    document.body.style.overflow = 'hidden';
    if (!reduced && gsap) gsap.fromTo(box, { opacity: 0 }, { opacity: 1, duration: 0.35 });
    show(i);
    $('.lightbox__close', box).focus();
  };
  const close = () => {
    stage.innerHTML = '';
    box.hidden = true;
    lenis?.start();
    document.body.style.overflow = '';
    lastFocus?.focus();
  };
  items.forEach((it, i) => it.addEventListener('click', () => open(i)));
  $('.lightbox__close', box).addEventListener('click', close);
  $('.lightbox__nav--prev', box).addEventListener('click', () => show(index - 1));
  $('.lightbox__nav--next', box).addEventListener('click', () => show(index + 1));
  box.addEventListener('click', (e) => { if (e.target === box || e.target === stage) close(); });
  document.addEventListener('keydown', (e) => {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') show(index - 1);
    if (e.key === 'ArrowRight') show(index + 1);
  });
}

/* ======================================================= Champ de points 2D */
/* Canvas léger pour les en-têtes de pages, le bloc vert et la bande sombre. */
function initInkCanvases() {
  $$('canvas[data-ink]').forEach((canvas) => {
    const dark = canvas.dataset.ink === 'dark';
    const ctx = canvas.getContext('2d');
    const colors = dark ? [PALETTE.jaune, PALETTE.vert, '#ff4a3d', '#ffffff'] : [PALETTE.vert, '#D6D600', PALETTE.rouge, PALETTE.noir];
    let w = 0, h = 0, dpr = Math.min(window.devicePixelRatio || 1, 2), dots = [], running = false, raf = 0;
    const mouse = { x: -9999, y: -9999 };
    const resize = () => {
      const r = canvas.getBoundingClientRect();
      w = r.width; h = r.height;
      canvas.width = w * dpr; canvas.height = h * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      const n = Math.round(Math.min(70, (w * h) / 16000));
      dots = Array.from({ length: n }, () => ({
        x: Math.random() * w, y: Math.random() * h,
        r: 1.2 + Math.random() * 3.2,
        vx: (Math.random() - 0.5) * 0.25, vy: -0.08 - Math.random() * 0.25,
        c: colors[Math.floor(Math.random() * colors.length)],
        a: 0.25 + Math.random() * 0.55,
      }));
    };
    const draw = () => {
      ctx.clearRect(0, 0, w, h);
      for (const d of dots) {
        const dx = d.x - mouse.x, dy = d.y - mouse.y, dist = Math.hypot(dx, dy);
        if (dist < 120) { d.x += (dx / dist) * 1.4; d.y += (dy / dist) * 1.4; }
        d.x += d.vx; d.y += d.vy;
        if (d.y < -10) { d.y = h + 10; d.x = Math.random() * w; }
        if (d.x < -10) d.x = w + 10; if (d.x > w + 10) d.x = -10;
        ctx.globalAlpha = d.a;
        ctx.fillStyle = d.c;
        ctx.beginPath(); ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2); ctx.fill();
      }
      // fils d'encre entre points proches
      ctx.globalAlpha = dark ? 0.12 : 0.07;
      ctx.strokeStyle = dark ? '#ffffff' : '#000000';
      ctx.lineWidth = 1;
      for (let i = 0; i < dots.length; i++) {
        for (let j = i + 1; j < dots.length; j++) {
          const a = dots[i], b = dots[j];
          if (Math.abs(a.x - b.x) < 110 && Math.abs(a.y - b.y) < 110 && Math.hypot(a.x - b.x, a.y - b.y) < 110) {
            ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
          }
        }
      }
      if (running) raf = requestAnimationFrame(draw);
    };
    resize();
    new ResizeObserver(resize).observe(canvas);
    const host = canvas.parentElement;
    host.addEventListener('pointermove', (e) => { const r = canvas.getBoundingClientRect(); mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top; });
    host.addEventListener('pointerleave', () => { mouse.x = mouse.y = -9999; });
    onVisible(canvas, (vis) => {
      if (vis && !running) { running = true; draw(); }
      if (!vis) { running = false; cancelAnimationFrame(raf); }
    }, { rootMargin: '0px' });
  });
}

/* ================================================================ Animations */
function initSmoothScroll() {
  if (!window.Lenis || isMobile) return;
  lenis = new window.Lenis({ duration: 1.15, smoothWheel: true });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((t) => lenis.raf(t * 1000));
  gsap.ticker.lagSmoothing(0);

  // Ancres internes
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href*="#"]');
    if (!a) return;
    const url = new URL(a.href, location.href);
    if (url.pathname !== location.pathname || !url.hash) return;
    const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
    if (!target) return;
    e.preventDefault();
    lenis.scrollTo(target, { offset: -90 });
    history.replaceState(null, '', url.hash);
  });
  if (location.hash) {
    const t = document.getElementById(decodeURIComponent(location.hash.slice(1)));
    if (t) setTimeout(() => lenis.scrollTo(t, { offset: -90, immediate: true }), 50);
  }
}

function initHeader() {
  const header = $('.site-header');
  const bar = $('.scroll-progress');
  let last = 0;
  ScrollTrigger.create({
    start: 0, end: 'max',
    onUpdate: (self) => {
      const y = self.scroll();
      header.classList.toggle('is-scrolled', y > 10);
      header.classList.toggle('is-hidden', y > 300 && y > last && !document.body.classList.contains('menu-open'));
      last = y;
      if (bar) bar.style.transform = `scaleX(${self.progress})`;
    },
  });
}

function initPageTransitions() {
  const panels = $$('.transition__panel');
  if (!panels.length) return;
  const [vert, jaune, rouge, ink] = panels;
  gsap.set(panels, { y: 0, yPercent: 100 });
  // Entrée : la page précédente a laissé un rideau, on le lève.
  if (html.classList.contains('is-entering')) {
    gsap.set(panels, { yPercent: 0 });
    const tl = gsap.timeline({ onComplete: () => html.classList.remove('is-entering') });
    tl.to(ink, { yPercent: -100, duration: 0.7, ease: 'power4.inOut' })
      .to([rouge, jaune, vert], { yPercent: -100, duration: 0.7, stagger: 0.07, ease: 'power4.inOut' }, '-=0.45');
  }
  // Sortie : on ferme le rideau avant de changer de page.
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (a.target === '_blank' || a.hasAttribute('download')) return;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin || /\/admin(\/|$)/.test(url.pathname)) return;
    if (url.pathname === location.pathname && url.search === location.search) return;
    if (!/^https?:$/.test(url.protocol)) return;
    e.preventDefault();
    try { sessionStorage.setItem('cenacle-nav', '1'); } catch (_) { /* navigation privée */ }
    gsap.timeline({ onComplete: () => { location.href = url.href; } })
      .fromTo([vert, jaune, rouge], { yPercent: 100 }, { yPercent: 0, duration: 0.55, stagger: 0.07, ease: 'power4.inOut' })
      .fromTo(ink, { yPercent: 100 }, { yPercent: 0, duration: 0.5, ease: 'power4.inOut' }, '-=0.3');
  });
  // Retour arrière (cache navigateur) : on s'assure que le rideau est levé.
  window.addEventListener('pageshow', (e) => {
    if (e.persisted) gsap.set(panels, { yPercent: 100 });
  });
}

function splitHeading(el) {
  const type = el.dataset.split === 'chars' ? 'chars' : 'words';
  gsap.set(el, { visibility: 'visible' });
  SplitText.create(el, {
    type: type === 'chars' ? 'lines,words,chars' : 'lines,words',
    mask: 'lines',
    linesClass: 'split-line',
    autoSplit: true,
    onSplit(self) {
      return gsap.from(type === 'chars' ? self.chars : self.words, {
        yPercent: 115, rotate: type === 'chars' ? 0 : 3,
        duration: type === 'chars' ? 0.9 : 1,
        stagger: type === 'chars' ? 0.025 : 0.06,
        ease: 'power4.out',
        scrollTrigger: { trigger: el, start: 'top 88%', once: true },
      });
    },
  });
}

function initReveals() {
  $$('[data-split]').forEach(splitHeading);

  ScrollTrigger.batch('[data-reveal]', {
    start: 'top 90%',
    once: true,
    onEnter: (els) => gsap.fromTo(els, { y: 46, opacity: 0 }, { y: 0, opacity: 1, duration: 1, stagger: 0.09, ease: 'power3.out', overwrite: true }),
  });
  // Sécurité : éléments déjà au-dessus du viewport (ancre, rechargement en bas de page)
  ScrollTrigger.addEventListener('refresh', () => {
    $$('[data-reveal]').forEach((el) => {
      if (el.getBoundingClientRect().bottom < 0) gsap.set(el, { opacity: 1, y: 0 });
    });
  });

  $$('[data-float]').forEach((el, i) => {
    gsap.to(el, { y: gsap.utils.random(-26, 26), x: gsap.utils.random(-18, 18), duration: gsap.utils.random(3, 5.5), repeat: -1, yoyo: true, ease: 'sine.inOut', delay: i * 0.3 });
  });

  $$('[data-parallax] img').forEach((img) => {
    gsap.fromTo(img, { yPercent: -10 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: img.closest('[data-parallax]'), start: 'top bottom', end: 'bottom top', scrub: true } });
  });

  $$('[data-year]').forEach((el) => {
    gsap.from(el, { xPercent: -8, opacity: 0, duration: 1.2, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 85%', once: true } });
  });
}

function parseCounter(raw) {
  const m = String(raw).match(/^(\D*)([\d\s.,]+)(.*)$/);
  if (!m) return null;
  const n = parseFloat(m[2].replace(/\s/g, '').replace(',', '.'));
  return Number.isFinite(n) ? { prefix: m[1], n, suffix: m[3] } : null;
}

function initCounters() {
  $$('[data-counter]').forEach((el) => {
    const c = parseCounter(el.dataset.counter);
    if (!c) return;
    const from = c.n >= 1000 ? c.n - 40 : 0;
    const obj = { v: from };
    el.textContent = c.prefix + from + c.suffix;
    gsap.to(obj, {
      v: c.n, duration: c.n >= 1000 ? 1.8 : 1.6, ease: 'power2.out',
      scrollTrigger: { trigger: el, start: 'top 92%', once: true },
      onUpdate: () => { el.textContent = c.prefix + Math.round(obj.v) + c.suffix; },
    });
  });
}

function initHero() {
  const hero = $('[data-hero]');
  if (!hero) return;
  const title = $('[data-hero-title]', hero);
  const media = $('[data-hero-media]', hero);
  const frame = $('[data-blob]', hero);
  const accent = $('.accent', title);
  gsap.set(title, { visibility: 'visible' });
  const split = SplitText.create(title, { type: 'lines,words,chars', mask: 'lines', linesClass: 'split-line' });

  const tl = gsap.timeline({ defaults: { ease: 'power4.out' }, delay: html.classList.contains('is-entering') ? 0.7 : 0.15 });
  tl.from(split.chars, { yPercent: 120, duration: 1.1, stagger: 0.018 })
    .fromTo(accent, { '--hl': 0 }, { '--hl': 1, duration: 0.9, ease: 'power3.inOut' }, '-=0.4')
    .fromTo(media, { opacity: 0, scale: 0.86, rotate: -4 }, { opacity: 1, scale: 1, rotate: 0, duration: 1.4 }, 0.2)
    .fromTo($$('[data-hero-in]', hero), { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.9, stagger: 0.1 }, 0.5)
    .fromTo($$('[data-badge]', hero), { scale: 0.6, opacity: 0, y: 20 }, { scale: 1, opacity: 1, y: 0, duration: 0.8, stagger: 0.12, ease: 'back.out(1.8)' }, 1)
    .from('[data-feather]', { y: -120, rotate: -40, opacity: 0, duration: 1.6, ease: 'elastic.out(1, 0.6)' }, 1.1);

  // Contour organique qui « respire »
  const shapes = [
    '50% 50% 46% 54% / 54% 46% 54% 46%',
    '58% 42% 52% 48% / 46% 56% 44% 54%',
    '44% 56% 40% 60% / 58% 42% 58% 42%',
    '52% 48% 58% 42% / 50% 54% 46% 50%',
  ];
  const morph = gsap.timeline({ repeat: -1, yoyo: true, defaults: { duration: 4, ease: 'sine.inOut' } });
  shapes.slice(1).forEach((s) => morph.to(frame, { borderRadius: s }));
  gsap.to('.hero__blob-bg', { rotate: 360, duration: 60, repeat: -1, ease: 'none' });
  gsap.to('[data-feather]', { rotate: 18, y: -10, duration: 3, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 2.7 });
  $$('[data-badge]', hero).forEach((b, i) => gsap.to(b, { y: i ? 10 : -10, duration: 2.6 + i, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 2 }));

  // Parallaxe à la souris
  if (finePointer) {
    const qx = gsap.quickTo(media, 'x', { duration: 1, ease: 'power3' });
    const qy = gsap.quickTo(media, 'y', { duration: 1, ease: 'power3' });
    hero.addEventListener('pointermove', (e) => {
      const r = hero.getBoundingClientRect();
      qx(((e.clientX - r.left) / r.width - 0.5) * 24);
      qy(((e.clientY - r.top) / r.height - 0.5) * 24);
    });
  }
  // Sortie au défilement
  gsap.to('.hero__inner', { yPercent: 12, opacity: 0.2, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
}

function initMarquees() {
  $$('.marquee__track').forEach((track) => {
    const loop = gsap.to(track, { xPercent: -50, duration: 38, ease: 'none', repeat: -1 });
    ScrollTrigger.create({
      trigger: track, start: 'top bottom', end: 'bottom top',
      onUpdate: (self) => {
        const v = self.getVelocity() / 600;
        gsap.to(loop, { timeScale: (self.direction || 1) * Math.max(1, Math.min(5, Math.abs(v))), duration: 0.3, overwrite: true });
        gsap.to(loop, { timeScale: self.direction || 1, duration: 1.2, delay: 0.3 });
      },
    });
  });
}

function initHorizontalHistory() {
  const section = $('[data-hscroll]');
  if (!section) return;
  const track = $('[data-hscroll-track]', section);
  const progress = $('[data-hscroll-progress]', section);
  const mm = gsap.matchMedia();
  mm.add('(min-width: 961px)', () => {
    const distance = () => Math.max(0, track.scrollWidth - window.innerWidth);
    const tween = gsap.to(track, {
      x: () => -distance(), ease: 'none',
      scrollTrigger: {
        trigger: section, start: 'top top', end: () => '+=' + distance(), pin: true, scrub: 0.8, invalidateOnRefresh: true, anticipatePin: 1,
        onUpdate: (self) => gsap.set(progress, { scaleX: self.progress }),
      },
    });
    $$('.milestone', track).forEach((card) => {
      gsap.from($$('h3, p, .milestone__date', card), {
        y: 40, opacity: 0, stagger: 0.08, duration: 0.8, ease: 'power3.out',
        scrollTrigger: { trigger: card, containerAnimation: tween, start: 'left 85%', once: true },
      });
      gsap.fromTo($('.milestone__year', card), { xPercent: 30 }, {
        xPercent: -10, ease: 'none',
        scrollTrigger: { trigger: card, containerAnimation: tween, start: 'left right', end: 'right left', scrub: true },
      });
    });
  });
  mm.add('(max-width: 960px)', () => {
    $('.hscroll__viewport', section).style.overflowX = 'auto';
    gsap.set(progress, { scaleX: 1 });
  });
}

function initTimelinePage() {
  const tl = $('[data-timeline]');
  if (!tl) return;
  const fill = $('[data-timeline-fill]', tl);
  gsap.fromTo(fill, { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: tl, start: 'top 60%', end: 'bottom 60%', scrub: true } });
  $$('[data-tl-item]', tl).forEach((item) => {
    const fromLeft = item.classList.contains('tl-item--left') && window.innerWidth > 960;
    gsap.fromTo(item, { opacity: 0, x: fromLeft ? -60 : 60 }, { opacity: 1, x: 0, duration: 1, ease: 'power3.out', scrollTrigger: { trigger: item, start: 'top 85%', once: true } });
    gsap.from($('.tl-item__dot', item), { scale: 0, duration: 0.6, ease: 'back.out(3)', scrollTrigger: { trigger: item, start: 'top 75%', once: true } });
  });
}

function initQuotes() {
  $$('[data-quote]').forEach((p) => {
    const split = SplitText.create(p, { type: 'words', wordsClass: 'word' });
    gsap.fromTo(split.words, { opacity: 0.12 }, {
      opacity: 1, stagger: 0.05, ease: 'none',
      scrollTrigger: { trigger: p, start: 'top 80%', end: 'bottom 45%', scrub: true },
    });
  });
  const mark = $('.quote__mark');
  if (mark) gsap.fromTo(mark, { yPercent: -10, rotate: -12 }, { yPercent: 12, rotate: 8, ease: 'none', scrollTrigger: { trigger: mark.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } });
}

function initJoinBlocks() {
  $$('[data-join]').forEach((block) => {
    gsap.fromTo(block, { clipPath: 'inset(12% 10% 12% 10% round 36px)' }, {
      clipPath: 'inset(0% 0% 0% 0% round 36px)', ease: 'power2.out',
      scrollTrigger: { trigger: block, start: 'top 95%', end: 'top 45%', scrub: true },
    });
  });
}

function initLegacy() {
  const cards = $$('.legacy-card');
  if (!cards.length) return;
  gsap.from(cards, { y: 80, opacity: 0, duration: 1, stagger: 0.12, ease: 'power3.out', scrollTrigger: { trigger: '.legacy-grid', start: 'top 85%', once: true } });
}

function initTiltAndMagnet() {
  if (!finePointer) return;
  $$('[data-tilt]').forEach((card) => {
    const rx = gsap.quickTo(card, 'rotationX', { duration: 0.6, ease: 'power3' });
    const ry = gsap.quickTo(card, 'rotationY', { duration: 0.6, ease: 'power3' });
    gsap.set(card, { transformPerspective: 900 });
    card.addEventListener('pointermove', (e) => {
      const r = card.getBoundingClientRect();
      ry(((e.clientX - r.left) / r.width - 0.5) * 8);
      rx(-((e.clientY - r.top) / r.height - 0.5) * 8);
    });
    card.addEventListener('pointerleave', () => { rx(0); ry(0); });
  });
  $$('[data-magnetic]').forEach((btn) => {
    const qx = gsap.quickTo(btn, 'x', { duration: 0.5, ease: 'power3' });
    const qy = gsap.quickTo(btn, 'y', { duration: 0.5, ease: 'power3' });
    btn.addEventListener('pointermove', (e) => {
      const r = btn.getBoundingClientRect();
      qx((e.clientX - r.left - r.width / 2) * 0.3);
      qy((e.clientY - r.top - r.height / 2) * 0.4);
    });
    btn.addEventListener('pointerleave', () => { qx(0); qy(0); });
  });
}

function initBook3d() {
  const book = $('[data-book] .book3d');
  if (!book) return;
  gsap.to(book, { rotateY: -8, rotateX: 4, y: -12, duration: 3.5, repeat: -1, yoyo: true, ease: 'sine.inOut' });
  gsap.fromTo('.book3d__cover', { rotateY: -70 }, { rotateY: 0, duration: 1.6, delay: 0.5, ease: 'power3.out' });
}

/* ================================================================ Three.js */
function webglAvailable() {
  try {
    const c = document.createElement('canvas');
    return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
  } catch (_) { return false; }
}

async function initThree() {
  const heroCanvas = $('#hero-gl');
  const africaCanvas = $('#africa-gl');
  if ((!heroCanvas && !africaCanvas) || !webglAvailable()) return;
  const THREE = await import('three');
  if (heroCanvas) heroScene(THREE, heroCanvas);
  if (africaCanvas) africaScene(THREE, africaCanvas);
}

function makeRenderer(THREE, canvas) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'low-power' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
  renderer.setClearColor(0x000000, 0);
  return renderer;
}

function runWhenVisible(canvas, frame) {
  let running = false, raf = 0;
  const loop = (t) => { frame(t / 1000); if (running) raf = requestAnimationFrame(loop); };
  onVisible(canvas, (vis) => {
    if (vis && !running) { running = true; raf = requestAnimationFrame(loop); }
    if (!vis) { running = false; cancelAnimationFrame(raf); }
  }, { rootMargin: '0px' });
}

/** Hero : « poussière d'encre » aux couleurs du logo, en profondeur. */
function heroScene(THREE, canvas) {
  const renderer = makeRenderer(THREE, canvas);
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(50, 1, 0.1, 100);
  camera.position.z = 10;

  const count = isMobile ? 500 : 1400;
  const pos = new Float32Array(count * 3);
  const col = new Float32Array(count * 3);
  const size = new Float32Array(count);
  const seed = new Float32Array(count);
  const swatch = [new THREE.Color(PALETTE.vert), new THREE.Color('#D2D200'), new THREE.Color(PALETTE.rouge), new THREE.Color('#111111')];
  const weights = [0.42, 0.28, 0.18, 0.12];
  for (let i = 0; i < count; i++) {
    pos[i * 3] = (Math.random() - 0.5) * 26;
    pos[i * 3 + 1] = (Math.random() - 0.5) * 20;
    pos[i * 3 + 2] = -8 + Math.random() * 10;
    let r = Math.random(), k = 0;
    while (r > weights[k] && k < 3) { r -= weights[k]; k++; }
    swatch[k].toArray(col, i * 3);
    size[i] = 0.6 + Math.pow(Math.random(), 3) * 4.5;
    seed[i] = Math.random();
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  geo.setAttribute('aColor', new THREE.BufferAttribute(col, 3));
  geo.setAttribute('aSize', new THREE.BufferAttribute(size, 1));
  geo.setAttribute('aSeed', new THREE.BufferAttribute(seed, 1));

  const uniforms = {
    uTime: { value: 0 }, uMouse: { value: new THREE.Vector2() }, uScroll: { value: 0 },
    uPR: { value: renderer.getPixelRatio() },
  };
  const mat = new THREE.ShaderMaterial({
    uniforms, transparent: true, depthWrite: false,
    vertexShader: /* glsl */`
      uniform float uTime; uniform vec2 uMouse; uniform float uScroll; uniform float uPR;
      attribute vec3 aColor; attribute float aSize; attribute float aSeed;
      varying vec3 vColor; varying float vAlpha;
      void main() {
        vec3 p = position;
        p.y = mod(p.y + 10.0 + uTime * (0.12 + aSeed * 0.25) + uScroll * (2.0 + aSeed * 4.0), 20.0) - 10.0;
        p.x += sin(uTime * 0.35 + aSeed * 6.2831) * 0.35;
        float depth = (p.z + 8.0) / 10.0;
        p.xy += uMouse * depth * 0.6;
        vec4 mv = modelViewMatrix * vec4(p, 1.0);
        gl_Position = projectionMatrix * mv;
        gl_PointSize = aSize * uPR * (9.0 / -mv.z);
        vColor = aColor;
        vAlpha = 0.18 + depth * 0.55;
      }`,
    fragmentShader: /* glsl */`
      varying vec3 vColor; varying float vAlpha;
      void main() {
        float d = length(gl_PointCoord - 0.5);
        float a = smoothstep(0.5, 0.18, d) * vAlpha;
        if (a < 0.01) discard;
        gl_FragColor = vec4(vColor, a);
      }`,
  });
  scene.add(new THREE.Points(geo, mat));

  const resize = () => {
    const r = canvas.getBoundingClientRect();
    renderer.setSize(r.width, r.height, false);
    camera.aspect = r.width / Math.max(1, r.height);
    camera.updateProjectionMatrix();
  };
  resize();
  new ResizeObserver(resize).observe(canvas);

  const target = new THREE.Vector2();
  window.addEventListener('pointermove', (e) => { target.set((e.clientX / window.innerWidth - 0.5) * 2, -(e.clientY / window.innerHeight - 0.5) * 2); });
  ScrollTrigger.create({ trigger: canvas.parentElement, start: 'top top', end: 'bottom top', onUpdate: (s) => { uniforms.uScroll.value = s.progress; } });

  canvas.style.opacity = 0;
  gsap.to(canvas, { opacity: 1, duration: 1.6, delay: 0.3 });
  runWhenVisible(canvas, (t) => {
    uniforms.uTime.value = t;
    uniforms.uMouse.value.lerp(target, 0.04);
    renderer.render(scene, camera);
  });
}

/** Bienvenue : le logo (Afrique, livre, plume) recomposé en particules au défilement. */
function africaScene(THREE, canvas) {
  const wrap = canvas.parentElement;
  const fallback = $('.welcome__fallback', wrap);
  const src = fallback?.currentSrc || fallback?.src;
  if (!src) return;
  const img = new Image();
  img.decoding = 'async';
  img.src = src;
  img.onload = () => build(img);

  function build(image) {
    // Échantillonnage des pixels colorés du logo (partie graphique, sans le texte du bas)
    const sw = isMobile ? 150 : 210;
    const sh = Math.round(sw * image.height / image.width);
    const crop = Math.round(sh * 0.74);
    const c2 = document.createElement('canvas');
    c2.width = sw; c2.height = sh;
    const ctx = c2.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(image, 0, 0, sw, sh);
    const data = ctx.getImageData(0, 0, sw, crop).data;

    const pal = [
      [80, 160, 40], [240, 240, 0], [200, 20, 20], [0, 0, 0],
    ].map(([r, g, b]) => ({ r, g, b, c: new THREE.Color(r / 255, g / 255, b / 255) }));
    const pts = [];
    for (let y = 0; y < crop; y++) {
      for (let x = 0; x < sw; x++) {
        const i = (y * sw + x) * 4;
        const r = data[i], g = data[i + 1], b = data[i + 2];
        if (r > 215 && g > 215 && b > 215) continue; // fond blanc
        let best = 0, bd = Infinity;
        pal.forEach((p, k) => { const d = (p.r - r) ** 2 + (p.g - g) ** 2 + (p.b - b) ** 2; if (d < bd) { bd = d; best = k; } });
        pts.push([x, y, best]);
      }
    }
    const max = isMobile ? 4500 : 9000;
    for (let i = pts.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [pts[i], pts[j]] = [pts[j], pts[i]]; }
    const list = pts.slice(0, max);
    const n = list.length;

    const renderer = makeRenderer(THREE, canvas);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, 1, 0.1, 100);
    camera.position.z = 12;

    const scale = 7.6 / sw;
    const aTarget = new Float32Array(n * 3);
    const aStart = new Float32Array(n * 3);
    const aColor = new Float32Array(n * 3);
    const aSeed = new Float32Array(n);
    list.forEach(([x, y, k], i) => {
      aTarget[i * 3] = (x - sw / 2) * scale;
      aTarget[i * 3 + 1] = -(y - crop / 2) * scale;
      aTarget[i * 3 + 2] = (Math.random() - 0.5) * 0.25;
      const th = Math.random() * Math.PI * 2, ph = Math.acos(2 * Math.random() - 1), rad = 6 + Math.random() * 6;
      aStart[i * 3] = rad * Math.sin(ph) * Math.cos(th);
      aStart[i * 3 + 1] = rad * Math.sin(ph) * Math.sin(th);
      aStart[i * 3 + 2] = rad * Math.cos(ph) - 4;
      pal[k].c.toArray(aColor, i * 3);
      aSeed[i] = Math.random();
    });
    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(aTarget, 3));
    geo.setAttribute('aStart', new THREE.BufferAttribute(aStart, 3));
    geo.setAttribute('aColor', new THREE.BufferAttribute(aColor, 3));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(aSeed, 1));

    const uniforms = {
      uProgress: { value: 0 }, uTime: { value: 0 }, uMouse: { value: new THREE.Vector3(99, 99, 0) },
      uHover: { value: 0 }, uPR: { value: renderer.getPixelRatio() }, uSize: { value: isMobile ? 3.4 : 3.0 },
    };
    const mat = new THREE.ShaderMaterial({
      uniforms, transparent: true, depthWrite: false,
      vertexShader: /* glsl */`
        uniform float uProgress; uniform float uTime; uniform vec3 uMouse; uniform float uHover; uniform float uPR; uniform float uSize;
        attribute vec3 aStart; attribute vec3 aColor; attribute float aSeed;
        varying vec3 vColor; varying float vAlpha;
        void main() {
          float delay = aSeed * 0.45;
          float t = clamp((uProgress - delay) / 0.55, 0.0, 1.0);
          t = t * t * (3.0 - 2.0 * t);
          vec3 p = mix(aStart, position, t);
          float travel = t * (1.0 - t) * 4.0;
          p.x += sin(aSeed * 40.0 + uTime) * travel * 0.8;
          p.y += cos(aSeed * 33.0 + uTime * 0.8) * travel * 0.8;
          p.z += sin(uTime * 1.3 + aSeed * 6.2831) * 0.05;
          vec2 d = p.xy - uMouse.xy;
          float dist = length(d);
          float force = smoothstep(1.4, 0.0, dist) * uHover * t;
          p.xy += normalize(d + 0.0001) * force * 0.9;
          p.z += force * 1.5;
          vec4 mv = modelViewMatrix * vec4(p, 1.0);
          gl_Position = projectionMatrix * mv;
          gl_PointSize = uSize * uPR * (1.0 + travel * 1.5) * (12.0 / -mv.z);
          vColor = aColor;
          vAlpha = 0.35 + 0.65 * t;
        }`,
      fragmentShader: /* glsl */`
        varying vec3 vColor; varying float vAlpha;
        void main() {
          float d = length(gl_PointCoord - 0.5);
          float a = smoothstep(0.5, 0.25, d) * vAlpha;
          if (a < 0.02) discard;
          gl_FragColor = vec4(vColor, a);
        }`,
    });
    const points = new THREE.Points(geo, mat);
    scene.add(points);

    let halfH = 1, halfW = 1;
    const resize = () => {
      const r = canvas.getBoundingClientRect();
      renderer.setSize(r.width, r.height, false);
      camera.aspect = r.width / Math.max(1, r.height);
      // Recul de la caméra pour que le logo tienne dans le cadre
      const fit = 4.6 / Math.min(1, camera.aspect * 1.05);
      camera.position.z = fit / Math.tan((camera.fov * Math.PI) / 360);
      camera.updateProjectionMatrix();
      halfH = Math.tan((camera.fov * Math.PI) / 360) * camera.position.z;
      halfW = halfH * camera.aspect;
    };
    resize();
    new ResizeObserver(resize).observe(canvas);
    wrap.classList.add('gl-ready');

    const mouseTarget = new THREE.Vector3(99, 99, 0);
    let rotTarget = 0;
    wrap.addEventListener('pointermove', (e) => {
      const r = canvas.getBoundingClientRect();
      const nx = ((e.clientX - r.left) / r.width) * 2 - 1;
      const ny = -(((e.clientY - r.top) / r.height) * 2 - 1);
      mouseTarget.set(nx * halfW, ny * halfH, 0);
      rotTarget = nx * 0.25;
      gsap.to(uniforms.uHover, { value: 1, duration: 0.4, overwrite: true });
    });
    wrap.addEventListener('pointerleave', () => { rotTarget = 0; gsap.to(uniforms.uHover, { value: 0, duration: 0.8, overwrite: true }); });

    ScrollTrigger.create({
      trigger: wrap, start: 'top 90%', end: 'center 55%', scrub: 1.2,
      onUpdate: (s) => { uniforms.uProgress.value = s.progress; },
    });
    ScrollTrigger.refresh();

    runWhenVisible(canvas, (t) => {
      uniforms.uTime.value = t;
      uniforms.uMouse.value.lerp(mouseTarget, 0.12);
      points.rotation.y += (rotTarget - points.rotation.y) * 0.05;
      points.rotation.x = Math.sin(t * 0.4) * 0.04;
      renderer.render(scene, camera);
    });
  }
}

/* ================================================================ Démarrage */
function boot() {
  initMobileMenu();
  initLightbox();

  if (reduced || !gsap || !ScrollTrigger || !SplitText) {
    html.classList.remove('anim');
    window.__cenacleReady = true;
    return;
  }
  gsap.registerPlugin(ScrollTrigger, SplitText);
  window.__cenacleReady = true;

  initSmoothScroll();
  initPageTransitions();
  initHeader();

  // Les polices doivent être chargées avant de découper les titres.
  const fontsReady = document.fonts ? document.fonts.ready : Promise.resolve();
  fontsReady.then(() => {
    initHero();
    initReveals();
    initCounters();
    initMarquees();
    initHorizontalHistory();
    initTimelinePage();
    initQuotes();
    initJoinBlocks();
    initLegacy();
    initTiltAndMagnet();
    initBook3d();
    initInkCanvases();
    ScrollTrigger.refresh();
  });

  const startThree = () => initThree().catch((err) => console.warn('[Cénacle] WebGL indisponible :', err));
  if ('requestIdleCallback' in window) requestIdleCallback(startThree, { timeout: 1500 });
  else setTimeout(startThree, 400);
}

boot();
