/**
 * SBL Marketing Live Search & Global Omnisearch Engine
 * Provides instant live typing results (<1ms client-side + debounced backend API)
 */

export function registerSearch(Alpine) {
    const defaultQuickLinks = [
        {
            title: "Dashboard",
            subtitle: "Overview, analytics & quick metrics",
            url: "/dashboard",
            icon: "📊",
            category: "Navigation",
            badge: "Home",
        },
        {
            title: "Leads CRM",
            subtitle: "Lead pipeline, follow-ups & converted members",
            url: "/leads",
            icon: "👥",
            category: "Navigation",
            badge: "Leads",
        },
        {
            title: "Packages",
            subtitle: "Starter, National & International membership packs",
            url: "/packages",
            icon: "📦",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Ranks & Earnings",
            subtitle: "Career progression, rank criteria & incentives",
            url: "/ranks",
            icon: "🏆",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Counseling Guide",
            subtitle:
                "Investor vs Networker sales scripts & objection handling",
            url: "/counseling",
            icon: "🎯",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Commission Calculator",
            subtitle: "100-Week ROI & 10-Gen Matrix simulation",
            url: "/commission",
            icon: "🧮",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Official Links",
            subtitle: "SBL web portals, online stores & partner platforms",
            url: "/links",
            icon: "🔗",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Marketing Resources",
            subtitle: "Official leaflets, presentation decks, PDFs & brochures",
            url: "/resources",
            icon: "📁",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Team Explorer",
            subtitle: "10-Slot placement genealogy & tree mindmap",
            url: "/team",
            icon: "👥",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Abbreviation & Glossary",
            subtitle: "Official SBL definitions, terms & acronyms",
            url: "/abbreviations",
            icon: "📖",
            category: "Tools",
            badge: "Marketing",
        },
        {
            title: "Official Contacts",
            subtitle: "WhatsApp helplines & departmental directory",
            url: "/contacts",
            icon: "📞",
            category: "Tools",
            badge: "Marketing",
        },
    ];

    Alpine.data("globalOmnisearch", () => ({
        open: false,
        query: "",
        selectedIndex: 0,
        loading: false,
        results: [],
        categories: {},
        debounceTimer: null,
        activeTab: "all",

        init() {
            // Omnisearch modal disabled
        },

        toggleModal() {
            if (this.open) {
                this.closeModal();
            } else {
                this.openModal();
            }
        },

        openModal() {
            this.open = true;
            this.selectedIndex = 0;
            this.$nextTick(() => {
                const input = document.getElementById(
                    "global-omnisearch-input",
                );
                if (input) {
                    input.focus();
                    input.select();
                }
            });
            if (!this.query.trim()) {
                this.resetToQuickLinks();
            }
        },

        closeModal() {
            this.open = false;
        },

        resetToQuickLinks() {
            this.results = [...defaultQuickLinks];
            this.groupResults();
        },

        onInput() {
            const q = this.query.trim().toLowerCase();
            this.selectedIndex = 0;

            if (!q) {
                this.resetToQuickLinks();
                return;
            }

            // 1. Instant Client-Side Zero-Latency Search
            this.searchLocal(q);

            // 2. Debounced Backend API Search for Deep Database Records
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchBackendSearch(q);
            }, 180);
        },

        searchLocal(q) {
            const matches = [];

            // Search Navigation Tools
            for (const item of defaultQuickLinks) {
                if (
                    item.title.toLowerCase().includes(q) ||
                    item.subtitle.toLowerCase().includes(q)
                ) {
                    matches.push(item);
                }
            }

            // Search Live Client DATA if available (e.g. on Cloudflare Edge or injected in DOM)
            if (window.DATA) {
                // Leads
                if (Array.isArray(window.DATA.leads)) {
                    for (const l of window.DATA.leads) {
                        const nameMatch =
                            l.name && l.name.toLowerCase().includes(q);
                        const phoneMatch =
                            (l.phone && l.phone.includes(q)) ||
                            (l.mobile && l.mobile.includes(q)) ||
                            (l.whatsapp && l.whatsapp.includes(q));
                        const locMatch =
                            l.location && l.location.toLowerCase().includes(q);
                        if (nameMatch || phoneMatch || locMatch) {
                            matches.push({
                                title: l.name,
                                subtitle: `${l.mobile || l.phone || l.whatsapp || ""}${l.location ? " • " + l.location : ""} • [${l.stage || "Lead"}]`,
                                url: `/leads/${l.id}`,
                                icon: "👤",
                                category: "Leads",
                                badge: l.stage || "Lead",
                            });
                            if (matches.length >= 15) break;
                        }
                    }
                }

                // Team Members (Nodes)
                if (Array.isArray(window.DATA.nodes)) {
                    for (const n of window.DATA.nodes) {
                        const nameMatch =
                            n.member_name &&
                            n.member_name.toLowerCase().includes(q);
                        const codeMatch =
                            n.member_code &&
                            n.member_code.toLowerCase().includes(q);
                        const phoneMatch = n.phone && n.phone.includes(q);
                        if (nameMatch || codeMatch || phoneMatch) {
                            matches.push({
                                title: n.member_name,
                                subtitle: `Code: ${n.member_code || "SBL-" + n.id}${n.phone ? " • " + n.phone : ""}${n.slot_label ? " • " + n.slot_label : ""}`,
                                url: `/team/${n.id}`,
                                icon: "🌳",
                                category: "Team",
                                badge: n.member_code || "Member",
                            });
                            if (matches.length >= 25) break;
                        }
                    }
                }

                // Contacts
                if (Array.isArray(window.DATA.contacts)) {
                    for (const c of window.DATA.contacts) {
                        if (
                            (c.department &&
                                c.department.toLowerCase().includes(q)) ||
                            (c.contact_person &&
                                c.contact_person.toLowerCase().includes(q)) ||
                            (c.phone && c.phone.includes(q))
                        ) {
                            matches.push({
                                title:
                                    c.department +
                                    (c.contact_person
                                        ? ` (${c.contact_person})`
                                        : ""),
                                subtitle: `📞 ${c.phone}${c.whatsapp ? " • WA: " + c.whatsapp : ""}`,
                                url: "/contacts",
                                icon: "📞",
                                category: "Contacts",
                                badge: c.badge || "Helpline",
                            });
                        }
                    }
                }

                // Ecosystem / Official Links
                if (Array.isArray(window.DATA.ecosystem)) {
                    for (const e of window.DATA.ecosystem) {
                        if (
                            (e.title && e.title.toLowerCase().includes(q)) ||
                            (e.url && e.url.toLowerCase().includes(q)) ||
                            (e.category && e.category.toLowerCase().includes(q))
                        ) {
                            matches.push({
                                title: e.title,
                                subtitle: e.url,
                                url: e.url,
                                icon: "🔗",
                                category: "Links",
                                badge: e.category || "Link",
                                external: true,
                            });
                        }
                    }
                }
            }

            this.results = matches;
            this.groupResults();
        },

        async fetchBackendSearch(q) {
            try {
                this.loading = true;
                const response = await fetch(
                    `/api/search?q=${encodeURIComponent(q)}`,
                    {
                        headers: { Accept: "application/json" },
                    },
                );
                if (response.ok) {
                    const data = await response.json();
                    if (data && Array.isArray(data.results)) {
                        // Merge or replace with server results
                        this.results = data.results;
                        this.groupResults();
                    }
                }
            } catch (err) {
                // Silently fallback to client-side results
                console.debug("Search fetch fallback", err);
            } finally {
                this.loading = false;
            }
        },

        groupResults() {
            const groups = {};
            for (const item of this.results) {
                const cat = item.category || "Other";
                if (!groups[cat]) groups[cat] = [];
                groups[cat].push(item);
            }
            this.categories = groups;
        },

        navigateDown() {
            if (this.results.length === 0) return;
            this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
            this.scrollSelectedIntoView();
        },

        navigateUp() {
            if (this.results.length === 0) return;
            this.selectedIndex =
                (this.selectedIndex - 1 + this.results.length) %
                this.results.length;
            this.scrollSelectedIntoView();
        },

        scrollSelectedIntoView() {
            this.$nextTick(() => {
                const activeEl = document.querySelector(
                    `[data-search-idx="${this.selectedIndex}"]`,
                );
                if (activeEl) {
                    activeEl.scrollIntoView({ block: "nearest" });
                }
            });
        },

        selectCurrent() {
            if (this.results[this.selectedIndex]) {
                const item = this.results[this.selectedIndex];
                if (item.external) {
                    window.open(item.url, "_blank");
                } else {
                    window.location.href = item.url;
                }
            }
        },

        highlightMatch(text) {
            if (!this.query.trim() || !text) return text;
            const q = this.query.trim().replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
            const regex = new RegExp(`(${q})`, "gi");
            return text.replace(
                regex,
                '<mark class="bg-orange-100 text-orange-900 font-bold px-0.5 rounded">$1</mark>',
            );
        },
    }));

    // Register Team Member Live Dropdown search component
    Alpine.data("memberLiveSearch", () => ({
        open: false,
        searchQuery: "",
        results: [],
        selectedIndex: -1,
        debounceTimer: null,

        onInput() {
            const q = this.searchQuery.trim().toLowerCase();
            if (!q) {
                this.results = [];
                this.open = false;
                return;
            }

            // Quick client match first
            let localNodes = [];
            if (window.DATA && Array.isArray(window.DATA.nodes)) {
                localNodes = window.DATA.nodes
                    .filter((n) => {
                        return (
                            (n.member_name &&
                                n.member_name.toLowerCase().includes(q)) ||
                            (n.member_code &&
                                n.member_code.toLowerCase().includes(q)) ||
                            (n.phone && n.phone.includes(q))
                        );
                    })
                    .slice(0, 8)
                    .map((n) => ({
                        id: n.id,
                        member_name: n.member_name,
                        member_code: n.member_code || "SBL-" + n.id,
                        phone: n.phone,
                        rank_title: n.rank_title,
                        url: `/team/${n.id}`,
                    }));
            }

            this.results = localNodes;
            this.open = localNodes.length > 0;

            // Debounced API fetch
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `/team-search?search=${encodeURIComponent(q)}&json=1`,
                        {
                            headers: { Accept: "application/json" },
                        },
                    );
                    if (res.ok) {
                        const data = await res.json();
                        if (Array.isArray(data) && data.length > 0) {
                            this.results = data;
                            this.open = true;
                        }
                    }
                } catch (e) {
                    // Fallback to localNodes
                }
            }, 180);
        },
    }));
}

