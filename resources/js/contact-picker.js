/**
 * SBL Tools - Mobile Contact Picker Helper
 * Supports Web Contact Picker API (Chrome Android / PWA)
 */

export function normalizePhoneNumber(raw) {
    if (!raw) return "";
    let cleaned = String(raw).replace(/[\s\-\(\)\.]/g, "");
    if (cleaned.startsWith("+880")) {
        cleaned = "0" + cleaned.substring(4);
    } else if (cleaned.startsWith("880")) {
        cleaned = "0" + cleaned.substring(3);
    }
    return cleaned;
}

export function isContactPickerSupported() {
    return "contacts" in navigator && "ContactsManager" in window;
}

export async function pickMobileContact(
    phoneTarget,
    nameTarget = null,
    options = {},
) {
    if (!isContactPickerSupported()) {
        const msg =
            options.fallbackMessage ||
            "কন্টাক্ট পিকার মূলত মোবাইল ক্রোম বা অ্যান্ড্রয়েড ব্রাউজারে কাজ করে।";
        if (window.dispatchEvent) {
            window.dispatchEvent(
                new CustomEvent("notify", {
                    detail: { message: msg, type: "info" },
                }),
            );
        } else {
            alert(msg);
        }
        return;
    }

    try {
        const props = ["tel", "name"];
        const contacts = await navigator.contacts.select(props, {
            multiple: false,
        });
        if (contacts && contacts.length > 0) {
            const contact = contacts[0];

            // 1. Fill phone number
            if (contact.tel && contact.tel.length > 0) {
                const phoneEl =
                    typeof phoneTarget === "string"
                        ? document.querySelector(phoneTarget)
                        : phoneTarget;
                if (phoneEl) {
                    const cleanPhone = normalizePhoneNumber(contact.tel[0]);
                    phoneEl.value = cleanPhone;
                    phoneEl.dispatchEvent(
                        new Event("input", { bubbles: true }),
                    );
                    phoneEl.dispatchEvent(
                        new Event("change", { bubbles: true }),
                    );
                }
            }

            // 2. Fill name if target is provided and currently empty
            if (nameTarget && contact.name && contact.name.length > 0) {
                const nameEl =
                    typeof nameTarget === "string"
                        ? document.querySelector(nameTarget)
                        : nameTarget;
                if (nameEl && (!nameEl.value || nameEl.value.trim() === "")) {
                    nameEl.value = contact.name[0];
                    nameEl.dispatchEvent(new Event("input", { bubbles: true }));
                    nameEl.dispatchEvent(
                        new Event("change", { bubbles: true }),
                    );
                }
            }

            if (typeof options.onSuccess === "function") {
                options.onSuccess(contact);
            }
        }
    } catch (err) {
        // User cancelled or browser denied
        if (err.name !== "AbortError") {
            console.warn("Contact picker error:", err);
        }
    }
}

// Attach globally to window
if (typeof window !== "undefined") {
    window.pickMobileContact = pickMobileContact;
    window.isContactPickerSupported = isContactPickerSupported;
    window.normalizePhoneNumber = normalizePhoneNumber;
}
