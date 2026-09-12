/**
 * SBL User Management - Interactive Alpine Component
 */

export function sanitizePhone(raw) {
    if (!raw) return "";
    let clean = raw.replace(/[^0-9]/g, "");
    if (clean.startsWith("8801") && clean.length === 13) {
        clean = clean.substring(2);
    }
    return clean;
}

export function formatBdPhone(raw) {
    const clean = sanitizePhone(raw);
    if (!clean) return "";
    if (clean.length === 11 && clean.startsWith("01")) {
        return `${clean.substring(0, 3)} ${clean.substring(3, 7)} ${clean.substring(7)}`;
    }
    return clean;
}

export function getWhatsAppLink(raw) {
    const clean = sanitizePhone(raw);
    if (!clean) return "";
    const full = clean.startsWith("01") ? "88" + clean : clean;
    return `https://wa.me/${full}`;
}

export function userManagement(config = {}) {
    return {
        users: Array.isArray(config.users) ? config.users : [],
        roles: Array.isArray(config.roles) ? config.roles : [],
        currentUserId: Number(config.currentUserId) || 0,
        currentUserIsSuperAdmin: Boolean(config.isSuperAdmin),
        csrfToken:
            config.csrfToken ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") ||
            "",

        // Search & Filters
        searchQuery: "",
        selectedRole: "",
        selectedStatus: "",

        // UI States
        detailsDrawerOpen: false,
        selectedUser: null,

        createModalOpen: false,
        editModalOpen: false,
        editingUser: {
            id: null,
            name: "",
            email: "",
            phone: "",
            designation: "",
            role_id: "",
            status: "active",
        },

        permissionsModalOpen: false,
        permissionUser: null,

        deleteWarningModalOpen: false,
        userToDelete: null,

        activeMenuId: null,

        init() {
            // Watch escape key
            window.addEventListener("keydown", (e) => {
                if (e.key === "Escape") {
                    this.closeAllDrawers();
                }
            });

            // If global DATA exists and updates
            window.addEventListener("sbl-users-updated", (e) => {
                if (e.detail && Array.isArray(e.detail.users)) {
                    this.users = e.detail.users;
                }
            });
        },

        closeAllDrawers() {
            this.detailsDrawerOpen = false;
            this.createModalOpen = false;
            this.editModalOpen = false;
            this.permissionsModalOpen = false;
            this.deleteWarningModalOpen = false;
            this.activeMenuId = null;
        },

        toggleMenu(userId, event) {
            if (event) event.stopPropagation();
            this.activeMenuId = this.activeMenuId === userId ? null : userId;
        },

        closeMenu() {
            this.activeMenuId = null;
        },

        get filteredUsers() {
            const query = (this.searchQuery || "").trim().toLowerCase();
            return this.users.filter((u) => {
                // Role filter
                if (this.selectedRole) {
                    const roleSlug = (u.role_slug || "").toLowerCase();
                    if (roleSlug !== this.selectedRole.toLowerCase()) {
                        return false;
                    }
                }

                // Status filter
                if (this.selectedStatus) {
                    if (
                        (u.status || "active").toLowerCase() !==
                        this.selectedStatus.toLowerCase()
                    ) {
                        return false;
                    }
                }

                // Search query
                if (!query) return true;

                const name = (u.name || "").toLowerCase();
                const phone = (u.phone || "").toLowerCase();
                const email = (u.email || "").toLowerCase();
                const designation = (u.designation || "").toLowerCase();
                const roleName = (u.role_name || "").toLowerCase();

                return (
                    name.includes(query) ||
                    phone.includes(query) ||
                    email.includes(query) ||
                    designation.includes(query) ||
                    roleName.includes(query)
                );
            });
        },

        get counts() {
            const all = this.users || [];
            return {
                total: all.length,
                active: all.filter((u) => (u.status || "active") === "active")
                    .length,
                staff: all.filter((u) => (u.role_slug || "") !== "super-admin")
                    .length,
                superAdmins: all.filter(
                    (u) => (u.role_slug || "") === "super-admin",
                ).length,
                inactive: all.filter((u) => u.status === "inactive").length,
            };
        },

        resetFilters() {
            this.searchQuery = "";
            this.selectedRole = "";
            this.selectedStatus = "";
        },

        // Details Drawer
        openDetails(user) {
            this.selectedUser = user;
            this.detailsDrawerOpen = true;
            this.activeMenuId = null;
        },

        closeDetails() {
            this.detailsDrawerOpen = false;
            this.selectedUser = null;
        },

        // Edit Modal
        openEdit(user, event) {
            if (event) event.stopPropagation();
            this.editingUser = {
                id: user.id,
                name: user.name || "",
                email: user.email || "",
                phone: user.phone || "",
                designation: user.designation || "",
                role_id:
                    user.role_id || (this.roles[0] ? this.roles[0].id : ""),
                status: user.status || "active",
            };
            this.editModalOpen = true;
            this.activeMenuId = null;
        },

        // Permissions Matrix Modal
        openPermissions(user, event) {
            if (event) event.stopPropagation();
            this.permissionUser = user;
            this.permissionsModalOpen = true;
            this.activeMenuId = null;
        },

        closePermissions() {
            this.permissionsModalOpen = false;
            this.permissionUser = null;
        },

        // Delete / Deactivate Safety
        promptDelete(user, event) {
            if (event) event.stopPropagation();
            this.activeMenuId = null;

            if (user.id === this.currentUserId) {
                alert("আপনি নিজের অ্যাকাউন্ট ডিলিট করতে পারবেন না।");
                return;
            }

            if (user.email === "admin@sbl.test") {
                alert(
                    "মূল সিস্টেম অ্যাডমিনিস্ট্রেটর অ্যাকাউন্ট ডিলিট করা সম্ভব নয়।",
                );
                return;
            }

            if (
                user.role_slug === "super-admin" &&
                !this.currentUserIsSuperAdmin
            ) {
                alert("সুপার অ্যাডমিন অ্যাকাউন্ট ডিলিট করার অনুমতি আপনার নেই।");
                return;
            }

            const leadsCount = Number(user.leads_count) || 0;
            const tasksCount = Number(user.tasks_count) || 0;

            if (leadsCount > 0 || tasksCount > 0) {
                this.userToDelete = user;
                this.deleteWarningModalOpen = true;
                return;
            }

            // Normal confirm if zero workload
            if (
                confirm(
                    `আপনি কি নিশ্চিত যে ইউজার '${user.name}' সম্পূর্ণ মুছে ফেলতে চান?`,
                )
            ) {
                this.submitDeleteForm(user.id);
            }
        },

        submitDeleteForm(userId, force = false) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = `/users/${userId}`;

            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value =
                this.csrfToken ||
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ||
                "";
            form.appendChild(csrfInput);

            const methodInput = document.createElement("input");
            methodInput.type = "hidden";
            methodInput.name = "_method";
            methodInput.value = "DELETE";
            form.appendChild(methodInput);

            if (force) {
                const forceInput = document.createElement("input");
                forceInput.type = "hidden";
                forceInput.name = "force_delete";
                forceInput.value = "1";
                form.appendChild(forceInput);
            }

            document.body.appendChild(form);
            form.submit();
        },

        deactivateUser(user) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = `/users/${user.id}`;

            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value =
                this.csrfToken ||
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ||
                "";
            form.appendChild(csrfInput);

            const methodInput = document.createElement("input");
            methodInput.type = "hidden";
            methodInput.name = "_method";
            methodInput.value = "PUT";
            form.appendChild(methodInput);

            const fields = {
                name: user.name,
                phone: user.phone || "",
                email: user.email || "",
                designation: user.designation || "",
                role_id: user.role_id,
                status: "inactive",
            };

            for (const [key, val] of Object.entries(fields)) {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = key;
                input.value = val;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        },

        // Helpers
        formatPhone(phone) {
            return formatBdPhone(phone);
        },

        getWhatsApp(phone) {
            return getWhatsAppLink(phone);
        },

        getRoleColor(roleSlug) {
            switch (roleSlug) {
                case "super-admin":
                    return "bg-purple-100 text-purple-800 border-purple-200";
                case "sales-manager":
                    return "bg-indigo-100 text-indigo-800 border-indigo-200";
                case "sales-agent":
                    return "bg-emerald-100 text-emerald-800 border-emerald-200";
                case "marketing-officer":
                    return "bg-amber-100 text-amber-800 border-amber-200";
                case "member":
                    return "bg-sky-100 text-sky-800 border-sky-200";
                case "demo-member":
                    return "bg-slate-100 text-slate-700 border-slate-300";
                default:
                    return "bg-slate-100 text-slate-700 border-slate-200";
            }
        },

        getRoleBgIcon(roleSlug) {
            switch (roleSlug) {
                case "super-admin":
                    return "🛡️";
                case "sales-manager":
                    return "👔";
                case "sales-agent":
                    return "💼";
                case "marketing-officer":
                    return "📣";
                default:
                    return "👤";
            }
        },

        getAvatarLetter(name) {
            if (!name) return "U";
            return name.trim().charAt(0).toUpperCase();
        },
    };
}

export function registerUsers(Alpine) {
    Alpine.data("userManagement", (config = {}) => userManagement(config));
}

if (typeof window !== "undefined") {
    window.userManagement = userManagement;
    window.registerUsers = registerUsers;
}
