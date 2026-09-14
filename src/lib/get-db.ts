import { Context } from 'hono';
import { drizzle } from 'drizzle-orm/d1';
import * as schema from '../db/schema';

export function getDb(c: Context<any>) {
  if (!c.env?.DB) {
    throw new Error('Cloudflare D1 database binding "DB" is missing in environment. Check wrangler.toml.');
  }

  const db = drizzle(c.env.DB, { schema });
  return { db };
}
