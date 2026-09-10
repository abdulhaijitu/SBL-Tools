import bcrypt from "bcryptjs";

// SBL Growth Manager - Cloudflare Worker Edge Application
// Serves the exact, 100% pixel-perfect compiled Laravel Blade views and handles real D1 CRUD on the edge.

const SBL_LOGO_BASE64 = __SBL_LOGO_BASE64__;
const SBL_PACKAGES_QR_BASE64 = __SBL_PACKAGES_QR_BASE64__;
const SBL_PACKAGES_SHEET_BASE64 = __SBL_PACKAGES_SHEET_BASE64__;
const CSS_CONTENT = __CSS_CONTENT__;
const JS_CONTENT = __JS_CONTENT__;
const CSS_PATH = __CSS_PATH__;
const JS_PATH = __JS_PATH__;
const PAGES = __PAGES__;
const CLIENT_SYNC_JS = __CLIENT_SYNC_JS__;
const MANIFEST_CONTENT = __MANIFEST_CONTENT__;
const SW_CONTENT = __SW_CONTENT__;
const OFFLINE_CONTENT = __OFFLINE_CONTENT__;

const APP_SECRET = "jbGgydtFYDKPLRpynPVv4O4XgYQNxvMTVDzoSBWrbMY=";

async function signSession(userId, secret = APP_SECRET) {
    const expiry = Date.now() + 30 * 24 * 60 * 60 * 1000; // 30 days
    const data = `${userId}.${expiry}`;
    const enc = new TextEncoder();
    const key = await crypto.subtle.importKey(
        "raw",
        enc.encode(secret),
        { name: "HMAC", hash: "SHA-256" },
        false,
        ["sign"],
    );
    const sigBuf = await crypto.subtle.sign("HMAC", key, enc.encode(data));
    const sigHex = Array.from(new Uint8Array(sigBuf))
        .map((b) => b.toString(16).padStart(2, "0"))
        .join("");
    return `${data}.${sigHex}`;
}

async function verifySession(token, secret = APP_SECRET) {
    if (!token || typeof token !== "string") return null;
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const [userIdStr, expiryStr, expectedSigHex] = parts;
    const expiry = parseInt(expiryStr, 10);
    if (isNaN(expiry) || Date.now() > expiry) return null;

    const data = `${userIdStr}.${expiryStr}`;
    const enc = new TextEncoder();
    try {
        const key = await crypto.subtle.importKey(
            "raw",
            enc.encode(secret),
            { name: "HMAC", hash: "SHA-256" },
            false,
            ["verify"],
        );
        const match = expectedSigHex.match(/.{1,2}/g);
        if (!match) return null;
        const sigBytes = new Uint8Array(
            match.map((byte) => parseInt(byte, 16)),
        );
        const isValid = await crypto.subtle.verify(
            "HMAC",
            key,
            sigBytes,
            enc.encode(data),
        );
        return isValid ? parseInt(userIdStr, 10) : null;
    } catch (e) {
        return null;
    }
}

function getCookie(name, cookieStr) {
    if (!cookieStr) return null;
    const match = cookieStr.match(
        new RegExp("(?:^|;\\s*)" + name + "=([^;]*)"),
    );
    return match ? decodeURIComponent(match[1]) : null;
}

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

let schemaMigrated = false;
async function ensureD1Schema(db) {
    if (schemaMigrated || !db) return;
    try {
        await db.prepare("ALTER TABLE leads ADD COLUMN photo TEXT").run();
    } catch (e) {}
    try {
        await db
            .prepare(
                "CREATE TABLE IF NOT EXISTS marketing_resources (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT, category TEXT, type TEXT, url TEXT, file_path TEXT, description TEXT, is_active INTEGER DEFAULT 1, created_at DATETIME, updated_at DATETIME)",
            )
            .run();
    } catch (e) {}
    try {
        await db
            .prepare(
                "CREATE TABLE IF NOT EXISTS abbreviations (id INTEGER PRIMARY KEY AUTOINCREMENT, short_form TEXT UNIQUE, full_form TEXT, meaning TEXT, category TEXT, created_at DATETIME, updated_at DATETIME)",
            )
            .run();
    } catch (e) {}
    schemaMigrated = true;
}

