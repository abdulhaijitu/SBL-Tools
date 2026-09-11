/**
 * SBL Contact & Support Directory - Interactive Alpine Manager
 */

export function sanitizePhone(raw) {
    if (!raw) return "";
    return raw.replace(/[^0-9+]/g, "");
}

export function sanitizeWhatsApp(raw, phone) {
    let num = (raw || phone || "").replace(/[^0-9]/g, "");
    if (!num) return "";
    if (num.startsWith("880")) return num;
    if (num.startsWith("01")) return "88" + num;
    return num;
}

export function calculateOperatingStatus(contact, isBn) {
    if (!contact) {
        return {
            isOpen: false,
            status: "closed",
            label: isBn ? "বন্ধ" : "Closed",
            badgeClass: "bg-slate-50 text-slate-700 border-slate-200",
            dotClass: "bg-slate-400",
        };
    }

    if (
        contact.is_24_hours ||
        (contact.hours_en && contact.hours_en.toLowerCase().includes("24/7"))
    ) {
        return {
            isOpen: true,
            status: "24_7",
            label: isBn ? "২৪/৭ খোলা" : "Open 24/7",
            badgeClass:
                "bg-emerald-50 text-emerald-700 border-emerald-200 ring-1 ring-emerald-500/20",
            dotClass: "bg-emerald-500 animate-pulse",
        };
    }

    const now = new Date();
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    const bdDate = new Date(utc + 3600000 * 6);
    const day = bdDate.getDay(); // 0: Sun, 1: Mon, ..., 5: Fri, 6: Sat
    const hour = bdDate.getHours();
    const min = bdDate.getMinutes();
    const currentMins = hour * 60 + min;

    // Check days if specified (e.g. "Sunday - Thursday")
    if (contact.days) {
        const d = contact.days.toLowerCase();
        if (d.includes("sun - thu") || d.includes("sun-thu")) {
            if (day === 5 || day === 6) {
                // Friday or Saturday
                return {
                    isOpen: false,
                    status: "weekend",
                    label: isBn ? "আজ সাপ্তাহিক ছুটি" : "Closed Today",
                    badgeClass: "bg-amber-50 text-amber-700 border-amber-200",
                    dotClass: "bg-amber-500",
                };
            }
        }
    }

    let openMins = 10 * 60; // 10:00 AM
    let closeMins = 20 * 60; // 08:00 PM
    if (contact.open_time && contact.close_time) {
        const [oh, om] = contact.open_time.split(":").map(Number);
        const [ch, cm] = contact.close_time.split(":").map(Number);
        if (!isNaN(oh) && !isNaN(ch)) {
            openMins = oh * 60 + (om || 0);
            closeMins = ch * 60 + (cm || 0);
        }
    }

    if (currentMins >= openMins && currentMins < closeMins) {
        return {
            isOpen: true,
            status: "open",
            label: isBn ? "এখন খোলা" : "Open Now",
            badgeClass:
                "bg-emerald-50 text-emerald-700 border-emerald-200 ring-1 ring-emerald-500/20",
            dotClass: "bg-emerald-500 animate-pulse",
        };
    } else {
        return {
            isOpen: false,
            status: "closed",
            label: isBn ? "এখন বন্ধ" : "Closed Now",
            badgeClass: "bg-slate-100 text-slate-600 border-slate-200",
            dotClass: "bg-slate-400",
        };
    }
}

