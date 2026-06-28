var AuraAnimations = (function() {
    'use strict';

    var isInitialized = false;
    var _pageInitialized = false;

    function init() {
        if (isInitialized) return;
        isInitialized = true;

        gsap.registerPlugin(ScrollTrigger);

        gsap.defaults({
            ease: 'power3.out',
            duration: 1
        });

        ScrollTrigger.config({
            limitCallbacks: true,
            ignoreMobileResize: true
        });
    }

    // ── Hero Entrance ──
    function animateHero(container) {
        if (!container) return;
        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        var tagline = container.querySelector('[data-animate="hero-tagline"]');
        var heading = container.querySelector('[data-animate="hero-heading"]');
        var links = container.querySelectorAll('[data-animate="hero-link"]');

        if (tagline) {
            tl.from(tagline, {
                y: 40, opacity: 0, duration: 0.8,
                ease: 'power2.out'
            }, 0.1);
        }

        if (heading && !heading.hasAttribute('data-split-done')) {
            heading.setAttribute('data-split-done', '');
            var words = heading.textContent.trim().split(/\s+/);
            heading.innerHTML = '';
            var spans = [];
            words.forEach(function(word, wordIndex) {
                var wordSpan = document.createElement('span');
                wordSpan.style.display = 'inline-block';
                wordSpan.style.whiteSpace = 'nowrap';
                
                var chars = word.split('');
                chars.forEach(function(char) {
                    var span = document.createElement('span');
                    span.style.display = 'inline-block';
                    span.className = 'split-char';
                    span.textContent = char;
                    wordSpan.appendChild(span);
                    spans.push(span);
                });
                
                heading.appendChild(wordSpan);
                if (wordIndex < words.length - 1) {
                    var spaceSpan = document.createElement('span');
                    spaceSpan.style.display = 'inline-block';
                    spaceSpan.textContent = '\u00A0';
                    heading.appendChild(spaceSpan);
                }
            });
            tl.from(spans, {
                y: 80,
                opacity: 0,
                rotationX: -90,
                stagger: 0.012,
                duration: 0.9,
                ease: 'power3.out'
            }, 0.3);
        }

        if (links.length) {
            tl.from(links, {
                y: 50, opacity: 0, stagger: 0.12, duration: 0.7
            }, 0.7);
        }

        return tl;
    }

    // ── Scroll Reveal ──
    function createScrollReveal(elements, opts) {
        if (!elements || !elements.length) return;
        opts = Object.assign({
            y: 50, opacity: 0, duration: 1, stagger: 0.15,
            start: 'top 85%', toggleActions: 'play none none reverse',
            ease: 'power3.out', scale: 1, clearProps: 'transform'
        }, opts);

        var items = [];
        if (elements.forEach) {
            elements.forEach(function(el) { items.push(el); });
        } else {
            items.push(elements);
        }

        gsap.from(items, {
            y: opts.y, opacity: opts.opacity, duration: opts.duration,
            stagger: opts.stagger, ease: opts.ease,
            scale: opts.scale, clearProps: opts.clearProps,
            scrollTrigger: {
                trigger: items[0].parentElement || items[0],
                start: opts.start, toggleActions: opts.toggleActions
            }
        });
    }

    // ── Counter Animation ──
    function animateCounters(container) {
        var counters = container ? container.querySelectorAll('[data-count]') : document.querySelectorAll('[data-count]');
        if (!counters.length) return;

        counters.forEach(function(el) {
            var target = parseFloat(el.getAttribute('data-count'));
            var suffix = el.getAttribute('data-suffix') || '';
            var prefix = el.getAttribute('data-prefix') || '';
            var decimals = parseInt(el.getAttribute('data-decimals')) || 0;

            gsap.to(el, {
                textContent: target,
                duration: 3,
                ease: 'power2.out',
                snap: { textContent: Math.pow(10, -decimals) },
                onUpdate: function() {
                    var val = parseFloat(el.textContent);
                    el.textContent = prefix + val.toFixed(decimals) + suffix;
                },
                scrollTrigger: {
                    trigger: el.closest('section') || el.parentElement,
                    start: 'top 85%', toggleActions: 'play none none reverse'
                }
            });
        });
    }

    // ── Parallax ──
    function createParallax(elements, depth) {
        if (!elements) return;
        depth = depth || 0.3;
        var items = elements.forEach ? elements : [elements];

        items.forEach(function(el) {
            gsap.to(el, {
                y: function() { return window.innerHeight * depth * 0.4; },
                ease: 'none',
                scrollTrigger: {
                    trigger: el, start: 'top bottom', end: 'bottom top', scrub: true
                }
            });
        });
    }

    // ── Split Text ──
    function splitText(element, opts) {
        if (!element || element.hasAttribute('data-split-done')) return;
        element.setAttribute('data-split-done', '');
        opts = Object.assign({
            type: 'chars', duration: 0.9, stagger: 0.025,
            y: 50, opacity: 0, ease: 'power3.out',
            scrollTrigger: true, triggerStart: 'top 85%'
        }, opts);

        var text = element.textContent.trim();
        element.innerHTML = '';
        var words = text.split(/\s+/);
        var spans = [];
        words.forEach(function(word, wordIndex) {
            var wordSpan = document.createElement('span');
            wordSpan.style.display = 'inline-block';
            wordSpan.style.whiteSpace = 'nowrap';
            
            var chars = word.split('');
            chars.forEach(function(char) {
                var span = document.createElement('span');
                span.style.display = 'inline-block';
                span.className = 'split-char';
                span.textContent = char;
                wordSpan.appendChild(span);
                spans.push(span);
            });
            
            element.appendChild(wordSpan);
            if (wordIndex < words.length - 1) {
                var spaceSpan = document.createElement('span');
                spaceSpan.style.display = 'inline-block';
                spaceSpan.textContent = '\u00A0';
                element.appendChild(spaceSpan);
            }
        });

        var config = {
            y: opts.y, opacity: opts.opacity,
            duration: opts.duration, stagger: opts.stagger,
            ease: opts.ease
        };

        if (opts.scrollTrigger) {
            config.scrollTrigger = {
                trigger: element, start: opts.triggerStart,
                toggleActions: 'play none none reverse'
            };
        }

        gsap.from(spans, config);
    }

    // ── Image Clip Reveal ──
    function revealImage(imgElements) {
        if (!imgElements || !imgElements.length) return;
        var items = imgElements.forEach ? imgElements : [imgElements];

        items.forEach(function(img) {
            if (img.hasAttribute('data-reveal-done')) return;
            img.setAttribute('data-reveal-done', '');

            var parent = img.parentElement;
            if (!parent) return;

            if (window.getComputedStyle(img).position !== 'absolute' &&
                window.getComputedStyle(img).position !== 'fixed') {
                img.style.position = 'relative';
                img.style.zIndex = '1';
            }

            var wrapper = document.createElement('div');
            wrapper.style.cssText = 'overflow:hidden;position:relative;width:100%;height:100%;';
            img.parentNode.insertBefore(wrapper, img);
            wrapper.appendChild(img);

            var clipReveal = document.createElement('div');
            clipReveal.style.cssText =
                'position:absolute;inset:0;z-index:2;background:#e8e5db;pointer-events:none;' +
                'transform-origin:center center;transform:scaleX(1);';
            wrapper.appendChild(clipReveal);

            gsap.fromTo(clipReveal,
                { scaleX: 1, scaleY: 1 },
                {
                    scaleX: 0, scaleY: 1,
                    duration: 1.2,
                    ease: 'power3.inOut',
                    scrollTrigger: {
                        trigger: wrapper,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                }
            );

            gsap.fromTo(img,
                { scale: 1.1, opacity: 0.8 },
                {
                    scale: 1, opacity: 1,
                    duration: 1.2,
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: wrapper,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                }
            );
        });
    }

    // ── Magnetic Hover ──
    function magneticHover(element, strength) {
        if (!element) return;
        strength = strength || 0.3;

        element.addEventListener('mousemove', function(e) {
            var rect = element.getBoundingClientRect();
            var x = (e.clientX - rect.left - rect.width / 2) * strength;
            var y = (e.clientY - rect.top - rect.height / 2) * strength;
            gsap.to(element, { x: x, y: y, duration: 0.4, ease: 'power2.out', overwrite: 'auto' });
        });

        element.addEventListener('mouseleave', function() {
            gsap.to(element, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.3)', overwrite: 'auto' });
        });
    }

    // ── Cursor Glow ──
    function initCursorGlow() {
        if (document.getElementById('aura-cursor-glow')) return;
        var cursor = document.createElement('div');
        cursor.id = 'aura-cursor-glow';
        cursor.style.cssText =
            'position:fixed;pointer-events:none;z-index:9999;' +
            'width:300px;height:300px;border-radius:50%;' +
            'background:radial-gradient(circle,rgba(232,229,219,0.15) 0%,transparent 70%);' +
            'transform:translate(-50%,-50%);' +
            'transition:opacity 0.3s;opacity:0;' +
            'will-change:transform;';
        document.body.appendChild(cursor);

        var showing = false;
        document.addEventListener('mousemove', function(e) {
            gsap.to(cursor, {
                x: e.clientX, y: e.clientY,
                duration: 1.2, ease: 'power2.out', overwrite: 'auto'
            });
            if (!showing) {
                showing = true;
                gsap.to(cursor, { opacity: 1, duration: 0.5 });
            }
        });
    }

    // ── SVG Line Draw ──
    function animateSVGDraw(paths, opts) {
        if (!paths) return;
        opts = Object.assign({
            duration: 1.5, stagger: 0.2, ease: 'power2.inOut', scrollTrigger: false
        }, opts);

        var items = paths.forEach ? paths : [paths];
        items.forEach(function(path) {
            var length = path.getTotalLength();
            path.style.strokeDasharray = length;
            path.style.strokeDashoffset = length;
        });

        gsap.to(items, {
            strokeDashoffset: 0, duration: opts.duration,
            stagger: opts.stagger, ease: opts.ease,
            scrollTrigger: opts.scrollTrigger ? {
                trigger: items[0].closest('section'),
                start: 'top 85%'
            } : undefined
        });
    }

    // ── Stagger Grid ──
    function staggerGrid(items, opts) {
        if (!items || !items.length) return;
        opts = Object.assign({
            y: 70, opacity: 0, scale: 0.92, duration: 0.9,
            stagger: 0.08, ease: 'power3.out', start: 'top 85%'
        }, opts);

        gsap.from(items, {
            y: opts.y, opacity: opts.opacity, scale: opts.scale,
            duration: opts.duration, stagger: opts.stagger, ease: opts.ease,
            scrollTrigger: {
                trigger: items[0].parentElement,
                start: opts.start, toggleActions: 'play none none reverse'
            }
        });
    }

    // ── Smooth Links ──
    function smoothLinks(selector) {
        var links = document.querySelectorAll(selector || 'a[href^="#"]');
        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    if (window.lenis) {
                        window.lenis.scrollTo(target, { offset: -80, duration: 1.5 });
                    } else {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }

    // ── Floating Reveal ──
    function createFloatingReveal(elements) {
        if (!elements || !elements.length) return;
        elements.forEach(function(el) {
            gsap.fromTo(el,
                { y: 30, opacity: 0 },
                {
                    y: 0, opacity: 1, duration: 1.2,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: el, start: 'top 90%',
                        toggleActions: 'play none none reverse'
                    }
                }
            );
        });
    }

    // ── Scale In ──
    function scaleIn(elements) {
        if (!elements || !elements.length) return;
        elements.forEach(function(el) {
            gsap.fromTo(el,
                { scale: 0.8, opacity: 0 },
                {
                    scale: 1, opacity: 1, duration: 1,
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: el, start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                }
            );
        });
    }

    // ── Init All ──
    function initPageAnimations() {
        if (_pageInitialized) return;
        _pageInitialized = true;
        init();

        animateHero(document.querySelector('section:first-of-type'));
        animateCounters();
        smoothLinks();
        initCursorGlow();

        document.querySelectorAll('[data-reveal]').forEach(function(el) {
            createScrollReveal(el);
        });

        document.querySelectorAll('[data-split]').forEach(function(el) {
            splitText(el);
        });

        document.querySelectorAll('[data-parallax]').forEach(function(el) {
            createParallax(el, parseFloat(el.getAttribute('data-parallax')) || 0.3);
        });

        document.querySelectorAll('[data-stagger]').forEach(function(parent) {
            var children = parent.querySelectorAll('[data-stagger-item]');
            if (children.length) staggerGrid(children);
        });

        document.querySelectorAll('[data-magnetic]').forEach(function(el) {
            magneticHover(el);
        });

        document.querySelectorAll('[data-image-reveal]').forEach(function(el) {
            revealImage(el);
        });

        document.querySelectorAll('[data-float-reveal]').forEach(function(el) {
            createFloatingReveal([el]);
        });

        document.querySelectorAll('[data-scale-in]').forEach(function(el) {
            scaleIn([el]);
        });

        ScrollTrigger.refresh();
    }

    return {
        init: init,
        initPage: initPageAnimations,
        animateHero: animateHero,
        scrollReveal: createScrollReveal,
        animateCounters: animateCounters,
        parallax: createParallax,
        splitText: splitText,
        svgDraw: animateSVGDraw,
        staggerGrid: staggerGrid,
        magneticHover: magneticHover,
        revealImage: revealImage,
        cursorGlow: initCursorGlow,
        smoothLinks: smoothLinks,
        refresh: function() { ScrollTrigger.refresh(); }
    };
})();
