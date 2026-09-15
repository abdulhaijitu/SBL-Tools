import { Hono } from "hono";
import { eq, and, sql, desc } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const treeRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

treeRouter.use("*", authMiddleware);

// Get member network tree with child counts and projects
treeRouter.get("/", async (c) => {
    const authUser = c.get("user");
    const parentId = c.req.query("parentId");
    const { db } = await getDb(c);

    let nodes;
    if (parentId) {
        nodes = await db
            .select({
                id: schema.binaryNodes.id,
                parentId: schema.binaryNodes.parentId,
                placementPosition: schema.binaryNodes.placementPosition,
                memberName: schema.binaryNodes.memberName,
                memberId: schema.binaryNodes.memberId,
                phone: schema.binaryNodes.phone,
                rank: schema.binaryNodes.rank,
                packageName: schema.binaryNodes.packageName,
                status: schema.binaryNodes.status,
                sponsorName: schema.binaryNodes.sponsorName,
                notes: schema.binaryNodes.notes,
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
                parentId: schema.binaryNodes.parentId,
                placementPosition: schema.binaryNodes.placementPosition,
                memberName: schema.binaryNodes.memberName,
                memberId: schema.binaryNodes.memberId,
                phone: schema.binaryNodes.phone,
                rank: schema.binaryNodes.rank,
                packageName: schema.binaryNodes.packageName,
                status: schema.binaryNodes.status,
                sponsorName: schema.binaryNodes.sponsorName,
                notes: schema.binaryNodes.notes,
                createdAt: schema.binaryNodes.createdAt,
            })
            .from(schema.binaryNodes)
            .where(
                and(
                    sql`${schema.binaryNodes.parentId} IS NULL`,
                    authUser.role === "super_admin"
                        ? undefined
                        : eq(schema.binaryNodes.userId, authUser.userId),
                ),
            )
            .orderBy(schema.binaryNodes.placementPosition);

        // Fallback: If no root node (parentId is null), fetch any top nodes for user
        if (nodes.length === 0) {
            nodes = await db
                .select({
                    id: schema.binaryNodes.id,
                    parentId: schema.binaryNodes.parentId,
                    placementPosition: schema.binaryNodes.placementPosition,
                    memberName: schema.binaryNodes.memberName,
                    memberId: schema.binaryNodes.memberId,
                    phone: schema.binaryNodes.phone,
                    rank: schema.binaryNodes.rank,
                    packageName: schema.binaryNodes.packageName,
                    status: schema.binaryNodes.status,
                    sponsorName: schema.binaryNodes.sponsorName,
                    notes: schema.binaryNodes.notes,
                    createdAt: schema.binaryNodes.createdAt,
                })
                .from(schema.binaryNodes)
                .where(
                    authUser.role === "super_admin"
                        ? undefined
                        : eq(schema.binaryNodes.userId, authUser.userId),
                )
                .orderBy(schema.binaryNodes.placementPosition)
                .limit(50);
        }
    }

    // Enrich each node with its direct children count and attached projects
    const enrichedNodes = await Promise.all(
        nodes.map(async (node) => {
            // Count children
            const [childCountRow] = await db
                .select({ count: sql<number>`count(*)` })
                .from(schema.binaryNodes)
                .where(eq(schema.binaryNodes.parentId, node.id));

            // Fetch attached projects
            const projects = await db
                .select()
                .from(schema.memberProjects)
                .where(eq(schema.memberProjects.nodeId, node.id))
                .orderBy(desc(schema.memberProjects.createdAt));

            const totalProjectInvest = projects.reduce(
                (sum, p) => sum + Number(p.amountBdt || 0),
                0,
            );

            return {
                ...node,
                childCount: childCountRow?.count || 0,
                projects,
                totalProjectInvest,
                activeProject:
                    projects[0]?.projectName || node.packageName || "Starter",
            };
        }),
    );

    return c.json(enrichedNodes);
});

