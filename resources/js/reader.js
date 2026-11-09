const bounded = (value, min, max) => Math.min(max, Math.max(min, value));

export function pageObservation(rect, viewportHeight) {
    const visible = Math.max(0, Math.min(rect.bottom, viewportHeight) - Math.max(rect.top, 0));
    return { viewed: rect.height > 0 && visible >= Math.min(rect.height, viewportHeight) / 2, visible, offset: bounded(-rect.top / rect.height || 0, 0, 1) };
}

export function nativePosition(rect, viewportHeight) {
    return Math.round(bounded((viewportHeight - rect.top) / rect.height * 100 || 0, 0, 100));
}

export function nativeObservation(position, furthest) {
    return { position, furthest_position: Math.max(position, furthest) };
}

export function createReadingSaver({ send, status, confirmed = () => {} }) {
    let revision = 0, savedRevision = 0, payload, inFlight, failed = false, stale = false;
    return {
        update(value) {
            if (stale || JSON.stringify(payload) === JSON.stringify(value)) return;
            payload = structuredClone(value);
            revision++;
            status('unsaved');
        },
        dirty: () => revision !== savedRevision,
        async save() {
            if (stale || revision === savedRevision) return;
            if (inFlight) {
                await inFlight;
                if (!failed && !stale && revision !== savedRevision) return this.save();
                return;
            }
            const sentRevision = revision, sentPayload = structuredClone(payload);
            status('saving');
            failed = false;
            inFlight = (async () => {
                try {
                    const result = await send(sentPayload);
                    savedRevision = sentRevision;
                    confirmed(result);
                    status(savedRevision === revision ? 'saved' : 'unsaved');
                } catch (error) {
                    failed = true;
                    stale = error.stale === true;
                    status(stale ? 'stale' : 'failed');
                }
            })();
            await inFlight;
            inFlight = null;
        },
    };
}

