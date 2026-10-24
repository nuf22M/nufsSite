/**
 * Portfolio Script
 * NM Nufail | Software Engineer
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- Mobile Menu ---
    const navToggle = document.getElementById('nav-toggle');
    const navList = document.getElementById('nav-list');
    const navLinks = document.querySelectorAll('.nav-link');

    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navList.classList.toggle('active');
            const icon = navToggle.querySelector('svg');
            if (navList.classList.contains('active')) {
                icon.innerHTML = '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>';
            } else {
                icon.innerHTML = '<line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>';
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navList.classList.remove('active');
            if (navToggle) {
                const icon = navToggle.querySelector('svg');
                icon.innerHTML = '<line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line>';
            }
        });
    });

    // --- Active Link on Scroll ---
    const sections = document.querySelectorAll('section[id]');

    function scrollActive() {
        const scrollY = window.pageYOffset;

        sections.forEach(current => {
            const sectionHeight = current.offsetHeight;
            const sectionTop = current.offsetTop - 100;
            const sectionId = current.getAttribute('id');
            const navLink = document.querySelector(`.nav-list a[href*=${sectionId}]`);

            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                if (navLink) navLink.classList.add('active');
            } else {
                if (navLink) navLink.classList.remove('active');
            }
        });

        const header = document.querySelector('.header');
        if (scrollY >= 50) {
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
        } else {
            header.style.boxShadow = 'none';
        }
    }

    window.addEventListener('scroll', scrollActive);

    // --- Scroll Reveal Animation ---
    const revealElements = document.querySelectorAll('.reveal');

    function reveal() {
        revealElements.forEach(el => {
            const windowHeight = window.innerHeight;
            const elementTop = el.getBoundingClientRect().top;
            if (elementTop < windowHeight - 100) {
                el.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', reveal);
    reveal(); // run on load

    // --- Contact Form Submission ---
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(contactForm);
            const data = Object.fromEntries(formData.entries());
            console.log('Form Submitted:', data);

            const submitBtn = contactForm.querySelector('button');
            const originalText = submitBtn.textContent;

            submitBtn.textContent = 'Message Sent!';
            submitBtn.classList.remove('btn-primary');
            submitBtn.style.backgroundColor = '#22c55e';
            submitBtn.style.color = '#fff';

            contactForm.reset();

            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.style.backgroundColor = '';
                submitBtn.style.color = '';
                submitBtn.classList.add('btn-primary');
            }, 3000);
        });
    }

    // --- RioStudio Lightbox ---
    const lightbox = document.getElementById('riostudio-lightbox');
    const backdrop = lightbox && lightbox.querySelector('.lightbox-backdrop');
    const closeBtn = lightbox && lightbox.querySelector('.lightbox-close');
    const triggers = document.querySelectorAll('.riostudio-preview-btn, .riostudio-thumb');

    function openLightbox() {
        if (!lightbox) return;
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
        if (closeBtn) closeBtn.focus();
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
    }

    triggers.forEach(el => el.addEventListener('click', openLightbox));
    if (backdrop) backdrop.addEventListener('click', closeLightbox);
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox && !lightbox.hidden) closeLightbox();
    });

});
