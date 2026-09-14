import { Hono } from "hono";
import { eq, and } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const treeRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

treeRouter.use("*", authMiddleware);

// Get member network tree
treeRouter.get("/", async (c) => {
    const authUser = c.get("user");
    const parentId = c.req.query("parentId");
    const { db } = await getDb(c);

    let nodes;
    if (parentId) {
        nodes = await db
            .select({
                id: schema.binaryNodes.id,
                placementPosition: schema.binaryNodes.placementPosition,
                memberName: schema.binaryNodes.memberName,
                memberId: schema.binaryNodes.memberId,
                phone: schema.binaryNodes.phone,
                rank: schema.binaryNodes.rank,
                packageName: schema.binaryNodes.packageName,
                status: schema.binaryNodes.status,
                sponsorName: schema.binaryNodes.sponsorName,
                createdAt: schema.binaryNodes.createdAt,
            })
            .from(schema.binaryNodes)
            .where(
                and(
                    eq(schema.binaryNodes.parentId, Number(parentId)),
                    authUser.role === "super_admin"
                        ? undefined
                        : eq(schema.binaryNodes.userId, authUser.userId),
                ),
            )
            .orderBy(schema.binaryNodes.placementPosition);
    } else {
        // Return root nodes for current user
        nodes = await db
            .select({
                id: schema.binaryNodes.id,
                placementPosition: schema.binaryNodes.placementPosition,
                memberName: schema.binaryNodes.memberName,
                memberId: schema.binaryNodes.memberId,
                phone: schema.binaryNodes.phone,
                rank: schema.binaryNodes.rank,
                packageName: schema.binaryNodes.packageName,
                status: schema.binaryNodes.status,
                sponsorName: schema.binaryNodes.sponsorName,
                createdAt: schema.binaryNodes.createdAt,
            })
            .from(schema.binaryNodes)
            .where(
                authUser.role === "super_admin"
                    ? undefined
                    : eq(schema.binaryNodes.userId, authUser.userId),
            )
            .limit(50);
    }

    return c.json(nodes);
});

// Add placement node (10-slot structure)
treeRouter.post("/nodes", async (c) => {
    const authUser = c.get("user");
    const body = await c.req.json();

    if (!body.memberName || !body.placementPosition) {
        return c.json(
            { error: "Member name and placement position are required" },
            400,
        );
    }

    const position = Number(body.placementPosition);
    if (position < 1 || position > 10) {
        return c.json(
            { error: "Placement position must be between 1 and 10" },
            400,
        );
    }

    const { db } = await getDb(c);

    // Check unique slot availability under parent
    if (body.parentId) {
        const existing = await db
            .select()
            .from(schema.binaryNodes)
            .where(
                and(
                    eq(schema.binaryNodes.parentId, Number(body.parentId)),
                    eq(schema.binaryNodes.placementPosition, position),
                ),
            )
            .limit(1);

        if (existing.length > 0) {
            return c.json(
                {
                    error: `Slot ${position} is already occupied under this parent node.`,
                },
                409,
            );
        }
    }

    const [node] = await db
        .insert(schema.binaryNodes)
        .values({
            userId: authUser.userId,
            parentId: body.parentId ? Number(body.parentId) : null,
            placementPosition: position,
            sponsorId: body.sponsorId ? Number(body.sponsorId) : null,
            sponsorName: body.sponsorName || null,
            memberId: body.memberId || null,
            memberName: body.memberName,
            phone: body.phone || null,
            passwordEncrypted: body.password || null,
            tpinEncrypted: body.tpin || null,
            rank: body.rank || "Associate",
            packageName: body.packageName || "Basic Starter",
            status: "active",
            notes: body.notes || null,
        })
        .returning();

    return c.json(node, 201);
});
