export interface Env {
    DB: D1Database;
    R2_STORAGE?: R2Bucket;
    ASSETS?: Fetcher;
    JWT_SECRET: string;
}

export interface AuthUser {
    userId: number;
    email: string;
    role: string;
    phone?: string;
}

export type HonoVariables = {
    user: AuthUser;
};
