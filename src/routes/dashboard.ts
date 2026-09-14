import { Hono } from "hono";
import { eq, and, isNull, sql } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const dashboardRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

dashboardRouter.use("*", authMiddleware);

dashboardRouter.get("/summary", async (c) => {
    const authUser = c.get("user");
    const { db } = await getDb(c);

    const userCondition =
        authUser.role !== "super_admin" && authUser.role !== "manager"
            ? eq(schema.leads.ownerUserId, authUser.userId)
            : undefined;

    const baseConditions = [isNull(schema.leads.deletedAt)];
    if (userCondition) baseConditions.push(userCondition);

    // 1. Stage summary counts
    const stageCounts = await db
        .select({
            stage: schema.leads.stage,
            count: sql<number>`count(*)::int`,
        })
        .from(schema.leads)
        .where(and(...baseConditions))
        .groupBy(schema.leads.stage);

    // 2. Temperature summary counts
    const tempCounts = await db
        .select({
            temperature: schema.leads.temperature,
            count: sql<number>`count(*)::int`,
        })
        .from(schema.leads)
        .where(and(...baseConditions))
        .groupBy(schema.leads.temperature);

    // 3. Pending tasks for user
    const pendingTasks = await db
        .select()
        .from(schema.tasks)
        .where(
            and(
                eq(schema.tasks.userId, authUser.userId),
                eq(schema.tasks.status, "pending"),
            ),
        )
        .limit(10);

    // 4. Overdue and today's follow-ups
    const now = new Date();
    const startOfDay = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
    );
    const endOfDay = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        23,
        59,
        59,
    );

    const todayFollowUps = await db
        .select({
            id: schema.leads.id,
            name: schema.leads.name,
            mobile: schema.leads.mobile,
            temperature: schema.leads.temperature,
            nextActionType: schema.leads.nextActionType,
            nextActionAt: schema.leads.nextActionAt,
        })
        .from(schema.leads)
        .where(
            and(
                ...baseConditions,
                sql`${schema.leads.nextActionAt} >= ${startOfDay} AND ${schema.leads.nextActionAt} <= ${endOfDay}`,
            ),
        );

    const overdueFollowUps = await db
        .select({
            id: schema.leads.id,
            name: schema.leads.name,
            mobile: schema.leads.mobile,
            temperature: schema.leads.temperature,
            nextActionType: schema.leads.nextActionType,
            nextActionAt: schema.leads.nextActionAt,
        })
        .from(schema.leads)
        .where(
            and(
                ...baseConditions,
                sql`${schema.leads.nextActionAt} < ${startOfDay}`,
            ),
        )
        .limit(10);

    return c.json({
        funnel: stageCounts,
        temperatures: tempCounts,
        tasks: pendingTasks,
        todayFollowUps,
        overdueFollowUps,
    });
});
