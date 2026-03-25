// script.js - shared interactivity enhancements

// 1. Auto-focus login username field if exists
const usernameInput = document.querySelector('input[name="username"]');
if (usernameInput) {
  usernameInput.focus();
}

// 2. Animated dashboard counters (number roll-up)
const countEls = document.querySelectorAll('[data-counter]');
if (countEls.length) {
  countEls.forEach(el => {
    const target = Number(el.dataset.counter) || 0;
    let current = 0;
    const duration = 800;
    const step = Math.max(1, Math.floor(target / (duration / 20)));

    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        el.textContent = target;
        clearInterval(timer);
      } else {
        el.textContent = current;
      }
    }, 20);
  });
}

// 3. Sale form quick validations
const saleForm = document.querySelector('form[action="sales.php"]') || document.querySelector('form');
if (saleForm && saleForm.querySelector('input[name="quantity"]')) {
  saleForm.addEventListener('submit', (e) => {
    const q = Number(saleForm.querySelector('input[name="quantity"]').value);
    if (!Number.isInteger(q) || q <= 0) {
      e.preventDefault();
      alert('Please enter a valid quantity greater than 0.');
    }
  });
}

// 4. Confirm all delete links by class or name
const deleteLinks = document.querySelectorAll('a[href*="delete"]');
deleteLinks.forEach(link => {
  link.addEventListener('click', (event) => {
    const targetText = link.textContent.trim() || link.getAttribute('href');
    if (!confirm(`This action is irreversible. Continue with ${targetText}?`)) {
      event.preventDefault();
    }
  });
});

// 5. Highlight active nav if any
const current = window.location.pathname.split('/').pop();
const navLinks = document.querySelectorAll('a');
navLinks.forEach(a => {
  if (a.getAttribute('href') === current) {
    a.style.fontWeight = '700';
    a.style.textDecoration = 'underline';
  }
});
