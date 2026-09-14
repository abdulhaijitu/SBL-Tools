import { Hono } from "hono";
import { Env, AuthUser } from "../types/env";
import { authMiddleware } from "../middleware/auth";

export const uploadRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

uploadRouter.use("*", authMiddleware);

uploadRouter.post("/", async (c) => {
    const r2 = c.env.R2_STORAGE;

    if (!r2) {
        return c.json(
            {
                error: "R2 storage binding is not configured in this environment",
            },
            500,
        );
    }

    const formData = await c.req.parseBody();
    const file = formData["file"];

    if (!file || !(file instanceof File)) {
        return c.json({ error: "Please provide a valid file to upload" }, 400);
    }

    // Generate unique R2 key
    const ext = file.name.split(".").pop() || "bin";
    const r2Key = `uploads/${Date.now()}-${Math.random().toString(36).substring(2, 9)}.${ext}`;

    const arrayBuffer = await file.arrayBuffer();
    await r2.put(r2Key, arrayBuffer, {
        httpMetadata: {
            contentType: file.type,
        },
    });

    return c.json(
        {
            message: "File uploaded to R2 successfully",
            key: r2Key,
            name: file.name,
            size: file.size,
            type: file.type,
        },
        201,
    );
});
