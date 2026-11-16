(() => {
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
    const intro = document.getElementById('site-intro');
    if (intro && document.documentElement.classList.contains('show-site-intro')) {
        window.setTimeout(() => {
            document.documentElement.classList.remove('show-site-intro');
            try { sessionStorage.setItem('tkpm-intro-seen', '1'); } catch (_) {}
        }, reducedMotion ? 50 : 2850);
    }
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-primary-navigation]');
    if (toggle && nav) {
        const closeMenu = () => {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka menu navigasi');
            nav.classList.remove('is-open');
        };
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            nav.classList.toggle('is-open', open);
        });
        nav.addEventListener('click', (event) => { if (event.target.closest('a')) closeMenu(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeMenu(); });
        document.addEventListener('click', (event) => { if (!nav.contains(event.target) && !toggle.contains(event.target)) closeMenu(); });
    }
    const revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && !reducedMotion) {
        const observer = new IntersectionObserver((entries, current) => entries.forEach((entry) => {
            if (entry.isIntersecting) { entry.target.classList.add('is-visible'); current.unobserve(entry.target); }
        }), { threshold: .01, rootMargin: '0px 0px -20px 0px' });
        revealItems.forEach((item) => observer.observe(item));
    } else revealItems.forEach((item) => item.classList.add('is-visible'));

    const counters = document.querySelectorAll('[data-count-to]');
    if ('IntersectionObserver' in window && !reducedMotion) {
        const counterObserver = new IntersectionObserver((entries, observer) => entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const element = entry.target, target = Number(element.dataset.countTo || 0), start = performance.now();
            const draw = (now) => {
                const progress = Math.min(1, (now - start) / 900);
                element.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3))).toLocaleString('id-ID');
                if (progress < 1) requestAnimationFrame(draw);
            };
            requestAnimationFrame(draw);
            observer.unobserve(entry.target);
        }), { threshold: .65 });
        counters.forEach((counter) => counterObserver.observe(counter));
    }
    const lightbox = document.getElementById('site-lightbox');
    const image = lightbox?.querySelector('[data-lightbox-image]');
    const caption = lightbox?.querySelector('[data-lightbox-caption]');
    const close = () => {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
        if (image) image.src = '';
    };
    document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
        if (!lightbox || !image) return;
        image.src = button.dataset.lightboxSrc;
        image.alt = button.dataset.lightboxAlt || '';
        if (caption) caption.textContent = button.dataset.lightboxCaption || '';
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
        lightbox.querySelector('[data-lightbox-close]')?.focus();
    }));
    lightbox?.querySelectorAll('[data-lightbox-close]').forEach((button) => button.addEventListener('click', close));
    lightbox?.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });

    const wizard = document.querySelector('[data-registration-wizard]');
    if (wizard) {
        const pages = [...wizard.querySelectorAll('[data-wizard-step]')];
        const indicators = [...document.querySelectorAll('[data-wizard-indicator]')];
        const pageOne = wizard.querySelector('[data-wizard-step="1"]');
        const pageTwo = wizard.querySelector('[data-wizard-step="2"]');
        const parentName = wizard.querySelector('#nama_ayah');
        const motherName = wizard.querySelector('#nama_ibu');
        const password = wizard.querySelector('#password_orangtua');
        const passwordConfirmation = wizard.querySelector('#konfirmasi_password_orangtua');

        const capitalizeValue = (value, mode) => {
            if (mode === 'words') {
                return value.toLocaleLowerCase('id-ID').replace(/(^|[\s'-])(\p{L})/gu, (match, separator, letter) => separator + letter.toLocaleUpperCase('id-ID'));
            }
            let capitalizeNext = true;
            return value.replace(/\p{L}/gu, (letter) => {
                if (!capitalizeNext) return letter;
                capitalizeNext = false;
                return letter.toLocaleUpperCase('id-ID');
            }).replace(/([.!?]\s+|\n+)\p{L}/gu, (match) => match.toLocaleUpperCase('id-ID'));
        };

        wizard.querySelectorAll('[data-capitalize]').forEach((input) => {
            const formatInput = (event) => {
                if (event?.isComposing) return;
                const mode = input.dataset.capitalize;
                const start = input.selectionStart;
                const end = input.selectionEnd;
                const value = input.value;
                const formatted = capitalizeValue(value, mode);
                if (formatted === value) return;
                input.value = formatted;
                if (start !== null && end !== null) {
                    input.setSelectionRange(
                        capitalizeValue(value.slice(0, start), mode).length,
                        capitalizeValue(value.slice(0, end), mode).length,
                    );
                }
            };
            input.addEventListener('input', formatInput);
            input.addEventListener('compositionend', formatInput);
            input.addEventListener('blur', formatInput);
            formatInput();
        });

        const updateCustomValidity = () => {
            const hasParentName = Boolean(parentName?.value.trim() || motherName?.value.trim());
            parentName?.setCustomValidity(hasParentName ? '' : 'Isi nama ayah atau ibu.');
            if (password) {
                const passwordStartsWithUppercase = !password.value || /^\p{Lu}/u.test(password.value);
                password.setCustomValidity(passwordStartsWithUppercase ? '' : 'Huruf pertama kata sandi harus kapital.');
            }
            if (passwordConfirmation) {
                passwordConfirmation.setCustomValidity(!passwordConfirmation.value || passwordConfirmation.value === password?.value ? '' : 'Kata sandi belum sama.');
            }
        };

        [parentName, motherName, password, passwordConfirmation].filter(Boolean).forEach((input) => {
            input.addEventListener('input', updateCustomValidity);
        });

        const showStep = (step, shouldScroll = true) => {
            pages.forEach((page) => {
                const isCurrent = Number(page.dataset.wizardStep) === step;
                page.classList.toggle('is-current', isCurrent);
                page.setAttribute('aria-hidden', String(!isCurrent));
                if ('inert' in page) page.inert = !isCurrent;
            });
            indicators.forEach((indicator) => {
                const isCurrent = Number(indicator.dataset.wizardIndicator) === step;
                indicator.classList.toggle('is-active', isCurrent);
                indicator.classList.toggle('is-complete', Number(indicator.dataset.wizardIndicator) < step);
                if (isCurrent) indicator.setAttribute('aria-current', 'step');
                else indicator.removeAttribute('aria-current');
            });
            wizard.dataset.currentStep = String(step);
            if (shouldScroll) {
                wizard.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
                window.setTimeout(() => pages.find((page) => Number(page.dataset.wizardStep) === step)?.querySelector('h2')?.focus({ preventScroll: true }), reducedMotion ? 0 : 250);
            }
        };

        const firstInvalidControl = (page) => [...page.querySelectorAll('input, select, textarea')]
            .find((control) => !control.checkValidity());
        const getPageOneIssue = () => {
            updateCustomValidity();
            const invalid = firstInvalidControl(pageOne);
            if (invalid) return { control: invalid };
            const regionForm = pageOne.querySelector('[data-region-form]');
            const manualEntry = regionForm?.querySelector('[data-region-fallback]');
            if (regionForm && manualEntry?.hidden !== false) {
                const incomplete = [...regionForm.querySelectorAll('[data-region-level]')]
                    .find((select) => select.disabled || !select.value);
                if (incomplete) return { region: incomplete };
            }
            return null;
        };
        const reportPageOneIssue = (issue) => {
            if (issue.control) {
                issue.control.reportValidity();
                issue.control.focus({ preventScroll: true });
                issue.control.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
                return;
            }
            if (!issue.region.disabled) {
                issue.region.reportValidity();
                issue.region.focus({ preventScroll: true });
            }
            issue.region.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
        };

        wizard.noValidate = true;
        wizard.classList.add('is-ready');
        showStep(Number(wizard.dataset.initialStep || 1), false);
        wizard.querySelector('[data-wizard-next]')?.addEventListener('click', () => {
            const issue = getPageOneIssue();
            if (issue) { reportPageOneIssue(issue); return; }
            showStep(2);
        });
        wizard.querySelector('[data-wizard-back]')?.addEventListener('click', () => showStep(1));
        wizard.addEventListener('submit', (event) => {
            const issue = getPageOneIssue();
            if (issue) {
                event.preventDefault();
                showStep(1, false);
                window.requestAnimationFrame(() => reportPageOneIssue(issue));
                return;
            }
            const invalid = firstInvalidControl(pageTwo);
            if (!invalid) return;
            event.preventDefault();
            showStep(2, false);
            window.requestAnimationFrame(() => {
                invalid.reportValidity();
                invalid.focus({ preventScroll: true });
                invalid.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
            });
        });
        pageTwo?.querySelectorAll('.answer-option input').forEach((input) => {
            input.closest('.answer-option').classList.toggle('is-selected', input.checked);
            input.addEventListener('change', () => {
                wizard.querySelectorAll('.answer-option input').forEach((option) => {
                    if (option.name === input.name) option.closest('.answer-option').classList.toggle('is-selected', option.checked);
                });
            });
        });
    }

    const regionForm = document.querySelector('[data-region-form]');
    if (regionForm) {
        const levels = Object.fromEntries([...regionForm.querySelectorAll('[data-region-level]')]
            .map((select) => [select.dataset.regionLevel, select]));
        const selectsWrap = regionForm.querySelector('[data-region-selects]');
        const fallbackWrap = regionForm.querySelector('[data-region-fallback]');
        const fallbackInputs = [...regionForm.querySelectorAll('[data-fallback-name]')];
        const base = regionForm.dataset.apiBase.replace(/\/$/, '');
        const cache = new Map();
        let revision = 0;

        const getData = async (path) => {
            if (!cache.has(path)) {
                const request = (async () => {
                    const controller = new AbortController();
                    const timeout = window.setTimeout(() => controller.abort(), 8000);
                    try {
                        const response = await fetch(`${base}${path}`, {
                            headers: { Accept: 'application/json' },
                            signal: controller.signal,
                        });
                        if (!response.ok) throw new Error('Daftar wilayah tidak dapat dimuat.');
                        const payload = await response.json();
                        if (!Array.isArray(payload.data)) throw new Error('Format daftar wilayah tidak sesuai.');
                        return payload.data;
                    } finally {
                        window.clearTimeout(timeout);
                    }
                })();
                cache.set(path, request);
            }
            try {
                return await cache.get(path);
            } catch (error) {
                cache.delete(path);
                throw error;
            }
        };

        const setOptions = (select, items, placeholder, selectedName = '') => {
            select.replaceChildren(new Option(placeholder, ''));
            items.forEach((item) => {
                const option = new Option(item.name, item.name);
                option.dataset.id = item.id;
                if (item.postal_code) option.dataset.postalCode = item.postal_code;
                select.add(option);
            });
            const match = [...select.options].find((option) => option.value.toLocaleLowerCase('id-ID') === selectedName.trim().toLocaleLowerCase('id-ID'));
            select.value = match ? match.value : '';
            select.disabled = items.length === 0;
        };

        const resetSelect = (select, placeholder) => {
            select.replaceChildren(new Option(placeholder, ''));
            select.disabled = true;
        };

        const activateManualEntry = () => {
            const currentValues = {
                provinsi: levels.province.value,
                kabupaten_kota: levels.regency.value,
                kecamatan: levels.district.value,
                kelurahan_desa: levels.village.value,
                kode_pos: levels.postal.value,
            };
            selectsWrap.hidden = true;
            Object.values(levels).forEach((select) => { select.disabled = true; });
            fallbackInputs.forEach((input) => {
                input.name = input.dataset.fallbackName;
                input.required = true;
                input.disabled = false;
                input.value = currentValues[input.dataset.fallbackName] || input.value;
            });
            fallbackWrap.hidden = false;
        };

        const selectedId = (select) => select.selectedOptions[0]?.dataset.id || '';
        const old = {
            province: regionForm.dataset.oldProvince || '',
            regency: regionForm.dataset.oldRegency || '',
            district: regionForm.dataset.oldDistrict || '',
            village: regionForm.dataset.oldVillage || '',
            postal: regionForm.dataset.oldPostal || '',
        };

        const setPostal = (village, requestedPostal = '') => {
            const postal = levels.postal;
            postal.replaceChildren(new Option('Pilih kode pos', ''));
            if (village?.postal_code && /^\d{5}$/.test(String(village.postal_code))) {
                postal.add(new Option(village.postal_code, village.postal_code));
                postal.value = village.postal_code;
                postal.disabled = false;
                return true;
            }
            if (requestedPostal && /^\d{5}$/.test(requestedPostal)) {
                postal.add(new Option(requestedPostal, requestedPostal));
                postal.value = requestedPostal;
                postal.disabled = false;
                return true;
            }
            resetSelect(postal, 'Kode pos belum tersedia');
            return false;
        };

        const loadVillages = async (selectedVillage = '', selectedPostal = '', currentRevision = revision) => {
            const districtId = selectedId(levels.district);
            resetSelect(levels.village, 'Memuat kelurahan/desa…');
            resetSelect(levels.postal, 'Terisi setelah memilih kelurahan/desa');
            if (!districtId) return;
            const items = await getData(`/villages/${encodeURIComponent(districtId)}.json`);
            if (currentRevision !== revision) return;
            setOptions(levels.village, items, 'Pilih kelurahan/desa', selectedVillage);
            const village = items.find((item) => item.name === levels.village.value);
            if (village && !setPostal(village, selectedPostal)) activateManualEntry();
        };

        const loadDistricts = async (selectedDistrict = '', selectedVillage = '', selectedPostal = '', currentRevision = revision) => {
            const regencyId = selectedId(levels.regency);
            resetSelect(levels.district, 'Memuat kecamatan…');
            resetSelect(levels.village, 'Pilih kecamatan terlebih dahulu');
            resetSelect(levels.postal, 'Terisi setelah memilih kelurahan/desa');
            if (!regencyId) return;
            const items = await getData(`/districts/${encodeURIComponent(regencyId)}.json`);
            if (currentRevision !== revision) return;
            setOptions(levels.district, items, 'Pilih kecamatan', selectedDistrict);
            if (levels.district.value) await loadVillages(selectedVillage, selectedPostal, currentRevision);
        };

        const loadRegencies = async (selectedRegency = '', selectedDistrict = '', selectedVillage = '', selectedPostal = '', currentRevision = revision) => {
            const provinceId = selectedId(levels.province);
            resetSelect(levels.regency, 'Memuat kabupaten/kota…');
            resetSelect(levels.district, 'Pilih kabupaten/kota terlebih dahulu');
            resetSelect(levels.village, 'Pilih kecamatan terlebih dahulu');
            resetSelect(levels.postal, 'Terisi setelah memilih kelurahan/desa');
            if (!provinceId) return;
            const items = await getData(`/regencies/${encodeURIComponent(provinceId)}.json`);
            if (currentRevision !== revision) return;
            setOptions(levels.regency, items, 'Pilih kabupaten/kota', selectedRegency);
            if (levels.regency.value) await loadDistricts(selectedDistrict, selectedVillage, selectedPostal, currentRevision);
        };

        const restoreOldSelection = async () => {
            const currentRevision = ++revision;
            try {
                const items = await getData('/provinces.json');
                if (currentRevision !== revision) return;
                setOptions(levels.province, items, 'Pilih provinsi', old.province);
                if (levels.province.value) {
                    await loadRegencies(old.regency, old.district, old.village, old.postal, currentRevision);
                }
            } catch (_) {
                if (currentRevision === revision) activateManualEntry();
            }
        };

        levels.province.addEventListener('change', async () => {
            const currentRevision = ++revision;
            old.regency = old.district = old.village = old.postal = '';
            try { await loadRegencies('', '', '', '', currentRevision); }
            catch (_) { if (currentRevision === revision) activateManualEntry(); }
        });
        levels.regency.addEventListener('change', async () => {
            const currentRevision = ++revision;
            old.district = old.village = old.postal = '';
            try { await loadDistricts('', '', '', currentRevision); }
            catch (_) { if (currentRevision === revision) activateManualEntry(); }
        });
        levels.district.addEventListener('change', async () => {
            const currentRevision = ++revision;
            old.village = old.postal = '';
            try { await loadVillages('', '', currentRevision); }
            catch (_) { if (currentRevision === revision) activateManualEntry(); }
        });
        levels.village.addEventListener('change', () => {
            const village = [...levels.village.options].find((option) => option.value === levels.village.value);
            if (village && !setPostal({ postal_code: village.dataset.postalCode })) activateManualEntry();
        });
        restoreOldSelection();
    }
})();
