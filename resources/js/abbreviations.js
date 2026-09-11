const FORMULAS = {
    AOV: "Total Sales Revenue ÷ Total Orders",
    ROAS: "Sales Revenue ÷ Ad Spend",
    CPA: "Ad Spend ÷ Total Confirmed Orders",
    CPM: "(Ad Spend ÷ Total Impressions) × 1,000",
    CPC: "Ad Spend ÷ Total Clicks",
    CTR: "(Total Clicks ÷ Total Impressions) × 100",
    CVR: "(Total Orders ÷ Total Landing Page Visitors) × 100",
    CAC: "Total Acquisition Costs ÷ Total New Customers",
    COGS: "Direct Material + Labor + Sourcing / Purchase Cost",
    "P&L": "Total Gross Revenues − (COGS + Ad Spend + Operations + Returns)"
};

const EXAMPLES = {
    ROAS: {
        metric1: "Ad Spend: ৳5,000",
        metric2: "Sales: ৳20,000",
        result: "4X ROAS",
        note_bn: "বিজ্ঞাপনে ৫,০০০ টাকা খরচ করে ২০,০০০ টাকার সেলস পাওয়া গেলে আরও বেশি বাজেট স্কেল করা যায়।"
    },
    CTR: {
        metric1: "Clicks: 200",
        impressions: "Impressions: 10,000",
        result: "2.0% CTR",
        note_bn: "সাধারণত ২% বা তার বেশি CTR থাকলে অ্যাড কপি ও ভিজ্যুয়াল আকর্ষণীয় বলে বিবেচনা করা হয়।"
    },
    CVR: {
        metric1: "Orders: 3",
        metric2: "Visitors: 100",
        result: "3.0% Conversion Rate",
        note_bn: "১০০ জন ল্যান্ডিং পেজে ভিজিট করে ৩ জন অর্ডার কনফার্ম করলে কনভার্সন রেট ৩%।"
    },
    AOV: {
        metric1: "Total Sales: ৳50,000",
        metric2: "Total Orders: 25",
        result: "৳2,000 AOV",
        note_bn: "বান্ডেল অফার বা আপসেল দিয়ে প্রতি অর্ডারের গড় মান বৃদ্ধি করা সম্ভব।"
    },
    CPA: {
        metric1: "Ad Spend: ৳6,000",
        metric2: "Orders: 20",
        result: "৳300 CPA",
        note_bn: "প্রতিটি কনফার্মড অর্ডারের জন্য বিজ্ঞাপনে খরচ ৩০০ টাকা।"
    }
};

const SBL_SPECIFIC = new Set(["SBL", "BV", "PV", "MB", "GB", "ROI", "MR", "KYC"]);

const ESSENTIAL_TERMS = ["SBL", "COD", "BV", "ROI", "MB", "GB", "ROAS", "RTO"];

const RELATED_TERMS = {
    ROAS: ["CPA", "CTR", "CPC", "CVR", "AOV"],
    CPA: ["ROAS", "CPC", "CVR", "CAC"],
    CPC: ["CTR", "CPM", "CPA", "ROAS"],
    CTR: ["CPC", "CPM", "CVR", "UGC"],
    CVR: ["CTR", "LP", "CPA", "ROAS"],
    CPM: ["CPC", "CTR", "ROAS"],
    AOV: ["ROAS", "COGS", "P&L", "CVR"],
    CAC: ["LTV", "CPA", "ROAS"],
    LTV: ["CAC", "AOV", "ROI"],
    LP: ["CVR", "CTR", "UGC"],
    UGC: ["CTR", "CVR", "LP"],
    BV: ["PV", "MB", "GB", "SBL"],
    PV: ["BV", "MB", "GB", "SBL"],
    MB: ["BV", "PV", "GB", "ROI"],
    GB: ["MB", "BV", "PV", "SBL"],
    SBL: ["BV", "PV", "MB", "GB", "MR"],
    ROI: ["MB", "P&L", "COGS", "ROAS"],
    COD: ["RTO", "NDR", "AWB", "POD"],
    RTO: ["NDR", "COD", "AWB", "3PL"],
    NDR: ["RTO", "AWB", "COD", "TAT"],
    AWB: ["POD", "RTO", "3PL", "TAT"],
    "3PL": ["AWB", "TAT", "SLA", "POD"],
    TAT: ["SLA", "3PL", "AWB"],
    SLA: ["TAT", "3PL", "POD"],
    POD: ["AWB", "COD", "3PL"],
    SKU: ["MOQ", "COGS", "POS"],
    MOQ: ["SKU", "COGS", "B2B"],
    B2B: ["B2C", "D2C", "MR"],
    B2C: ["B2B", "D2C", "COD"],
    D2C: ["B2C", "LP", "ROAS"],
    MR: ["SBL", "B2B", "B2C"],
    COGS: ["P&L", "AOV", "SKU"],
    "P&L": ["COGS", "ROI", "MFS"],
    MFS: ["COD", "POS", "P&L"],
    POS: ["SKU", "MFS", "COGS"],
    KYC: ["OTP", "SBL", "MFS"],
    OTP: ["KYC", "MFS"]
};

