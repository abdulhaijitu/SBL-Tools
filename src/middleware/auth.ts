import { Context, Next } from "hono";
import { verify } from "hono/jwt";
import { Env, AuthUser } from "../types/env";

export async function authMiddleware(
    c: Context<{ Bindings: Env; Variables: { user: AuthUser } }>,
    next: Next,
) {
    const authHeader = c.req.header("Authorization");

    if (!authHeader || !authHeader.startsWith("Bearer ")) {
        return c.json(
            { error: "Unauthorized: Missing or invalid token format" },
            401,
        );
    }

    const token = authHeader.split(" ")[1];
    const secret =
        c.env.JWT_SECRET || "super-secret-jwt-key-replace-in-production";

    try {
        const payload = await verify(token, secret, "HS256");
        c.set("user", {
            userId: Number(payload.sub),
            email: String(payload.email),
            role: String(payload.role || "member"),
            phone: payload.phone ? String(payload.phone) : undefined,
        });
        await next();
    } catch {
        return c.json({ error: "Unauthorized: Invalid or expired token" }, 401);
    }
}
