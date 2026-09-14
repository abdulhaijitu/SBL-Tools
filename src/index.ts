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

// Health check
app.get("/", (c) => {
    return c.json({
        status: "online",
        system: "SBL Growth Manager Edge API",
        runtime: "Cloudflare Workers + Hono",
        database: "PostgreSQL (Drizzle ORM + Hyperdrive)",
        storage: "Cloudflare R2",
        timestamp: new Date().toISOString(),
    });
});

app.get("/health", (c) => c.json({ status: "healthy" }));

// Mount API Modules
app.route("/api/auth", authRouter);
app.route("/api/leads", leadsRouter);
app.route("/api/dashboard", dashboardRouter);
app.route("/api/tree", treeRouter);
app.route("/api/toolkit", toolkitRouter);
app.route("/api/financials", financialsRouter);
app.route("/api/upload", uploadRouter);

// Global Error Handler
app.onError((err, c) => {
    console.error("Unhandled Application Error:", err);
    return c.json(
        {
            error: err.message || "Internal Server Error",
        },
        500,
    );
});

// 404 Handler
app.notFound((c) => {
    return c.json({ error: "Endpoint not found" }, 404);
});

export default app;
