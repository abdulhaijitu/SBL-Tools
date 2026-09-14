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

    // Toolkit
    getLinks: () => request<any[]>("/toolkit/links"),
    getContacts: () => request<any[]>("/toolkit/contacts"),
    getAbbreviations: () => request<any[]>("/toolkit/abbreviations"),
    getResources: () => request<any[]>("/toolkit/resources"),

    // Team Tree
    getTreeNodes: (parentId?: number) =>
        request<any[]>(`/tree${parentId ? `?parentId=${parentId}` : ""}`),
    addTreeNode: (node: any) =>
        request<any>("/tree/nodes", {
            method: "POST",
            body: JSON.stringify(node),
        }),

    // Financials
    getPlans: () => request<any[]>("/financials/plans"),
    getMyInvestments: () => request<any[]>("/financials/my-investments"),
    calculatePackage: (data: {
        amountBdt: number;
        durationDays: number;
        ratePercent: number;
    }) =>
        request<any>("/financials/calculate", {
            method: "POST",
            body: JSON.stringify(data),
        }),
};
