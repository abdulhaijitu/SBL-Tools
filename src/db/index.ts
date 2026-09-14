import { drizzle } from "drizzle-orm/d1";
import * as schema from "./schema";

export type D1DatabaseInstance = ReturnType<typeof createD1Client>;

/**
 * Creates a Drizzle ORM client using Cloudflare D1.
 */
export function createD1Client(d1: D1Database) {
    return drizzle(d1, { schema });
}

export * from "./schema";
