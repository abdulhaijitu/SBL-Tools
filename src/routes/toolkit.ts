import { Hono } from "hono";
import { eq, desc, asc } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const toolkitRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

toolkitRouter.use("*", authMiddleware);

// ==========================================
// 1. ECOSYSTEM LINKS HUB (CRUD)
// ==========================================

// GET /toolkit/links
toolkitRouter.get("/links", async (c) => {
    const { db } = await getDb(c);
    const links = await db
        .select()
        .from(schema.ecosystemLinks)
        .orderBy(asc(schema.ecosystemLinks.sortOrder), schema.ecosystemLinks.title);
    return c.json(links);
});

// POST /toolkit/links
toolkitRouter.post("/links", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const body = await c.req.json();
    if (!body.title || !body.url) {
        return c.json({ error: "Title and URL are required" }, 400);
    }

    const { db } = await getDb(c);
    const [newLink] = await db
        .insert(schema.ecosystemLinks)
        .values({
            title: body.title.trim(),
            url: body.url.trim(),
            category: body.category?.trim() || "General",
            description: body.description?.trim() || null,
            sortOrder: Number(body.sortOrder || 0),
            isActive: body.isActive !== undefined ? Boolean(body.isActive) : true,
        })
        .returning();

    return c.json(newLink, 201);
});

// PUT /toolkit/links/:id
toolkitRouter.put("/links/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const body = await c.req.json();
    const { db } = await getDb(c);

    const updateData: any = {};
    if (body.title !== undefined) updateData.title = body.title.trim();
    if (body.url !== undefined) updateData.url = body.url.trim();
    if (body.category !== undefined) updateData.category = body.category.trim();
    if (body.description !== undefined) updateData.description = body.description?.trim() || null;
    if (body.sortOrder !== undefined) updateData.sortOrder = Number(body.sortOrder);
    if (body.isActive !== undefined) updateData.isActive = Boolean(body.isActive);

    const [updated] = await db
        .update(schema.ecosystemLinks)
        .set(updateData)
        .where(eq(schema.ecosystemLinks.id, id))
        .returning();

    if (!updated) {
        return c.json({ error: "Link not found" }, 404);
    }

    return c.json(updated);
});

// DELETE /toolkit/links/:id
toolkitRouter.delete("/links/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const { db } = await getDb(c);

    await db
        .delete(schema.ecosystemLinks)
        .where(eq(schema.ecosystemLinks.id, id));

    return c.json({ message: "Link deleted successfully", id });
});

// ==========================================
// 2. SBL CONTACTS DIRECTORY (CRUD)
// ==========================================

// GET /toolkit/contacts
toolkitRouter.get("/contacts", async (c) => {
    const { db } = await getDb(c);
    const contacts = await db
        .select()
        .from(schema.sblContacts)
        .orderBy(asc(schema.sblContacts.sortOrder), schema.sblContacts.name);
    return c.json(contacts);
});

// POST /toolkit/contacts
toolkitRouter.post("/contacts", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const body = await c.req.json();
    if (!body.name || !body.phone) {
        return c.json({ error: "Name and Phone are required" }, 400);
    }

    const { db } = await getDb(c);
    const [newContact] = await db
        .insert(schema.sblContacts)
        .values({
            name: body.name.trim(),
            designation: body.designation?.trim() || "Officer",
            department: body.department?.trim() || null,
            phone: body.phone.trim(),
            whatsapp: body.whatsapp?.trim() || null,
            email: body.email?.trim() || null,
            notes: body.notes?.trim() || null,
            sortOrder: Number(body.sortOrder || 0),
        })
        .returning();

    return c.json(newContact, 201);
});

// PUT /toolkit/contacts/:id
toolkitRouter.put("/contacts/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const body = await c.req.json();
    const { db } = await getDb(c);

    const updateData: any = {};
    if (body.name !== undefined) updateData.name = body.name.trim();
    if (body.designation !== undefined) updateData.designation = body.designation.trim();
    if (body.department !== undefined) updateData.department = body.department?.trim() || null;
    if (body.phone !== undefined) updateData.phone = body.phone.trim();
    if (body.whatsapp !== undefined) updateData.whatsapp = body.whatsapp?.trim() || null;
    if (body.email !== undefined) updateData.email = body.email?.trim() || null;
    if (body.notes !== undefined) updateData.notes = body.notes?.trim() || null;
    if (body.sortOrder !== undefined) updateData.sortOrder = Number(body.sortOrder);

    const [updated] = await db
        .update(schema.sblContacts)
        .set(updateData)
        .where(eq(schema.sblContacts.id, id))
        .returning();

    if (!updated) {
        return c.json({ error: "Contact not found" }, 404);
    }

    return c.json(updated);
});

