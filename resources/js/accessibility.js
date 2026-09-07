export function enhanceAccessibility(Alpine) {
    let currentDialog = null;
    let returnFocus = null;
    let scheduled = false;
    const focusable = 'button:not([disabled]), a[href], input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex="0"]';
    const visible = element => element.getClientRects().length && getComputedStyle(element).visibility !== 'hidden';
    const update = () => {
        scheduled = false;
        const dialogs = [...document.querySelectorAll('[role="dialog"]')];
        const next = dialogs.reverse().find(visible) || null;
        if (next === currentDialog) return;
        if (next) {
            if (!currentDialog) returnFocus = document.activeElement;
            currentDialog = next;
            const heading = next.querySelector('h2,h3,h4');
            if (heading && !next.hasAttribute('aria-label')) next.setAttribute('aria-label', heading.textContent.trim());
            if (!next.contains(document.activeElement)) {
                (next.querySelector(focusable) || next).focus({preventScroll: true});
            }
            document.body.style.overflow = 'hidden';
        } else {
            currentDialog = null;
            document.body.style.overflow = '';
            if (returnFocus?.isConnected) returnFocus.focus({preventScroll: true});
            returnFocus = null;
        }
    };
    new MutationObserver(() => {
        if (!scheduled) { scheduled = true; requestAnimationFrame(update); }
    }).observe(document.body, {subtree: true, attributes: true, attributeFilter: ['style', 'class']});
    document.addEventListener('keydown', event => {
        const sidebar = document.querySelector('.app-sidebar[data-open="true"]');
        const region = currentDialog || (innerWidth < 1024 ? sidebar : null);
        if (!region) return;
        if (event.key === 'Escape' && currentDialog) {
            const state = currentDialog.getAttribute('x-show');
            if (/^[a-zA-Z_$][\w$]*$/.test(state || '')) Alpine.$data(currentDialog)[state] = false;
            event.preventDefault();
        }
        if (event.key === 'Tab') {
            const elements = [...region.querySelectorAll(focusable)].filter(visible);
            if (!elements.length) { event.preventDefault(); region.focus(); return; }
            const first = elements[0], last = elements.at(-1);
            if (event.shiftKey && (document.activeElement === first || !region.contains(document.activeElement))) {event.preventDefault(); last.focus();}
            else if (!event.shiftKey && (document.activeElement === last || !region.contains(document.activeElement))) {event.preventDefault(); first.focus();}
        }
    });
    document.querySelectorAll('label:not([for])').forEach((label, index) => {
        const input = label.querySelector('input,select,textarea') || label.parentElement.querySelector('input:not([type=hidden]),select,textarea');
        if (input) { input.id ||= `field-${index}`; label.htmlFor = input.id; }
    });
    document.querySelectorAll('button[title],a[title]').forEach(button => {
        if (!button.textContent.trim() && !button.hasAttribute('aria-label')) button.setAttribute('aria-label', button.title);
    });
    update();
}
