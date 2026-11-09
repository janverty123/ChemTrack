import { moduleScene, sceneProgress } from './landing-motion.js';

const landing = document.querySelector('[data-landing]');

if (landing) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const shouldReduceMotion = () => reducedMotion.matches || document.documentElement?.dataset.reducedMotion === 'true';
    const menuButton = landing.querySelector('[data-landing-menu]');
    const menu = landing.querySelector('#landing-menu');
    const collapsedMenu = 'inset(0 0 calc(100% - 52px) calc(100% - 124px) round 26px)';
    const expandedMenu = 'inset(0 0 0 0 round 28px)';
    let menuAnimation = null;

    if (typeof menu.showModal === 'function') {
        menuButton.hidden = false;
        const closeMenu = (animate = true) => {
            if (!menu.open || (menu.dataset.closing && animate)) return;
            menuAnimation?.cancel();
            if (!animate || shouldReduceMotion()) return menu.close();
            menu.dataset.closing = 'true';
            menuAnimation = menu.animate([{ clipPath: expandedMenu, opacity: 1 }, { clipPath: collapsedMenu, opacity: 0 }], { duration: 180, easing: 'cubic-bezier(.4,0,1,1)', fill: 'forwards' });
            menuAnimation.finished.then(() => menu.close()).catch(() => {});
        };
        menuButton.addEventListener('click', (event) => {
            if (menu.open) return;
            menu.showModal();
            menuButton.setAttribute('aria-expanded', 'true');
            if (shouldReduceMotion() || event.detail === 0) return;
            menuAnimation = menu.animate([{ clipPath: collapsedMenu, opacity: .8 }, { clipPath: expandedMenu, opacity: 1 }], { duration: 280, easing: 'cubic-bezier(.16,1,.3,1)' });
            menu.querySelectorAll('nav a').forEach((link, index) => link.animate([{ transform: 'translateY(22px)', opacity: .15 }, { transform: 'translateY(0)', opacity: 1 }], { duration: 240, delay: 45 + index * 25, easing: 'cubic-bezier(.16,1,.3,1)', fill: 'backwards' }));
        });
        menu.querySelector('[data-landing-close]').addEventListener('click', (event) => closeMenu(event.detail !== 0));
        menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMenu(false)));
        menu.addEventListener('cancel', (event) => { event.preventDefault(); closeMenu(false); });
        menu.addEventListener('click', (event) => {
            const bounds = menu.getBoundingClientRect();
            if (event.target === menu && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) closeMenu();
        });
        menu.addEventListener('close', () => {
            menuAnimation?.cancel();
            delete menu.dataset.closing;
            menuButton.setAttribute('aria-expanded', 'false');
            menuButton.focus({ preventScroll: true });
        });
    }

    const story = landing.querySelector('[data-reading-scene]');
    const viewport = story.querySelector('[data-story-viewport]');
    const page = story.querySelector('[data-story-document]');
    const next = story.querySelector('[data-story-next]');
    const tracker = story.querySelector('[data-story-tracker]');
    const heroFigure = landing.querySelector('.landing-hero-product');
    let scheduled = false;
    let readingTravel = 0;

    const updateScenes = () => {
        scheduled = false;
        if (shouldReduceMotion()) return;
        const bounds = story.getBoundingClientRect();
        const inset = window.innerHeight < 650 ? 24 : 88;
        const state = moduleScene(sceneProgress(bounds.top, bounds.height, window.innerHeight, inset));
        const coverage = Math.round(state.reading * 100);
        page.style.transform = `translateY(${-readingTravel * state.reading}px)`;
        next.style.transform = `translateY(${(1 - state.handoff) * 100}%)`;
        next.style.opacity = state.handoff;
        next.inert = state.handoff < 1;
        next.setAttribute('aria-hidden', String(state.handoff < 1));
        // This illustrates an explicit completion action and never saves learner progress.
        story.dataset.stage = state.stage;
        story.querySelector('.landing-oval-fill').style.strokeDasharray = `${coverage} 100`;
        tracker.textContent = state.stage === 'checkpoint' ? 'Next · Checkpoint' : `Reading · ${coverage}% viewed`;
        story.querySelector('[data-story-label]').textContent = state.stage === 'checkpoint' ? 'Checkpoint preview' : 'Reading preview';
        story.querySelector('[data-story-save]').textContent = state.stage === 'reading' ? 'Saved' : 'Reading complete';
        story.querySelector('[data-story-completion]').textContent = state.stage === 'reading' ? 'End of the sample reading' : 'Reading marked complete';
        story.querySelectorAll('[data-story-step]').forEach((item) => item.classList.toggle('is-active', item.dataset.storyStep === state.stage));
        heroFigure.style.transform = `translateY(${-Math.min(14, Math.max(0, window.scrollY * .025))}px)`;
    };
    const scheduleScenes = () => {
        if (!scheduled && !shouldReduceMotion()) {
            scheduled = true;
            window.requestAnimationFrame(updateScenes);
        }
    };
    const measure = () => {
        readingTravel = Math.max(0, page.scrollHeight - viewport.clientHeight);
        scheduleScenes();
    };

    const demo = landing.querySelector('[data-checkpoint-demo]');
    const options = demo.querySelector('[data-demo-options]');
    const radios = [...options.querySelectorAll('input')];
    const submit = demo.querySelector('[data-demo-submit]');
    const play = demo.querySelector('[data-demo-play]');
    const status = demo.querySelector('[data-demo-status]');
    const receipt = demo.querySelector('[data-demo-receipt]');
    let timers = [];
    let interacted = false;
    let played = false;
    const clearPlayback = () => { timers.forEach(clearTimeout); timers = []; };
    const later = (callback, delay) => timers.push(setTimeout(callback, delay));
    const reset = () => {
        clearPlayback();
        radios.forEach((radio) => { radio.checked = false; });
        options.disabled = false;
        submit.disabled = true;
        submit.textContent = 'Submit answer';
        receipt.hidden = true;
        demo.dataset.state = 'ready';
        status.textContent = 'Choose an answer';
    };
    const confirm = () => {
        options.disabled = true;
        submit.disabled = true;
        submit.textContent = 'Submitted';
        status.textContent = 'Submission confirmed';
        receipt.hidden = false;
        demo.dataset.state = 'submitted';
    };
    const saveAnswer = () => {
        submit.disabled = true;
        demo.dataset.state = 'saving';
        status.textContent = 'Saving answer…';
        later(() => { status.textContent = 'Answer saved'; submit.disabled = false; demo.dataset.state = 'saved'; }, shouldReduceMotion() ? 0 : 450);
    };
    radios.forEach((radio) => radio.addEventListener('change', () => {
        interacted = true;
        clearPlayback();
        saveAnswer();
    }));
    submit.addEventListener('click', () => {
        if (!radios.some((radio) => radio.checked) || submit.disabled) return;
        interacted = true;
        clearPlayback();
        status.textContent = 'Submitting…';
        demo.dataset.state = 'submitting';
        submit.disabled = true;
        later(confirm, shouldReduceMotion() ? 0 : 350);
    });
    const playExample = () => {
        reset();
        played = true;
        play.textContent = 'Replay example ↻';
        if (shouldReduceMotion()) { status.textContent = 'Choose an answer, then submit to try the example'; return; }
        later(() => { radios[0].checked = true; saveAnswer(); }, 600);
        later(() => { submit.disabled = true; status.textContent = 'Submitting…'; demo.dataset.state = 'submitting'; }, 1800);
        later(confirm, 2200);
    };
    play.hidden = false;
    play.addEventListener('click', () => { interacted = true; playExample(); });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { clearPlayback(); if (demo.dataset.state !== 'submitted') { submit.disabled = !radios.some((radio) => radio.checked); status.textContent = 'Example paused · choose an answer or replay'; } }
    });

    const setMotionPreference = () => {
        clearPlayback();
        landing.classList.toggle('motion-ready', !shouldReduceMotion());
        if (shouldReduceMotion()) {
            page.style.removeProperty('transform');
            next.style.removeProperty('transform');
            next.style.removeProperty('opacity');
            next.inert = false;
            next.removeAttribute('aria-hidden');
            heroFigure.style.removeProperty('transform');
            story.querySelector('.landing-oval-fill').style.removeProperty('stroke-dasharray');
            story.dataset.stage = 'reading';
            story.querySelector('[data-story-label]').textContent = 'Reading preview';
            story.querySelector('[data-story-save]').textContent = 'Lesson preview';
            story.querySelector('[data-story-completion]').textContent = 'End of the sample reading';
            tracker.textContent = 'Reading preview';
            if (demo.dataset.state !== 'submitted') reset();
        } else measure();
    };
    setMotionPreference();
    reducedMotion.addEventListener('change', setMotionPreference);
    window.addEventListener('scroll', scheduleScenes, { passive: true });
    window.addEventListener('resize', measure, { passive: true });
    document.fonts?.ready.then(measure);
    if ('ResizeObserver' in window) new ResizeObserver(measure).observe(viewport);

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                if (entry.target === demo) {
                    if (!played && !interacted && !shouldReduceMotion()) playExample();
                } else if (!shouldReduceMotion()) entry.target.classList.add('is-arriving');
                observer.unobserve(entry.target);
            });
        }, { threshold: .35 });
        observer.observe(demo);
        landing.querySelectorAll('[data-landing-reveal]').forEach((element) => observer.observe(element));
    }
}
