import { Hono } from "hono";
import * as bcrypt from "bcryptjs";
import { sign } from "hono/jwt";
import { eq, sql } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const usersRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

usersRouter.use("*", authMiddleware);

// Middleware check for super_admin
usersRouter.use("*", async (c, next) => {
    const authUser = c.get("user");
    if (authUser.role !== "super_admin") {
        return c.json(
            { error: "অনুমতি নেই: শুধুমাত্র সুপার অ্যাডমিন ইউজার ম্যানেজমেন্ট এক্সেস করতে পারবে।" },
            403,
        );
    }
    await next();
});

// List all users with roles and lead stats
usersRouter.get("/", async (c) => {
    const { db } = await getDb(c);

    const userList = await db
        .select({
            id: schema.users.id,
            name: schema.users.name,
            email: schema.users.email,
            phone: schema.users.phone,
            designation: schema.users.designation,
            status: schema.users.status,
            createdAt: schema.users.createdAt,
            roleName: schema.roles.name,
            roleDisplayName: schema.roles.displayName,
        })
        .from(schema.users)
        .leftJoin(schema.userRoles, eq(schema.users.id, schema.userRoles.userId))
        .leftJoin(schema.roles, eq(schema.userRoles.roleId, schema.roles.id))
        .orderBy(schema.users.id);

    // Fetch lead counts per user
    const leadCounts = await db
        .select({
            userId: schema.leads.ownerUserId,
            count: sql<number>`count(*)`,
        })
        .from(schema.leads)
        .where(sql`${schema.leads.deletedAt} is null`)
        .groupBy(schema.leads.ownerUserId);

    const leadCountMap = new Map<number, number>();
    for (const row of leadCounts) {
        if (row.userId) leadCountMap.set(row.userId, row.count);
    }

    // Fetch binary node count per user
    const nodeCounts = await db
        .select({
            userId: schema.binaryNodes.userId,
            count: sql<number>`count(*)`,
        })
        .from(schema.binaryNodes)
        .groupBy(schema.binaryNodes.userId);

    const nodeCountMap = new Map<number, number>();
    for (const row of nodeCounts) {
        if (row.userId) nodeCountMap.set(row.userId, row.count);
    }

    const enriched = userList.map((u) => ({
        ...u,
        role: u.roleName || "member",
        roleDisplay: u.roleDisplayName || (u.roleName === "super_admin" ? "Super Admin" : u.roleName === "demo" ? "Demo User" : "Associate Member"),
        leadCount: leadCountMap.get(u.id) || 0,
        nodeCount: nodeCountMap.get(u.id) || 0,
    }));

    return c.json(enriched);
});

