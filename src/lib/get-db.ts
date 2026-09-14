import { Context } from "hono";
import { drizzle } from "drizzle-orm/node-postgres";
import { Client } from "pg";
import * as schema from "../db/schema";

export async function getDb(c: Context<any>) {
    const connectionString =
        c.env?.HYPERDRIVE?.connectionString ||
        c.env?.DATABASE_URL ||
        (typeof process !== "undefined"
            ? process.env?.DATABASE_URL
            : undefined);

    if (!connectionString) {
        throw new Error(
            "Database connection string is missing. Check HYPERDRIVE or DATABASE_URL binding.",
        );
    }

    const client = new Client({ connectionString });
    await client.connect();

    const db = drizzle(client, { schema });

    // In Cloudflare Workers, waitUntil ensures DB connection is cleanly terminated after response
    if (c.executionCtx && typeof c.executionCtx.waitUntil === "function") {
        c.executionCtx.waitUntil(client.end());
    }

    return { db, client };
}