// In-Page Real-Time Live Filtering Helpers
// 1. Leads Table & Kanban Live Filter
window.filterLeadsLive = function (query) {
    const q = (query || "").trim().toLowerCase();

    // Filter Table Rows
    const tableRows = document.querySelectorAll("table tbody tr[data-lead-id]");
    let visibleCount = 0;

    tableRows.forEach((row) => {
        const text = row.innerText.toLowerCase();
        const matches = !q || text.includes(q);
        row.style.display = matches ? "" : "none";
        if (matches) visibleCount++;
    });

    // Filter Kanban Cards
    const kanbanCards = document.querySelectorAll("div[data-lead-id]");
    kanbanCards.forEach((card) => {
        if (card.closest("tbody")) return; // skip table rows
        const text = card.innerText.toLowerCase();
        const matches = !q || text.includes(q);
        card.style.display = matches ? "" : "none";
    });

    // Update Counter Badge if exists
    const counterBadge = document.getElementById("leads-live-counter");
    if (counterBadge) {
        if (q) {
            counterBadge.textContent = `Showing ${visibleCount} of ${tableRows.length} leads`;
            counterBadge.classList.remove("hidden");
        } else {
            counterBadge.classList.add("hidden");
        }
    }
};

// 2. Team Directory Table Live Filter
window.filterDirectoryLive = function (query) {
    const q = (query || "").trim().toLowerCase();
    const rows = document.querySelectorAll("table tbody tr[data-node-id]");
    let count = 0;

    rows.forEach((row) => {
        const text = row.innerText.toLowerCase();
        const matches = !q || text.includes(q);
        row.style.display = matches ? "" : "none";
        if (matches) count++;
    });

    const badge = document.getElementById("directory-live-counter");
    if (badge) {
        if (q) {
            badge.textContent = `Showing ${count} of ${rows.length} members`;
            badge.classList.remove("hidden");
        } else {
            badge.classList.add("hidden");
        }
    }
};

// 3. User Management Table Live Filter
window.filterUsersLive = function (query) {
    const q = (query || "").trim().toLowerCase();
    const rows = document.querySelectorAll("table tbody tr[data-user-id]");
    let count = 0;

    rows.forEach((row) => {
        const text = row.innerText.toLowerCase();
        const matches = !q || text.includes(q);
        row.style.display = matches ? "" : "none";
        if (matches) count++;
    });

    const badge = document.getElementById("users-live-counter");
    if (badge) {
        if (q) {
            badge.textContent = `Showing ${count} of ${rows.length} users`;
            badge.classList.remove("hidden");
        } else {
            badge.classList.add("hidden");
        }
    }
};
