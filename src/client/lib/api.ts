const API_BASE = "/api";

export function getAuthToken(): string | null {
    return localStorage.getItem("sbl_token");
}

export function setAuthToken(token: string): void {
    localStorage.setItem("sbl_token", token);
}

export function clearAuthToken(): void {
    localStorage.removeItem("sbl_token");
}

export function getBackupAdminToken(): string | null {
    return localStorage.getItem("sbl_admin_backup_token");
}

export function setBackupAdminToken(token: string): void {
    localStorage.setItem("sbl_admin_backup_token", token);
}

export function clearBackupAdminToken(): void {
    localStorage.removeItem("sbl_admin_backup_token");
}

async function request<T>(
    endpoint: string,
    options: RequestInit = {},
): Promise<T> {
    const token = getAuthToken();
    const headers = new Headers(options.headers || {});

    if (!headers.has("Content-Type") && !(options.body instanceof FormData)) {
        headers.set("Content-Type", "application/json");
    }

    if (token) {
        headers.set("Authorization", `Bearer ${token}`);
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
        ...options,
        headers,
    });

    if (response.status === 401) {
        clearAuthToken();
        window.dispatchEvent(new Event("auth:unauthorized"));
    }

    const data = (await response.json()) as any;
    if (!response.ok) {
        throw new Error(data?.error || "Request failed");
    }

    return data as T;
}

export const api = {
    // Auth
    login: (credentials: any) =>
        request<{ token: string; user: any }>("/auth/login", {
            method: "POST",
            body: JSON.stringify(credentials),
        }),
    register: (userData: any) =>
        request("/auth/register", {
            method: "POST",
            body: JSON.stringify(userData),
        }),
    getMe: () => request<{ user: any }>("/auth/me"),

    // Dashboard
    getDashboardSummary: () => request<any>("/dashboard/summary"),

    // Leads
    getLeads: (params?: {
        stage?: string;
        temperature?: string;
        search?: string;
    }) => {
        const query = new URLSearchParams(params as any).toString();
        return request<any[]>(`/leads${query ? `?${query}` : ""}`);
    },
    getLeadSources: () => request<any[]>("/leads/sources"),
    getLead: (id: number) => request<any>(`/leads/${id}`),
    createLead: (lead: any) =>
        request<any>("/leads", {
            method: "POST",
            body: JSON.stringify(lead),
        }),
    updateLead: (id: number, lead: any) =>
        request<any>(`/leads/${id}`, {
            method: "PUT",
            body: JSON.stringify(lead),
        }),
    deleteLead: (id: number) =>
        request<any>(`/leads/${id}`, {
            method: "DELETE",
        }),
    addActivity: (leadId: number, activity: any) =>
        request<any>(`/leads/${leadId}/activity`, {
            method: "POST",
            body: JSON.stringify(activity),
        }),

    // Toolkit (Full CRUD)
    getLinks: () => request<any[]>("/toolkit/links"),
    createLink: (data: any) =>
        request<any>("/toolkit/links", {
            method: "POST",
            body: JSON.stringify(data),
        }),
    updateLink: (id: number, data: any) =>
        request<any>(`/toolkit/links/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        }),
    deleteLink: (id: number) =>
        request<any>(`/toolkit/links/${id}`, {
            method: "DELETE",
        }),

    getContacts: () => request<any[]>("/toolkit/contacts"),
    createContact: (data: any) =>
        request<any>("/toolkit/contacts", {
            method: "POST",
            body: JSON.stringify(data),
        }),
    updateContact: (id: number, data: any) =>
        request<any>(`/toolkit/contacts/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        }),
    deleteContact: (id: number) =>
        request<any>(`/toolkit/contacts/${id}`, {
            method: "DELETE",
        }),

    getAbbreviations: () => request<any[]>("/toolkit/abbreviations"),
    createAbbreviation: (data: any) =>
        request<any>("/toolkit/abbreviations", {
            method: "POST",
            body: JSON.stringify(data),
        }),
    updateAbbreviation: (id: number, data: any) =>
        request<any>(`/toolkit/abbreviations/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        }),
    deleteAbbreviation: (id: number) =>
        request<any>(`/toolkit/abbreviations/${id}`, {
            method: "DELETE",
        }),

    getResources: () => request<any[]>("/toolkit/resources"),
    createResource: (data: any) =>
        request<any>("/toolkit/resources", {
            method: "POST",
            body: JSON.stringify(data),
        }),
    updateResource: (id: number, data: any) =>
        request<any>(`/toolkit/resources/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        }),
    deleteResource: (id: number) =>
        request<any>(`/toolkit/resources/${id}`, {
            method: "DELETE",
        }),

    // Team Tree (Full CRUD)
    getTreeNodes: (parentId?: number) =>
        request<any[]>(`/tree${parentId ? `?parentId=${parentId}` : ""}`),
    getTreeNode: (id: number) => request<any>(`/tree/nodes/${id}`),
    addTreeNode: (node: any) =>
        request<any>("/tree/nodes", {
            method: "POST",
            body: JSON.stringify(node),
        }),
    updateTreeNode: (id: number, data: any) =>
        request<any>(`/tree/nodes/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        }),
    deleteTreeNode: (id: number) =>
        request<any>(`/tree/nodes/${id}`, {
            method: "DELETE",
        }),
    addProjectToNode: (data: {
        nodeId: number;
        projectName: string;
        amountBdt: number;
        referenceNote?: string;
    }) =>
        request<any>("/tree/projects", {
            method: "POST",
            body: JSON.stringify(data),
        }),
    getNodeProjects: (nodeId: number) =>
        request<any[]>(`/tree/nodes/${nodeId}/projects`),

    // Financials
    getPlans: () => request<any[]>("/financials/plans"),
    getRanks: () => request<any[]>("/financials/ranks"),
    getMyInvestments: () => request<any[]>("/financials/my-investments"),
    calculatePackage: (data: any) =>
        request<any>("/financials/calculate", {
            method: "POST",
            body: JSON.stringify(data),
        }),

    // Users & Roles (Super Admin)
    getUsers: () => request<any[]>("/users"),
    createUser: (userData: any) =>
        request<any>("/users", {
            method: "POST",
            body: JSON.stringify(userData),
        }),
    updateUser: (id: number, userData: any) =>
        request<any>(`/users/${id}`, {
            method: "PUT",
            body: JSON.stringify(userData),
        }),
    deleteUser: (id: number) =>
        request<any>(`/users/${id}`, {
            method: "DELETE",
        }),
    impersonateUser: (id: number) =>
        request<{ token: string; user: any }>(`/users/${id}/impersonate`, {
            method: "POST",
        }),
};