export function registerAbbreviations(Alpine) {
    Alpine.data("abbreviationManager", () => ({
        terms: [],
        canManage: false,
        search: "",
        categoryFilter: "all",
        sortBy: "recommended", // 'recommended', 'az', 'category'
        editing: false,
        busy: false,
        error: "",
        form: {},

        // Term Details Drawer & Bottom Sheet
        selectedTerm: null,
        drawerOpen: false,

        // Saved / Favorites
        savedTerms: [],

        // Quick Learning Mode
        learningOpen: false,
        learningIndex: 0,
        learningList: [],

        init() {
            try {
                this.terms = JSON.parse(this.$el.dataset.terms || "[]");
            } catch {
                this.terms = [];
            }
            this.canManage = this.$el.dataset.canManage === "1";

            // Load saved favorites from localStorage
            try {
                const saved = localStorage.getItem("sbl_saved_terms");
                this.savedTerms = saved ? JSON.parse(saved) : [];
            } catch {
                this.savedTerms = [];
            }

            // Check URL query param for deep-linking (e.g. ?term=ROI or #term-ROI)
            this.$nextTick(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const queryTerm = urlParams.get("term") || (window.location.hash ? window.location.hash.replace("#term-", "") : "");
                if (queryTerm) {
                    this.openByCode(queryTerm);
                }
            });
        },

        // Categories with live counts
        get categories() {
            const list = [
                { slug: "all", name_en: "All Terms", name_bn: "সকল টার্ম", icon: "✨", count: this.terms.length },
                { slug: "sbl", name_en: "SBL Related", name_bn: "SBL সম্পর্কিত", icon: "🌐", count: this.terms.filter(t => this.isSblSpecific(t.code)).length },
                { slug: "saved", name_en: "My Terms", name_bn: "আমার সংরক্ষিত", icon: "⭐", count: this.savedTerms.length },
                { slug: "ecommerce", name_en: "E-Commerce", name_bn: "ই-কমার্স", icon: "🛒", count: this.terms.filter(t => t.category_slug === "ecommerce").length },
                { slug: "marketing", name_en: "Marketing", name_bn: "মার্কেটিং", icon: "🎯", count: this.terms.filter(t => t.category_slug === "marketing").length },
                { slug: "logistics", name_en: "Logistics", name_bn: "লজিস্টিকস", icon: "🚚", count: this.terms.filter(t => t.category_slug === "logistics").length },
                { slug: "network", name_en: "Commission", name_bn: "কমিশন ও নেটওয়ার্ক", icon: "⚡", count: this.terms.filter(t => t.category_slug === "network").length },
                { slug: "finance", name_en: "Finance", name_bn: "ফাইন্যান্স", icon: "💼", count: this.terms.filter(t => t.category_slug === "finance").length }
            ];
            return list.filter(c => c.slug === "saved" || c.count > 0);
        },

        // Essential terms for "Start Here"
        get essentialTerms() {
            return this.terms.filter(t => ESSENTIAL_TERMS.includes(t.code));
        },

        // Filtered & Sorted terms
        get filtered() {
            const query = this.search.trim().toLowerCase();
            let result = this.terms;

            // 1. Category Filter
            if (this.categoryFilter === "saved") {
                result = result.filter(t => this.isFavorite(t.code));
            } else if (this.categoryFilter === "sbl") {
                result = result.filter(t => this.isSblSpecific(t.code));
            } else if (this.categoryFilter !== "all") {
                result = result.filter(t => t.category_slug === this.categoryFilter);
            }

            // 2. Search Query Filter
            if (query) {
                result = result.filter(t =>
                    ["code", "name", "meaning_bn", "description_bn", "tag", "category"].some(k =>
                        String(t[k] || "").toLowerCase().includes(query)
                    )
                );
            }

            // 3. Sorting
            if (this.sortBy === "az") {
                result = [...result].sort((a, b) => (a.code || "").localeCompare(b.code || ""));
            } else if (this.sortBy === "category") {
                result = [...result].sort((a, b) => (a.category || "").localeCompare(b.category || ""));
            }

            return result;
        },

        isSblSpecific(code) {
            return SBL_SPECIFIC.has(String(code).toUpperCase());
        },

        getFormula(code) {
            return FORMULAS[code] || null;
        },

        getExample(code) {
            return EXAMPLES[code] || null;
        },

        getRelatedTerms(code) {
            const relCodes = RELATED_TERMS[code] || [];
            return this.terms.filter(t => relCodes.includes(t.code));
        },

        // Favorites management
        isFavorite(code) {
            return this.savedTerms.includes(code);
        },

        toggleFavorite(code) {
            if (this.isFavorite(code)) {
                this.savedTerms = this.savedTerms.filter(c => c !== code);
                this.$dispatch("notify", { message: code + " removed from My Terms." });
            } else {
                this.savedTerms.push(code);
                this.$dispatch("notify", { message: code + " saved to My Terms." });
            }
            try {
                localStorage.setItem("sbl_saved_terms", JSON.stringify(this.savedTerms));
            } catch (e) {
                console.error("Failed to save term:", e);
            }
        },

        // Term Details Drawer
        openDetails(term) {
            this.selectedTerm = term;
            this.drawerOpen = true;
            try {
                const url = new URL(window.location.href);
                url.searchParams.set("term", term.code);
                window.history.replaceState({}, "", url.toString());
            } catch {}
        },

        closeDetails() {
            this.drawerOpen = false;
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete("term");
                window.history.replaceState({}, "", url.pathname + (url.search ? url.search : ""));
            } catch {}
        },

        openByCode(code) {
            const term = this.terms.find(t => String(t.code).toUpperCase() === String(code).toUpperCase());
            if (term) {
                this.openDetails(term);
            }
        },

        // Copy formatted term
        async copyTerm(term) {
            if (!term) return;
            const text = `${term.code} — ${term.name}\nMeaning: ${term.meaning_bn}\nCategory: ${term.category || term.category_slug}`;
            try {
                await navigator.clipboard.writeText(text);
                this.$dispatch("notify", { message: "Copied " + term.code + " to clipboard!" });
            } catch {
                this.copyFallback(text);
            }
        },

        copyFallback(text) {
            const el = document.createElement("textarea");
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand("copy");
            document.body.removeChild(el);
            this.$dispatch("notify", { message: "Copied to clipboard!" });
        },

        // Native share API
        async shareTerm(term) {
            if (!term) return;
            const text = `${term.code} — ${term.name}\n${term.meaning_bn}\nLearn more on SBL Business Glossary.`;
            const shareUrl = window.location.origin + "/abbreviations?term=" + encodeURIComponent(term.code);

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: `${term.code} - ${term.name}`,
                        text: text,
                        url: shareUrl
                    });
                    return;
                } catch (e) {
                    if (e.name === "AbortError") return;
                }
            }

            // Fallback: WhatsApp share link
            const waUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text + "\n" + shareUrl);
            window.open(waUrl, "_blank");
        },

        // Quick Learning Mode
        startLearning() {
            const pool = this.filtered.length > 0 ? this.filtered : this.terms;
            // Shuffle and pick 5
            const shuffled = [...pool].sort(() => 0.5 - Math.random());
            this.learningList = shuffled.slice(0, 5);
            this.learningIndex = 0;
            this.learningOpen = true;
        },

        nextLearning() {
            if (this.learningIndex < this.learningList.length - 1) {
                this.learningIndex++;
            } else {
                this.learningOpen = false;
                this.$dispatch("notify", { message: "Great job! You reviewed 5 terms." });
            }
        },

        prevLearning() {
            if (this.learningIndex > 0) {
                this.learningIndex--;
            }
        },

        // Admin Edit Form
        edit(term) {
            this.form = term
                ? { ...term }
                : {
                      code: "",
                      name: "",
                      category_slug: "ecommerce",
                      meaning_bn: "",
                      description_bn: "",
                      icon: "📖",
                      tag: "",
                      needs_review: 0
                  };
            this.error = "";
            this.editing = true;
            this.$nextTick(() => {
                const input = this.$el.querySelector("input[data-autofocus]");
                if (input) input.focus();
            });
        },

        async request(path, method, body) {
            const response = await fetch(path, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')?.content || ""
                },
                body: body ? JSON.stringify(body) : undefined
            });
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(
                    data.errors
                        ? Object.values(data.errors).flat().join(" ")
                        : data.message || "Unable to save changes. Please try again."
                );
            }
            return response.status === 204 ? null : response.json();
        },

        async save() {
            if (this.busy) return;
            this.busy = true;
            this.error = "";
            try {
                const term = await this.request(
                    "/abbreviations" + (this.form.id ? "/" + this.form.id : ""),
                    this.form.id ? "PUT" : "POST",
                    this.form
                );
                const index = this.terms.findIndex(t => t.id === term.id);
                if (index < 0) this.terms.unshift(term);
                else this.terms.splice(index, 1, term);

                if (this.selectedTerm && this.selectedTerm.id === term.id) {
                    this.selectedTerm = term;
                }

                this.editing = false;
                this.$dispatch("notify", {
                    message: "Term saved successfully."
                });
            } catch (e) {
                this.error = e.message;
            } finally {
                this.busy = false;
            }
        },

        async remove(term) {
            if (this.busy || !confirm("Delete " + term.code + "? This cannot be undone.")) return;
            this.busy = true;
            this.error = "";
            try {
                await this.request("/abbreviations/" + term.id, "DELETE");
                this.terms = this.terms.filter(t => t.id !== term.id);
                if (this.selectedTerm && this.selectedTerm.id === term.id) {
                    this.drawerOpen = false;
                }
                if (this.form.id === term.id) this.editing = false;
                this.$dispatch("notify", { message: "Term deleted." });
            } catch (e) {
                this.error = e.message;
            } finally {
                this.busy = false;
            }
        },

        toggleReviewStatus(term) {
            term.needs_review = term.needs_review ? 0 : 1;
            this.$dispatch("notify", {
                message: term.code + " marked as " + (term.needs_review ? "Needs Review." : "Verified.")
            });
        }
    }));
}
