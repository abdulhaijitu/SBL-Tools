/**
 * SBL Toolkit Package Analytics Tracker
 * Supports standard GTM/GA4 dataLayer and custom DOM events.
 */
export function trackToolkitEvent(eventName, customParams = {}) {
    const baseParams = {
        package_id: 'membership-10000',
        package_name: 'SBL Membership Package',
        package_price_bdt: 10000,
        component_variant: 'feature',
        cta_location: 'card',
        timestamp: new Date().toISOString()
    };

    const payload = { ...baseParams, ...customParams };

    // 1. Dispatch custom DOM event
    window.dispatchEvent(new CustomEvent('toolkit-analytics', {
        detail: { event: eventName, ...payload },
        bubbles: true
    }));

    // 2. Google Tag Manager dataLayer support
    if (Array.isArray(window.dataLayer)) {
        window.dataLayer.push({
            event: eventName,
            ...payload
        });
    }

    // 3. Google Analytics 4 gtag support
    if (typeof window.gtag === 'function') {
        window.gtag('event', eventName, payload);
    }
}

// Attach globally for Alpine or inline access
if (typeof window !== 'undefined') {
    window.trackToolkitEvent = trackToolkitEvent;
}
