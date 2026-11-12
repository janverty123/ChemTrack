// Landing page: the interactive checkpoint example and gentle reveal-on-scroll.
// The example never contacts the server and never changes any learner record.
const landing = document.querySelector('[data-landing]');

if (landing) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const shouldReduceMotion = () => reducedMotion.matches || document.documentElement?.dataset.reducedMotion === 'true';

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
    const playExample = () => {
        reset();
        played = true;
        play.textContent = 'Replay example ↻';
        if (shouldReduceMotion()) { status.textContent = 'Choose an answer, then submit to try the example'; return; }
        later(() => { radios[0].checked = true; saveAnswer(); }, 600);
        later(() => { submit.disabled = true; status.textContent = 'Submitting…'; demo.dataset.state = 'submitting'; }, 1800);
        later(confirm, 2200);
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
    play.hidden = false;
    play.addEventListener('click', () => { interacted = true; playExample(); });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) return;
        clearPlayback();
        if (demo.dataset.state !== 'submitted') {
            submit.disabled = !radios.some((radio) => radio.checked);
            status.textContent = 'Example paused · choose an answer or replay';
        }
    });

    const setMotionPreference = () => {
        landing.classList.toggle('motion-ready', !shouldReduceMotion());
        if (shouldReduceMotion()) {
            clearPlayback();
            if (demo.dataset.state !== 'submitted') reset();
        }
    };
    setMotionPreference();
    reducedMotion.addEventListener('change', setMotionPreference);

    const reveals = [...landing.querySelectorAll('[data-landing-reveal]')];
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                if (entry.target === demo) {
                    if (!played && !interacted && !shouldReduceMotion()) playExample();
                } else entry.target.classList.add('is-arriving');
                observer.unobserve(entry.target);
            });
        }, { threshold: .25 });
        observer.observe(demo);
        reveals.forEach((element) => observer.observe(element));
    } else {
        reveals.forEach((element) => element.classList.add('is-arriving'));
    }
}
