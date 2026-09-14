export interface Env {
    HYPERDRIVE?: {
        connectionString: string;
    };
    R2_STORAGE?: R2Bucket;
    DATABASE_URL?: string; // Fallback for local testing
    JWT_SECRET: string;
}

export interface AuthUser {
    userId: number;
    email: string;
    role: string;
}

export type HonoVariables = {
    user: AuthUser;
};
