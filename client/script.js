// ── Mobile nav toggle ──
const ham = document.getElementById('hamburger');
const nav = document.getElementById('navLinks');

ham.addEventListener('click', () => nav.classList.toggle('open'));
nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => nav.classList.remove('open')));

// ── Scroll reveal ──
const reveals = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      revealObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });

reveals.forEach(el => revealObserver.observe(el));

// ── Counter animation ──
function animateCount(id, target, suffix = '') {
  const el = document.getElementById(id);
  if (!el) return;
  const duration = 1600;
  const start = performance.now();

  function step(now) {
    const t = Math.min((now - start) / duration, 1);
    const val = Math.floor(t * t * (3 - 2 * t) * target);
    el.textContent = val.toLocaleString() + suffix;
    if (t < 1) requestAnimationFrame(step);
    else el.textContent = target.toLocaleString() + suffix;
  }

  requestAnimationFrame(step);
}

const numbersSection = document.getElementById('numbers');
let counted = false;

new IntersectionObserver(([e]) => {
  if (e.isIntersecting && !counted) {
    counted = true;
    animateCount('cnt-employees', 120000);
    animateCount('cnt-km', 22000);
    animateCount('cnt-sub', 154);
    animateCount('cnt-countries', 15, '+');
    animateCount('cnt-ports', 4);
  }
}, { threshold: 0.3 }).observe(numbersSection);

// ── Contact form mock submit ──
document.getElementById('formBtn').addEventListener('click', () => {
  const btn = document.getElementById('formBtn');
  const msg = document.getElementById('formMsg');

  btn.textContent = 'Sending...';
  btn.disabled = true;

  setTimeout(() => {
    btn.textContent = 'Send Message';
    btn.disabled = false;
    msg.textContent = 'Thank you — your message has been sent successfully.';
    setTimeout(() => msg.textContent = '', 4000);
  }, 1400);
});
