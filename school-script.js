// Hamburger menu
const hamburger = document.getElementById('hamburger');
const nav = document.getElementById('nav');
hamburger.addEventListener('click', () => {
  nav.classList.toggle('open');
  hamburger.classList.toggle('open');
});

// Close nav on link click (mobile)
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => { nav.classList.remove('open'); hamburger.classList.remove('open'); });
});

// Header scroll effect
const header = document.getElementById('header');
window.addEventListener('scroll', () => {
  header.classList.toggle('scrolled', window.scrollY > 60);
  document.getElementById('backToTop').classList.toggle('visible', window.scrollY > 400);
});

// Active nav on scroll
const sections = document.querySelectorAll('section[id]');
window.addEventListener('scroll', () => {
  const scrollY = window.scrollY + 100;
  sections.forEach(sec => {
    const top = sec.offsetTop;
    const height = sec.offsetHeight;
    const id = sec.getAttribute('id');
    const link = document.querySelector(`.nav-link[href="#${id}"]`);
    if (link) link.classList.toggle('active', scrollY >= top && scrollY < top + height);
  });
});

// Back to top
document.getElementById('backToTop').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// Animate stats counter
const counters = document.querySelectorAll('.stat-number');
let counted = false;
const runCounters = () => {
  counters.forEach(el => {
    const target = +el.dataset.target;
    const duration = 1500;
    const step = target / (duration / 16);
    let current = 0;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { el.textContent = target; clearInterval(timer); }
      else el.textContent = Math.floor(current);
    }, 16);
  });
};
const statsSection = document.querySelector('.stats');
const observer = new IntersectionObserver(entries => {
  if (entries[0].isIntersecting && !counted) { counted = true; runCounters(); }
}, { threshold: 0.3 });
if (statsSection) observer.observe(statsSection);

// Scroll-in animations
const animObserver = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); animObserver.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.academic-card, .news-card, .news-list-item, .contact-card, .gallery-item, .value-item').forEach(el => {
  el.classList.add('anim-fade');
  animObserver.observe(el);
});

// Announcement ticker duplicate for infinite scroll
const ticker = document.querySelector('.ann-ticker');
if (ticker) {
  ticker.innerHTML += ticker.innerHTML;
}

// Contact form — ส่งข้อมูลไปที่ contact.php (SMTP z.com)
async function handleForm(e) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('submitBtn');
  const errEl = document.getElementById('formError');

  // แสดงสถานะกำลังส่ง
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';
  errEl.style.display = 'none';

  try {
    const res = await fetch('contact.php', {
      method: 'POST',
      body: new FormData(form),
    });
    const data = await res.json();

    if (data.success) {
      form.style.display = 'none';
      document.getElementById('formSuccess').style.display = 'block';
    } else {
      errEl.textContent = data.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
      errEl.style.display = 'block';
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> ส่งข้อความ';
    }
  } catch {
    errEl.textContent = 'ไม่สามารถเชื่อมต่อได้ กรุณาลองใหม่อีกครั้ง';
    errEl.style.display = 'block';
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> ส่งข้อความ';
  }
}

// Lightbox
function openLightbox(el) {
  const img = el.querySelector('img');
  if (!img || !img.src) return;
  document.getElementById('lightbox-img').src = img.src;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});
