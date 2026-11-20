/* ==========================================================================
   Le Cénacle — scripts du panel d'administration
   ========================================================================== */
(function () {
  'use strict';
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
  const csrf = $('meta[name="csrf-token"]')?.content || $('input[name="_csrf"]')?.value || '';
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const gsap = window.gsap;

  /* ---------- Menu latéral mobile ---------- */
  const side = $('#adm-side');
  const burger = $('.adm-burger');
  const backdrop = $('.adm-backdrop');
  if (side && burger) {
    const toggle = (open) => {
      side.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', String(open));
      backdrop.hidden = !open;
    };
    burger.addEventListener('click', () => toggle(!side.classList.contains('is-open')));
    backdrop.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggle(false); });
  }

  /* ---------- Notifications ---------- */
  $$('.toast').forEach((t, i) => {
    const close = () => {
      if (gsap && !reduced) gsap.to(t, { x: 40, opacity: 0, duration: 0.3, onComplete: () => t.remove() });
      else t.remove();
    };
    $('button', t).addEventListener('click', close);
    if (gsap && !reduced) gsap.from(t, { x: 60, opacity: 0, duration: 0.5, delay: i * 0.08, ease: 'power3.out' });
    if (t.classList.contains('toast--success')) setTimeout(close, 5000 + i * 500);
  });
  function toast(msg, type = 'success') {
    const box = $('.adm-toasts');
    if (!box) return alert(msg);
    const t = document.createElement('div');
    t.className = 'toast toast--' + type;
    t.innerHTML = '<span></span><button type="button" aria-label="Fermer">×</button>';
    t.querySelector('span').textContent = msg;
    box.appendChild(t);
    t.querySelector('button').onclick = () => t.remove();
    if (gsap && !reduced) gsap.from(t, { x: 60, opacity: 0, duration: 0.4 });
    setTimeout(() => t.remove(), 4000);
  }

  /* ---------- Confirmations ---------- */
  document.addEventListener('submit', (e) => {
    const f = e.target;
    if (f.dataset.confirm && !confirm(f.dataset.confirm)) e.preventDefault();
    if (!e.defaultPrevented) dirty = false;
  });

  /* ---------- Publier / masquer en un clic ---------- */
  $$('[data-toggle]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      try {
        const res = await fetch(btn.dataset.toggle, { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: new URLSearchParams({ _csrf: csrf }) });
        const data = await res.json();
        if (!data.ok) throw new Error();
        btn.classList.toggle('is-on', !!data.published);
        btn.textContent = data.published ? 'Publié' : 'Masqué';
        toast(data.published ? 'Élément publié sur le site.' : 'Élément masqué du site.');
      } catch (_) {
        toast('Impossible de modifier le statut.', 'error');
      }
      btn.disabled = false;
    });
  });

  /* ---------- Glisser-déposer pour l'ordre ---------- */
  if (window.Sortable) {
    $$('[data-sortable]').forEach((list) => {
      window.Sortable.create(list, {
        handle: '.drag', animation: 180, ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
        onEnd: async () => {
          const body = new URLSearchParams({ _csrf: csrf });
          $$('[data-id]', list).forEach((el) => body.append('ids[]', el.dataset.id));
          try {
            const res = await fetch(list.dataset.sortable, { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body });
            if (!(await res.json()).ok) throw new Error();
            toast('Nouvel ordre enregistré.');
          } catch (_) { toast('L\'ordre n\'a pas pu être enregistré.', 'error'); }
        },
      });
    });
  }

  /* ---------- Aperçu des images avant envoi ---------- */
  $$('[data-img-field]').forEach((field) => {
    const input = $('input[type=file]', field);
    const prev = $('.img-field__preview', field);
    input?.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      prev.classList.remove('img-field__preview--empty');
      prev.innerHTML = '<img alt="">';
      prev.querySelector('img').src = url;
      const label = input.closest('label');
      if (label) label.firstChild && (label.lastChild.previousSibling.textContent = ' ' + file.name.slice(0, 26));
      dirty = true;
    });
  });

  /* ---------- Barre de mise en forme ---------- */
  $$('[data-rich]').forEach((wrap) => {
    const ta = $('textarea', wrap);
    $$('[data-md]', wrap).forEach((b) => b.addEventListener('click', () => {
      const s = ta.selectionStart, en = ta.selectionEnd;
      const sel = ta.value.slice(s, en);
      let before = '', after = '', text = sel;
      switch (b.dataset.md) {
        case 'b': before = '**'; after = '**'; text = sel || 'texte en gras'; break;
        case 'i': before = '*'; after = '*'; text = sel || 'texte en italique'; break;
        case 'h': before = (s > 0 && ta.value[s - 1] !== '\n' ? '\n\n' : '') + '## '; text = sel || 'Intertitre'; break;
        case 'ul': text = (sel || 'élément').split('\n').map((l) => '- ' + l).join('\n'); before = s > 0 && ta.value[s - 1] !== '\n' ? '\n' : ''; break;
        case 'a': {
          const href = prompt('Adresse du lien (https://…)', 'https://');
          if (!href) return;
          before = '['; after = '](' + href + ')'; text = sel || 'texte du lien'; break;
        }
      }
      ta.setRangeText(before + text + after, s, en, 'end');
      ta.focus();
      dirty = true;
    }));
  });

  /* ---------- Envoi multiple avec progression ---------- */
  $$('[data-dropzone]').forEach((zone) => {
    const input = $('input[type=file]', zone);
    const bar = $('.dropzone__progress', zone);
    const send = (files) => {
      if (!files.length) return;
      const fd = new FormData();
      fd.append('_csrf', csrf);
      Array.from(files).forEach((f) => fd.append('files[]', f));
      const xhr = new XMLHttpRequest();
      xhr.open('POST', zone.action);
      bar.hidden = false;
      xhr.upload.onprogress = (e) => { if (e.lengthComputable) bar.firstElementChild.style.width = (e.loaded / e.total) * 100 + '%'; };
      xhr.onload = () => { window.location.href = window.location.href.split('#')[0]; };
      xhr.onerror = () => { bar.hidden = true; toast('L\'envoi a échoué. Vérifiez votre connexion.', 'error'); };
      xhr.send(fd);
    };
    input.addEventListener('change', () => send(input.files));
    ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.remove('is-over'); }));
    zone.addEventListener('drop', (e) => send(e.dataTransfer.files));
  });

  /* ---------- Modifications non enregistrées ---------- */
  let dirty = false;
  $$('[data-dirty-check]').forEach((form) => {
    form.addEventListener('input', () => { dirty = true; });
  });
  window.addEventListener('beforeunload', (e) => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });

  /* ---------- Divers ---------- */
  $$('[data-checkall]').forEach((all) => all.addEventListener('change', () => {
    $$('input[name="ids[]"]', all.closest('form')).forEach((c) => { c.checked = all.checked; });
  }));
  $$('[data-autosubmit]').forEach((s) => s.addEventListener('change', () => s.form.submit()));
  $$('[data-copy]').forEach((b) => b.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(b.dataset.copy); toast('Lien copié.'); } catch (_) { prompt('Copiez ce lien :', b.dataset.copy); }
  }));
  $$('[data-pw]').forEach((b) => b.addEventListener('click', () => {
    const input = b.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
    b.setAttribute('aria-label', input.type === 'password' ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
  }));

  /* ---------- Animations d'entrée ---------- */
  if (gsap && !reduced) {
    gsap.from('.adm-head', { y: 16, opacity: 0, duration: 0.6, ease: 'power3.out' });
    gsap.from('.kpi, .dash > .panel, .table-wrap, .editor > *, .media-card, .settings-form, .empty-state, .media-add > *', {
      y: 24, opacity: 0, duration: 0.6, stagger: 0.04, ease: 'power3.out', delay: 0.05, clearProps: 'transform,opacity',
    });
    $$('[data-count]').forEach((el) => {
      const n = parseInt(el.dataset.count, 10) || 0;
      const o = { v: 0 };
      gsap.to(o, { v: n, duration: 1.2, ease: 'power2.out', onUpdate: () => { el.textContent = Math.round(o.v); } });
    });
    if ($('.auth__box')) {
      gsap.from('.auth__box > *', { y: 24, opacity: 0, duration: 0.7, stagger: 0.06, ease: 'power3.out' });
      gsap.from('.auth__quote', { y: 40, opacity: 0, duration: 1.2, delay: 0.3, ease: 'power3.out' });
    }
  }

  /* ---------- Constellation de l'écran de connexion ---------- */
  const c = $('#auth-gl');
  if (c && !reduced) {
    const ctx = c.getContext('2d');
    const cols = ['#50A028', '#F0F000', '#C81414', '#ffffff'];
    let w, h, dots = [];
    const dpr = Math.min(devicePixelRatio || 1, 2);
    const mouse = { x: -999, y: -999 };
    const size = () => {
      w = c.clientWidth; h = c.clientHeight;
      c.width = w * dpr; c.height = h * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      dots = Array.from({ length: Math.round((w * h) / 9000) }, () => ({
        x: Math.random() * w, y: Math.random() * h, vx: (Math.random() - 0.5) * 0.3, vy: (Math.random() - 0.5) * 0.3,
        r: 1 + Math.random() * 2.6, c: cols[Math.floor(Math.random() * cols.length)],
      }));
    };
    size();
    addEventListener('resize', size);
    c.parentElement.addEventListener('pointermove', (e) => { const r = c.getBoundingClientRect(); mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top; });
    (function draw() {
      ctx.clearRect(0, 0, w, h);
      for (const d of dots) {
        d.x += d.vx; d.y += d.vy;
        if (d.x < 0 || d.x > w) d.vx *= -1;
        if (d.y < 0 || d.y > h) d.vy *= -1;
        const md = Math.hypot(d.x - mouse.x, d.y - mouse.y);
        ctx.globalAlpha = md < 160 ? 1 : 0.6;
        ctx.fillStyle = d.c;
        ctx.beginPath(); ctx.arc(d.x, d.y, d.r, 0, 6.283); ctx.fill();
      }
      ctx.strokeStyle = '#ffffff';
      for (let i = 0; i < dots.length; i++) for (let j = i + 1; j < dots.length; j++) {
        const a = dots[i], b = dots[j], dd = Math.hypot(a.x - b.x, a.y - b.y);
        if (dd < 120) { ctx.globalAlpha = (1 - dd / 120) * 0.18; ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke(); }
      }
      requestAnimationFrame(draw);
    })();
  }
})();
