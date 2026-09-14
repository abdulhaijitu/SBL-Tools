import { Hono } from "hono";
import { cors } from "hono/cors";
import { logger } from "hono/logger";
import { Env, HonoVariables } from "./types/env";
import { authRouter } from "./routes/auth";
import { leadsRouter } from "./routes/leads";
import { dashboardRouter } from "./routes/dashboard";
import { treeRouter } from "./routes/tree";
import { toolkitRouter } from "./routes/toolkit";
import { financialsRouter } from "./routes/financials";
import { uploadRouter } from "./routes/upload";

const app = new Hono<{ Bindings: Env; Variables: HonoVariables }>();

// Global Middleware
app.use("*", logger());
app.use(
    "*",
    cors({
        origin: "*",
        allowHeaders: ["Content-Type", "Authorization"],
        allowMethods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    }),
);

// Health & Status endpoints under /api
app.get("/api/status", (c) => {
    return c.json({
        status: "online",
        system: "SBL Growth Manager Edge API",
        runtime: "Cloudflare Workers + Hono",
        database: "Cloudflare D1 (Drizzle ORM)",
        storage: "Cloudflare R2",
        timestamp: new Date().toISOString(),
    });
});

app.get("/api/health", (c) => c.json({ status: "healthy" }));

// Mount API Modules
app.route("/api/auth", authRouter);
app.route("/api/leads", leadsRouter);
app.route("/api/dashboard", dashboardRouter);
app.route("/api/tree", treeRouter);
app.route("/api/toolkit", toolkitRouter);
app.route("/api/financials", financialsRouter);
app.route("/api/upload", uploadRouter);

// Global Error Handler for API
app.onError((err, c) => {
    console.error("Unhandled Application Error:", err);
    return c.json(
        {
            error: err.message || "Internal Server Error",
        },
        500,
    );
});

// Fallback to React Frontend Static Assets (HTML, CSS, JS, Images)
app.all("*", async (c) => {
    if (c.env?.ASSETS) {
        return c.env.ASSETS.fetch(c.req.raw);
    }
    return c.text("SBL Tools API is running. UI assets not bundled.", 404);
});

export default app;
