import { Hono } from "hono";
import { eq, desc } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const toolkitRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

toolkitRouter.use("*", authMiddleware);

// 1. Ecosystem Links
toolkitRouter.get("/links", async (c) => {
    const { db } = await getDb(c);
    const links = await db
        .select()
        .from(schema.ecosystemLinks)
        .where(eq(schema.ecosystemLinks.isActive, true))
        .orderBy(schema.ecosystemLinks.title);
    return c.json(links);
});

// 2. SBL Contacts Directory
toolkitRouter.get("/contacts", async (c) => {
    const { db } = await getDb(c);
    const contacts = await db
        .select()
        .from(schema.sblContacts)
        .orderBy(schema.sblContacts.name);
    return c.json(contacts);
});

// 3. Abbreviations Glossary
toolkitRouter.get("/abbreviations", async (c) => {
    const { db } = await getDb(c);
    const abbreviations = await db
        .select()
        .from(schema.abbreviations)
        .orderBy(schema.abbreviations.abbreviation);
    return c.json(abbreviations);
});

// 4. Marketing Resources
toolkitRouter.get("/resources", async (c) => {
    const { db } = await getDb(c);
    const resources = await db
        .select()
        .from(schema.marketingResources)
        .orderBy(desc(schema.marketingResources.createdAt));
    return c.json(resources);
});
