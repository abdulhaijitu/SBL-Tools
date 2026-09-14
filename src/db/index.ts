import { drizzle } from "drizzle-orm/node-postgres";
import { Client } from "pg";
import * as schema from "./schema";

export type DatabaseInstance = ReturnType<typeof createDbClient>;

/**
 * Creates a Drizzle ORM client using node-postgres (`pg`).
 * Compatible with Cloudflare Hyperdrive via `env.HYPERDRIVE.connectionString`
 * and local Node.js environment via `process.env.DATABASE_URL`.
 */
export function createDbClient(connectionString: string) {
    const client = new Client({
        connectionString,
    });

    // Client connection is managed per-request or per-session
    const db = drizzle(client, { schema });

    return {
        db,
        client,
        connect: () => client.connect(),
        end: () => client.end(),
    };
}

export * from "./schema";