// Create new user (Super Admin sets Mobile Number as username + Password + Role)
usersRouter.post("/", async (c) => {
    const body = await c.req.json();
    const name = (body.name || "").trim();
    const phone = (body.phone || body.mobile || "").trim();
    const password = (body.password || "").trim();
    const role = (body.role || "member").trim().toLowerCase(); // super_admin, member, demo
    const designation = (body.designation || "").trim();
    let email = (body.email || "").trim().toLowerCase();

    if (!name || !phone || !password) {
        return c.json(
            { error: "নাম, মোবাইল নম্বর (ইউজারনেম) এবং পাসওয়ার্ড দেওয়া বাধ্যতামূলক।" },
            400,
        );
    }

    if (password.length < 4) {
        return c.json(
            { error: "পাসওয়ার্ড ন্যূনতম ৪ অক্ষরের হতে হবে।" },
            400,
        );
    }

    // Clean phone number (strip spaces, dashes)
    const cleanPhone = phone.replace(/[^0-9+]/g, "");

    // Fallback email if not provided
    if (!email) {
        email = `${cleanPhone.replace(/\+/g, "")}@sbl.internal`;
    }

    const { db } = await getDb(c);

    // Check duplicate phone or email
    const existing = await db
        .select()
        .from(schema.users)
        .where(
            sql`${schema.users.phone} = ${cleanPhone} OR ${schema.users.email} = ${email}`,
        )
        .limit(1);

    if (existing.length > 0) {
        return c.json(
            { error: "এই মোবাইল নম্বর বা ইমেইল দিয়ে ইতোমধ্যে একটি অ্যাকাউন্ট রয়েছে।" },
            409,
        );
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    const [newUser] = await db
        .insert(schema.users)
        .values({
            name,
            phone: cleanPhone,
            email,
            password: hashedPassword,
            designation:
                designation ||
                (role === "super_admin"
                    ? "Super Administrator"
                    : role === "demo"
                    ? "Demo Showcase"
                    : "Associate Member"),
            status: "active",
        })
        .returning();

    // Assign Role in user_roles
    const [targetRole] = await db
        .select()
        .from(schema.roles)
        .where(eq(schema.roles.name, role))
        .limit(1);

    if (targetRole) {
        await db.insert(schema.userRoles).values({
            userId: newUser.id,
            roleId: targetRole.id,
        });
    }

    // If role is demo, seed initial sample demo leads and sample tree node so demo account has immediate preview
    if (role === "demo") {
        try {
            await db.insert(schema.leads).values([
                {
                    name: "রাকিবুল ইসলাম (ডেমো)",
                    mobile: "01711000001",
                    whatsapp: "01711000001",
                    location: "মিরপুর, ঢাকা",
                    professionOrBusiness: "ব্যবসায়ী (গার্মেন্টস)",
                    stage: "interested",
                    temperature: "hot",
                    score: 70,
                    ownerUserId: newUser.id,
                    notes: "ডেমো লিড: লিডারশিপ এলিট প্যাকেজটিতে আগ্রহ প্রকাশ করেছেন।",
                },
                {
                    name: "তানজিনা আক্তার (ডেমো)",
                    mobile: "01811000002",
                    location: "ধানমন্ডি, ঢাকা",
                    professionOrBusiness: "ব্যাংকার",
                    stage: "follow_up",
                    temperature: "warm",
                    score: 50,
                    ownerUserId: newUser.id,
                    notes: "ডেমো লিড: প্রেজেন্টেশন স্লাইডার দেখেছেন, আগামী সপ্তাহে সিদ্ধান্ত নেবেন।",
                },
            ]);

            await db.insert(schema.binaryNodes).values({
                userId: newUser.id,
                placementPosition: 1,
                memberName: newUser.name,
                phone: newUser.phone,
                rank: "Associate",
                packageName: "Basic Starter",
                status: "active",
                notes: "ডেমো রুট নোড",
            });
        } catch (e) {
            console.error("Demo seeding error:", e);
        }
    }

    return c.json(
        {
            message: "ইউজার সফলভাবে তৈরি করা হয়েছে",
            user: {
                id: newUser.id,
                name: newUser.name,
                phone: newUser.phone,
                email: newUser.email,
                role,
                status: newUser.status,
            },
        },
        201,
    );
});

// Update user
usersRouter.put("/:id", async (c) => {
    const id = Number(c.req.param("id"));
    const body = await c.req.json();
    const { db } = await getDb(c);

    const updateData: any = {
        updatedAt: new Date(),
    };

    if (body.name) updateData.name = body.name.trim();
    if (body.phone) updateData.phone = body.phone.trim().replace(/[^0-9+]/g, "");
    if (body.designation !== undefined) updateData.designation = body.designation.trim();
    if (body.status) updateData.status = body.status;

    if (body.password && body.password.trim().length >= 4) {
        updateData.password = await bcrypt.hash(body.password.trim(), 10);
    }

    const [updatedUser] = await db
        .update(schema.users)
        .set(updateData)
        .where(eq(schema.users.id, id))
        .returning();

    // If role changed
    if (body.role) {
        const [targetRole] = await db
            .select()
            .from(schema.roles)
            .where(eq(schema.roles.name, body.role.trim().toLowerCase()))
            .limit(1);

        if (targetRole) {
            await db
                .delete(schema.userRoles)
                .where(eq(schema.userRoles.userId, id));

            await db.insert(schema.userRoles).values({
                userId: id,
                roleId: targetRole.id,
            });
        }
    }

    return c.json({
        message: "ইউজার তথ্য আপডেট করা হয়েছে",
        user: updatedUser,
    });
});

// Delete user
usersRouter.delete("/:id", async (c) => {
    const id = Number(c.req.param("id"));
    const authUser = c.get("user");

    if (id === authUser.userId) {
        return c.json(
            { error: "নিজের অ্যাকাউন্ট ডিলিট করা সম্ভব নয়।" },
            400,
        );
    }

    if (id === 1) {
        return c.json(
            { error: "সুপার অ্যাডমিনের প্রাথমিক মূল অ্যাকাউন্ট ডিলিট করা যাবে না।" },
            400,
        );
    }

    const { db } = await getDb(c);

    await db.delete(schema.users).where(eq(schema.users.id, id));

    return c.json({ message: "ইউজার সফলভাবে মুছে ফেলা হয়েছে।" });
});

// Impersonate (Login as this user)
usersRouter.post("/:id/impersonate", async (c) => {
    const id = Number(c.req.param("id"));
    const authUser = c.get("user");
    const { db } = await getDb(c);

    const [targetUser] = await db
        .select()
        .from(schema.users)
        .where(eq(schema.users.id, id))
        .limit(1);

    if (!targetUser) {
        return c.json({ error: "ইউজার পাওয়া যায়নি।" }, 404);
    }

    // Fetch target user's role
    const roles = await db
        .select({ roleName: schema.roles.name })
        .from(schema.userRoles)
        .innerJoin(schema.roles, eq(schema.userRoles.roleId, schema.roles.id))
        .where(eq(schema.userRoles.userId, targetUser.id));

    const roleName = roles[0]?.roleName || "member";
    const secret =
        c.env.JWT_SECRET || "super-secret-jwt-key-replace-in-production";

    // Generate token for target user, with impersonatedBy flag
    const token = await sign(
        {
            sub: targetUser.id.toString(),
            email: targetUser.email,
            phone: targetUser.phone,
            name: targetUser.name,
            role: roleName,
            impersonatedBy: authUser.userId,
            exp: Math.floor(Date.now() / 1000) + 60 * 60 * 24, // 24 hours
        },
        secret,
    );

    return c.json({
        token,
        user: {
            id: targetUser.id,
            name: targetUser.name,
            email: targetUser.email,
            phone: targetUser.phone,
            designation: targetUser.designation,
            role: roleName,
            impersonatedBy: authUser.userId,
        },
    });
});
