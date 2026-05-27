/* global.js — Pasca y Pesca
   Hamburger menu + Back-to-top para todas las páginas
*/
document.addEventListener('DOMContentLoaded', function () {

  /* ============================
     HAMBURGER MENU
  ============================ */
  var nav      = document.querySelector('header nav');
  var navLinks = document.querySelector('header nav .nav-links');

  if (nav && navLinks) {
    var btn = document.createElement('button');
    btn.className   = 'hamburger';
    btn.setAttribute('aria-label', 'Abrir menú');
    btn.setAttribute('aria-expanded', 'false');
    btn.innerHTML   = '<i class="fa-solid fa-bars"></i>';

    // Insertar el botón antes de .nav-links
    nav.insertBefore(btn, navLinks);

    btn.addEventListener('click', function () {
      var open = navLinks.classList.toggle('nav-open');
      btn.setAttribute('aria-expanded', open);
      btn.innerHTML = open
        ? '<i class="fa-solid fa-xmark"></i>'
        : '<i class="fa-solid fa-bars"></i>';
    });

    // Cerrar al hacer clic fuera del nav
    document.addEventListener('click', function (e) {
      if (!nav.contains(e.target) && navLinks.classList.contains('nav-open')) {
        navLinks.classList.remove('nav-open');
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<i class="fa-solid fa-bars"></i>';
      }
    });
  }

  /* ============================
     BACK TO TOP
  ============================ */
  var topBtn = document.createElement('button');
  topBtn.id        = 'back-to-top';
  topBtn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
  topBtn.setAttribute('aria-label', 'Volver arriba');
  document.body.appendChild(topBtn);

  window.addEventListener('scroll', function () {
    if (window.scrollY > 300) {
      topBtn.classList.add('visible');
    } else {
      topBtn.classList.remove('visible');
    }
  }, { passive: true });

  topBtn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

});
