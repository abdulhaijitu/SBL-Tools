/**
 * SBL Roles & Permissions Management - Interactive Alpine Component
 */

export function roleManagement(config = {}) {
    return {
        roles: Array.isArray(config.roles) ? config.roles : [],
        permissions: Array.isArray(config.permissions)
            ? config.permissions
            : [],
        allPermissionsCount: Number(config.allPermissionsCount) || 20,
        currentUserIsSuperAdmin: Boolean(config.isSuperAdmin),

        // Navigation & Filter States
        activeTab:
            new URLSearchParams(location.search).get("tab") === "permissions" ||
            location.hash === "#permissions"
                ? "permissions"
                : "roles",
        roleFilter: "all", // 'all', 'system', 'custom'

        // Search inputs
        permSearch: "",
        catalogSearch: "",
        catalogModule: "all",

        // Modals & Drawer States
        createModalOpen: false,
        newRole: {
            name: "",
            description: "",
            copy_role_id: "",
        },

        editInfoModalOpen: false,
        editingInfoRole: {
            id: null,
            name: "",
            description: "",
            slug: "",
            is_system: false,
        },

        permEditorOpen: false,
        activeRole: null,
        checkedPermIds: [],
        originalPermIds: [],
        unsavedChangesOpen: false,

        deleteModalOpen: false,
        roleToDelete: null,
        deleteWarning: null,

        // Toast feedback
        toast: {
            show: false,
            message: "",
            type: "success",
        },

        init() {
            // Listen for browser popstate or tab changes
            window.addEventListener("popstate", () => {
                const tab = new URLSearchParams(location.search).get("tab");
                if (tab === "permissions" || tab === "roles") {
                    this.activeTab = tab;
                }
            });
        },

        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            if (tab === "roles") {
                url.searchParams.delete("tab");
            } else {
                url.searchParams.set("tab", tab);
            }
            history.replaceState(null, "", url);
        },

        // Filtered Roles
        get filteredRoles() {
            return this.roles.filter((r) => {
                if (this.roleFilter === "system") return Boolean(r.is_system);
                if (this.roleFilter === "custom") return !r.is_system;
                return true;
            });
        },

        get systemRolesCount() {
            return this.roles.filter((r) => Boolean(r.is_system)).length;
        },

        get customRolesCount() {
            return this.roles.filter((r) => !r.is_system).length;
        },

        get modulesList() {
            const mods = new Set();
            this.permissions.forEach((p) => {
                if (p.module) mods.add(p.module);
            });
            return Array.from(mods).sort();
        },

        // Permissions grouped by module
        get groupedModules() {
            const groups = {};
            const q = this.permSearch.trim().toLowerCase();

            this.permissions.forEach((p) => {
                const mod = p.module || "Other";
                if (!groups[mod]) {
                    groups[mod] = [];
                }

                // If searching, test module or perm name or slug
                if (q) {
                    const match =
                        mod.toLowerCase().includes(q) ||
                        (p.name && p.name.toLowerCase().includes(q)) ||
                        (p.slug && p.slug.toLowerCase().includes(q)) ||
                        (p.description &&
                            p.description.toLowerCase().includes(q));
                    if (match) {
                        groups[mod].push(p);
                    }
                } else {
                    groups[mod].push(p);
                }
            });

            // Filter out empty modules if searching
            const result = {};
            Object.keys(groups)
                .sort()
                .forEach((mod) => {
                    if (groups[mod].length > 0) {
                        result[mod] = groups[mod];
                    }
                });
            return result;
        },

        get filteredCatalogPermissions() {
            const q = this.catalogSearch.trim().toLowerCase();
            return this.permissions.filter((p) => {
                const matchesModule =
                    this.catalogModule === "all" ||
                    p.module === this.catalogModule;
                if (!matchesModule) return false;

                if (!q) return true;
                return (
                    (p.name && p.name.toLowerCase().includes(q)) ||
                    (p.slug && p.slug.toLowerCase().includes(q)) ||
                    (p.module && p.module.toLowerCase().includes(q)) ||
                    (p.description && p.description.toLowerCase().includes(q))
                );
            });
        },

        get isDirty() {
            if (!this.activeRole) return false;
            if (this.checkedPermIds.length !== this.originalPermIds.length)
                return true;
            const currentSet = new Set(this.checkedPermIds);
            return this.originalPermIds.some((id) => !currentSet.has(id));
        },

        // Key Modules summary for card
        getRoleKeyModules(role) {
            if (!role || !role.permissions || role.permissions.length === 0) {
                return "None";
            }
            const mods = [];
            role.permissions.forEach((p) => {
                if (p.module && !mods.includes(p.module)) {
                    mods.push(p.module);
                }
            });
            if (mods.length === 0) return "None";
            if (mods.length <= 4) return mods.join(" • ");
            return mods.slice(0, 3).join(" • ") + ` • +${mods.length - 3} more`;
        },

        getCoveragePercent(role) {
            const total = this.allPermissionsCount || 1;
            const count =
                Number(role.permissions_count) ||
                (role.permissions ? role.permissions.length : 0);
            return Math.min(100, Math.round((count / total) * 100));
        },

        // Permissions Editor Actions
        openPermEditor(role) {
            this.activeRole = role;
            const permIds = Array.isArray(role.permissions)
                ? role.permissions.map((p) => Number(p.id))
                : [];
            this.checkedPermIds = [...permIds];
            this.originalPermIds = [...permIds];
            this.permSearch = "";
            this.unsavedChangesOpen = false;
            this.permEditorOpen = true;
            document.body.classList.add("overflow-hidden");
        },

        requestClosePermEditor() {
            if (
                this.isDirty &&
                this.activeRole &&
                this.activeRole.slug !== "super-admin"
            ) {
                this.unsavedChangesOpen = true;
            } else {
                this.forceClosePermEditor();
            }
        },

        forceClosePermEditor() {
            this.unsavedChangesOpen = false;
            this.permEditorOpen = false;
            this.activeRole = null;
            document.body.classList.remove("overflow-hidden");
        },

        confirmDiscardChanges() {
            this.checkedPermIds = [...this.originalPermIds];
            this.forceClosePermEditor();
        },

        isPermChecked(permId) {
            return this.checkedPermIds.includes(Number(permId));
        },

        togglePerm(permId) {
            if (this.activeRole && this.activeRole.slug === "super-admin") {
                return; // Super admin permissions cannot be toggled off
            }
            const id = Number(permId);
            const index = this.checkedPermIds.indexOf(id);
            if (index > -1) {
                this.checkedPermIds.splice(index, 1);
            } else {
                this.checkedPermIds.push(id);
            }
        },

        toggleModule(moduleName, check) {
            if (this.activeRole && this.activeRole.slug === "super-admin")
                return;

            const modulePerms = this.permissions.filter(
                (p) => p.module === moduleName,
            );
            const moduleIds = modulePerms.map((p) => Number(p.id));

            if (check) {
                moduleIds.forEach((id) => {
                    if (!this.checkedPermIds.includes(id)) {
                        this.checkedPermIds.push(id);
                    }
                });
            } else {
                this.checkedPermIds = this.checkedPermIds.filter(
                    (id) => !moduleIds.includes(id),
                );
            }
        },

        isModuleAllChecked(moduleName) {
            const modulePerms = this.permissions.filter(
                (p) => p.module === moduleName,
            );
            if (modulePerms.length === 0) return false;
            return modulePerms.every((p) =>
                this.checkedPermIds.includes(Number(p.id)),
            );
        },

        getModuleCheckedCount(moduleName) {
            const modulePerms = this.permissions.filter(
                (p) => p.module === moduleName,
            );
            return modulePerms.filter((p) =>
                this.checkedPermIds.includes(Number(p.id)),
            ).length;
        },

        getModuleTotalCount(moduleName) {
            return this.permissions.filter((p) => p.module === moduleName)
                .length;
        },

        isPermDangerous(perm) {
            if (!perm) return false;
            const s = (perm.slug || "").toLowerCase();
            return (
                s.includes("delete") ||
                s.includes("roles.manage") ||
                s.includes("users.manage")
            );
        },

        // Role Creation & Duplication
        openCreateModal(copyRoleId = "") {
            this.newRole = {
                name: "",
                description: "",
                copy_role_id: copyRoleId ? String(copyRoleId) : "",
            };
            this.createModalOpen = true;
        },

        duplicateRole(role) {
            this.newRole = {
                name: `Copy of ${role.name}`,
                description: role.description
                    ? `Cloned from ${role.name}. ${role.description}`
                    : `Cloned from ${role.name}`,
                copy_role_id: String(role.id),
            };
            this.createModalOpen = true;
        },

        // Edit Role Information
        openEditInfo(role) {
            this.editingInfoRole = {
                id: role.id,
                name: role.name,
                description: role.description || "",
                slug: role.slug,
                is_system: Boolean(role.is_system),
            };
            this.editInfoModalOpen = true;
        },

        // Delete Role Safety
        promptDeleteRole(role) {
            if (role.is_system) {
                alert(
                    `System role "${role.name}" is protected and cannot be deleted.`,
                );
                return;
            }
            if (Number(role.users_count) > 0) {
                this.deleteWarning = `Cannot delete "${role.name}" because it is assigned to ${role.users_count} user(s). Please reassign them in User Management first.`;
                this.roleToDelete = role;
                this.deleteModalOpen = true;
                return;
            }
            this.deleteWarning = null;
            this.roleToDelete = role;
            this.deleteModalOpen = true;
        },

        showToast(message, type = "success") {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },
    };
}

export function registerRoles(Alpine) {
    Alpine.data("roleManagement", roleManagement);
}
