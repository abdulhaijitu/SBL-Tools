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

// 1. Global Logger Middleware
app.use("*", logger());

// 2. Strict Security Headers Middleware
app.use("*", async (c, next) => {
    await next();

    // Security Headers
    c.res.headers.set("X-Content-Type-Options", "nosniff");
    c.res.headers.set("X-Frame-Options", "SAMEORIGIN");
    c.res.headers.set("X-XSS-Protection", "1; mode=block");
    c.res.headers.set("Referrer-Policy", "strict-origin-when-cross-origin");
    c.res.headers.set("Permissions-Policy", "camera=(), microphone=(), geolocation=()");
    c.res.headers.set(
        "Strict-Transport-Security",
        "max-age=31536000; includeSubDomains; preload"
    );

    // Content Security Policy
    c.res.headers.set(
        "Content-Security-Policy",
        "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: https: blob:; connect-src 'self' https:; object-src 'none'; base-uri 'self';"
    );
});

// 3. CORS Configuration
app.use(
    "*",
    cors({
        origin: "*",
        allowHeaders: ["Content-Type", "Authorization", "X-Requested-With"],
        allowMethods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
        maxAge: 86400,
    }),
);

// 4. Health & Status endpoints under /api
app.get("/api/status", (c) => {
    return c.json({
        status: "online",
        system: "SBL Growth Manager Edge API",
        runtime: "Cloudflare Workers + Hono",
        database: "Cloudflare D1 (Drizzle ORM)",
        storage: "Cloudflare R2",
        security: "Enforced (HSTS, CSP, Sniff-Protection)",
        speed: "Edge CDN Cached",
        timestamp: new Date().toISOString(),
    });
});

app.get("/api/health", (c) => c.json({ status: "healthy" }));

// 5. Mount API Modules
app.route("/api/auth", authRouter);
app.route("/api/leads", leadsRouter);
app.route("/api/dashboard", dashboardRouter);
app.route("/api/tree", treeRouter);
app.route("/api/toolkit", toolkitRouter);
app.route("/api/financials", financialsRouter);
app.route("/api/upload", uploadRouter);

// 6. Global Error Handler for API
app.onError((err, c) => {
    console.error("Unhandled Application Error:", err);
    return c.json(
        {
            error: err.message || "Internal Server Error",
        },
        500,
    );
});

// 7. Static Asset Serving with Speed & Cache Optimization
app.all("*", async (c) => {
    if (c.env?.ASSETS) {
        const response = await c.env.ASSETS.fetch(c.req.raw);
        const url = new URL(c.req.url);

        // Clone response to attach caching headers
        const newResponse = new Response(response.body, response);

        // Immutable long-term caching for hashed build assets
        if (url.pathname.startsWith("/assets/")) {
            newResponse.headers.set(
                "Cache-Control",
                "public, max-age=31536000, immutable"
            );
        } else if (
            url.pathname.endsWith(".webp") ||
            url.pathname.endsWith(".png") ||
            url.pathname.endsWith(".jpg") ||
            url.pathname.endsWith(".jpeg") ||
            url.pathname.endsWith(".ico")
        ) {
            // Images: Cache for 7 days with stale-while-revalidate
            newResponse.headers.set(
                "Cache-Control",
                "public, max-age=604800, stale-while-revalidate=86400"
            );
        } else {
            // HTML documents: Revalidate to always pick up latest SPA releases
            newResponse.headers.set(
                "Cache-Control",
                "public, max-age=0, must-revalidate"
            );
        }

        return newResponse;
    }
    return c.text("SBL Tools API is running. UI assets not bundled.", 404);
});

export default app;
