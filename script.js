/**
 * Portfolio Script
 * NM Nufail | Software Engineer
 */

document.documentElement.classList.add('js-enabled');

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

    // --- Lightbox Helper (handles all project lightboxes) ---
    function initLightbox(lightboxId, triggerSelectors) {
        const lb = document.getElementById(lightboxId);
        if (!lb) return;
        const bd = lb.querySelector('.lightbox-backdrop');
        const cb = lb.querySelector('.lightbox-close');
        const trigs = document.querySelectorAll(triggerSelectors);

        function open()  { lb.hidden = false; document.body.style.overflow = 'hidden'; if (cb) cb.focus(); }
        function close() { lb.hidden = true;  document.body.style.overflow = ''; }

        trigs.forEach(el => el.addEventListener('click', open));
        if (bd) bd.addEventListener('click', close);
        if (cb) cb.addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !lb.hidden) close(); });
    }

    initLightbox('riostudio-lightbox',     '.riostudio-preview-btn, .riostudio-thumb');
    initLightbox('busreservation-lightbox', '.busreservation-preview-btn, .busreservation-thumb');

    // --- Graphic Design Gallery ---
    const gdItems = Array.from(document.querySelectorAll('.gd-item'));
    const gdMore = document.querySelector('.gd-more');
    const GD_VISIBLE_COUNT = 3;

    gdItems.forEach((item, index) => {
        if (index >= GD_VISIBLE_COUNT) item.classList.add('is-hidden');
    });

    function gdUpdateMoreButton() {
        if (!gdMore) return;
        const expanded = gdMore.getAttribute('aria-expanded') === 'true';
        gdMore.textContent = expanded ? 'Show Less' : `Show More Designs (${gdItems.length - GD_VISIBLE_COUNT})`;
    }

    if (gdMore) {
        gdUpdateMoreButton();
        gdMore.addEventListener('click', () => {
            const expanded = gdMore.getAttribute('aria-expanded') === 'true';
            gdItems.forEach((item, index) => {
                if (index >= GD_VISIBLE_COUNT) item.classList.toggle('is-hidden', expanded);
            });
            gdMore.setAttribute('aria-expanded', String(!expanded));
            gdUpdateMoreButton();
            if (expanded) {
                const gallery = document.querySelector('.gd-gallery');
                const top = gallery.getBoundingClientRect().top + window.scrollY - 100;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    }

    // --- Graphic Design Gallery Lightbox ---
    const gdLightbox = document.getElementById('gd-lightbox');

    if (gdItems.length && gdLightbox) {
        const gdImage = document.getElementById('gd-lightbox-img');
        const gdPrev = gdLightbox.querySelector('.lightbox-prev');
        const gdNext = gdLightbox.querySelector('.lightbox-next');
        const gdBackdrop = gdLightbox.querySelector('.lightbox-backdrop');
        const gdClose = gdLightbox.querySelector('.lightbox-close');
        let gdIndex = 0;

        function gdVisibleItems() {
            return gdItems.filter(item => !item.classList.contains('is-hidden'));
        }

        function gdShow(index) {
            const items = gdVisibleItems();
            if (!items.length) return;
            gdIndex = (index + items.length) % items.length;
            const item = items[gdIndex];
            const thumb = item.querySelector('img');
            gdImage.src = item.dataset.full;
            gdImage.alt = thumb ? thumb.alt : 'Graphic design preview';
        }

        function gdOpen(item) {
            gdShow(gdVisibleItems().indexOf(item));
            gdLightbox.hidden = false;
            document.body.style.overflow = 'hidden';
            gdClose.focus();
        }

        function gdCloseLightbox() {
            gdLightbox.hidden = true;
            document.body.style.overflow = '';
        }

        gdItems.forEach((item) => {
            item.addEventListener('click', () => gdOpen(item));
        });

        if (gdPrev) gdPrev.addEventListener('click', () => gdShow(gdIndex - 1));
        if (gdNext) gdNext.addEventListener('click', () => gdShow(gdIndex + 1));
        if (gdBackdrop) gdBackdrop.addEventListener('click', gdCloseLightbox);
        if (gdClose) gdClose.addEventListener('click', gdCloseLightbox);

        document.addEventListener('keydown', (e) => {
            if (gdLightbox.hidden) return;
            if (e.key === 'Escape') gdCloseLightbox();
            if (e.key === 'ArrowLeft') gdShow(gdIndex - 1);
            if (e.key === 'ArrowRight') gdShow(gdIndex + 1);
        });
    }

});