export function registerContacts(Alpine) {
    Alpine.data("contactsManager", () => ({
        contacts: [],
        canManage: false,
        searchQuery: "",
        selectedCategory: "all",
        selectedContact: null,
        drawerOpen: false,
        createModalOpen: false,
        editModalOpen: false,
        copiedField: null,
        editingContact: {
            id: null,
            department_en: "",
            department_bn: "",
            contact_person: "",
            phone: "",
            whatsapp: "",
            email: "",
            available_hours: "10:00 AM - 08:00 PM",
            hours_en: "10:00 AM - 08:00 PM",
            hours_bn: "সকাল ১০:০০ - রাত ০৮:০০",
            days: "Daily",
            description_en: "",
            description_bn: "",
            service_label_en: "",
            service_label_bn: "",
            category: "customer_care",
            verification_status: "needs_review",
            is_primary: false,
            is_24_hours: false,
            icon: "📞",
            sort_order: 0,
        },

        init() {
            try {
                this.contacts =
                    window.DATA &&
                    Array.isArray(window.DATA.contacts) &&
                    window.DATA.contacts.length > 0
                        ? window.DATA.contacts
                        : JSON.parse(this.$el.dataset.contacts || "[]");
            } catch {
                this.contacts = [];
            }

            this.canManage = this.$el.dataset.canManage === "1";

            // Listen for live updates from D1 background worker
            window.addEventListener("contacts-updated", (e) => {
                if (e.detail && Array.isArray(e.detail.contacts)) {
                    this.contacts = e.detail.contacts;
                }
            });

            // Deep-link check (e.g. ?contact=2 or #contact-2)
            this.$nextTick(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const queryId =
                    urlParams.get("contact") ||
                    (window.location.hash
                        ? window.location.hash.replace("#contact-", "")
                        : "");
                if (queryId) {
                    const found = this.contacts.find(
                        (c) => String(c.id) === String(queryId),
                    );
                    if (found) this.openDrawer(found);
                }
            });
        },

        get isBn() {
            return this.$store.lang && this.$store.lang.current === "bn";
        },

        get categories() {
            const list = [
                {
                    id: "all",
                    name_en: "All Contacts",
                    name_bn: "সকল সাপোর্ট",
                    icon: "✨",
                },
                {
                    id: "customer_care",
                    name_en: "Customer Care",
                    name_bn: "কাস্টমার কেয়ার",
                    icon: "🎧",
                },
                {
                    id: "dropshipping",
                    name_en: "Dropshipping",
                    name_bn: "ড্রপশিপিং",
                    icon: "📦",
                },
                {
                    id: "business",
                    name_en: "Business & Investor",
                    name_bn: "বিজনেস ও ইনভেস্টর",
                    icon: "💼",
                },
                {
                    id: "technical",
                    name_en: "Technical Support",
                    name_bn: "টেকনিক্যাল সাপোর্ট",
                    icon: "🛠️",
                },
                {
                    id: "training",
                    name_en: "Training & Counseling",
                    name_bn: "ট্রেনিং ও কাউন্সেলিং",
                    icon: "🎓",
                },
                {
                    id: "accounts",
                    name_en: "Accounts & Payout",
                    name_bn: "অ্যাকাউন্টস ও পে-আউট",
                    icon: "💳",
                },
            ];

            return list.map((cat) => {
                const count =
                    cat.id === "all"
                        ? this.contacts.length
                        : this.contacts.filter((c) => c.category === cat.id)
                              .length;
                return { ...cat, count };
            });
        },

        get featuredContacts() {
            return this.contacts
                .filter((c) => Boolean(Number(c.is_primary)))
                .slice(0, 3);
        },

        get filteredContacts() {
            let list = this.contacts;

            if (this.selectedCategory !== "all") {
                list = list.filter((c) => c.category === this.selectedCategory);
            }

            const q = this.searchQuery.trim().toLowerCase();
            if (q) {
                list = list.filter((c) => {
                    const str = [
                        c.department_en,
                        c.department_bn,
                        c.department,
                        c.contact_person,
                        c.phone,
                        c.whatsapp,
                        c.email,
                        c.service_label_en,
                        c.service_label_bn,
                        c.badge,
                        c.description_en,
                        c.description_bn,
                        c.description,
                        c.category,
                    ]
                        .filter(Boolean)
                        .join(" ")
                        .toLowerCase();
                    return str.includes(q);
                });
            }

            return list;
        },

        getDeptName(c) {
            if (!c) return "";
            if (this.isBn) {
                return c.department_bn || c.department || c.department_en || "";
            }
            return c.department_en || c.department || c.department_bn || "";
        },

        getServiceLabel(c) {
            if (!c) return "";
            if (this.isBn) {
                return (
                    c.service_label_bn || c.badge || c.service_label_en || ""
                );
            }
            return c.service_label_en || c.badge || c.service_label_bn || "";
        },

        getDescription(c) {
            if (!c) return "";
            if (this.isBn) {
                return (
                    c.description_bn || c.description || c.description_en || ""
                );
            }
            return c.description_en || c.description || c.description_bn || "";
        },

        getHours(c) {
            if (!c) return "";
            if (this.isBn) {
                return (
                    c.hours_bn ||
                    c.available_hours ||
                    c.hours_en ||
                    "১০:০০ AM - ০৮:০০ PM"
                );
            }
            return (
                c.hours_en ||
                c.available_hours ||
                c.hours_bn ||
                "10:00 AM - 08:00 PM"
            );
        },

        getCleanPhone(c) {
            return sanitizePhone(c?.phone);
        },

        getCleanWa(c) {
            return sanitizeWhatsApp(c?.whatsapp, c?.phone);
        },

        getStatus(c) {
            return calculateOperatingStatus(c, this.isBn);
        },

        getVerification(c) {
            const status = c?.verification_status || "needs_review";
            if (status === "verified") {
                return {
                    status: "verified",
                    label: this.isBn ? "✓ ভেরিফাইড" : "✓ Verified",
                    badgeClass:
                        "bg-emerald-50 text-emerald-700 border-emerald-200",
                    description: this.isBn
                        ? "এসবিএল কর্তৃক নিশ্চিত অফিসিয়াল সাপোর্ট চ্যানেল"
                        : "Official confirmed SBL support channel",
                    icon: "🛡️",
                };
            }
            if (status === "inactive") {
                return {
                    status: "inactive",
                    label: this.isBn ? "নিষ্ক্রিয়" : "Inactive",
                    badgeClass: "bg-rose-50 text-rose-700 border-rose-200",
                    description: this.isBn
                        ? "এই হটলাইনটি বর্তমানে সাময়িকভাবে বন্ধ রয়েছে"
                        : "This hotline is currently temporarily inactive",
                    icon: "⛔",
                };
            }
            // default / needs_review
            return {
                status: "needs_review",
                label: this.isBn ? "⚠️ রিভিউ প্রয়োজন" : "⚠️ Needs Review",
                badgeClass: "bg-amber-50 text-amber-700 border-amber-200",
                description: this.isBn
                    ? "প্লেসহোল্ডার তথ্য — অফিসিয়াল নম্বর শীঘ্রই হালনাগাদ করা হবে"
                    : "Placeholder contact — awaiting official verification",
                icon: "⚠️",
            };
        },

        openDrawer(c) {
            this.selectedContact = c;
            this.drawerOpen = true;
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, "", `#contact-${c.id}`);
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.selectedContact = null;
            if (window.history && window.history.replaceState) {
                const url = window.location.pathname + window.location.search;
                window.history.replaceState(null, "", url);
            }
        },

        copyToClipboard(text, title) {
            if (!text) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copiedField = title;
                    setTimeout(() => {
                        this.copiedField = null;
                    }, 2000);
                    window.dispatchEvent(
                        new CustomEvent("notify", {
                            detail: {
                                message: this.isBn
                                    ? title + " কপি হয়েছে!"
                                    : title + " copied to clipboard!",
                                type: "success",
                            },
                        }),
                    );
                });
            }
        },

        shareContact(c) {
            if (!c) return;
            const dept = this.getDeptName(c);
            const hours = this.getHours(c);
            const shareText =
                `📞 SBL Support: ${dept}\n` +
                (c.contact_person ? `👤 Contact: ${c.contact_person}\n` : "") +
                `☎️ Phone: ${c.phone}\n` +
                (c.whatsapp ? `💬 WhatsApp: ${c.whatsapp}\n` : "") +
                (c.email ? `✉️ Email: ${c.email}\n` : "") +
                `🕒 Hours: ${hours}\n` +
                `🌐 SBL Contact Directory: ${window.location.origin}/contacts#contact-${c.id}`;

            if (navigator.share) {
                navigator
                    .share({
                        title: `${dept} - SBL Support`,
                        text: shareText,
                        url: `${window.location.origin}/contacts#contact-${c.id}`,
                    })
                    .catch(() => {});
            } else {
                this.copyToClipboard(
                    shareText,
                    this.isBn ? "কন্টাক্ট তথ্য" : "Contact Details",
                );
            }
        },

        openEditModal(c) {
            this.editingContact = {
                id: c.id,
                department_en: c.department_en || c.department || "",
                department_bn: c.department_bn || c.department || "",
                contact_person: c.contact_person || "",
                phone: c.phone || "",
                whatsapp: c.whatsapp || c.phone || "",
                email: c.email || "",
                available_hours:
                    c.available_hours || c.hours_en || "10:00 AM - 08:00 PM",
                hours_en:
                    c.hours_en || c.available_hours || "10:00 AM - 08:00 PM",
                hours_bn: c.hours_bn || "সকাল ১০:০০ - রাত ০৮:০০",
                days: c.days || "Daily",
                description_en: c.description_en || c.description || "",
                description_bn: c.description_bn || c.description || "",
                service_label_en: c.service_label_en || c.badge || "",
                service_label_bn: c.service_label_bn || c.badge || "",
                category: c.category || "customer_care",
                verification_status: c.verification_status || "needs_review",
                is_primary: Boolean(Number(c.is_primary)),
                is_24_hours: Boolean(Number(c.is_24_hours)),
                icon: c.icon || "📞",
                sort_order: c.sort_order || 0,
            };
            this.editModalOpen = true;
        },
    }));
}