// DELETE /toolkit/contacts/:id
toolkitRouter.delete("/contacts/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const { db } = await getDb(c);

    await db
        .delete(schema.sblContacts)
        .where(eq(schema.sblContacts.id, id));

    return c.json({ message: "Contact deleted successfully", id });
});

// ==========================================
// 3. ABBREVIATIONS GLOSSARY (CRUD)
// ==========================================

// GET /toolkit/abbreviations
toolkitRouter.get("/abbreviations", async (c) => {
    const { db } = await getDb(c);
    const abbreviations = await db
        .select()
        .from(schema.abbreviations)
        .orderBy(asc(schema.abbreviations.sortOrder), schema.abbreviations.abbreviation);
    return c.json(abbreviations);
});

// POST /toolkit/abbreviations
toolkitRouter.post("/abbreviations", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const body = await c.req.json();
    if (!body.abbreviation || !body.term || !body.definition) {
        return c.json({ error: "Abbreviation, Term, and Definition are required" }, 400);
    }

    const { db } = await getDb(c);
    const [newAbbr] = await db
        .insert(schema.abbreviations)
        .values({
            abbreviation: body.abbreviation.trim().toUpperCase(),
            term: body.term.trim(),
            definition: body.definition.trim(),
            category: body.category?.trim() || "General",
            sortOrder: Number(body.sortOrder || 0),
        })
        .returning();

    return c.json(newAbbr, 201);
});

// PUT /toolkit/abbreviations/:id
toolkitRouter.put("/abbreviations/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const body = await c.req.json();
    const { db } = await getDb(c);

    const updateData: any = {};
    if (body.abbreviation !== undefined) updateData.abbreviation = body.abbreviation.trim().toUpperCase();
    if (body.term !== undefined) updateData.term = body.term.trim();
    if (body.definition !== undefined) updateData.definition = body.definition.trim();
    if (body.category !== undefined) updateData.category = body.category?.trim() || "General";
    if (body.sortOrder !== undefined) updateData.sortOrder = Number(body.sortOrder);

    const [updated] = await db
        .update(schema.abbreviations)
        .set(updateData)
        .where(eq(schema.abbreviations.id, id))
        .returning();

    if (!updated) {
        return c.json({ error: "Glossary term not found" }, 404);
    }

    return c.json(updated);
});

// DELETE /toolkit/abbreviations/:id
toolkitRouter.delete("/abbreviations/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const { db } = await getDb(c);

    await db
        .delete(schema.abbreviations)
        .where(eq(schema.abbreviations.id, id));

    return c.json({ message: "Glossary term deleted successfully", id });
});

// ==========================================
// 4. MARKETING RESOURCES (CRUD)
// ==========================================

// GET /toolkit/resources
toolkitRouter.get("/resources", async (c) => {
    const { db } = await getDb(c);
    const resources = await db
        .select()
        .from(schema.marketingResources)
        .orderBy(desc(schema.marketingResources.createdAt));
    return c.json(resources);
});

// POST /toolkit/resources
toolkitRouter.post("/resources", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const body = await c.req.json();
    if (!body.title || !body.fileR2Key) {
        return c.json({ error: "Title and File URL/Key are required" }, 400);
    }

    const { db } = await getDb(c);
    const [newResource] = await db
        .insert(schema.marketingResources)
        .values({
            title: body.title.trim(),
            category: body.category?.trim() || "PDF",
            fileR2Key: body.fileR2Key.trim(),
            downloadCount: 0,
            fileSize: body.fileSize ? Number(body.fileSize) : null,
        })
        .returning();

    return c.json(newResource, 201);
});

// PUT /toolkit/resources/:id
toolkitRouter.put("/resources/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const body = await c.req.json();
    const { db } = await getDb(c);

    const updateData: any = {};
    if (body.title !== undefined) updateData.title = body.title.trim();
    if (body.category !== undefined) updateData.category = body.category.trim();
    if (body.fileR2Key !== undefined) updateData.fileR2Key = body.fileR2Key.trim();
    if (body.fileSize !== undefined) updateData.fileSize = Number(body.fileSize);

    const [updated] = await db
        .update(schema.marketingResources)
        .set(updateData)
        .where(eq(schema.marketingResources.id, id))
        .returning();

    if (!updated) {
        return c.json({ error: "Resource not found" }, 404);
    }

    return c.json(updated);
});

// DELETE /toolkit/resources/:id
toolkitRouter.delete("/resources/:id", async (c) => {
    const authUser = c.get("user");
    if (authUser.role === "demo") {
        return c.json({ error: "Demo mode is read-only" }, 403);
    }

    const id = Number(c.req.param("id"));
    const { db } = await getDb(c);

    await db
        .delete(schema.marketingResources)
        .where(eq(schema.marketingResources.id, id));

    return c.json({ message: "Resource deleted successfully", id });
});