export default {
    async fetch(request, env, ctx) {
        const url = new URL(request.url);
        const path = url.pathname;

        // 1. Compiled Vite CSS
        if (path === CSS_PATH) {
            return new Response(CSS_CONTENT, {
                headers: {
                    "Content-Type": "text/css; charset=utf-8",
                    "Cache-Control": "public, max-age=31536000, immutable",
                },
            });
        }

        // 2. Compiled Vite JS
        if (path === JS_PATH) {
            return new Response(JS_CONTENT, {
                headers: {
                    "Content-Type": "application/javascript; charset=utf-8",
                    "Cache-Control": "public, max-age=31536000, immutable",
                },
            });
        }

        // 3. Logo & Static Images
        if (
            path === "/images/sbl-logo.png" ||
            path === "/favicon.png" ||
            path === "/apple-touch-icon.png"
        ) {
            const binaryString = atob(SBL_LOGO_BASE64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return new Response(bytes.buffer, {
                headers: {
                    "Content-Type": "image/png",
                    "Cache-Control": "public, max-age=31536000, immutable",
                },
            });
        }

        if (path === "/images/sbl-packages-qr.png") {
            const binaryString = atob(SBL_PACKAGES_QR_BASE64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return new Response(bytes.buffer, {
                headers: {
                    "Content-Type": "image/png",
                    "Cache-Control": "public, max-age=31536000, immutable",
                },
            });
        }

        if (path === "/images/sbl-packages-sheet.png") {
            const binaryString = atob(SBL_PACKAGES_SHEET_BASE64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return new Response(bytes.buffer, {
                headers: {
                    "Content-Type": "image/png",
                    "Cache-Control": "public, max-age=31536000, immutable",
                },
            });
        }

        // 4. PWA Assets (Manifest, Service Worker, Offline HTML, Icons)
        if (path === "/manifest.webmanifest" || path === "/manifest.json") {
            return new Response(MANIFEST_CONTENT, {
                headers: {
                    "Content-Type": "application/manifest+json; charset=utf-8",
                    "Cache-Control": "public, max-age=86400",
                },
            });
        }

        if (path === "/sw.js") {
            return new Response(SW_CONTENT, {
                headers: {
                    "Content-Type": "application/javascript; charset=utf-8",
                    "Cache-Control": "no-cache, no-store, must-revalidate",
                },
            });
        }

        if (path === "/offline.html") {
            return new Response(OFFLINE_CONTENT, {
                headers: {
                    "Content-Type": "text/html; charset=utf-8",
                    "Cache-Control": "public, max-age=3600",
                },
            });
        }

        if (path.startsWith("/icons/")) {
            const binaryString = atob(SBL_LOGO_BASE64);
            const len = binaryString.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            return new Response(bytes.buffer, {
                headers: {
                    "Content-Type": "image/png",
                    "Cache-Control": "public, max-age=31536000, immutable",
                },
            });
        }

        if (path === "/ping") {
            return new Response("pong", { status: 200 });
        }

        const db = env.DB || env.sbl_database;

        // 5. Authentication: Logout
        if (path === "/logout") {
            return new Response(null, {
                status: 302,
                headers: {
                    Location: "/login",
                    "Set-Cookie":
                        "sbl_session=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0; Secure",
                },
            });
        }

        // 6. Authentication: Login POST
        if (path === "/login" && request.method === "POST") {
            let formData = null;
            const contentType = request.headers.get("content-type") || "";
            if (
                contentType.includes("form") ||
                contentType.includes("multipart") ||
                contentType.includes("urlencoded")
            ) {
                try {
                    formData = await request.formData();
                } catch (e) {}
            } else if (contentType.includes("json")) {
                try {
                    const j = await request.json();
                    formData = {
                        get(k) {
                            return j[k] !== undefined && j[k] !== null
                                ? String(j[k])
                                : null;
                        },
                    };
                } catch (e) {}
            }

            const loginInput = (
                formData
                    ? formData.get("login") ||
                      formData.get("email") ||
                      formData.get("phone") ||
                      ""
                    : ""
            )
                .toString()
                .trim();
            const passwordInput = (
                formData ? formData.get("password") || "" : ""
            ).toString();

            if (!loginInput || !passwordInput) {
                return new Response(null, {
                    status: 302,
                    headers: { Location: "/login?error=empty" },
                });
            }

            let user = null;
            if (db) {
                try {
                    const cleanPhone = loginInput.replace(/[^0-9]/g, "");
                    const phoneVariants = [loginInput];
                    if (cleanPhone) {
                        phoneVariants.push(cleanPhone);
                        if (cleanPhone.startsWith("01")) {
                            phoneVariants.push("88" + cleanPhone);
                            phoneVariants.push("+88" + cleanPhone);
                        } else if (cleanPhone.startsWith("8801")) {
                            phoneVariants.push(cleanPhone.slice(2));
                        }
                    }

                    const placeholders = phoneVariants
                        .map(() => "?")
                        .join(", ");
                    const sql = `SELECT * FROM users WHERE email = ? OR phone IN (${placeholders}) LIMIT 1`;
                    user = await db
                        .prepare(sql)
                        .bind(loginInput, ...phoneVariants)
                        .first();
                } catch (e) {
                    console.error("Login user query error:", e);
                }
            }

            if (!user) {
                return new Response(null, {
                    status: 302,
                    headers: { Location: "/login?error=invalid" },
                });
            }

            if (user.status === "inactive") {
                return new Response(null, {
                    status: 302,
                    headers: { Location: "/login?error=inactive" },
                });
            }

            let validPass = false;
            if (user.password) {
                const hashCompat = user.password.replace(/^\$2y\$/, "$2a$");
                try {
                    validPass = bcrypt.compareSync(passwordInput, hashCompat);
                } catch (e) {
                    console.error("Bcrypt compare error:", e);
                }
            }

            if (!validPass) {
                return new Response(null, {
                    status: 302,
                    headers: { Location: "/login?error=invalid" },
                });
            }

            const sessionToken = await signSession(user.id, APP_SECRET);
            return new Response(null, {
                status: 302,
                headers: {
                    Location: "/dashboard",
                    "Set-Cookie": `sbl_session=${sessionToken}; Path=/; HttpOnly; SameSite=Lax; Max-Age=2592000; Secure`,
                },
            });
        }

        // Glossary management is opt-in until an administrator password is configured.
        let abbreviationAdmin = false;
        if (/^\/abbreviations(?:\/\d+)?$/.test(path)) {
            const password = env.ABBREVIATIONS_ADMIN_PASSWORD;
            if (password) {
                const expected = "Basic " + btoa("admin:" + password);
                const supplied = request.headers.get("Authorization") || "";
                const digest = async (value) =>
                    new Uint8Array(
                        await crypto.subtle.digest(
                            "SHA-256",
                            new TextEncoder().encode(value),
                        ),
                    );
                const [a, b] = await Promise.all([
                    digest(expected),
                    digest(supplied),
                ]);
                abbreviationAdmin =
                    a.reduce((diff, value, i) => diff | (value ^ b[i]), 0) ===
                    0;
                if (!abbreviationAdmin)
                    return new Response("Administrator sign-in required.", {
                        status: 401,
                        headers: {
                            "WWW-Authenticate":
                                'Basic realm="Abbreviations", charset="UTF-8"',
                        },
                    });
            }
            if (request.method !== "GET") {
                if (!abbreviationAdmin)
                    return Response.json(
                        {
                            message:
                                "Abbreviation management is not configured.",
                        },
                        { status: 403 },
                    );
                if (request.headers.get("Origin") !== url.origin)
                    return Response.json(
                        { message: "Invalid origin." },
                        { status: 403 },
                    );
                if (!db)
                    return Response.json(
                        { message: "Database unavailable." },
                        { status: 503 },
                    );
                const id = /^\/abbreviations\/(\d+)$/.exec(path)?.[1];
                try {
                    if (
                        id &&
                        !(await db
                            .prepare(
                                "SELECT id FROM abbreviations WHERE id = ?",
                            )
                            .bind(id)
                            .first())
                    )
                        return Response.json(
                            { message: "Abbreviation not found." },
                            { status: 404 },
                        );
                    if (id && request.method === "DELETE") {
                        await db
                            .prepare("DELETE FROM abbreviations WHERE id = ?")
                            .bind(id)
                            .run();
                        return new Response(null, { status: 204 });
                    }
                    if (
                        (!id && request.method !== "POST") ||
                        (id && request.method !== "PUT")
                    )
                        return new Response(null, { status: 405 });
                    let data;
                    try {
                        data = await request.json();
                    } catch {
                        return Response.json(
                            { message: "Invalid JSON." },
                            { status: 422 },
                        );
                    }
                    if (
                        !data ||
                        typeof data !== "object" ||
                        Array.isArray(data)
                    )
                        return Response.json(
                            { message: "Invalid term." },
                            { status: 422 },
                        );
                    const categories = {
                        ecommerce: "E-Commerce Core",
                        marketing: "Marketing & Ads",
                        logistics: "Logistics & Delivery",
                        network: "SBL Network & System",
                        finance: "Finance & Operations",
                    };
                    if (!Object.hasOwn(categories, data.category_slug))
                        return Response.json(
                            { message: "Select a valid category." },
                            { status: 422 },
                        );
                    for (const [key, max] of Object.entries({
                        code: 50,
                        name: 200,
                        meaning_bn: 1000,
                        description_bn: 3000,
                        icon: 20,
                        tag: 100,
                    })) {
                        if (data[key] == null && ["icon", "tag"].includes(key))
                            data[key] = "";
                        if (
                            typeof data[key] !== "string" ||
                            [...data[key]].length > max ||
                            (!["icon", "tag"].includes(key) &&
                                !data[key].trim())
                        )
                            return Response.json(
                                { message: "Invalid " + key + "." },
                                { status: 422 },
                            );
                        data[key] = data[key].trim();
                    }
                    if (
                        await db
                            .prepare(
                                "SELECT id FROM abbreviations WHERE code = ? AND id != ?",
                            )
                            .bind(data.code, id || 0)
                            .first()
                    )
                        return Response.json(
                            { message: "This short form already exists." },
                            { status: 422 },
                        );
                    const values = [
                        data.code,
                        data.name,
                        categories[data.category_slug],
                        data.category_slug,
                        data.meaning_bn,
                        data.description_bn,
                        data.icon || "📖",
                        data.tag,
                    ];
                    let savedId = id;
                    if (id)
                        await db
                            .prepare(
                                "UPDATE abbreviations SET code=?, name=?, category=?, category_slug=?, meaning_bn=?, description_bn=?, icon=?, tag=?, updated_at=CURRENT_TIMESTAMP WHERE id=?",
                            )
                            .bind(...values, id)
                            .run();
                    else {
                        const result = await db
                            .prepare(
                                "INSERT INTO abbreviations (code,name,category,category_slug,meaning_bn,description_bn,icon,tag,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)",
                            )
                            .bind(...values)
                            .run();
                        savedId = result.meta.last_row_id;
                    }
                    return Response.json(
                        await db
                            .prepare("SELECT * FROM abbreviations WHERE id=?")
                            .bind(savedId)
                            .first(),
                        { status: id ? 200 : 201 },
                    );
                } catch (error) {
                    console.error("Abbreviation write failed", error);
                    return Response.json(
                        {
                            message:
                                "Unable to save abbreviation. Check database configuration.",
                        },
                        { status: 503 },
                    );
                }
            }
        }

        // Team Member Credentials GET endpoint
        if (
            path.match(/^\/(?:team|binary)\/\d+\/credentials$/) &&
            request.method === "GET"
        ) {
            const decryptLaravelCredential = async (payload) => {
                if (
                    !payload ||
                    typeof payload !== "string" ||
                    !payload.startsWith("eyJ")
                )
                    return payload;
                try {
                    const json = JSON.parse(atob(payload));
                    const appKey =
                        "jbGgydtFYDKPLRpynPVv4O4XgYQNxvMTVDzoSBWrbMY=";
                    const rawKey = Uint8Array.from(atob(appKey), (c) =>
                        c.charCodeAt(0),
                    );
                    const iv = Uint8Array.from(atob(json.iv), (c) =>
                        c.charCodeAt(0),
                    );
                    const ciphertext = Uint8Array.from(atob(json.value), (c) =>
                        c.charCodeAt(0),
                    );
                    const key = await crypto.subtle.importKey(
                        "raw",
                        rawKey,
                        { name: "AES-CBC" },
                        false,
                        ["decrypt"],
                    );
                    const decrypted = await crypto.subtle.decrypt(
                        { name: "AES-CBC", iv },
                        key,
                        ciphertext,
                    );
                    const decStr = new TextDecoder().decode(decrypted);
                    const match = decStr.match(/^s:\d+:"(.*)";$/s);
                    return match ? match[1] : decStr;
                } catch (e) {
                    return payload;
                }
            };

            const parts = path.split("/");
            const nodeId = parseInt(parts[2], 10);
            if (db && nodeId) {
                try {
                    const row = await db
                        .prepare(
                            "SELECT password_plain, tpin FROM binary_nodes WHERE id = ?",
                        )
                        .bind(nodeId)
                        .first();
                    if (row) {
                        const plainPassword =
                            (await decryptLaravelCredential(
                                row.password_plain,
                            )) || "sbl123456";
                        const plainTpin =
                            (await decryptLaravelCredential(row.tpin)) ||
                            "1234";
                        return Response.json(
                            {
                                password_plain: plainPassword,
                                tpin: plainTpin,
                            },
                            {
                                headers: {
                                    "Content-Type": "application/json",
                                    "Cache-Control": "no-store, private",
                                },
                            },
                        );
                    }
                } catch (e) {
                    console.error("D1 credentials error:", e);
                }
            }
            return Response.json({
                password_plain: "sbl123456",
                tpin: "1234",
            });
        }

        await ensureD1Schema(db);

        // Authenticate Session Cookie early so all POST/PUT/DELETE handlers have the authenticated user ID
        const sessionCookie = getCookie(
            "sbl_session",
            request.headers.get("Cookie") || "",
        );
        const authUserId = await verifySession(sessionCookie, APP_SECRET);
        const currentUserId = authUserId ? Number(authUserId) : 1;

        // 4. Handle POST, PUT, PATCH, DELETE Form Actions on Cloudflare D1
        if (
            request.method === "POST" ||
            request.method === "PUT" ||
            request.method === "PATCH" ||
            request.method === "DELETE" ||
            path.startsWith("/currency")
        ) {
            let effectiveMethod = request.method;
            let formData = null;
            const contentType = request.headers.get("content-type") || "";

            if (
                contentType.includes("form") ||
                contentType.includes("multipart") ||
                contentType.includes("urlencoded")
            ) {
                try {
                    formData = await request.formData();
                    const spoofed = formData.get("_method");
                    if (spoofed) {
                        effectiveMethod = spoofed.toUpperCase();
                    }
                } catch (e) {
                    console.error("Error parsing formData:", e);
                }
            } else if (contentType.includes("json")) {
                try {
                    const jsonBody = await request.clone().json();
                    if (jsonBody && typeof jsonBody === "object") {
                        formData = {
                            _map: jsonBody,
                            get(k) {
                                return this._map[k] !== undefined &&
                                    this._map[k] !== null
                                    ? String(this._map[k])
                                    : null;
                            },
                            has(k) {
                                return (
                                    this._map[k] !== undefined &&
                                    this._map[k] !== null
                                );
                            },
                        };
                        if (jsonBody._method) {
                            effectiveMethod = String(
                                jsonBody._method,
                            ).toUpperCase();
                        }
                    }
                } catch (e) {}
            }

            // Currency Switch Handler
            if (path === "/currency/switch" || path.startsWith("/currency/")) {
                let curr = "USD";
                if (formData && formData.get("currency")) {
                    curr = formData.get("currency").toUpperCase();
                } else if (contentType.includes("json")) {
                    try {
                        const jsonBody = await request.json();
                        if (jsonBody && jsonBody.currency) {
                            curr = jsonBody.currency.toUpperCase();
                        }
                    } catch (e) {}
                } else {
                    const parts = path.split("/");
                    if (parts[2]) curr = parts[2].toUpperCase();
                }
                if (curr !== "USD" && curr !== "BDT") curr = "USD";

                const cookieHeader = `sbl_currency=${curr}; Path=/; Max-Age=31536000; SameSite=Lax`;
                if (
                    contentType.includes("json") ||
                    request.headers.get("accept")?.includes("json")
                ) {
                    return new Response(
                        JSON.stringify({
                            success: true,
                            currency: curr,
                            symbol: curr === "BDT" ? "৳" : "$",
                            rate: curr === "BDT" ? 120 : 1,
                        }),
                        {
                            headers: {
                                "Content-Type": "application/json",
                                "Set-Cookie": cookieHeader,
                            },
                        },
                    );
                }
                return new Response(null, {
                    status: 302,
                    headers: {
                        Location: request.headers.get("Referer") || "/",
                        "Set-Cookie": cookieHeader,
                    },
                });
            }

            // 4a. Leads Handlers
            if (path === "/leads" && effectiveMethod === "POST" && formData) {
                let newLeadId = null;
                let insertError = null;

                if (db) {
                    try {
                        const name =
                            (formData.get("name") || "").trim() ||
                            "Unnamed Lead";
                        const mobile = (formData.get("mobile") || "").trim();
                        const whatsapp =
                            (formData.get("whatsapp") || "").trim() || null;
                        const email =
                            (formData.get("email") || "").trim() || null;
                        const location =
                            (formData.get("location") || "").trim() || null;
                        const profession =
                            (
                                formData.get("profession_or_business") || ""
                            ).trim() || null;
                        const rawSourceId =
                            Number(formData.get("lead_source_id")) || 1;
                        const stage = formData.get("stage") || "new";
                        const interests =
                            formData.getAll("interest_types[]") || [];
                        const interestsJson = JSON.stringify(interests);
                        const nextActionType =
                            formData.get("next_action_type") || null;
                        const nextActionAt =
                            formData.get("next_action_at") || null;
                        const notes = formData.get("notes") || null;
                        const score =
                            (interests.length > 0 ? 20 : 0) +
                            (nextActionAt ? 15 : 0);
                        const temperature =
                            score >= 50 ? "hot" : score >= 25 ? "warm" : "cold";

                        // Debounce duplicate submissions within 15 seconds to prevent double-click duplicates
                        try {
                            const recentDup = await db
                                .prepare(
                                    "SELECT id FROM leads WHERE name = ? AND mobile = ? AND datetime(created_at) >= datetime('now', '-15 seconds') LIMIT 1",
                                )
                                .bind(name, mobile)
                                .first();
                            if (recentDup && recentDup.id) {
                                console.warn(
                                    `Debounced duplicate lead submission for ${name} (${mobile}), reusing existing ID #${recentDup.id}`,
                                );
                                const wantsJson =
                                    request.headers
                                        .get("accept")
                                        ?.includes("json") ||
                                    contentType.includes("json");
                                if (wantsJson) {
                                    return Response.json({
                                        success: true,
                                        lead_id: recentDup.id,
                                        message: "Lead already created",
                                        debounced: true,
                                    });
                                }
                                return Response.redirect(
                                    new URL(
                                        "/leads?saved=1&lead_id=" +
                                            recentDup.id,
                                        request.url,
                                    ),
                                    302,
                                );
                            }
                        } catch (eDup) {}

                        // Verify owner user ID exists in DB to prevent foreign key errors
                        let safeOwnerId = currentUserId || 1;
                        try {
                            const userCheck = await db
                                .prepare("SELECT id FROM users WHERE id = ?")
                                .bind(safeOwnerId)
                                .first();
                            if (!userCheck) {
                                const firstUser = await db
                                    .prepare(
                                        "SELECT id FROM users ORDER BY id ASC LIMIT 1",
                                    )
                                    .first();
                                safeOwnerId = firstUser
                                    ? Number(firstUser.id)
                                    : 1;
                            }
                        } catch (e) {
                            safeOwnerId = 1;
                        }

                        // Verify source ID exists in DB to prevent foreign key errors
                        let safeSourceId = rawSourceId;
                        try {
                            const srcCheck = await db
                                .prepare(
                                    "SELECT id FROM lead_sources WHERE id = ?",
                                )
                                .bind(safeSourceId)
                                .first();
                            if (!srcCheck) {
                                const firstSrc = await db
                                    .prepare(
                                        "SELECT id FROM lead_sources ORDER BY id ASC LIMIT 1",
                                    )
                                    .first();
                                safeSourceId = firstSrc
                                    ? Number(firstSrc.id)
                                    : 1;
                            }
                        } catch (e) {
                            safeSourceId = 1;
                        }

                        // Safe Photo handling: limit size to prevent D1 row/statement limits
                        let photo = formData.get("photo") || null;
                        if (
                            photo &&
                            (typeof photo !== "string" || photo.length > 60000)
                        ) {
                            console.warn(
                                "Photo payload exceeds 60KB, omitting photo to guarantee lead save",
                            );
                            photo = null;
                        }

                        // TIER 1: Full insert with all columns and photo
                        try {
                            const insRes = await db
                                .prepare(
                                    "INSERT INTO leads (name, mobile, whatsapp, email, photo, location, profession_or_business, lead_source_id, interest_types, stage, temperature, score, is_manual_score, owner_user_id, next_action_type, next_action_at, last_contact_at, notes, created_at, updated_at) " +
                                        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                )
                                .bind(
                                    name,
                                    mobile,
                                    whatsapp,
                                    email,
                                    photo,
                                    location,
                                    profession,
                                    safeSourceId,
                                    interestsJson,
                                    stage,
                                    temperature,
                                    score,
                                    safeOwnerId,
                                    nextActionType,
                                    nextActionAt,
                                    notes,
                                )
                                .run();
                            newLeadId = insRes?.meta?.last_row_id;
                        } catch (e1) {
                            console.error(
                                "D1 Leads Tier 1 insert error, attempting Tier 2 safe fallback:",
                                e1,
                            );
                            insertError = e1;

                            // TIER 2: Safe Fallback insert (omitting photo & secondary columns)
                            try {
                                const fallbackRes = await db
                                    .prepare(
                                        "INSERT INTO leads (name, mobile, whatsapp, email, location, profession_or_business, lead_source_id, interest_types, stage, temperature, score, is_manual_score, owner_user_id, notes, created_at, updated_at) " +
                                            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        name,
                                        mobile,
                                        whatsapp,
                                        email,
                                        location,
                                        profession,
                                        safeSourceId,
                                        interestsJson,
                                        stage,
                                        temperature,
                                        score,
                                        safeOwnerId,
                                        notes,
                                    )
                                    .run();
                                newLeadId = fallbackRes?.meta?.last_row_id;
                                insertError = null; // Successfully rescued and saved!
                            } catch (e2) {
                                console.error(
                                    "D1 Leads Tier 2 fallback insert error:",
                                    e2,
                                );
                                insertError = e2;
                            }
                        }

                        // Isolated Secondary Operations (Tasks & Activities)
                        if (newLeadId) {
                            if (nextActionAt) {
                                try {
                                    await db
                                        .prepare(
                                            "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                                                "VALUES (?, ?, ?, 'Medium', ?, ?, ?, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                        )
                                        .bind(
                                            nextActionType
                                                ? nextActionType + ": " + name
                                                : "Follow-up: " + name,
                                            nextActionType || "Follow-up",
                                            nextActionAt,
                                            notes,
                                            newLeadId,
                                            safeOwnerId,
                                        )
                                        .run();
                                } catch (taskErr) {
                                    console.error(
                                        "Non-fatal secondary task insert error:",
                                        taskErr,
                                    );
                                }
                            }

                            try {
                                await db
                                    .prepare(
                                        "INSERT INTO activities (lead_id, user_id, type, title, description, performed_at, created_at, updated_at) " +
                                            "VALUES (?, ?, 'lead_created', 'New Lead Added', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        newLeadId,
                                        safeOwnerId,
                                        "Lead created: " + name,
                                    )
                                    .run();
                            } catch (actErr) {
                                console.error(
                                    "Non-fatal secondary activity insert error:",
                                    actErr,
                                );
                            }
                        }
                    } catch (e) {
                        console.error("D1 Leads create processing error:", e);
                        insertError = e;
                    }
                }

                const wantsJson =
                    request.headers.get("accept")?.includes("json") ||
                    contentType.includes("json");
                if (wantsJson) {
                    if (newLeadId) {
                        return Response.json({
                            success: true,
                            lead_id: newLeadId,
                            message: "Lead created successfully",
                        });
                    } else {
                        return Response.json(
                            {
                                success: false,
                                error: insertError
                                    ? insertError.message
                                    : "Failed to create lead",
                            },
                            { status: 422 },
                        );
                    }
                }

                if (newLeadId) {
                    return Response.redirect(
                        new URL(
                            "/leads?saved=1&lead_id=" + newLeadId,
                            request.url,
                        ),
                        302,
                    );
                } else if (insertError) {
                    // Fail-safe: Redirect back to create with explicit error message so draft is restored
                    return Response.redirect(
                        new URL(
                            "/leads/create?error=" +
                                encodeURIComponent(
                                    insertError.message ||
                                        "Database insert error",
                                ),
                            request.url,
                        ),
                        302,
                    );
                } else {
                    return Response.redirect(
                        new URL("/leads", request.url),
                        302,
                    );
                }
            }

            if (path.startsWith("/leads/")) {
                const parts = path.split("/");
                const leadId = parseInt(parts[2], 10);

                if (effectiveMethod === "DELETE" && leadId) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "UPDATE leads SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(leadId)
                                .run();
                        } catch (e) {
                            console.error("D1 Leads delete error:", e);
                        }
                    }
                    const referer = request.headers.get("Referer");
                    let dest = "/leads?deleted_lead=" + leadId;
                    if (referer) {
                        try {
                            const refUrl = new URL(referer);
                            if (refUrl.pathname === "/leads") {
                                refUrl.searchParams.set(
                                    "deleted_lead",
                                    String(leadId),
                                );
                                dest = refUrl.pathname + refUrl.search;
                            }
                        } catch (e) {}
                    }
                    return Response.redirect(new URL(dest, request.url), 302);
                }

                if (effectiveMethod === "PUT" && leadId && formData) {
                    if (db) {
                        try {
                            const name = formData.get("name") || "Unnamed Lead";
                            const mobile = formData.get("mobile") || "";
                            const whatsapp = formData.get("whatsapp") || null;
                            const email = formData.get("email") || null;
                            const location = formData.get("location") || null;
                            const profession =
                                formData.get("profession_or_business") || null;
                            const sourceId =
                                Number(formData.get("lead_source_id")) || 1;
                            const stage = formData.get("stage") || "new";
                            const interests =
                                formData.getAll("interest_types[]") || [];
                            const interestsJson = JSON.stringify(interests);
                            const notes = formData.get("notes") || null;
                            const score = formData.get("score")
                                ? Number(formData.get("score"))
                                : 30;

                            const photo =
                                formData.get("photo") !== null
                                    ? formData.get("photo") || null
                                    : undefined;
                            if (photo !== undefined) {
                                await db
                                    .prepare(
                                        "UPDATE leads SET name = ?, mobile = ?, whatsapp = ?, email = ?, photo = ?, location = ?, profession_or_business = ?, lead_source_id = ?, interest_types = ?, stage = ?, notes = ?, score = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(
                                        name,
                                        mobile,
                                        whatsapp,
                                        email,
                                        photo,
                                        location,
                                        profession,
                                        sourceId,
                                        interestsJson,
                                        stage,
                                        notes,
                                        score,
                                        leadId,
                                    )
                                    .run();
                            } else {
                                await db
                                    .prepare(
                                        "UPDATE leads SET name = ?, mobile = ?, whatsapp = ?, email = ?, location = ?, profession_or_business = ?, lead_source_id = ?, interest_types = ?, stage = ?, notes = ?, score = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(
                                        name,
                                        mobile,
                                        whatsapp,
                                        email,
                                        location,
                                        profession,
                                        sourceId,
                                        interestsJson,
                                        stage,
                                        notes,
                                        score,
                                        leadId,
                                    )
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Leads update error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/leads/" + leadId, request.url),
                        302,
                    );
                }

                if (
                    parts[3] === "stage" &&
                    effectiveMethod === "POST" &&
                    leadId &&
                    formData
                ) {
                    if (db) {
                        try {
                            const stage = formData.get("stage") || "new";
                            await db
                                .prepare(
                                    "UPDATE leads SET stage = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(stage, leadId)
                                .run();
                        } catch (e) {
                            console.error("D1 Leads stage update error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/leads", request.url),
                        302,
                    );
                }

                if (
                    parts[3] === "convert" &&
                    effectiveMethod === "POST" &&
                    leadId
                ) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "UPDATE leads SET stage = 'converted', converted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(leadId)
                                .run();
                        } catch (e) {
                            console.error("D1 Leads convert error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/leads/" + leadId, request.url),
                        302,
                    );
                }

                if (
                    parts[3] === "activities" &&
                    effectiveMethod === "POST" &&
                    leadId &&
                    formData
                ) {
                    if (db) {
                        try {
                            const actType = formData.get("type") || "Call";
                            const title =
                                formData.get("title") ||
                                actType + " Interaction";
                            const description =
                                formData.get("description") || null;
                            const nextActionType =
                                formData.get("next_action_type") || null;
                            const nextActionAt =
                                formData.get("next_action_at") || null;

                            await db
                                .prepare(
                                    "INSERT INTO activities (lead_id, user_id, type, title, description, performed_at, created_at, updated_at) " +
                                        "VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                )
                                .bind(
                                    leadId,
                                    currentUserId,
                                    actType,
                                    title,
                                    description,
                                )
                                .run();

                            await db
                                .prepare(
                                    "UPDATE leads SET last_contact_at = CURRENT_TIMESTAMP, next_action_type = ?, next_action_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(nextActionType, nextActionAt, leadId)
                                .run();

                            if (nextActionAt) {
                                await db
                                    .prepare(
                                        "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                                            "VALUES (?, ?, ?, 'High', ?, ?, ?, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        (nextActionType || "Follow-up") +
                                            ": " +
                                            title,
                                        nextActionType || "Follow-up",
                                        nextActionAt,
                                        description,
                                        leadId,
                                        currentUserId,
                                    )
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Lead activities error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/leads/" + leadId, request.url),
                        302,
                    );
                }
            }

            // 4b. SBL Team Explorer & Placement Handlers
            if (
                (path === "/binary" ||
                    path === "/binary/place" ||
                    path === "/team" ||
                    path === "/team/place") &&
                effectiveMethod === "POST" &&
                formData
            ) {
                if (db) {
                    try {
                        const memberName =
                            formData.get("member_name") || "New Member";
                        const phone = formData.get("phone") || null;
                        const email = formData.get("email") || null;
                        const passwordPlain =
                            formData.get("password_plain") ||
                            formData.get("password") ||
                            "sbl123456";
                        const tpin = formData.get("tpin") || "1234";
                        const packageName =
                            formData.get("package_name") || "National 120k";
                        const rankName = formData.get("rank_name") || "Member";
                        const parentId = Number(formData.get("parent_id")) || 1;
                        const branch = (
                            formData.get("branch") ||
                            formData.get("position") ||
                            "LEFT"
                        ).toUpperCase();
                        const position = branch.toLowerCase();
                        const slotNumber =
                            Number(formData.get("slot_number")) || 1;
                        let pointValue =
                            Number(formData.get("point_value")) || 100;
                        const leftTargetCount =
                            Number(formData.get("left_target_count")) || 5;
                        const rightTargetCount =
                            Number(formData.get("right_target_count")) || 5;
                        const isTarget = formData.get("is_target") ? 1 : 0;
                        const targetDate = formData.get("target_date") || null;
                        const targetNotes =
                            formData.get("target_notes") || null;
                        let contributions =
                            formData.get("contributions") || "[]";
                        if (typeof contributions !== "string") {
                            contributions = JSON.stringify(contributions);
                        }
                        const sponsorName =
                            formData.get("sponsor_name") || null;
                        const sponsorId = formData.get("sponsor_id")
                            ? Number(formData.get("sponsor_id"))
                            : null;
                        const userId = formData.get("user_id")
                            ? Number(formData.get("user_id"))
                            : null;
                        const memberCode =
                            formData.get("member_code") ||
                            "SBL-" + Math.floor(1000 + Math.random() * 9000);

                        await db
                            .prepare(
                                "INSERT INTO binary_nodes (member_name, member_code, phone, email, password_plain, tpin, package_name, rank_name, parent_id, sponsor_id, sponsor_name, user_id, branch, slot_number, position, point_value, left_target_count, right_target_count, contributions, is_active, is_target, target_date, target_notes, left_count, right_count, left_bv, right_bv, carry_left, carry_right, matched_pairs, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                memberName,
                                memberCode,
                                phone,
                                email,
                                passwordPlain,
                                tpin,
                                packageName,
                                rankName,
                                parentId,
                                sponsorId,
                                sponsorName,
                                userId,
                                branch,
                                slotNumber,
                                position,
                                pointValue,
                                leftTargetCount,
                                rightTargetCount,
                                contributions,
                                isTarget,
                                targetDate,
                                targetNotes,
                            )
                            .run();

                        if (position === "left") {
                            await db
                                .prepare(
                                    "UPDATE binary_nodes SET left_count = left_count + 1, left_bv = left_bv + ?, carry_left = carry_left + ? WHERE id = ?",
                                )
                                .bind(pointValue, pointValue, parentId)
                                .run();
                        } else if (position === "right") {
                            await db
                                .prepare(
                                    "UPDATE binary_nodes SET right_count = right_count + 1, right_bv = right_bv + ?, carry_right = carry_right + ? WHERE id = ?",
                                )
                                .bind(pointValue, pointValue, parentId)
                                .run();
                        }
                    } catch (e) {
                        console.error("D1 Binary create error:", e);
                    }
                }
                const ref = request.headers.get("referer");
                if (ref)
                    return Response.redirect(new URL(ref, request.url), 302);
                return Response.redirect(new URL("/team", request.url), 302);
            }

            // Convert Target to Active handler
            if (
                (path.startsWith("/binary/") || path.startsWith("/team/")) &&
                path.endsWith("/convert-target") &&
                effectiveMethod === "POST"
            ) {
                const parts = path.split("/");
                const nodeId = parseInt(parts[2], 10);
                if (nodeId && db) {
                    try {
                        await db
                            .prepare(
                                "UPDATE binary_nodes SET is_target = 0, is_active = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                            )
                            .bind(nodeId)
                            .run();
                    } catch (e) {
                        console.error("D1 Binary convert-target error:", e);
                    }
                }
                const ref = request.headers.get("referer");
                if (ref)
                    return Response.redirect(new URL(ref, request.url), 302);
                return Response.redirect(new URL("/team", request.url), 302);
            }

            if (path.startsWith("/binary/") || path.startsWith("/team/")) {
                const parts = path.split("/");
                const nodeId = parseInt(parts[2], 10);
                if (effectiveMethod === "DELETE" && nodeId) {
                    if (db) {
                        try {
                            const isCascade = formData
                                ? formData.get("cascade") === "1" ||
                                  formData.get("force") === "1"
                                : true;

                            async function getDescendantIds(parentId) {
                                const direct = await db
                                    .prepare(
                                        "SELECT id FROM binary_nodes WHERE parent_id = ?",
                                    )
                                    .bind(parentId)
                                    .all();
                                let ids = [];
                                if (direct && direct.results) {
                                    for (const row of direct.results) {
                                        ids.push(row.id);
                                        const subIds = await getDescendantIds(
                                            row.id,
                                        );
                                        ids = ids.concat(subIds);
                                    }
                                }
                                return ids;
                            }

                            const check = await db
                                .prepare(
                                    "SELECT count(*) as count FROM binary_nodes WHERE parent_id = ?",
                                )
                                .bind(nodeId)
                                .first();
                            const hasChildren = check && check.count > 0;

                            if (!hasChildren || isCascade) {
                                if (hasChildren) {
                                    const descIds =
                                        await getDescendantIds(nodeId);
                                    for (const did of descIds) {
                                        try {
                                            await db
                                                .prepare(
                                                    "DELETE FROM investments WHERE binary_node_id = ?",
                                                )
                                                .bind(did)
                                                .run();
                                        } catch (_) {}
                                        await db
                                            .prepare(
                                                "DELETE FROM binary_nodes WHERE id = ?",
                                            )
                                            .bind(did)
                                            .run();
                                    }
                                }

                                const node = await db
                                    .prepare(
                                        "SELECT * FROM binary_nodes WHERE id = ?",
                                    )
                                    .bind(nodeId)
                                    .first();
                                if (node && node.parent_id) {
                                    const pv = Number(node.point_value) || 0;
                                    if (
                                        node.position === "left" ||
                                        node.branch === "LEFT"
                                    ) {
                                        await db
                                            .prepare(
                                                "UPDATE binary_nodes SET left_count = MAX(0, left_count - 1), left_bv = MAX(0, left_bv - ?), carry_left = MAX(0, carry_left - ?) WHERE id = ?",
                                            )
                                            .bind(pv, pv, node.parent_id)
                                            .run();
                                    } else if (
                                        node.position === "right" ||
                                        node.branch === "RIGHT"
                                    ) {
                                        await db
                                            .prepare(
                                                "UPDATE binary_nodes SET right_count = MAX(0, right_count - 1), right_bv = MAX(0, right_bv - ?), carry_right = MAX(0, carry_right - ?) WHERE id = ?",
                                            )
                                            .bind(pv, pv, node.parent_id)
                                            .run();
                                    }
                                }
                                try {
                                    await db
                                        .prepare(
                                            "DELETE FROM investments WHERE binary_node_id = ?",
                                        )
                                        .bind(nodeId)
                                        .run();
                                } catch (_) {}
                                await db
                                    .prepare(
                                        "DELETE FROM binary_nodes WHERE id = ?",
                                    )
                                    .bind(nodeId)
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Binary delete error:", e);
                        }
                    }
                    const ref = request.headers.get("referer");
                    if (ref)
                        return Response.redirect(
                            new URL(ref, request.url),
                            302,
                        );
                    return Response.redirect(
                        new URL("/team?deleted_node=" + nodeId, request.url),
                        302,
                    );
                }

                if (effectiveMethod === "PUT" && nodeId && formData) {
                    if (db) {
                        try {
                            const existing = await db
                                .prepare(
                                    "SELECT * FROM binary_nodes WHERE id = ?",
                                )
                                .bind(nodeId)
                                .first();

                            if (existing) {
                                const memberName =
                                    formData.get("member_name") ||
                                    existing.member_name ||
                                    "Unnamed Member";
                                const memberCode =
                                    formData.get("member_code") ||
                                    existing.member_code;
                                const phone = formData.has("phone")
                                    ? formData.get("phone")
                                    : existing.phone;
                                const email = formData.has("email")
                                    ? formData.get("email")
                                    : existing.email;
                                const passwordPlain =
                                    formData.get("password_plain") ||
                                    formData.get("password") ||
                                    existing.password_plain ||
                                    "sbl123456";
                                const tpin =
                                    formData.get("tpin") ||
                                    existing.tpin ||
                                    "1234";
                                const packageName =
                                    formData.get("package_name") ||
                                    existing.package_name ||
                                    "National 120k";
                                const rankName =
                                    formData.get("rank_name") ||
                                    existing.rank_name ||
                                    "Member";
                                const sponsorName = formData.has("sponsor_name")
                                    ? formData.get("sponsor_name")
                                    : existing.sponsor_name;
                                const sponsorId =
                                    formData.has("sponsor_id") &&
                                    formData.get("sponsor_id") !== ""
                                        ? Number(formData.get("sponsor_id"))
                                        : existing.sponsor_id;
                                const isTarget = formData.has("is_target")
                                    ? formData.get("is_target") === "1" ||
                                      formData.get("is_target") === "on"
                                        ? 1
                                        : 0
                                    : formData.has("member_name")
                                      ? 0
                                      : existing.is_target
                                        ? 1
                                        : 0;
                                const targetDate = formData.has("target_date")
                                    ? formData.get("target_date")
                                    : existing.target_date;
                                const targetNotes = formData.has("target_notes")
                                    ? formData.get("target_notes")
                                    : existing.target_notes;
                                const notes = formData.has("notes")
                                    ? formData.get("notes")
                                    : formData.has("target_notes")
                                      ? formData.get("target_notes")
                                      : existing.notes;

                                let contributions =
                                    formData.get("contributions");
                                let pointValue =
                                    Number(existing.point_value) || 0;
                                let contributionsArr = [];
                                if (contributions) {
                                    try {
                                        contributionsArr =
                                            typeof contributions === "string"
                                                ? JSON.parse(contributions)
                                                : contributions;
                                    } catch (e) {}
                                    if (
                                        Array.isArray(contributionsArr) &&
                                        contributionsArr.length > 0
                                    ) {
                                        pointValue = contributionsArr.reduce(
                                            (sum, c) =>
                                                sum + (Number(c.amount) || 0),
                                            0,
                                        );
                                        contributions =
                                            JSON.stringify(contributionsArr);
                                    }
                                } else {
                                    contributions =
                                        existing.contributions || "[]";
                                }

                                if (
                                    formData.has("point_value") &&
                                    formData.get("point_value") !== ""
                                ) {
                                    pointValue =
                                        Number(formData.get("point_value")) ||
                                        pointValue;
                                }

                                const userId =
                                    formData.has("user_id") &&
                                    formData.get("user_id") !== ""
                                        ? Number(formData.get("user_id"))
                                        : existing.user_id;
                                const isActive = formData.has("is_active")
                                    ? formData.get("is_active") === "1" ||
                                      formData.get("is_active") === "true"
                                        ? 1
                                        : 0
                                    : existing.is_active !== undefined
                                      ? existing.is_active
                                      : 1;

                                await db
                                    .prepare(
                                        "UPDATE binary_nodes SET member_name = ?, member_code = ?, phone = ?, email = ?, password_plain = ?, tpin = ?, package_name = ?, rank_name = ?, sponsor_id = ?, sponsor_name = ?, point_value = ?, contributions = ?, is_target = ?, target_date = ?, target_notes = ?, notes = ?, user_id = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(
                                        memberName,
                                        memberCode,
                                        phone,
                                        email,
                                        passwordPlain,
                                        tpin,
                                        packageName,
                                        rankName,
                                        sponsorId,
                                        sponsorName,
                                        pointValue,
                                        contributions,
                                        isTarget,
                                        targetDate,
                                        targetNotes,
                                        notes,
                                        userId,
                                        isActive,
                                        nodeId,
                                    )
                                    .run();

                                if (
                                    Array.isArray(contributionsArr) &&
                                    contributionsArr.length > 0
                                ) {
                                    try {
                                        await db
                                            .prepare(
                                                "DELETE FROM investments WHERE binary_node_id = ?",
                                            )
                                            .bind(nodeId)
                                            .run();
                                        for (const c of contributionsArr) {
                                            await db
                                                .prepare(
                                                    "INSERT INTO investments (binary_node_id, plan_name, amount, point_value, status, investment_date, note, created_at, updated_at) VALUES (?, ?, ?, ?, 'active', ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                                )
                                                .bind(
                                                    nodeId,
                                                    c.note ||
                                                        packageName ||
                                                        "Contribution",
                                                    Number(c.amount) || 0,
                                                    Number(c.amount) || 0,
                                                    c.date ||
                                                        new Date()
                                                            .toISOString()
                                                            .slice(0, 10),
                                                    c.note ||
                                                        "Contribution Record",
                                                )
                                                .run();
                                        }
                                    } catch (invErr) {
                                        console.error(
                                            "D1 investments sync error:",
                                            invErr,
                                        );
                                    }
                                }
                            }
                        } catch (e) {
                            console.error("D1 Binary update error:", e);
                        }
                    }

                    if (request.headers.get("accept")?.includes("json")) {
                        return Response.json({
                            success: true,
                            message: "Member updated successfully",
                        });
                    }

                    const ref = request.headers.get("referer");
                    if (ref)
                        return Response.redirect(
                            new URL(ref, request.url),
                            302,
                        );
                    return Response.redirect(
                        new URL("/team", request.url),
                        302,
                    );
                }

                // Member Notes update endpoint
                if (
                    path.match(/^\/(?:team|binary)\/\d+\/notes$/) &&
                    (effectiveMethod === "PATCH" ||
                        effectiveMethod === "POST" ||
                        effectiveMethod === "PUT")
                ) {
                    const parts = path.split("/");
                    const targetNodeId = parseInt(parts[2], 10);
                    let notesVal = "";
                    if (formData && formData.has("notes")) {
                        notesVal = formData.get("notes") || "";
                    } else {
                        try {
                            const body = await request.clone().json();
                            notesVal = body.notes || "";
                        } catch (e) {}
                    }
                    if (db && targetNodeId) {
                        try {
                            await db
                                .prepare(
                                    "UPDATE binary_nodes SET notes = ?, target_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(notesVal, notesVal, targetNodeId)
                                .run();
                        } catch (e) {
                            console.error("D1 member note update error:", e);
                        }
                    }
                    return Response.json({ success: true, notes: notesVal });
                }
            }

            // 4c. Team & Users Handlers (Auth Users - Mobile + Password)
            if (path === "/users" && effectiveMethod === "POST" && formData) {
                if (db) {
                    try {
                        const name = (formData.get("name") || "New User")
                            .toString()
                            .trim();
                        const phone = (formData.get("phone") || "")
                            .toString()
                            .trim();
                        const rawPass = (formData.get("password") || "")
                            .toString()
                            .trim();
                        let email = (formData.get("email") || "")
                            .toString()
                            .trim();
                        const designation = (formData.get("designation") || "")
                            .toString()
                            .trim();
                        const roleId = Number(formData.get("role_id")) || 2;
                        const status = (formData.get("status") || "active")
                            .toString()
                            .trim();

                        if (!email) {
                            const cleanP = phone.replace(/[^0-9]/g, "");
                            email =
                                (cleanP || "user_" + Date.now()) + "@sbl.test";
                        }

                        const passwordHash = rawPass
                            ? bcrypt
                                  .hashSync(rawPass, 10)
                                  .replace(/^\$2a\$/, "$2y$")
                            : bcrypt
                                  .hashSync("password", 10)
                                  .replace(/^\$2a\$/, "$2y$");

                        const insRes = await db
                            .prepare(
                                "INSERT INTO users (name, email, password, phone, designation, status, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                name,
                                email,
                                passwordHash,
                                phone,
                                designation,
                                status,
                            )
                            .run();

                        const newUserId = insRes?.meta?.last_row_id;
                        if (newUserId && roleId) {
                            await db
                                .prepare(
                                    "INSERT INTO role_user (user_id, role_id) VALUES (?, ?)",
                                )
                                .bind(newUserId, roleId)
                                .run();
                        }
                    } catch (e) {
                        console.error("D1 User create error:", e);
                    }
                }
                return Response.redirect(new URL("/users", request.url), 302);
            }

            if (path.startsWith("/users/")) {
                const parts = path.split("/");
                const userId = parseInt(parts[2], 10);

                if (effectiveMethod === "DELETE" && userId) {
                    if (db) {
                        try {
                            if (userId > 1) {
                                // Never delete root admin
                                await db
                                    .prepare(
                                        "DELETE FROM role_user WHERE user_id = ?",
                                    )
                                    .bind(userId)
                                    .run();
                                await db
                                    .prepare("DELETE FROM users WHERE id = ?")
                                    .bind(userId)
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 User delete error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/users?deleted_user=" + userId, request.url),
                        302,
                    );
                }

                if (effectiveMethod === "PUT" && userId && formData) {
                    if (db) {
                        try {
                            const name = (formData.get("name") || "")
                                .toString()
                                .trim();
                            let email = (formData.get("email") || "")
                                .toString()
                                .trim();
                            const phone = (formData.get("phone") || "")
                                .toString()
                                .trim();
                            const designation = (
                                formData.get("designation") || ""
                            )
                                .toString()
                                .trim();
                            const roleId = Number(formData.get("role_id")) || 2;
                            const status = (formData.get("status") || "active")
                                .toString()
                                .trim();
                            const newPass = (formData.get("password") || "")
                                .toString()
                                .trim();

                            if (!email) {
                                const cleanP = phone.replace(/[^0-9]/g, "");
                                email =
                                    (cleanP || "user_" + userId) + "@sbl.test";
                            }

                            if (newPass) {
                                const passwordHash = bcrypt
                                    .hashSync(newPass, 10)
                                    .replace(/^\$2a\$/, "$2y$");
                                await db
                                    .prepare(
                                        "UPDATE users SET name = ?, email = ?, phone = ?, password = ?, designation = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(
                                        name,
                                        email,
                                        phone,
                                        passwordHash,
                                        designation,
                                        status,
                                        userId,
                                    )
                                    .run();
                            } else {
                                await db
                                    .prepare(
                                        "UPDATE users SET name = ?, email = ?, phone = ?, designation = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(
                                        name,
                                        email,
                                        phone,
                                        designation,
                                        status,
                                        userId,
                                    )
                                    .run();
                            }

                            if (roleId) {
                                await db
                                    .prepare(
                                        "DELETE FROM role_user WHERE user_id = ?",
                                    )
                                    .bind(userId)
                                    .run();
                                await db
                                    .prepare(
                                        "INSERT INTO role_user (user_id, role_id) VALUES (?, ?)",
                                    )
                                    .bind(userId, roleId)
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 User update error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/users", request.url),
                        302,
                    );
                }
            }

            // 4d. Contacts Handlers
            if (
                path === "/contacts" &&
                effectiveMethod === "POST" &&
                formData
            ) {
                if (db) {
                    try {
                        const department =
                            formData.get("department") || "General Support";
                        const contactPerson =
                            formData.get("contact_person") || null;
                        const phone = formData.get("phone") || "";
                        const whatsapp = formData.get("whatsapp") || null;
                        const email = formData.get("email") || null;
                        const availableHours =
                            formData.get("available_hours") ||
                            "10:00 AM - 08:00 PM";
                        const description = formData.get("description") || null;
                        const icon = formData.get("icon") || "📞";
                        const badge = formData.get("badge") || null;
                        const isPrimary = formData.has("is_primary") ? 1 : 0;

                        await db
                            .prepare(
                                "INSERT INTO sbl_contacts (department, contact_person, phone, whatsapp, email, available_hours, description, icon, badge, is_primary, sort_order, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                department,
                                contactPerson,
                                phone,
                                whatsapp,
                                email,
                                availableHours,
                                description,
                                icon,
                                badge,
                                isPrimary,
                            )
                            .run();
                    } catch (e) {
                        console.error("D1 Contacts create error:", e);
                    }
                }
                return Response.redirect(
                    new URL("/contacts", request.url),
                    302,
                );
            }

            if (path.startsWith("/contacts/")) {
                const parts = path.split("/");
                const contactId = parseInt(parts[2], 10);
                if (effectiveMethod === "DELETE" && contactId) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "DELETE FROM sbl_contacts WHERE id = ?",
                                )
                                .bind(contactId)
                                .run();
                        } catch (e) {
                            console.error("D1 Contacts delete error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL(
                            "/contacts?deleted_contact=" + contactId,
                            request.url,
                        ),
                        302,
                    );
                }

                if (effectiveMethod === "PUT" && contactId && formData) {
                    if (db) {
                        try {
                            const department =
                                formData.get("department") || "General Support";
                            const contactPerson =
                                formData.get("contact_person") || null;
                            const phone = formData.get("phone") || "";
                            const whatsapp = formData.get("whatsapp") || null;
                            const email = formData.get("email") || null;
                            const availableHours =
                                formData.get("available_hours") ||
                                "10:00 AM - 08:00 PM";
                            const description =
                                formData.get("description") || null;
                            const icon = formData.get("icon") || "📞";
                            const badge = formData.get("badge") || null;
                            const isPrimary = formData.has("is_primary")
                                ? 1
                                : 0;

                            await db
                                .prepare(
                                    "UPDATE sbl_contacts SET department = ?, contact_person = ?, phone = ?, whatsapp = ?, email = ?, available_hours = ?, description = ?, icon = ?, badge = ?, is_primary = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(
                                    department,
                                    contactPerson,
                                    phone,
                                    whatsapp,
                                    email,
                                    availableHours,
                                    description,
                                    icon,
                                    badge,
                                    isPrimary,
                                    contactId,
                                )
                                .run();
                        } catch (e) {
                            console.error("D1 Contacts update error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/contacts", request.url),
                        302,
                    );
                }
            }

            // 4e. Tasks Handlers
            if (path === "/tasks" && effectiveMethod === "POST" && formData) {
                if (db) {
                    try {
                        const title = formData.get("title") || "New Task";
                        const type = formData.get("type") || "Follow-up";
                        const dueAt =
                            formData.get("due_at") || new Date().toISOString();
                        const priority = formData.get("priority") || "Medium";
                        const notes = formData.get("notes") || null;
                        const leadId = formData.get("related_lead_id")
                            ? Number(formData.get("related_lead_id"))
                            : null;

                        await db
                            .prepare(
                                "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                title,
                                type,
                                dueAt,
                                priority,
                                notes,
                                leadId,
                                currentUserId,
                            )
                            .run();

                        if (leadId) {
                            await db
                                .prepare(
                                    "UPDATE leads SET next_action_type = ?, next_action_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(type, dueAt, leadId)
                                .run();
                        }
                    } catch (e) {
                        console.error("D1 Tasks create error:", e);
                    }
                }
                const ref = request.headers.get("referer") || "";
                const m = ref.match(/\/leads\/(\d+)/);
                if (m)
                    return Response.redirect(
                        new URL("/leads/" + m[1], request.url),
                        302,
                    );
                return Response.redirect(new URL("/tasks", request.url), 302);
            }

            if (path.startsWith("/tasks/")) {
                const parts = path.split("/");
                const taskId = parseInt(parts[2], 10);
                if (
                    parts[3] === "complete" &&
                    effectiveMethod === "POST" &&
                    taskId
                ) {
                    if (db) {
                        try {
                            const outcome = formData
                                ? formData.get("outcome") || "Completed"
                                : "Completed";
                            const nextAction = formData
                                ? formData.get("next_action")
                                : null;
                            const nextActionAt = formData
                                ? formData.get("next_action_at")
                                : null;

                            await db
                                .prepare(
                                    "UPDATE tasks SET status = 'Completed', outcome = ?, next_action = ?, next_action_at = ?, completed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(outcome, nextAction, nextActionAt, taskId)
                                .run();

                            const taskRec = await db
                                .prepare("SELECT * FROM tasks WHERE id = ?")
                                .bind(taskId)
                                .first();
                            if (taskRec && taskRec.related_lead_id) {
                                const leadId = taskRec.related_lead_id;
                                await db
                                    .prepare(
                                        "UPDATE leads SET last_contact_at = CURRENT_TIMESTAMP, next_action_type = ?, next_action_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(
                                        nextAction || null,
                                        nextActionAt || null,
                                        leadId,
                                    )
                                    .run();

                                if (nextActionAt) {
                                    await db
                                        .prepare(
                                            "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                                                "VALUES (?, 'Follow-up', ?, 'High', ?, ?, ?, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                        )
                                        .bind(
                                            nextAction || "Follow-up",
                                            nextActionAt,
                                            "Generated from outcome: " +
                                                outcome,
                                            leadId,
                                            currentUserId,
                                        )
                                        .run();
                                }

                                await db
                                    .prepare(
                                        "INSERT INTO activities (lead_id, user_id, type, title, description, performed_at, created_at, updated_at) " +
                                            "VALUES (?, ?, 'task_completed', ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        leadId,
                                        currentUserId,
                                        "Task Completed: " +
                                            (taskRec.title || "Follow-up"),
                                        "Outcome: " +
                                            outcome +
                                            (nextAction
                                                ? " | Next: " + nextAction
                                                : ""),
                                    )
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Tasks complete error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/tasks", request.url),
                        302,
                    );
                }
                if (effectiveMethod === "PUT" && taskId && formData) {
                    if (db) {
                        try {
                            const title = formData.get("title") || "Task";
                            const type = formData.get("type") || "Follow-up";
                            const dueAt =
                                formData.get("due_at") ||
                                new Date().toISOString();
                            const priority =
                                formData.get("priority") || "Medium";
                            const notes = formData.get("notes") || null;
                            const leadId = formData.get("related_lead_id")
                                ? Number(formData.get("related_lead_id"))
                                : null;

                            await db
                                .prepare(
                                    "UPDATE tasks SET title = ?, type = ?, due_at = ?, priority = ?, notes = ?, related_lead_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(
                                    title,
                                    type,
                                    dueAt,
                                    priority,
                                    notes,
                                    leadId,
                                    taskId,
                                )
                                .run();

                            if (leadId) {
                                await db
                                    .prepare(
                                        "UPDATE leads SET next_action_type = ?, next_action_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                    )
                                    .bind(type, dueAt, leadId)
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Tasks update error:", e);
                        }
                    }
                    const ref = request.headers.get("referer") || "";
                    const m = ref.match(/\/leads\/(\d+)/);
                    if (m)
                        return Response.redirect(
                            new URL("/leads/" + m[1], request.url),
                            302,
                        );
                    return Response.redirect(
                        new URL("/tasks", request.url),
                        302,
                    );
                }
                if (effectiveMethod === "DELETE" && taskId) {
                    if (db) {
                        try {
                            await db
                                .prepare("DELETE FROM tasks WHERE id = ?")
                                .bind(taskId)
                                .run();
                        } catch (e) {
                            console.error("D1 Tasks delete error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/tasks", request.url),
                        302,
                    );
                }
            }

            // 4f. Ecosystem Handlers
            if (
                path === "/ecosystem" &&
                effectiveMethod === "POST" &&
                formData
            ) {
                if (db) {
                    try {
                        const title = formData.get("title") || "New Portal";
                        const urlVal = formData.get("url") || "#";
                        const category =
                            formData.get("category") || "Official Portals";
                        const badge = formData.get("badge") || null;
                        const description = formData.get("description") || null;
                        const icon = formData.get("icon") || "🌐";
                        const isActive = formData.has("is_active") ? 1 : 0;

                        await db
                            .prepare(
                                "INSERT INTO ecosystem_links (title, url, category, badge, description, icon, is_active, sort_order, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                title,
                                urlVal,
                                category,
                                badge,
                                description,
                                icon,
                                isActive,
                            )
                            .run();
                    } catch (e) {
                        console.error("D1 Ecosystem create error:", e);
                    }
                }
                const refEco = request.headers.get("Referer");
                const ecoDest =
                    refEco &&
                    (refEco.includes("/links") || refEco.includes("/toolkit"))
                        ? "/links"
                        : "/ecosystem";
                return Response.redirect(new URL(ecoDest, request.url), 302);
            }

            if (path.startsWith("/ecosystem/")) {
                const parts = path.split("/");
                const linkId = parseInt(parts[2], 10);
                if (effectiveMethod === "DELETE" && linkId) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "DELETE FROM ecosystem_links WHERE id = ?",
                                )
                                .bind(linkId)
                                .run();
                        } catch (e) {
                            console.error("D1 Ecosystem delete error:", e);
                        }
                    }
                    const refEcoDel = request.headers.get("Referer");
                    const ecoDestDel =
                        refEcoDel &&
                        (refEcoDel.includes("/links") ||
                            refEcoDel.includes("/toolkit"))
                            ? "/links"
                            : "/ecosystem";
                    return Response.redirect(
                        new URL(ecoDestDel, request.url),
                        302,
                    );
                }
                if (effectiveMethod === "PUT" && linkId && formData) {
                    if (db) {
                        try {
                            const title = formData.get("title") || "New Portal";
                            const urlVal = formData.get("url") || "#";
                            const category =
                                formData.get("category") || "Official Portals";
                            const badge = formData.get("badge") || null;
                            const description =
                                formData.get("description") || null;
                            const icon = formData.get("icon") || "🌐";
                            const isActive = formData.has("is_active") ? 1 : 0;

                            await db
                                .prepare(
                                    "UPDATE ecosystem_links SET title = ?, url = ?, category = ?, badge = ?, description = ?, icon = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(
                                    title,
                                    urlVal,
                                    category,
                                    badge,
                                    description,
                                    icon,
                                    isActive,
                                    linkId,
                                )
                                .run();
                        } catch (e) {
                            console.error("D1 Ecosystem update error:", e);
                        }
                    }
                    const refEcoPut = request.headers.get("Referer");
                    const ecoDestPut =
                        refEcoPut &&
                        (refEcoPut.includes("/links") ||
                            refEcoPut.includes("/toolkit"))
                            ? "/links"
                            : "/ecosystem";
                    return Response.redirect(
                        new URL(ecoDestPut, request.url),
                        302,
                    );
                }
            }

            // 4f2. Marketing Resources Handlers
            if (
                path === "/marketing-resources" &&
                effectiveMethod === "POST" &&
                formData
            ) {
                if (db) {
                    try {
                        const title =
                            formData.get("title") || "Untitled Resource";
                        const category =
                            formData.get("category") || "Leaflets & Sheets";
                        const fileType = formData.get("file_type") || "pdf";
                        const fileUrl = formData.get("file_url") || "#";
                        const fileSize = formData.get("file_size") || null;
                        const badge = formData.get("badge") || null;
                        const description = formData.get("description") || null;
                        const sortOrder = parseInt(
                            formData.get("sort_order") || "0",
                            10,
                        );
                        const isActive = formData.has("is_active") ? 1 : 1;

                        await db
                            .prepare(
                                "INSERT INTO marketing_resources (title, category, file_type, file_url, file_size, badge, description, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                title,
                                category,
                                fileType,
                                fileUrl,
                                fileSize,
                                badge,
                                description,
                                sortOrder,
                                isActive,
                            )
                            .run();
                    } catch (e) {
                        console.error("D1 Marketing Resource insert error:", e);
                    }
                }
                return Response.redirect(
                    new URL("/resources", request.url),
                    302,
                );
            }

            if (path.startsWith("/marketing-resources/")) {
                const parts = path.split("/");
                const resId = parseInt(parts[2], 10);
                if (effectiveMethod === "DELETE" && resId) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "DELETE FROM marketing_resources WHERE id = ?",
                                )
                                .bind(resId)
                                .run();
                        } catch (e) {
                            console.error(
                                "D1 Marketing Resource delete error:",
                                e,
                            );
                        }
                    }
                    return Response.redirect(
                        new URL("/resources", request.url),
                        302,
                    );
                }
                if (effectiveMethod === "PUT" && resId && formData) {
                    if (db) {
                        try {
                            const title =
                                formData.get("title") || "Untitled Resource";
                            const category =
                                formData.get("category") || "Leaflets & Sheets";
                            const fileType = formData.get("file_type") || "pdf";
                            const fileUrl = formData.get("file_url") || "#";
                            const fileSize = formData.get("file_size") || null;
                            const badge = formData.get("badge") || null;
                            const description =
                                formData.get("description") || null;
                            const sortOrder = parseInt(
                                formData.get("sort_order") || "0",
                                10,
                            );
                            const isActive = formData.has("is_active") ? 1 : 0;

                            await db
                                .prepare(
                                    "UPDATE marketing_resources SET title = ?, category = ?, file_type = ?, file_url = ?, file_size = ?, badge = ?, description = ?, sort_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(
                                    title,
                                    category,
                                    fileType,
                                    fileUrl,
                                    fileSize,
                                    badge,
                                    description,
                                    sortOrder,
                                    isActive,
                                    resId,
                                )
                                .run();
                        } catch (e) {
                            console.error(
                                "D1 Marketing Resource update error:",
                                e,
                            );
                        }
                    }
                    return Response.redirect(
                        new URL("/resources", request.url),
                        302,
                    );
                }
            }

            // 4g. Presentations Handlers
            if (
                path === "/presentations" &&
                effectiveMethod === "POST" &&
                formData
            ) {
                if (db) {
                    try {
                        const leadId = Number(formData.get("lead_id")) || null;
                        const type = formData.get("type") || "1-on-1 In-person";
                        const dateTime =
                            formData.get("date_time") ||
                            formData.get("presentation_at") ||
                            new Date().toISOString();
                        const topic = formData.get("topic") || null;
                        const questions = formData.get("questions") || null;
                        const objections = formData.get("objections") || null;
                        const outcome = formData.get("outcome") || null;
                        const nextFollowUpAt =
                            formData.get("next_follow_up_at") || null;
                        const notes = formData.get("notes") || null;

                        await db
                            .prepare(
                                "INSERT INTO presentations (lead_id, user_id, date_time, type, topic, questions, objections, outcome, next_follow_up_at, notes, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                leadId,
                                currentUserId,
                                dateTime,
                                type,
                                topic,
                                questions,
                                objections,
                                outcome,
                                nextFollowUpAt,
                                notes,
                            )
                            .run();

                        if (leadId) {
                            await db
                                .prepare(
                                    "UPDATE leads SET stage = 'presentation', last_contact_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(leadId)
                                .run();
                            await db
                                .prepare(
                                    "INSERT INTO activities (lead_id, user_id, type, title, description, performed_at, created_at, updated_at) " +
                                        "VALUES (?, ?, 'presentation', ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                )
                                .bind(
                                    leadId,
                                    currentUserId,
                                    "Presentation Conducted: " + type,
                                    topic ||
                                        notes ||
                                        "Presentation session recorded",
                                )
                                .run();

                            if (nextFollowUpAt) {
                                await db
                                    .prepare(
                                        "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                                            "VALUES (?, 'Follow-up', ?, 'High', 'Follow up on presentation session', ?, ?, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        "Follow-up: Presentation discussion",
                                        nextFollowUpAt,
                                        leadId,
                                        currentUserId,
                                    )
                                    .run();
                            }
                        }
                    } catch (e) {
                        console.error("D1 Presentation create error:", e);
                    }
                }
                const ref = request.headers.get("referer") || "";
                const m = ref.match(/\/leads\/(\d+)/);
                if (m)
                    return Response.redirect(
                        new URL("/leads/" + m[1], request.url),
                        302,
                    );
                return Response.redirect(
                    new URL("/presentations", request.url),
                    302,
                );
            }

            if (path.startsWith("/presentations/")) {
                const parts = path.split("/");
                const presId = parseInt(parts[2], 10);
                if (effectiveMethod === "DELETE" && presId) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "DELETE FROM presentations WHERE id = ?",
                                )
                                .bind(presId)
                                .run();
                        } catch (e) {
                            console.error("D1 Presentation delete error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/presentations", request.url),
                        302,
                    );
                }
            }

            // 4h. Content Calendar Handlers
            if (
                path === "/marketing/content-calendar" &&
                effectiveMethod === "POST" &&
                formData
            ) {
                if (db) {
                    try {
                        const title = formData.get("title") || "New Post";
                        const platform =
                            formData.get("platform") || "Facebook Profile";
                        const status = formData.get("status") || "Planned";
                        const scheduledAt =
                            formData.get("scheduled_at") ||
                            new Date().toISOString();
                        const topic = formData.get("topic") || null;
                        const caption = formData.get("caption") || null;
                        const cta = formData.get("cta") || null;
                        const reach = formData.get("reach")
                            ? Number(formData.get("reach"))
                            : null;
                        const engagement = formData.get("engagement")
                            ? Number(formData.get("engagement"))
                            : null;
                        const inboxCount = formData.get("inbox_count")
                            ? Number(formData.get("inbox_count"))
                            : null;
                        const leadsGenerated = formData.get("leads_generated")
                            ? Number(formData.get("leads_generated"))
                            : null;
                        const conversions = formData.get("conversions")
                            ? Number(formData.get("conversions"))
                            : null;
                        const notes = formData.get("notes") || null;

                        await db
                            .prepare(
                                "INSERT INTO content_items (title, platform, status, scheduled_at, topic, caption, cta, reach, engagement, inbox_count, leads_generated, conversions, notes, user_id, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                title,
                                platform,
                                status,
                                scheduledAt,
                                topic,
                                caption,
                                cta,
                                reach,
                                engagement,
                                inboxCount,
                                leadsGenerated,
                                conversions,
                                notes,
                                currentUserId,
                            )
                            .run();
                    } catch (e) {
                        console.error("D1 Content Calendar create error:", e);
                    }
                }
                return Response.redirect(
                    new URL("/marketing/content-calendar", request.url),
                    302,
                );
            }

            if (path.startsWith("/marketing/content-calendar/")) {
                const parts = path.split("/");
                const itemId = parseInt(parts[3], 10);
                if (effectiveMethod === "DELETE" && itemId) {
                    if (db) {
                        try {
                            await db
                                .prepare(
                                    "DELETE FROM content_items WHERE id = ?",
                                )
                                .bind(itemId)
                                .run();
                        } catch (e) {
                            console.error(
                                "D1 Content Calendar delete error:",
                                e,
                            );
                        }
                    }
                    return Response.redirect(
                        new URL("/marketing/content-calendar", request.url),
                        302,
                    );
                }
                if (effectiveMethod === "PUT" && itemId && formData) {
                    if (db) {
                        try {
                            const title = formData.get("title") || "Post";
                            const platform =
                                formData.get("platform") || "Facebook Profile";
                            const status = formData.get("status") || "Planned";
                            const scheduledAt =
                                formData.get("scheduled_at") ||
                                new Date().toISOString();
                            const topic = formData.get("topic") || null;
                            const caption = formData.get("caption") || null;
                            const cta = formData.get("cta") || null;
                            const reach = formData.get("reach")
                                ? Number(formData.get("reach"))
                                : null;
                            const engagement = formData.get("engagement")
                                ? Number(formData.get("engagement"))
                                : null;
                            const inboxCount = formData.get("inbox_count")
                                ? Number(formData.get("inbox_count"))
                                : null;
                            const leadsGenerated = formData.get(
                                "leads_generated",
                            )
                                ? Number(formData.get("leads_generated"))
                                : null;
                            const conversions = formData.get("conversions")
                                ? Number(formData.get("conversions"))
                                : null;
                            const notes = formData.get("notes") || null;

                            await db
                                .prepare(
                                    "UPDATE content_items SET title = ?, platform = ?, status = ?, scheduled_at = ?, topic = ?, caption = ?, cta = ?, reach = ?, engagement = ?, inbox_count = ?, leads_generated = ?, conversions = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(
                                    title,
                                    platform,
                                    status,
                                    scheduledAt,
                                    topic,
                                    caption,
                                    cta,
                                    reach,
                                    engagement,
                                    inboxCount,
                                    leadsGenerated,
                                    conversions,
                                    notes,
                                    itemId,
                                )
                                .run();
                        } catch (e) {
                            console.error(
                                "D1 Content Calendar update error:",
                                e,
                            );
                        }
                    }
                    return Response.redirect(
                        new URL("/marketing/content-calendar", request.url),
                        302,
                    );
                }
            }

            // 4i. Roles & Permissions Handlers
            if (path === "/roles" && effectiveMethod === "POST" && formData) {
                if (db) {
                    try {
                        const name = formData.get("name") || "New Role";
                        const slug = (
                            formData.get("slug") ||
                            name.toLowerCase().replace(/[^a-z0-9]+/g, "-")
                        ).replace(/^-|-$/g, "");
                        const description = formData.get("description") || null;
                        const permissions =
                            formData.getAll("permissions[]") || [];
                        const insRes = await db
                            .prepare(
                                "INSERT INTO roles (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(name, slug, description)
                            .run();

                        const roleId = insRes?.meta?.last_row_id;
                        if (roleId && permissions.length > 0) {
                            for (const pId of permissions) {
                                await db
                                    .prepare(
                                        "INSERT INTO permission_role (role_id, permission_id) VALUES (?, ?)",
                                    )
                                    .bind(roleId, Number(pId))
                                    .run();
                            }
                        }
                    } catch (e) {
                        console.error("D1 Role create error:", e);
                    }
                }
                return Response.redirect(new URL("/roles", request.url), 302);
            }

            if (path.startsWith("/roles/")) {
                const parts = path.split("/");
                const roleId = parseInt(parts[2], 10);
                if (effectiveMethod === "DELETE" && roleId) {
                    if (db) {
                        try {
                            if (roleId > 4) {
                                // Keep core roles intact
                                await db
                                    .prepare(
                                        "DELETE FROM permission_role WHERE role_id = ?",
                                    )
                                    .bind(roleId)
                                    .run();
                                await db
                                    .prepare(
                                        "DELETE FROM role_user WHERE role_id = ?",
                                    )
                                    .bind(roleId)
                                    .run();
                                await db
                                    .prepare("DELETE FROM roles WHERE id = ?")
                                    .bind(roleId)
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Role delete error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/roles", request.url),
                        302,
                    );
                }
                if (effectiveMethod === "PUT" && roleId && formData) {
                    if (db) {
                        try {
                            const name = formData.get("name") || "Role";
                            const description =
                                formData.get("description") || null;
                            const permissions =
                                formData.getAll("permissions[]") || [];
                            await db
                                .prepare(
                                    "UPDATE roles SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                                )
                                .bind(name, description, roleId)
                                .run();
                            await db
                                .prepare(
                                    "DELETE FROM permission_role WHERE role_id = ?",
                                )
                                .bind(roleId)
                                .run();
                            for (const pId of permissions) {
                                await db
                                    .prepare(
                                        "INSERT INTO permission_role (role_id, permission_id) VALUES (?, ?)",
                                    )
                                    .bind(roleId, Number(pId))
                                    .run();
                            }
                        } catch (e) {
                            console.error("D1 Role update error:", e);
                        }
                    }
                    return Response.redirect(
                        new URL("/roles", request.url),
                        302,
                    );
                }
            }

            // Smart Redirect based on Path
            if (path.startsWith("/users")) {
                return Response.redirect(new URL("/users", request.url), 302);
            }
            if (path.startsWith("/leads")) {
                return Response.redirect(new URL("/leads", request.url), 302);
            }
            if (path.startsWith("/binary")) {
                return Response.redirect(new URL("/binary", request.url), 302);
            }
            if (path.startsWith("/contacts")) {
                return Response.redirect(
                    new URL("/contacts", request.url),
                    302,
                );
            }
            if (path.startsWith("/tasks")) {
                return Response.redirect(new URL("/tasks", request.url), 302);
            }
            if (path.startsWith("/ecosystem")) {
                return Response.redirect(
                    new URL("/ecosystem", request.url),
                    302,
                );
            }
            if (path.startsWith("/roles")) {
                return Response.redirect(new URL("/roles", request.url), 302);
            }
            return Response.redirect(new URL(path, request.url), 302);
        }

        // 5. Query D1 for Live Dynamic Data
        let liveLeads = [];
        let deletedLeadIds = [];
        let liveNodes = [];
        let deletedNodeIds = [];
        let liveContacts = [];
        let liveTasks = [];
        let liveEcosystem = [];
        let liveUsers = [];
        let deletedUserIds = [];
        let livePresentations = [];
        let deletedPresIds = [];
        let liveContentItems = [];
        let liveActivities = [];
        let liveRoles = [];
        let liveResources = [];
        let liveAbbreviations = [];
        let sourcesMap = {
            1: "Direct Inbound",
            2: "Facebook Page",
            3: "LinkedIn Outreach",
            4: "Referral / Team",
            5: "Website / Landing Page",
            6: "Seminar / Workshop",
            7: "Investor Network",
            8: "Cold Calling",
        };

        if (db) {
            try {
                const [
                    leadsRes,
                    delLeadsRes,
                    nodesRes,
                    contactsRes,
                    tasksRes,
                    ecoRes,
                    sourcesRes,
                    usersRes,
                    presRes,
                    contentRes,
                    actRes,
                    rolesRes,
                ] = await Promise.all([
                    db
                        .prepare(
                            "SELECT * FROM leads WHERE deleted_at IS NULL ORDER BY id DESC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT id FROM leads WHERE deleted_at IS NOT NULL",
                        )
                        .all(),
                    db
                        .prepare("SELECT * FROM binary_nodes ORDER BY id ASC")
                        .all(),
                    db
                        .prepare(
                            "SELECT * FROM sbl_contacts ORDER BY sort_order ASC, id DESC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT t.*, l.name as lead_name, l.mobile as lead_mobile FROM tasks t LEFT JOIN leads l ON t.related_lead_id = l.id ORDER BY t.due_at ASC, t.id DESC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT * FROM ecosystem_links ORDER BY sort_order ASC, id DESC",
                        )
                        .all(),
                    db.prepare("SELECT id, name FROM lead_sources").all(),
                    db
                        .prepare(
                            "SELECT u.*, ru.role_id, r.name as role_name, r.slug as role_slug FROM users u LEFT JOIN role_user ru ON u.id = ru.user_id LEFT JOIN roles r ON ru.role_id = r.id ORDER BY u.id ASC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT p.*, l.name as lead_name, l.mobile as lead_mobile, l.stage as lead_stage, u.name as user_name FROM presentations p LEFT JOIN leads l ON p.lead_id = l.id LEFT JOIN users u ON p.user_id = u.id ORDER BY p.date_time DESC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT * FROM content_items ORDER BY scheduled_at DESC, id DESC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT a.*, u.name as user_name FROM activities a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.performed_at DESC, a.id DESC",
                        )
                        .all(),
                    db
                        .prepare(
                            "SELECT r.*, count(ru.user_id) as users_count FROM roles r LEFT JOIN role_user ru ON r.id = ru.role_id GROUP BY r.id ORDER BY r.id ASC",
                        )
                        .all(),
                ]);

                if (leadsRes?.results) liveLeads = leadsRes.results;
                if (delLeadsRes?.results) {
                    const activeLeadIds = new Set(
                        (liveLeads || []).map((l) => Number(l.id)),
                    );
                    deletedLeadIds = delLeadsRes.results
                        .map((r) => Number(r.id))
                        .filter((id) => !activeLeadIds.has(id));
                }
                if (nodesRes?.results) {
                    liveNodes = nodesRes.results;
                    for (const node of liveNodes) {
                        const children = liveNodes.filter(
                            (c) => Number(c.parent_id) === Number(node.id),
                        );
                        node.direct_left_count = children.filter(
                            (c) => c.branch === "LEFT" || c.position === "left",
                        ).length;
                        node.direct_right_count = children.filter(
                            (c) =>
                                c.branch === "RIGHT" || c.position === "right",
                        ).length;
                        node.direct_total_count =
                            node.direct_left_count + node.direct_right_count;
                    }
                }
                if (contactsRes?.results) liveContacts = contactsRes.results;
                if (tasksRes?.results) liveTasks = tasksRes.results;
                if (ecoRes?.results) liveEcosystem = ecoRes.results;
                if (usersRes?.results) liveUsers = usersRes.results;
                if (presRes?.results) livePresentations = presRes.results;
                if (contentRes?.results) liveContentItems = contentRes.results;
                if (actRes?.results) liveActivities = actRes.results;
                if (rolesRes?.results) liveRoles = rolesRes.results;

                try {
                    const mRes = await db
                        .prepare(
                            "SELECT * FROM marketing_resources WHERE is_active = 1 ORDER BY sort_order ASC, id DESC",
                        )
                        .all();
                    if (mRes?.results) liveResources = mRes.results;
                } catch (e) {}

                try {
                    const abbRes = await db
                        .prepare(
                            "SELECT * FROM abbreviations ORDER BY term ASC",
                        )
                        .all();
                    if (abbRes?.results) liveAbbreviations = abbRes.results;
                } catch (e) {}

                if (sourcesRes?.results) {
                    for (const s of sourcesRes.results) {
                        sourcesMap[s.id] = s.name;
                    }
                }

                const existingNodeIds = new Set(
                    liveNodes.map((r) => Number(r.id)),
                );
                const allKnownNodeIds = [
                    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,
                ];
                deletedNodeIds = allKnownNodeIds.filter(
                    (id) => !existingNodeIds.has(id),
                );

                const existingUserIds = new Set(
                    liveUsers.map((r) => Number(r.id)),
                );
                const allKnownUserIds = [1, 2, 3, 4];
                deletedUserIds = allKnownUserIds.filter(
                    (id) => !existingUserIds.has(id),
                );
            } catch (e) {
                console.error("D1 Query error:", e);
            }
        }

        // 5b. Authenticate Session Cookie (resolved early before form routes)
        let authUser = null;
        if (authUserId) {
            authUser = liveUsers.find(
                (u) => Number(u.id) === Number(authUserId),
            );
            if (authUser && authUser.status === "inactive") {
                authUser = null; // Revoke immediately
            }
        }

        // Login Route (Public Gateway)
        if (path === "/login") {
            if (authUser) {
                return new Response(null, {
                    status: 302,
                    headers: { Location: "/dashboard" },
                });
            }

            let loginHtml = PAGES.login || "<h1>Login</h1>";
            const errParam = url.searchParams.get("error");
            if (errParam) {
                let errMsg =
                    "মোবাইল নম্বর অথবা পাসওয়ার্ডটি সঠিক নয়। অনুগ্রহ করে পুনরায় চেষ্টা করুন।";
                let isInactive = false;
                if (errParam === "inactive") {
                    errMsg =
                        "আপনার অ্যাকাউন্টটি নিষ্ক্রিয় (Inactive) রয়েছে। অ্যাক্সেসের জন্য অ্যাডমিনের সাথে যোগাযোগ করুন।";
                    isInactive = true;
                } else if (errParam === "empty") {
                    errMsg =
                        "মোবাইল নম্বর এবং পাসওয়ার্ড উভয়ই সঠিকভাবে প্রদান করুন।";
                }

                const alertBox = `
                    <div class="mb-5 p-4 rounded-xl ${isInactive ? "bg-amber-500/15 border border-amber-500/30 text-amber-200" : "bg-rose-500/15 border border-rose-500/30 text-rose-200"} text-xs font-medium flex items-center gap-2.5 animate-pulse shadow-lg">
                        <span class="text-base">${isInactive ? "⛔" : "⚠️"}</span>
                        <span>${errMsg}</span>
                    </div>
                `;
                if (loginHtml.includes("<form")) {
                    loginHtml = loginHtml.replace("<form", alertBox + "<form");
                }
            }

            if (loginHtml.includes("</head>")) {
                loginHtml = loginHtml.replace(
                    "</head>",
                    '<style id="sbl-edge-styles">\n' +
                        CSS_CONTENT +
                        "\n</style>\n</head>",
                );
            }

            return new Response(loginHtml, {
                headers: {
                    "Content-Type": "text/html; charset=utf-8",
                    "Cache-Control":
                        "no-store, no-cache, must-revalidate, max-age=0",
                },
            });
        }

        // Protected Application Routes - Must be authenticated!
        if (!authUser) {
            return new Response(null, {
                status: 302,
                headers: {
                    Location: "/login",
                    "Set-Cookie":
                        "sbl_session=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0; Secure",
                },
            });
        }

        // 5c. Unified Search API Endpoint
        if (path === "/api/search") {
            const query = (url.searchParams.get("q") || "")
                .trim()
                .toLowerCase();
            if (!query) {
                return new Response(
                    JSON.stringify({ query: "", total: 0, results: {} }),
                    {
                        headers: {
                            "Content-Type": "application/json; charset=utf-8",
                        },
                    },
                );
            }

            const results = {
                tools: [],
                leads: [],
                team: [],
                resources: [],
                abbreviations: [],
                contacts: [],
                links: [],
            };

            // 1. Navigation Tools
            const navTools = [
                {
                    title: "Packages",
                    category: "Tool",
                    url: "/packages",
                    description: "SBL Product & Investment Packages",
                },
                {
                    title: "Ranks",
                    category: "Tool",
                    url: "/ranks",
                    description: "Ranks, Badges & Criteria",
                },
                {
                    title: "Counseling Guide",
                    category: "Tool",
                    url: "/counseling",
                    description: "Step-by-step Client Counseling Scripts",
                },
                {
                    title: "Commission Calculator",
                    category: "Tool",
                    url: "/commission",
                    description: "Sales & Team Binary Commission Simulator",
                },
                {
                    title: "Official Links",
                    category: "Tool",
                    url: "/links",
                    description: "Ecosystem Portals & Links Directory",
                },
                {
                    title: "Resources",
                    category: "Tool",
                    url: "/resources",
                    description: "Marketing Leaflets, Pitch Decks & Documents",
                },
                {
                    title: "Team Explorer",
                    category: "Tool",
                    url: "/team",
                    description: "Binary Tree, Network Structure & Directory",
                },
                {
                    title: "Abbreviations",
                    category: "Tool",
                    url: "/abbreviations",
                    description: "SBL Business Terms & Glossary",
                },
                {
                    title: "Contacts & Helplines",
                    category: "Tool",
                    url: "/contacts",
                    description:
                        "Customer Support, Management & Office Directory",
                },
                {
                    title: "Leads CRM",
                    category: "CRM",
                    url: "/leads",
                    description: "Client Pipelines, Warm Leads & Conversion",
                },
                {
                    title: "Dashboard",
                    category: "Navigation",
                    url: "/dashboard",
                    description: "Performance Overview & Metric Cards",
                },
                {
                    title: "Users & Roles",
                    category: "Administration",
                    url: "/users",
                    description: "Manage Team Members & Access Permissions",
                },
            ];

            for (const item of navTools) {
                if (
                    item.title.toLowerCase().includes(query) ||
                    (item.description &&
                        item.description.toLowerCase().includes(query))
                ) {
                    results.tools.push(item);
                }
            }

            // 2. Leads (scoped to user unless superadmin)
            const isSuperAdmin =
                authUser &&
                (authUser.role === "super-admin" ||
                    authUser.id === 1 ||
                    authUser.role_slug === "super-admin");
            const userLeads = (liveLeads || []).filter((l) => {
                if (!isSuperAdmin) {
                    return (
                        Number(l.assigned_to) === Number(authUser.id) ||
                        Number(l.owner_user_id) === Number(authUser.id)
                    );
                }
                return true;
            });

            for (const lead of userLeads) {
                const searchStr =
                    `${lead.name || ""} ${lead.mobile || ""} ${lead.whatsapp || ""} ${lead.location || ""} ${lead.profession_or_business || ""}`.toLowerCase();
                if (searchStr.includes(query)) {
                    results.leads.push({
                        id: lead.id,
                        title: lead.name,
                        subtitle: `${lead.mobile || ""} • ${lead.stage || "lead"}`,
                        category: "Lead",
                        url: `/leads/${lead.id}`,
                        stage: lead.stage,
                        mobile: lead.mobile,
                    });
                    if (results.leads.length >= 8) break;
                }
            }

            // 3. Team Nodes
            const userNodes = (liveNodes || []).filter((n) => {
                if (!isSuperAdmin) {
                    return Number(n.tree_owner_id) === Number(authUser.id);
                }
                return true;
            });

            for (const node of userNodes) {
                const searchStr =
                    `${node.member_name || ""} ${node.member_code || ""} ${node.phone || ""} ${node.rank_title || ""}`.toLowerCase();
                if (searchStr.includes(query)) {
                    results.team.push({
                        id: node.id,
                        title: node.member_name,
                        subtitle: `${node.member_code} • ${node.phone || ""}`,
                        category: "Team Member",
                        url: `/team?search=${encodeURIComponent(node.member_code)}`,
                        member_code: node.member_code,
                        side: node.side,
                    });
                    if (results.team.length >= 8) break;
                }
            }

            // 4. Resources
            for (const res of liveResources || []) {
                const searchStr =
                    `${res.title || ""} ${res.category || ""} ${res.description || ""}`.toLowerCase();
                if (searchStr.includes(query)) {
                    results.resources.push({
                        id: res.id,
                        title: res.title,
                        subtitle: res.category || "Resource",
                        category: "Resource",
                        url: res.file_url || "/resources",
                        file_type: res.file_type,
                    });
                    if (results.resources.length >= 6) break;
                }
            }

            // 5. Abbreviations
            for (const abbr of liveAbbreviations || []) {
                const searchStr =
                    `${abbr.term || ""} ${abbr.meaning || ""} ${abbr.description || ""}`.toLowerCase();
                if (searchStr.includes(query)) {
                    results.abbreviations.push({
                        id: abbr.id,
                        title: abbr.term,
                        subtitle: abbr.meaning,
                        category: "Abbreviation",
                        url: `/abbreviations#term-${encodeURIComponent(abbr.term)}`,
                    });
                    if (results.abbreviations.length >= 6) break;
                }
            }

            // 6. Helplines & Contacts
            for (const c of liveContacts || []) {
                const searchStr =
                    `${c.department || ""} ${c.contact_person || ""} ${c.phone || ""} ${c.mobile || ""}`.toLowerCase();
                if (searchStr.includes(query)) {
                    results.contacts.push({
                        id: c.id,
                        title: c.department || c.contact_person,
                        subtitle: `${c.contact_person ? c.contact_person + " • " : ""}${c.phone || c.mobile || ""}`,
                        category: "Contact",
                        url: "/contacts",
                    });
                    if (results.contacts.length >= 6) break;
                }
            }

            // 7. Official Links
            for (const l of liveEcosystem || []) {
                const searchStr =
                    `${l.title || ""} ${l.category || ""} ${l.url || ""} ${l.description || ""}`.toLowerCase();
                if (searchStr.includes(query)) {
                    results.links.push({
                        id: l.id,
                        title: l.title,
                        subtitle: l.category || l.url,
                        category: "Link",
                        url: l.url,
                    });
                    if (results.links.length >= 6) break;
                }
            }

            let total = 0;
            for (const cat in results) {
                total += results[cat].length;
            }

            return new Response(
                JSON.stringify({
                    query,
                    total,
                    results,
                }),
                {
                    headers: {
                        "Content-Type": "application/json; charset=utf-8",
                        "Cache-Control": "private, no-cache",
                    },
                },
            );
        }

        // 6. Select Page Template
        let html = null;

        if (path === "/" || path === "/dashboard") {
            let dashHtml = PAGES.dashboard;
            if (dashHtml) {
                // Compute today's date in Asia/Dhaka (UTC+6)
                let todayStr;
                let dhakaFormattedDate;
                try {
                    todayStr = new Intl.DateTimeFormat("en-CA", {
                        timeZone: "Asia/Dhaka",
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                    }).format(new Date());
                    dhakaFormattedDate = new Intl.DateTimeFormat("en-US", {
                        timeZone: "Asia/Dhaka",
                        weekday: "long",
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                    }).format(new Date());
                } catch (e) {
                    const dhakaTime = new Date(Date.now() + 6 * 3600 * 1000);
                    todayStr = dhakaTime.toISOString().slice(0, 10);
                    dhakaFormattedDate = dhakaTime.toDateString();
                }

                const nonDel = (liveLeads || []).filter((l) => !l.deleted_at);
                const activeCount = nonDel.filter(
                    (l) =>
                        l.stage !== "converted" &&
                        l.stage !== "lost" &&
                        l.stage !== "not_suitable",
                ).length;
                const addedTodayCount = nonDel.filter(
                    (l) =>
                        l.created_at && l.created_at.slice(0, 10) === todayStr,
                ).length;

                let dueFollowupsToday = 0;
                let overdueCount = 0;
                const nowUtc = new Date();
                nonDel.forEach((l) => {
                    if (
                        l.next_action_at &&
                        l.stage !== "converted" &&
                        l.stage !== "lost" &&
                        l.stage !== "not_suitable"
                    ) {
                        if (l.next_action_at.slice(0, 10) === todayStr)
                            dueFollowupsToday++;
                        if (new Date(l.next_action_at) < nowUtc) overdueCount++;
                    }
                });

                let tasksTodayCount = 0;
                (liveTasks || []).forEach((t) => {
                    if (
                        t.status !== "Completed" &&
                        t.status !== "Cancelled" &&
                        t.due_at &&
                        t.due_at.slice(0, 10) === todayStr
                    ) {
                        tasksTodayCount++;
                    }
                });

                const presCount = (livePresentations || []).filter(
                    (p) => !p.deleted_at,
                ).length;

                dashHtml = dashHtml.replace(
                    /<p id="dashboard-current-date">.*?<\/p>/,
                    `<p id="dashboard-current-date">${dhakaFormattedDate}</p>`,
                );
                dashHtml = dashHtml.replace(
                    /<strong id="active-leads">.*?<\/strong>/,
                    `<strong id="active-leads">${activeCount}</strong>`,
                );
                dashHtml = dashHtml.replace(
                    /<small id="added-today-count">.*?<\/small>|<small>.*? added today<\/small>/,
                    `<small id="added-today-count">${addedTodayCount} added today</small>`,
                );
                dashHtml = dashHtml.replace(
                    /<strong id="today-followup">.*?<\/strong>/,
                    `<strong id="today-followup">${dueFollowupsToday}</strong>`,
                );
                dashHtml = dashHtml.replace(
                    /<strong id="today-tasks-kpi">.*?<\/strong>/,
                    `<strong id="today-tasks-kpi">${tasksTodayCount}</strong>`,
                );
                dashHtml = dashHtml.replace(
                    /<strong id="total-presentations">.*?<\/strong>/,
                    `<strong id="total-presentations">${presCount}</strong>`,
                );
                dashHtml = dashHtml.replace(
                    /<span id="dashboard-overdue-badge"[^>]*>.*?<\/span>/,
                    `<span id="dashboard-overdue-badge" class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">${overdueCount} Overdue</span>`,
                );

                const totalLeads = nonDel.length;
                const stages = [
                    "new",
                    "contacted",
                    "interested",
                    "qualified",
                    "presentation",
                    "follow_up",
                    "decision",
                    "converted",
                ];
                stages.forEach((s) => {
                    const cnt = nonDel.filter(
                        (l) => (l.stage || "new") === s,
                    ).length;
                    const pct =
                        totalLeads > 0
                            ? Math.min(
                                  100,
                                  Math.round((cnt / totalLeads) * 100),
                              )
                            : 0;

                    const countRegex = new RegExp(
                        `(<span data-funnel-count="${s}"[^>]*>)[^<]*(<\\/span>)`,
                    );
                    dashHtml = dashHtml.replace(countRegex, `$1${cnt}$2`);

                    const barRegex = new RegExp(
                        `(data-funnel-bar="${s}"[^>]*style="width:\\s*)[^%]+(%")`,
                    );
                    dashHtml = dashHtml.replace(barRegex, `$1${pct}$2`);

                    const pctRegex = new RegExp(
                        `(<span data-funnel-pct="${s}"[^>]*>)[^<]*(<\\/span>)`,
                    );
                    dashHtml = dashHtml.replace(pctRegex, `$1${pct}% share$2`);
                });
            }
            html = dashHtml;
        } else if (path === "/leads/create") {
            html = PAGES.leads_create;
        } else if (path.match(/^\/leads\/\d+\/edit$/)) {
            const parts = path.split("/");
            const leadId = parseInt(parts[2], 10);
            let pageHtml = PAGES.leads_edit || PAGES.leads;

            const currentLead = liveLeads.find((l) => Number(l.id) === leadId);
            if (currentLead && pageHtml) {
                let interests = [];
                try {
                    interests =
                        typeof currentLead.interest_types === "string"
                            ? JSON.parse(currentLead.interest_types)
                            : currentLead.interest_types || [];
                } catch (e) {}

                const escapedName = escapeHtml(currentLead.name);
                const escapedMobile = escapeHtml(currentLead.mobile);
                const escapedWhatsapp = escapeHtml(
                    currentLead.whatsapp || currentLead.mobile || "",
                );
                const jsName = (currentLead.name || "")
                    .replace(/\\/g, "\\\\")
                    .replace(/'/g, "\\'");
                const jsMobile = (currentLead.mobile || "")
                    .replace(/\\/g, "\\\\")
                    .replace(/'/g, "\\'");
                const jsWhatsapp = (
                    currentLead.whatsapp ||
                    currentLead.mobile ||
                    ""
                )
                    .replace(/\\/g, "\\\\")
                    .replace(/'/g, "\\'");

                pageHtml = pageHtml
                    .replace(
                        /action="[^"]*\/leads\/\d+"/g,
                        `action="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /id="lead-edit-delete-form" action="[^"]*"/g,
                        `id="lead-edit-delete-form" action="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /id="delete-lead-form-\d+"/g,
                        `id="delete-lead-form-${currentLead.id}"`,
                    )
                    .replace(
                        /id="lead-edit-back-link" href="[^"]*"/g,
                        `id="lead-edit-back-link" href="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /id="lead-edit-cancel-link" href="[^"]*"/g,
                        `id="lead-edit-cancel-link" href="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /<title>.*?<\/title>/,
                        `<title>Edit Lead: ${escapedName} - SBL Growth Manager</title>`,
                    )
                    .replace(
                        /<h2 id="lead-edit-title"[^>]*>.*?<\/h2>/,
                        `<h2 id="lead-edit-title" class="text-base font-bold text-slate-900">Edit Lead: ${escapedName}</h2>`,
                    )
                    .replace(/name:\s*'[^']*'/, `name: '${jsName}'`)
                    .replace(/mobile:\s*'[^']*'/, `mobile: '${jsMobile}'`)
                    .replace(/whatsapp:\s*'[^']*'/, `whatsapp: '${jsWhatsapp}'`)
                    .replace(
                        /photoData:\s*'[^']*'/,
                        `photoData: '${(currentLead.photo || "").replace(/\\/g, "\\\\").replace(/'/g, "\\'")}'`,
                    )
                    .replace(
                        /name="photo" :value="photoData"/,
                        `name="photo" value="${escapeHtml(currentLead.photo || "")}" :value="photoData"`,
                    )
                    .replace(
                        /name="email" value="[^"]*"/g,
                        `name="email" value="${escapeHtml(currentLead.email || "")}"`,
                    )
                    .replace(
                        /name="location" value="[^"]*"/g,
                        `name="location" value="${escapeHtml(currentLead.location || "")}"`,
                    )
                    .replace(
                        /name="profession_or_business" value="[^"]*"/g,
                        `name="profession_or_business" value="${escapeHtml(currentLead.profession_or_business || "")}"`,
                    )
                    .replace(
                        /<textarea name="notes"[^>]*>[\s\S]*?<\/textarea>/,
                        `<textarea name="notes" rows="3" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 p-3">${escapeHtml(currentLead.notes || "")}</textarea>`,
                    )
                    .replace(
                        /<option value="(\w+)"([^>]*)selected/g,
                        '<option value="$1"$2',
                    )
                    .replace(
                        new RegExp(
                            `<option value="${currentLead.stage || "new"}"`,
                        ),
                        `<option value="${currentLead.stage || "new"}" selected`,
                    )
                    .replace(
                        new RegExp(
                            `<option value="${currentLead.lead_source_id || 1}"`,
                        ),
                        `<option value="${currentLead.lead_source_id || 1}" selected`,
                    );
            }
            html = pageHtml;
        } else if (path.match(/^\/leads\/\d+$/)) {
            const parts = path.split("/");
            const leadId = parseInt(parts[2], 10);
            let pageHtml = PAGES.leads_show || PAGES.leads;

            const currentLead = liveLeads.find((l) => Number(l.id) === leadId);
            if (currentLead && pageHtml) {
                const stageLabel = (currentLead.stage || "new")
                    .replace("_", " ")
                    .toUpperCase();
                const initialLetter = (currentLead.name || "L")
                    .charAt(0)
                    .toUpperCase();
                const sourceName =
                    sourcesMap[currentLead.lead_source_id] || "Direct";
                const score = currentLead.score || 25;
                const temp = (currentLead.temperature || "warm").toUpperCase();
                const escapedName = escapeHtml(currentLead.name);
                const escapedMobile = escapeHtml(currentLead.mobile);

                let interests = [];
                try {
                    interests =
                        typeof currentLead.interest_types === "string"
                            ? JSON.parse(currentLead.interest_types)
                            : currentLead.interest_types || [];
                } catch (e) {}

                let cleanWhatsapp = (
                    currentLead.whatsapp ||
                    currentLead.mobile ||
                    ""
                ).replace(/[^0-9]/g, "");
                if (cleanWhatsapp.startsWith("01") && cleanWhatsapp.length === 11) {
                    cleanWhatsapp = "88" + cleanWhatsapp;
                }

                pageHtml = pageHtml
                    .replace(
                        /data-lead-id="\d+"/g,
                        `data-lead-id="${currentLead.id}"`,
                    )
                    .replace(
                        /action="[^"]*\/leads\/\d+\/stage"/g,
                        `action="/leads/${currentLead.id}/stage"`,
                    )
                    .replace(
                        /action="[^"]*\/leads\/\d+\/convert"/g,
                        `action="/leads/${currentLead.id}/convert"`,
                    )
                    .replace(
                        /action="[^"]*\/leads\/\d+\/activities"/g,
                        `action="/leads/${currentLead.id}/activities"`,
                    )
                    .replace(
                        /action="[^"]*\/leads\/\d+"/g,
                        `action="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /href="[^"]*\/leads\/\d+\/edit"/g,
                        `href="/leads/${currentLead.id}/edit"`,
                    )
                    .replace(
                        /href="[^"]*\/leads\/\d+"/g,
                        `href="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /id="delete-lead-form-\d+"/g,
                        `id="delete-lead-form-${currentLead.id}"`,
                    )
                    .replace(
                        /id="lead-show-delete-form" action="[^"]*"/g,
                        `id="lead-show-delete-form" action="/leads/${currentLead.id}"`,
                    )
                    .replace(
                        /id="lead-show-stage-form" action="[^"]*"/g,
                        `id="lead-show-stage-form" action="/leads/${currentLead.id}/stage"`,
                    )
                    .replace(
                        /id="lead-show-convert-form" action="[^"]*"/g,
                        `id="lead-show-convert-form" action="/leads/${currentLead.id}/convert"`,
                    )
                    .replace(
                        /id="lead-show-activity-form"[\s\S]*?action="[^"]*"/,
                        `id="lead-show-activity-form" action="/leads/${currentLead.id}/activities"`,
                    )
                    .replace(
                        /id="lead-show-edit-link" href="[^"]*"/g,
                        `id="lead-show-edit-link" href="/leads/${currentLead.id}/edit"`,
                    )
                    .replace(
                        /value="1" name="lead_id"/g,
                        `value="${currentLead.id}" name="lead_id"`,
                    )
                    .replace(
                        /value="1" name="related_lead_id"/g,
                        `value="${currentLead.id}" name="related_lead_id"`,
                    )
                    .replace(
                        /<title>.*?<\/title>/,
                        `<title>${escapedName} - SBL Growth Manager</title>`,
                    )
                    .replace(
                        /<h1 id="app-page-title"[^>]*>[\s\S]*?<\/h1>/,
                        `<h1 id="app-page-title" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none truncate max-w-[200px] sm:max-w-md">${escapedName}</h1>`,
                    )
                    .replace(
                        /<h1 class="text-base sm:text-lg font-bold[^"]*"[^>]*>[\s\S]*?<\/h1>/,
                        `<h1 id="app-page-title" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none truncate max-w-[200px] sm:max-w-md">${escapedName}</h1>`,
                    )
                    .replace(
                        /<h2 id="lead-show-name"[^>]*>.*?<\/h2>/,
                        `<h2 id="lead-show-name" class="text-xl font-bold text-slate-900">${escapedName}</h2>`,
                    )
                    .replace(
                        /<div id="lead-show-avatar"[^>]*>.*?<\/div>/,
                        currentLead.photo
                            ? `<div id="lead-show-avatar" class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs overflow-hidden border border-orange-200/50"><img src="${escapeHtml(currentLead.photo)}" alt="${escapedName}" class="w-full h-full object-cover"></div>`
                            : `<div id="lead-show-avatar" class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs">${initialLetter}<\/div>`,
                    )
                    .replace(
                        /id="lead-show-stage-badge">.*?<\/span>/,
                        `id="lead-show-stage-badge">${stageLabel}</span>`,
                    )
                    .replace(
                        /id="lead-show-temp-badge">.*?<\/span>/,
                        `id="lead-show-temp-badge">${temp}</span>`,
                    )
                    .replace(
                        /id="lead-show-mobile-btn" href="[^"]*"/,
                        `id="lead-show-mobile-btn" href="tel:${escapedMobile}"`,
                    )
                    .replace(
                        /id="lead-show-mobile-text">.*?<\/span>/,
                        `id="lead-show-mobile-text">${escapedMobile}</span>`,
                    )
                    .replace(
                        /id="lead-show-wa-btn" href="[^"]*"/,
                        `id="lead-show-wa-btn" href="https://wa.me/${cleanWhatsapp}"`,
                    )
                    .replace(
                        /id="lead-show-score-text">.*?<\/span>/,
                        `id="lead-show-score-text">${score} / 100</span>`,
                    )
                    .replace(
                        /id="lead-show-score-bar"[^>]*style="[^"]*"/,
                        `id="lead-show-score-bar" style="width: ${score}%"`,
                    )
                    .replace(
                        /id="lead-show-source-text">.*?<\/span>/,
                        `id="lead-show-source-text">${escapeHtml(sourceName)}</span>`,
                    );

                const validLocation =
                    currentLead.location &&
                    !currentLead.location.startsWith("http://") &&
                    !currentLead.location.startsWith("https://") &&
                    !currentLead.location.includes("/leads/");
                if (validLocation) {
                    pageHtml = pageHtml
                        .replace(
                            /id="lead-show-location-container"[^>]*style="display:\s*none;?"/,
                            'id="lead-show-location-container"',
                        )
                        .replace(
                            /id="lead-show-location-text">.*?<\/span>/,
                            `id="lead-show-location-text">${escapeHtml(currentLead.location)}</span>`,
                        );
                } else {
                    pageHtml = pageHtml
                        .replace(
                            /id="lead-show-location-container"/,
                            'id="lead-show-location-container" style="display: none;"',
                        );
                }
            }
            html = pageHtml;
        } else if (path === "/leads" || path === "/members") {
            const viewMode = url.searchParams.get("view");
            html = viewMode === "kanban" ? PAGES.kanban : PAGES.leads;
        } else if (path === "/profile") {
            html = PAGES.profile || PAGES.dashboard;
        } else if (path === "/presentations") {
            let pageHtml = PAGES.presentations;
            const presCount = (livePresentations || []).length;
            pageHtml = pageHtml.replace(
                /<strong id="total-presentations">.*?<\/strong>/,
                `<strong id="total-presentations">${presCount}</strong>`,
            );
            html = pageHtml;
        } else if (path === "/toolkit") {
            const rawTab = url.searchParams.get("tab") || "packages";
            const tabMap = {
                ranks: "/ranks",
                compensation: "/ranks",
                counseling: "/counseling",
                commission: "/commission",
                calculator: "/commission",
                links: "/links",
                ecosystem: "/links",
                websites: "/links",
                resources: "/resources",
                packages: "/packages",
            };
            const target = tabMap[rawTab] || "/packages";
            return new Response(null, {
                status: 302,
                headers: {
                    Location: target,
                },
            });
        } else if (path === "/ecosystem") {
            return new Response(null, {
                status: 302,
                headers: {
                    Location: "/links",
                },
            });
        } else if (path === "/packages") {
            html = PAGES.packages;
        } else if (path === "/ranks") {
            html = PAGES.ranks;
        } else if (path === "/counseling") {
            html = PAGES.counseling;
        } else if (path === "/commission") {
            html = PAGES.commission;
        } else if (path === "/links") {
            html = PAGES.links;
        } else if (path === "/resources") {
            html = PAGES.resources;
        } else if (path === "/tasks") {
            html = PAGES.tasks;
        } else if (path === "/reports") {
            html = PAGES.reports;
        } else if (path === "/marketing/content-calendar") {
            html = PAGES.calendar;
        } else if (path === "/users" || path.startsWith("/users/")) {
            html = PAGES.users;
        } else if (path === "/roles" || path.startsWith("/roles/")) {
            html = PAGES.roles;
        } else if (path === "/abbreviations") {
            html = PAGES.abbreviations;
            try {
                const terms = await db
                    .prepare("SELECT * FROM abbreviations ORDER BY id")
                    .all();
                html = html.replace(
                    /data-terms="[^"]*"/,
                    () =>
                        'data-terms="' +
                        escapeHtml(JSON.stringify(terms.results)) +
                        '"',
                );
                html = html.replace(
                    /data-can-manage="[^"]*"/,
                    'data-can-manage="' + (abbreviationAdmin ? "1" : "0") + '"',
                );
            } catch (error) {
                console.error("Abbreviation DB error:", error);
            }
        } else if (path === "/contacts") {
            html = PAGES.contacts;
        } else if (
            path === "/binary" ||
            path.startsWith("/binary") ||
            path === "/team" ||
            path.startsWith("/team")
        ) {
            const viewMode = url.searchParams.get("view");
            if (viewMode === "table") {
                html = PAGES.binary_table || PAGES.binary;
            } else if (viewMode === "mindmap" || viewMode === "tree") {
                html = PAGES.binary_mindmap || PAGES.binary;
            } else {
                html = PAGES.binary;
            }
        } else {
            html = PAGES.dashboard;
        }

        let responseHtml = html;

        if (authUser && authUser.name) {
            responseHtml = responseHtml.replace(
                /<strong class="block truncate text-sm text-white">.*?<\/strong>/,
                `<strong class="block truncate text-sm text-white">${escapeHtml(authUser.name)}</strong>`,
            );
            const userInitial = escapeHtml(
                authUser.name.charAt(0).toUpperCase(),
            );
            responseHtml = responseHtml.replace(
                /<a href="[^"]*profile[^"]*" class="profile-avatar"[^>]*>.*?<\/a>/,
                `<a href="/profile" class="profile-avatar" aria-label="Your profile">${userInitial}</a>`,
            );
        }

        const safeUsers = liveUsers.map(({ password, ...u }) => u);

        const syncDataPayload = {
            authUser: authUser
                ? {
                      id: authUser.id,
                      name: authUser.name,
                      phone: authUser.phone,
                      email: authUser.email,
                      role_name: authUser.role_name,
                      designation: authUser.designation,
                  }
                : null,
            leads: liveLeads,
            deletedLeads: deletedLeadIds,
            nodes: liveNodes,
            deletedNodes: deletedNodeIds,
            contacts: liveContacts,
            tasks: liveTasks,
            ecosystem: liveEcosystem,
            users: safeUsers,
            deletedUsers: deletedUserIds,
            sources: sourcesMap,
            presentations: livePresentations,
            deletedPresentations: deletedPresIds,
            contentItems: liveContentItems,
            activities: liveActivities,
            roles: liveRoles,
            resources: liveResources,
            abbreviations: liveAbbreviations,
        };

        const dataScript =
            '<script id="sbl-live-d1-data">\n' +
            "window.DATA = " +
            JSON.stringify(syncDataPayload) +
            ";\nconst DATA = window.DATA;\n</script>\n";

        // 7. Inject Edge Styles
        if (responseHtml && responseHtml.includes("</head>")) {
            let syncStyles = "";
            if (deletedLeadIds.length > 0) {
                syncStyles +=
                    deletedLeadIds
                        .map(
                            (id) =>
                                'tr[data-lead-id="' +
                                id +
                                '"], .kanban-card[data-lead-id="' +
                                id +
                                '"], #mobile-leads-stack > div[data-lead-id="' +
                                id +
                                '"], .divide-y > div[data-lead-id="' +
                                id +
                                '"]',
                        )
                        .join(", ") + " { display: none !important; }\n";
            }
            if (deletedNodeIds.length > 0) {
                syncStyles +=
                    deletedNodeIds
                        .map(
                            (id) =>
                                'tr[data-node-id="' +
                                id +
                                '"], [data-node-id="' +
                                id +
                                '"]',
                        )
                        .join(", ") + " { display: none !important; }\n";
            }
            if (deletedUserIds.length > 0) {
                syncStyles +=
                    deletedUserIds
                        .map(
                            (id) =>
                                'tr[data-user-id="' +
                                id +
                                '"], [data-user-id="' +
                                id +
                                '"]',
                        )
                        .join(", ") + " { display: none !important; }\n";
            }

            responseHtml = responseHtml.replace(
                "</head>",
                () =>
                    '<style id="sbl-edge-styles">\n' +
                    CSS_CONTENT +
                    "\n" +
                    syncStyles +
                    "</style>\n" +
                    dataScript +
                    "</head>",
            );
        }

        // 8. Inject Live Dynamic Edge Synchronization Script (STRICT PATH ISOLATION)
        if (responseHtml && responseHtml.includes("</body>")) {
            const syncScript =
                '<script id="sbl-live-d1-sync">\n' +
                CLIENT_SYNC_JS +
                "\n" +
                "</script>\n";
            responseHtml = responseHtml.replace(
                "</body>",
                () => syncScript + "</body>",
            );
        }

        return new Response(responseHtml, {
            headers: {
                "Content-Type": "text/html; charset=utf-8",
                "Cache-Control":
                    "no-store, no-cache, must-revalidate, max-age=0",
                Pragma: "no-cache",
                Expires: "0",
                "X-Powered-By":
                    "Cloudflare Workers Edge (Laravel Pixel-Perfect Edition)",
            },
        });
    },
};