async function initReader(root) {
    const initial = JSON.parse(root.querySelector('[data-reader-state]').textContent);
    const status = root.querySelector('[data-reading-status]');
    const progressLabel = root.querySelector('[data-reader-progress]');
    const stageLabel = root.querySelector('[data-reader-stage]');
    const oval = root.querySelector('[data-oval-fill]');
    const saveButton = root.querySelector('[data-reading-save]');
    const isPdf = root.dataset.kind === 'pdf';
    const viewed = new Set(initial.viewed_pages || []);
    let currentPage = initial.current_page || 1, pageCount = initial.page_count || 0;
    let position = initial.position || 0, furthest = initial.furthest_position || 0;
    let saveState = initial.saved_at ? 'saved' : 'idle', savedAt = initial.saved_at, timer, ready = false;
    const percent = () => isPdf ? (pageCount ? Math.floor(viewed.size / pageCount * 100) : 0) : Math.floor(furthest);
    const paint = () => {
        const value = percent();
        const suffix = { idle: 'not saved yet', unsaved: 'unsaved', saving: 'saving', saved: 'saved', failed: 'save needed', stale: 'teacher help needed' }[saveState];
        oval.setAttribute('stroke-dasharray', `${value} 100`);
        stageLabel.textContent = isPdf && pageCount ? `Page ${currentPage} of ${pageCount}` : 'Current lesson';
        progressLabel.textContent = `${value}% viewed · ${suffix}`;
    };
    const saver = createReadingSaver({
        send: async payload => {
            const response = await fetch(root.dataset.saveUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ ...payload, version: initial.version }), signal: AbortSignal.timeout(15000),
            });
            if (!response.ok) {
                const error = new Error('Reading save was not confirmed');
                error.stale = response.status === 409;
                throw error;
            }
            return response.json();
        },
        confirmed: result => { savedAt = result.saved_at; },
        status: state => {
            saveState = state;
            const time = savedAt ? new Date(savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
            status.textContent = {
                unsaved: 'Reading observations have not been saved yet.',
                saving: 'Saving reading progress…',
                saved: `Reading progress saved${time ? ` at ${time}` : ''}. You can resume this confirmed location later.`,
                failed: 'Save not confirmed. Keep this page open, check your connection, and select Retry reading save.',
                stale: 'Reading saves are paused because the material version has changed. Contact your teacher before continuing with revised material. Your earlier reading record is preserved.',
            }[state];
            status.dataset.state = state;
            saveButton.textContent = state === 'failed' ? 'Retry reading save' : 'Save reading progress';
            saveButton.disabled = state === 'stale';
            paint();
        },
    });
    const record = () => {
        const payload = isPdf ? { position, current_page: currentPage, page_count: pageCount, viewed_pages: [...viewed].sort((a, b) => a - b) } : nativeObservation(position, furthest);
        saver.update(payload);
        clearTimeout(timer);
        if (saver.dirty() && saveState !== 'failed' && saveState !== 'stale') timer = setTimeout(() => saver.save(), 1000);
        paint();
    };
    saveButton.addEventListener('click', () => { clearTimeout(timer); saver.save(); });
    window.addEventListener('offline', () => { clearTimeout(timer); status.textContent = 'Connection lost. Keep this page open; new reading observations need a connection to save.'; });
    window.addEventListener('online', () => { if (saver.dirty()) saver.save(); });
    window.addEventListener('beforeunload', event => { if (saver.dirty()) { event.preventDefault(); event.returnValue = ''; } });

    if (!isPdf) {
        const content = root.querySelector('[data-native-content]');
        await document.fonts.ready;
        if (initial.saved_at && position > 0) {
            const rect = content.getBoundingClientRect();
            window.scrollTo({ top: Math.max(0, window.scrollY + rect.top + rect.height * position / 100 - window.innerHeight), behavior: 'instant' });
        }
        const observe = () => {
            if (document.visibilityState !== 'visible') return;
            const next = nativePosition(content.getBoundingClientRect(), window.innerHeight);
            if (next === position && ready) return;
            position = next;
            furthest = Math.max(furthest, position);
            ready = true;
            record();
        };
        let frame;
        const schedule = () => { cancelAnimationFrame(frame); frame = requestAnimationFrame(observe); };
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule);
        document.addEventListener('visibilitychange', schedule);
        observe();
        return;
    }

    const pagesRoot = root.querySelector('[data-pdf-pages]');
    const loadStatus = root.querySelector('[data-load-status]');
    const retry = root.querySelector('[data-document-retry]');
    const pageInput = root.querySelector('[data-page-input]');
    const zoomIn = root.querySelector('[data-zoom-in]');
    const zoomOut = root.querySelector('[data-zoom-out]');
    let pdf, loadingTask, observer, zoom = 1, generation = 0, pages = [], nearby = new Set(), observeFrame;
    const observe = () => {
        if (!ready || document.visibilityState !== 'visible') return;
        let strongest = null, strongestObservation = null, changed = false;
        for (const page of pages) {
            if (!page.rendered) continue;
            const observation = pageObservation(page.node.getBoundingClientRect(), window.innerHeight);
            if (observation.viewed && !viewed.has(page.number)) { viewed.add(page.number); changed = true; }
            if (observation.visible > (strongestObservation?.visible || 0)) { strongest = page; strongestObservation = observation; }
        }
        if (strongest) {
            const nextPosition = Math.round(strongestObservation.offset * 100);
            if (currentPage !== strongest.number || position !== nextPosition) changed = true;
            currentPage = strongest.number;
            position = nextPosition;
            if (document.activeElement !== pageInput) pageInput.value = currentPage;
        }
        if (changed) record();
        paint();
    };
    const schedule = () => { cancelAnimationFrame(observeFrame); observeFrame = requestAnimationFrame(observe); };
    const clearPage = page => {
        page.task?.cancel();
        page.task = null;
        page.rendering = false;
        page.renderToken = null;
        page.rendered = false;
        delete page.node.dataset.rendered;
        if (page.canvas) { page.canvas.width = 0; page.canvas.height = 0; page.canvas.remove(); page.canvas = null; }
        page.text?.remove(); page.text = null;
    };
    const renderPage = async page => {
        if (page.rendered || page.rendering || !nearby.has(page.number)) return;
        const run = generation;
        const token = Symbol();
        page.rendering = true;
        page.renderToken = token;
        try {
            const source = await pdf.getPage(page.number);
            if (run !== generation || page.renderToken !== token || !nearby.has(page.number)) return;
            const width = pagesRoot.clientWidth * zoom;
            const viewport = source.getViewport({ scale: width / source.getViewport({ scale: 1 }).width });
            page.ratio = viewport.height / viewport.width;
            page.node.style.width = `${viewport.width}px`;
            page.node.style.height = `${viewport.height}px`;
            const canvas = document.createElement('canvas');
            canvas.setAttribute('aria-hidden', 'true');
            const ratio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.floor(viewport.width * ratio);
            canvas.height = Math.floor(viewport.height * ratio);
            canvas.style.width = '100%'; canvas.style.height = '100%';
            page.canvas = canvas;
            page.node.append(canvas);
            page.task = source.render({ canvasContext: canvas.getContext('2d'), viewport, transform: ratio === 1 ? null : [ratio, 0, 0, ratio, 0, 0] });
            await page.task.promise;
            if (run !== generation || page.renderToken !== token) return;
            if (!nearby.has(page.number)) { clearPage(page); return; }
            const text = await source.getTextContent();
            if (run !== generation || page.renderToken !== token || !nearby.has(page.number)) return;
            page.text = document.createElement('span');
            page.text.className = 'sr-only';
            page.text.textContent = text.items.map(item => item.str || '').join(' ') || 'This PDF page has no extractable text. Use the original PDF or ask your teacher for an accessible copy.';
            page.node.append(page.text);
            page.rendered = true;
            page.node.dataset.rendered = 'true';
            schedule();
        } catch (error) {
            if (error.name !== 'RenderingCancelledException' && run === generation) {
                loadStatus.textContent = `Page ${page.number} could not be displayed. Retry the document or open the original PDF.`;
                retry.hidden = false;
            }
        } finally {
            if (page.renderToken === token) page.rendering = false;
        }
    };
    const jump = (number, offset = 0) => {
        const target = pages[number - 1];
        if (!target) return;
        window.scrollTo({ top: Math.max(0, window.scrollY + target.node.getBoundingClientRect().top + target.node.offsetHeight * offset), behavior: 'instant' });
    };
    const resizePages = () => {
        if (!pdf || !pagesRoot.clientWidth) return;
        generation++;
        for (const page of pages) {
            clearPage(page);
            page.node.style.width = `${pagesRoot.clientWidth * zoom}px`;
            page.node.style.height = `${pagesRoot.clientWidth * zoom * page.ratio}px`;
        }
        for (const number of nearby) renderPage(pages[number - 1]);
    };
    const load = async () => {
        ready = false;
        retry.hidden = true;
        loadStatus.textContent = 'Loading the document reader…';
        observer?.disconnect();
        generation++;
        for (const page of pages) clearPage(page);
        pagesRoot.replaceChildren();
        pages = []; nearby.clear();
        try {
            await loadingTask?.destroy();
            const [pdfjs, worker] = await Promise.all([import('pdfjs-dist'), import('pdfjs-dist/build/pdf.worker.min.mjs?url')]);
            pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
            loadingTask = pdfjs.getDocument({ url: root.dataset.documentUrl, withCredentials: true, isEvalSupported: false });
            pdf = await loadingTask.promise;
            if (pdf.numPages > 500) throw new Error('This document exceeds the reader page limit');
            if (pageCount && pageCount !== pdf.numPages) throw new Error('The saved document page count has changed. Reload this lesson.');
            pageCount = pdf.numPages;
            root.querySelector('[data-page-total]').textContent = `of ${pageCount}`;
            pageInput.max = pageCount;
            currentPage = bounded(currentPage, 1, pageCount);
            const firstPage = await pdf.getPage(1);
            const firstViewport = firstPage.getViewport({ scale: 1 });
            const ratio = firstViewport.height / firstViewport.width;
            observer = new IntersectionObserver(entries => {
                for (const entry of entries) {
                    const page = pages[Number(entry.target.dataset.page) - 1];
                    if (entry.isIntersecting) { nearby.add(page.number); renderPage(page); }
                    else { nearby.delete(page.number); clearPage(page); }
                }
                schedule();
            }, { rootMargin: '800px 0px' });
            for (let number = 1; number <= pageCount; number++) {
                const node = document.createElement('section');
                node.className = 'pdf-page'; node.dataset.page = number;
                node.setAttribute('aria-label', `Page ${number}`);
                node.style.width = `${pagesRoot.clientWidth * zoom}px`;
                node.style.height = `${pagesRoot.clientWidth * zoom * ratio}px`;
                const label = document.createElement('span'); label.className = 'pdf-page-label'; label.textContent = `Page ${number}`;
                node.append(label); pagesRoot.append(node);
                pages.push({ number, node, ratio, rendered: false, rendering: false });
            }
            if (initial.saved_at && currentPage !== 1) {
                const source = await pdf.getPage(currentPage);
                const viewport = source.getViewport({ scale: 1 });
                const target = pages[currentPage - 1];
                target.ratio = viewport.height / viewport.width;
                target.node.style.height = `${pagesRoot.clientWidth * zoom * target.ratio}px`;
            }
            await document.fonts.ready;
            ready = true;
            if (initial.saved_at) jump(currentPage, position / 100);
            for (const page of pages) observer.observe(page.node);
            loadStatus.textContent = 'Document ready. Pages render as you approach them. Original PDF is available for search, selection, and assistive reading.';
            paint();
        } catch {
            ready = false;
            loadStatus.textContent = 'The document reader could not load. Check your connection and retry, or open the original PDF. Reading progress has not changed.';
            retry.hidden = false;
        }
    };
    retry.addEventListener('click', load);
    root.querySelector('[data-page-go]').addEventListener('click', () => {
        if (!pageInput.checkValidity()) { pageInput.reportValidity(); return; }
        jump(Number(pageInput.value));
    });
    pageInput.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); root.querySelector('[data-page-go]').click(); } });
    const changeZoom = delta => {
        const page = currentPage, offset = position / 100;
        zoom = Math.round(bounded(zoom + delta, 0.75, 2) * 100) / 100;
        root.querySelector('[data-zoom-label]').textContent = `${Math.round(zoom * 100)}%`;
        zoomIn.disabled = zoom >= 2; zoomOut.disabled = zoom <= 0.75;
        resizePages(); jump(page, offset);
    };
    zoomIn.addEventListener('click', () => changeZoom(0.25));
    zoomOut.addEventListener('click', () => changeZoom(-0.25));
    window.addEventListener('scroll', schedule, { passive: true });
    document.addEventListener('visibilitychange', schedule);
    let oldWidth = 0;
    new ResizeObserver(entries => {
        const width = entries[0].contentRect.width;
        if (Math.abs(width - oldWidth) < 1) return;
        oldWidth = width;
        const page = currentPage, offset = position / 100;
        resizePages();
        if (ready) jump(page, offset);
    }).observe(pagesRoot);
    await load();
}

if (typeof document !== 'undefined') {
    for (const root of document.querySelectorAll('[data-reader]')) {
        initReader(root).catch(() => {
            root.querySelector('[data-reading-status]').textContent = 'The reader could not start. Reload to retry; reading observations have not been saved.';
        });
    }
}
