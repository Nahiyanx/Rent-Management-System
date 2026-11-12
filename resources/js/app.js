const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const dialogFocusableSelector =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

const dialogState = {
    current: null,
    lastFocused: null,
    observer: null,
    scheduled: false,
};

const visibleDialogs = () =>
    Array.from(document.querySelectorAll('[role="dialog"]')).filter(
        (dialog) => dialog.offsetWidth > 0 || dialog.offsetHeight > 0,
    );

const focusableWithin = (root) =>
    Array.from(root.querySelectorAll(dialogFocusableSelector)).filter(
        (node) => node.offsetWidth > 0 || node.offsetHeight > 0,
    );

const syncDialogs = () => {
    const dialogs = visibleDialogs();
    const topmost = dialogs[dialogs.length - 1] ?? null;

    document.documentElement.classList.toggle('dialog-open', dialogs.length > 0);

    if (topmost && topmost !== dialogState.current) {
        if (!dialogState.current) {
            dialogState.lastFocused = document.activeElement;
        }

        dialogState.current = topmost;

        requestAnimationFrame(() => {
            const firstField = Array.from(
                topmost.querySelectorAll('input:not([type="radio"]):not([type="hidden"]), select, textarea'),
            ).find((field) => field.offsetWidth > 0 || field.offsetHeight > 0);

            const first = firstField ?? focusableWithin(topmost)[0];

            (first ?? topmost).focus?.();
        });

        return;
    }

    if (!topmost && dialogState.current) {
        dialogState.current = null;

        const previous = dialogState.lastFocused;
        dialogState.lastFocused = null;

        if (previous && document.contains(previous) && typeof previous.focus === 'function') {
            previous.focus();
        }
    }
};

const scheduleDialogSync = () => {
    if (dialogState.scheduled) {
        return;
    }

    dialogState.scheduled = true;

    requestAnimationFrame(() => {
        dialogState.scheduled = false;
        syncDialogs();
    });
};

const startDialogManager = () => {
    if (dialogState.observer || !document.body) {
        return;
    }

    dialogState.observer = new MutationObserver(scheduleDialogSync);

    dialogState.observer.observe(document.body, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['style', 'class'],
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab' || !dialogState.current) {
            return;
        }

        const items = focusableWithin(dialogState.current);

        if (items.length === 0) {
            event.preventDefault();

            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('livewire:navigated', scheduleDialogSync);

    syncDialogs();
};

const registerAlpineComponents = () => {
    if (!window.Alpine) {
        return;
    }

    window.Alpine.data('sidebarNav', () => ({
        indicator: {
            top: 0,
            height: 0,
            ready: false,
        },

        init() {
            this.refreshIndicator = () => this.updateIndicator();

            this.$nextTick(() => this.updateIndicator());
            window.addEventListener('resize', this.refreshIndicator, { passive: true });
            document.addEventListener('livewire:navigated', this.refreshIndicator);

            if ('ResizeObserver' in window) {
                this.resizeObserver = new ResizeObserver(this.refreshIndicator);
                this.resizeObserver.observe(this.$el);
            }
        },

        updateIndicator() {
            const activeItem = this.$el.querySelector('[data-nav-active="true"]');

            if (!activeItem) {
                this.indicator.ready = false;

                return;
            }

            this.indicator = {
                top: activeItem.offsetTop,
                height: activeItem.offsetHeight,
                ready: true,
            };
        },

        destroy() {
            window.removeEventListener('resize', this.refreshIndicator);
            document.removeEventListener('livewire:navigated', this.refreshIndicator);
            this.resizeObserver?.disconnect();
        },
    }));

    window.Alpine.data('animatedMoney', (target = 0, symbol = '৳') => ({
        display: 0,
        target: Number(target) || 0,
        symbol: symbol || '৳',
        frame: null,

        init() {
            if (prefersReducedMotion()) {
                this.display = this.target;

                return;
            }

            const startedAt = performance.now();
            const duration = 900;

            const tick = (now) => {
                const progress = Math.min((now - startedAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);

                this.display = this.hasFraction
                    ? Math.round(this.target * eased * 100) / 100
                    : Math.round(this.target * eased);

                if (progress < 1) {
                    this.frame = requestAnimationFrame(tick);
                }
            };

            this.frame = requestAnimationFrame(tick);
        },

        destroy() {
            if (this.frame) {
                cancelAnimationFrame(this.frame);
            }
        },

        get hasFraction() {
            return Math.round(this.target * 100) % 100 !== 0;
        },

        get formatted() {
            const fractionDigits = this.hasFraction ? 2 : 0;

            return `${this.symbol}${new Intl.NumberFormat('en-US', {
                minimumFractionDigits: fractionDigits,
                maximumFractionDigits: fractionDigits,
            }).format(this.display)}`;
        },
    }));
};

document.addEventListener('alpine:init', registerAlpineComponents);

if (window.Alpine) {
    registerAlpineComponents();
}

startDialogManager();

let progressTimer;

document.addEventListener('livewire:navigate', () => {
    window.clearTimeout(progressTimer);
    progressTimer = window.setTimeout(() => {
        document.documentElement.classList.add('is-navigating');
    }, 110);
});

document.addEventListener('livewire:navigated', () => {
    window.clearTimeout(progressTimer);
    window.setTimeout(() => {
        document.documentElement.classList.remove('is-navigating');
    }, 160);
});
