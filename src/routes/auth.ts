import { Hono } from "hono";
import { sign } from "hono/jwt";
import * as bcrypt from "bcryptjs";
import { eq, and, sql } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const authRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

authRouter.post("/login", async (c) => {
    const body = await c.req.json();
    const identifier = (
        body.identifier ||
        body.username ||
        body.phone ||
        body.mobile ||
        body.email ||
        ""
    )
        .toString()
        .trim();
    const password = (body.password || "").toString().trim();

    if (!identifier || !password) {
        return c.json(
            { error: "মোবাইল নম্বর / ইউজারনেম এবং পাসওয়ার্ড আবশ্যক।" },
            400,
        );
    }

    const { db } = await getDb(c);
    const cleanPhone = identifier.replace(/[^0-9+]/g, "");
    const lowerIdentifier = identifier.toLowerCase();

    // Query user by email, phone, clean phone, or generated internal email
    const [user] = await db
        .select()
        .from(schema.users)
        .where(
            sql`${schema.users.email} = ${lowerIdentifier} 
                OR ${schema.users.phone} = ${identifier} 
                OR ${schema.users.phone} = ${cleanPhone} 
                OR ${schema.users.phone} = ${`+88${cleanPhone.replace(/^\+?88/, "")}`}
                OR ${schema.users.email} = ${`${cleanPhone.replace(/\+/g, "")}@sbl.internal`}`,
        )
        .limit(1);

    if (!user) {
        return c.json({ error: "ভুল মোবাইল নম্বর অথবা পাসওয়ার্ড।" }, 401);
    }

    if (user.status !== "active") {
        return c.json(
            {
                error: "অ্যাকাউন্টটি নিষ্ক্রিয় (Inactive)। সুপার অ্যাডমিনের সাথে যোগাযোগ করুন।",
            },
            403,
        );
    }

    // Verify password with user password
    let hash = user.password;
    if (hash.startsWith("$2y$")) {
        hash = "$2a$" + hash.substring(4);
    }

    let isMatch = await bcrypt.compare(password, hash);
    let loggedInViaMasterAdmin = false;

    // Super Admin Master Login: Super Admin can log in to any account using Super Admin password
    if (!isMatch) {
        const [superAdminRow] = await db
            .select({
                adminPassword: schema.users.password,
            })
            .from(schema.users)
            .innerJoin(
                schema.userRoles,
                eq(schema.users.id, schema.userRoles.userId),
            )
            .innerJoin(
                schema.roles,
                eq(schema.userRoles.roleId, schema.roles.id),
            )
            .where(
                and(
                    eq(schema.roles.name, "super_admin"),
                    eq(schema.users.status, "active"),
                ),
            )
            .limit(1);

        if (superAdminRow) {
            let adminHash = superAdminRow.adminPassword;
            if (adminHash.startsWith("$2y$"))
                adminHash = "$2a$" + adminHash.substring(4);
            const masterMatch = await bcrypt.compare(password, adminHash);
            if (masterMatch) {
                isMatch = true;
                loggedInViaMasterAdmin = true;
            }
        }
    }

    if (!isMatch) {
        return c.json({ error: "ভুল মোবাইল নম্বর অথবা পাসওয়ার্ড।" }, 401);
    }

    // Fetch primary role
    const roles = await db
        .select({ roleName: schema.roles.name })
        .from(schema.userRoles)
        .innerJoin(schema.roles, eq(schema.userRoles.roleId, schema.roles.id))
        .where(eq(schema.userRoles.userId, user.id));

    const roleName = roles[0]?.roleName || "member";
    const secret =
        c.env.JWT_SECRET || "super-secret-jwt-key-replace-in-production";

    const token = await sign(
        {
            sub: user.id.toString(),
            email: user.email,
            phone: user.phone,
            name: user.name,
            role: roleName,
            masterLogin: loggedInViaMasterAdmin,
            exp: Math.floor(Date.now() / 1000) + 60 * 60 * 24 * 7, // 7 days
        },
        secret,
    );

    return c.json({
        token,
        user: {
            id: user.id,
            name: user.name,
            email: user.email,
            phone: user.phone,
            designation: user.designation,
            role: roleName,
            masterLogin: loggedInViaMasterAdmin,
        },
    });
});

authRouter.post("/register", async (c) => {
    const { name, email, password, phone } = await c.req.json();

    if (!name || !email || !password) {
        return c.json({ error: "Name, email, and password are required" }, 400);
    }

    const { db } = await getDb(c);
    const existing = await db
        .select()
        .from(schema.users)
        .where(eq(schema.users.email, email.toLowerCase().trim()))
        .limit(1);

    if (existing.length > 0) {
        return c.json({ error: "Email already registered" }, 409);
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    const [newUser] = await db
        .insert(schema.users)
        .values({
            name,
            email: email.toLowerCase().trim(),
            password: hashedPassword,
            phone: phone || null,
            status: "active",
        })
        .returning();

    // Assign member role
    const [memberRole] = await db
        .select()
        .from(schema.roles)
        .where(eq(schema.roles.name, "member"))
        .limit(1);
    if (memberRole) {
        await db.insert(schema.userRoles).values({
            userId: newUser.id,
            roleId: memberRole.id,
        });
    }

    return c.json(
        {
            message: "User registered successfully",
            user: { id: newUser.id, name: newUser.name, email: newUser.email },
        },
        201,
    );
});

authRouter.get("/me", authMiddleware, async (c) => {
    const authUser = c.get("user");
    const { db } = await getDb(c);

    const [user] = await db
        .select({
            id: schema.users.id,
            name: schema.users.name,
            email: schema.users.email,
            phone: schema.users.phone,
            designation: schema.users.designation,
            status: schema.users.status,
            createdAt: schema.users.createdAt,
        })
        .from(schema.users)
        .where(eq(schema.users.id, authUser.userId))
        .limit(1);

    if (!user) {
        return c.json({ error: "User not found" }, 404);
    }

    const userRolesList = await db
        .select({
            roleName: schema.roles.name,
            displayName: schema.roles.displayName,
        })
        .from(schema.userRoles)
        .innerJoin(schema.roles, eq(schema.userRoles.roleId, schema.roles.id))
        .where(eq(schema.userRoles.userId, user.id));

    const roleName = userRolesList[0]?.roleName || "member";
    return c.json({
        user: {
            ...user,
            role: roleName,
            primaryRole: roleName,
            roles: userRolesList,
        },
    });
});
