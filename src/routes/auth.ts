import { Hono } from "hono";
import { sign } from "hono/jwt";
import * as bcrypt from "bcryptjs";
import { eq } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const authRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

authRouter.post("/login", async (c) => {
    const { email, password } = await c.req.json();

    if (!email || !password) {
        return c.json({ error: "Email and password are required" }, 400);
    }

    const { db } = await getDb(c);
    const [user] = await db
        .select()
        .from(schema.users)
        .where(eq(schema.users.email, email.toLowerCase().trim()))
        .limit(1);

    if (!user) {
        return c.json({ error: "Invalid email or password" }, 401);
    }

    if (user.status !== "active") {
        return c.json(
            { error: "Account is inactive. Please contact support." },
            403,
        );
    }

    // Handle bcrypt hash (Laravel $2y$ or $2a$)
    let hash = user.password;
    if (hash.startsWith("$2y$")) {
        hash = "$2a$" + hash.substring(4);
    }

    const isMatch = await bcrypt.compare(password, hash);
    if (!isMatch) {
        return c.json({ error: "Invalid email or password" }, 401);
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
            role: roleName,
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

    return c.json({
        user: {
            ...user,
            roles: userRolesList,
            primaryRole: userRolesList[0]?.roleName || "member",
        },
    });
});