// Add placement node (10-slot structure)
treeRouter.post("/nodes", async (c) => {
    const authUser = c.get("user");

    if (authUser.role === "demo") {
        return c.json(
            {
                error: "ডেমো অ্যাকাউন্টে নতুন প্লেসমেন্ট নোড তৈরি করার অনুমতি নেই (Demo mode is read-only)।",
            },
            403,
        );
    }

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

    const projectName = body.projectName || "Starter";

    const [node] = await db
        .insert(schema.binaryNodes)
        .values({
            userId: authUser.userId,
            parentId: body.parentId ? Number(body.parentId) : null,
            placementPosition: position,
            sponsorId: body.sponsorId ? Number(body.sponsorId) : null,
            sponsorName: body.sponsorName || null,
            memberId:
                body.memberId ||
                `SBL-${Math.floor(1000 + Math.random() * 9000)}`,
            memberName: body.memberName,
            phone: body.phone || null,
            passwordEncrypted: body.password || null,
            tpinEncrypted: body.tpin || null,
            rank: body.rank || "Associate",
            packageName: projectName,
            status: "active",
            notes: body.notes || null,
        })
        .returning();

    // Automatically create initial project if amount is provided or default
    const initialAmount = body.amountBdt
        ? Number(body.amountBdt)
        : projectName === "International"
          ? 550000
          : projectName === "National"
            ? 120000
            : 10000;

    const returnRate =
        projectName === "International"
            ? "2.00"
            : projectName === "National"
              ? "1.75"
              : "1.50";

    await db.insert(schema.memberProjects).values({
        nodeId: node.id,
        userId: authUser.userId,
        projectName,
        amountBdt: String(initialAmount),
        weeklyReturnRate: returnRate,
        durationWeeks: 100,
        status: "active",
        referenceNote: `Initial project assigned on placement (Slot ${position})`,
    });

    return c.json(node, 201);
});

// Add project under a member node
treeRouter.post("/projects", async (c) => {
    const authUser = c.get("user");

    if (authUser.role === "demo") {
        return c.json(
            {
                error: "ডেমো অ্যাকাউন্টে প্রজেক্ট অ্যাড করার অনুমতি নেই (Demo mode is read-only)।",
            },
            403,
        );
    }

    const body = await c.req.json();
    const nodeId = Number(body.nodeId);
    const projectName = (body.projectName || "").trim(); // Starter, National, International
    const amountBdt = Number(body.amountBdt);
    const referenceNote = (body.referenceNote || "").trim();

    if (!nodeId || !projectName || !amountBdt) {
        return c.json(
            { error: "নোড আইডি, প্রজেক্ট নাম এবং বিনিয়োগ অ্যামাউন্ট আবশ্যক।" },
            400,
        );
    }

    if (!["Starter", "National", "International"].includes(projectName)) {
        return c.json(
            {
                error: "প্রজেক্ট অবশ্যই Starter, National, অথবা International হতে হবে।",
            },
            400,
        );
    }

    const { db } = await getDb(c);

    // Verify node exists
    const [targetNode] = await db
        .select()
        .from(schema.binaryNodes)
        .where(eq(schema.binaryNodes.id, nodeId))
        .limit(1);

    if (!targetNode) {
        return c.json({ error: "মেম্বার নোড পাওয়া যায়নি।" }, 404);
    }

    // Return rates according to official SBL ecosystem document:
    // Starter: 10,000 Tk -> 15,000 Tk over 100 weeks (150 Tk/week)
    // National: 1.75% per week for 100 weeks
    // International: 2.0% per week for 100 weeks
    const returnRate =
        projectName === "International"
            ? "2.00"
            : projectName === "National"
              ? "1.75"
              : "1.50";

    const [newProject] = await db
        .insert(schema.memberProjects)
        .values({
            nodeId,
            userId: authUser.userId,
            projectName,
            amountBdt: String(amountBdt),
            weeklyReturnRate: returnRate,
            durationWeeks: 100,
            status: "active",
            referenceNote: referenceNote || `Added to ${targetNode.memberName}`,
        })
        .returning();

    // Update node's packageName to reflect latest project
    await db
        .update(schema.binaryNodes)
        .set({
            packageName: projectName,
            updatedAt: new Date(),
        })
        .where(eq(schema.binaryNodes.id, nodeId));

    return c.json(
        {
            message: "প্রজেক্ট সফলভাবে মেম্বারের আন্ডারে যুক্ত করা হয়েছে",
            project: newProject,
        },
        201,
    );
});

// Get all projects for a specific node
treeRouter.get("/nodes/:id/projects", async (c) => {
    const nodeId = Number(c.req.param("id"));
    const { db } = await getDb(c);

    const projects = await db
        .select()
        .from(schema.memberProjects)
        .where(eq(schema.memberProjects.nodeId, nodeId))
        .orderBy(desc(schema.memberProjects.createdAt));

    return c.json(projects);
});
