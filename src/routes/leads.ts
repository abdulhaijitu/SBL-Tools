import { Hono } from "hono";
import { eq, and, isNull, desc, like, or } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const leadsRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

leadsRouter.use("*", authMiddleware);

// Get lead sources
leadsRouter.get("/sources", async (c) => {
    const { db } = await getDb(c);
    const sources = await db
        .select()
        .from(schema.leadSources)
        .where(eq(schema.leadSources.isActive, true))
        .orderBy(schema.leadSources.sortOrder);
    return c.json(sources);
});

// List leads with filters
leadsRouter.get("/", async (c) => {
    const authUser = c.get("user");
    const stage = c.req.query("stage");
    const temperature = c.req.query("temperature");
    const search = c.req.query("search");

    const { db } = await getDb(c);

    const conditions = [isNull(schema.leads.deletedAt)];

    // If user is not super_admin or manager, scope to owned leads
    if (authUser.role !== "super_admin" && authUser.role !== "manager") {
        conditions.push(eq(schema.leads.ownerUserId, authUser.userId));
    }

    if (stage) {
        conditions.push(eq(schema.leads.stage, stage));
    }
    if (temperature) {
        conditions.push(eq(schema.leads.temperature, temperature));
    }
    if (search) {
        conditions.push(
            or(
                like(schema.leads.name, `%${search}%`),
                like(schema.leads.mobile, `%${search}%`),
                like(schema.leads.location, `%${search}%`),
            )!,
        );
    }

    const leadsList = await db
        .select({
            id: schema.leads.id,
            name: schema.leads.name,
            mobile: schema.leads.mobile,
            whatsapp: schema.leads.whatsapp,
            email: schema.leads.email,
            photoR2Key: schema.leads.photoR2Key,
            location: schema.leads.location,
            professionOrBusiness: schema.leads.professionOrBusiness,
            stage: schema.leads.stage,
            temperature: schema.leads.temperature,
            score: schema.leads.score,
            budgetRange: schema.leads.budgetRange,
            nextActionType: schema.leads.nextActionType,
            nextActionAt: schema.leads.nextActionAt,
            createdAt: schema.leads.createdAt,
            sourceName: schema.leadSources.name,
        })
        .from(schema.leads)
        .leftJoin(
            schema.leadSources,
            eq(schema.leads.leadSourceId, schema.leadSources.id),
        )
        .where(and(...conditions))
        .orderBy(desc(schema.leads.createdAt));

    return c.json(leadsList);
});

// Get lead details
leadsRouter.get("/:id", async (c) => {
    const id = Number(c.req.param("id"));
    const { db } = await getDb(c);

    const [lead] = await db
        .select()
        .from(schema.leads)
        .where(and(eq(schema.leads.id, id), isNull(schema.leads.deletedAt)))
        .limit(1);

    if (!lead) {
        return c.json({ error: "Lead not found" }, 404);
    }

    const activities = await db
        .select()
        .from(schema.activities)
        .where(eq(schema.activities.leadId, id))
        .orderBy(desc(schema.activities.createdAt));

    const tasks = await db
        .select()
        .from(schema.tasks)
        .where(eq(schema.tasks.leadId, id))
        .orderBy(desc(schema.tasks.createdAt));

    return c.json({
        ...lead,
        activities,
        tasks,
    });
});

// Create lead
leadsRouter.post("/", async (c) => {
    const authUser = c.get("user");
    const body = await c.req.json();

    if (!body.name || !body.mobile) {
        return c.json({ error: "Name and mobile number are required" }, 400);
    }

    const { db } = await getDb(c);

    // Compute lead score
    let score = 20;
    if (body.whatsapp) score += 10;
    if (body.email) score += 10;
    if (body.budgetRange && Number(body.budgetRange) > 50000) score += 20;
    if (body.decisionTimeline === "Immediate") score += 20;
    if (body.temperature === "hot") score += 20;
    else if (body.temperature === "warm") score += 10;

    const [newLead] = await db
        .insert(schema.leads)
        .values({
            name: body.name,
            mobile: body.mobile,
            whatsapp: body.whatsapp || null,
            email: body.email || null,
            photoR2Key: body.photoR2Key || null,
            facebookUrl: body.facebookUrl || null,
            location: body.location || null,
            professionOrBusiness: body.professionOrBusiness || null,
            leadSourceId: body.leadSourceId ? Number(body.leadSourceId) : null,
            leadSourceDetail: body.leadSourceDetail || null,
            stage: body.stage || "new",
            temperature: body.temperature || "cold",
            score: score,
            budgetRange: body.budgetRange ? String(body.budgetRange) : null,
            decisionTimeline: body.decisionTimeline || null,
            ownerUserId: authUser.userId,
            nextActionType: body.nextActionType || null,
            nextActionAt: body.nextActionAt
                ? new Date(body.nextActionAt)
                : null,
            notes: body.notes || null,
        })
        .returning();

    return c.json(newLead, 201);
});

// Update lead
leadsRouter.put("/:id", async (c) => {
    const id = Number(c.req.param("id"));
    const body = await c.req.json();
    const { db } = await getDb(c);

    const [updated] = await db
        .update(schema.leads)
        .set({
            name: body.name,
            mobile: body.mobile,
            whatsapp: body.whatsapp,
            email: body.email,
            photoR2Key: body.photoR2Key,
            location: body.location,
            professionOrBusiness: body.professionOrBusiness,
            leadSourceId: body.leadSourceId
                ? Number(body.leadSourceId)
                : undefined,
            stage: body.stage,
            temperature: body.temperature,
            score: body.score !== undefined ? Number(body.score) : undefined,
            budgetRange: body.budgetRange
                ? String(body.budgetRange)
                : undefined,
            decisionTimeline: body.decisionTimeline,
            nextActionType: body.nextActionType,
            nextActionAt: body.nextActionAt
                ? new Date(body.nextActionAt)
                : undefined,
            notes: body.notes,
            updatedAt: new Date(),
        })
        .where(eq(schema.leads.id, id))
        .returning();

    return c.json(updated);
});

// Soft delete lead
leadsRouter.delete("/:id", async (c) => {
    const id = Number(c.req.param("id"));
    const { db } = await getDb(c);

    await db
        .update(schema.leads)
        .set({ deletedAt: new Date() })
        .where(eq(schema.leads.id, id));

    return c.json({ message: "Lead deleted successfully" });
});

// Log activity for lead
leadsRouter.post("/:id/activity", async (c) => {
    const leadId = Number(c.req.param("id"));
    const authUser = c.get("user");
    const body = await c.req.json();

    if (!body.type) {
        return c.json({ error: "Activity type is required" }, 400);
    }

    const { db } = await getDb(c);

    const [activity] = await db
        .insert(schema.activities)
        .values({
            leadId,
            userId: authUser.userId,
            type: body.type,
            details: body.details || null,
            scheduledAt: body.scheduledAt ? new Date(body.scheduledAt) : null,
            completedAt: new Date(),
        })
        .returning();

    // Update lead last contact timestamp
    await db
        .update(schema.leads)
        .set({ lastContactAt: new Date() })
        .where(eq(schema.leads.id, leadId));

    return c.json(activity, 201);
});
