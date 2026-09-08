// SBL Growth Manager - Cloudflare Worker Edge Application
// Serves the exact, 100% pixel-perfect compiled Laravel Blade views and handles real D1 CRUD on the edge.

const SBL_LOGO_BASE64 = __SBL_LOGO_BASE64__;
const CSS_CONTENT = __CSS_CONTENT__;
const JS_CONTENT = __JS_CONTENT__;
const CSS_PATH = __CSS_PATH__;
const JS_PATH = __JS_PATH__;
const PAGES = __PAGES__;
const CLIENT_SYNC_JS = __CLIENT_SYNC_JS__;

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
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

        if (path === "/ping") {
            return new Response("pong", { status: 200 });
        }

        const db = env.DB || env.sbl_database;

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

                        const photo = formData.get("photo") || null;
                        const insRes = await db
                            .prepare(
                                "INSERT INTO leads (name, mobile, whatsapp, email, photo, location, profession_or_business, lead_source_id, interest_types, stage, temperature, score, is_manual_score, owner_user_id, next_action_type, next_action_at, last_contact_at, notes, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
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
                                temperature,
                                score,
                                nextActionType,
                                nextActionAt,
                                notes,
                            )
                            .run();

                        const newLeadId = insRes?.meta?.last_row_id;
                        if (nextActionAt && newLeadId) {
                            await db
                                .prepare(
                                    "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                                        "VALUES (?, ?, ?, 'Medium', ?, ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                )
                                .bind(
                                    nextActionType
                                        ? nextActionType + ": " + name
                                        : "Follow-up: " + name,
                                    nextActionType || "Follow-up",
                                    nextActionAt,
                                    notes,
                                    newLeadId,
                                )
                                .run();
                        }
                    } catch (e) {
                        console.error("D1 Leads create error:", e);
                    }
                }
                return Response.redirect(new URL("/leads", request.url), 302);
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
                                        "VALUES (?, 1, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                )
                                .bind(leadId, actType, title, description)
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
                                            "VALUES (?, ?, ?, 'High', ?, ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        (nextActionType || "Follow-up") +
                                            ": " +
                                            title,
                                        nextActionType || "Follow-up",
                                        nextActionAt,
                                        description,
                                        leadId,
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

            // 4c. Team & Users Handlers (POST, PUT, DELETE)
            if (path === "/users" && effectiveMethod === "POST" && formData) {
                if (db) {
                    try {
                        const name = formData.get("name") || "New Team Member";
                        const email = formData.get("email") || "";
                        const phone = formData.get("phone") || null;
                        const designation = formData.get("designation") || null;
                        const roleId = Number(formData.get("role_id")) || 3;
                        const status = formData.get("status") || "active";
                        const passwordHash =
                            "$2y$12$e/e8u9R52f4q4z1V0h.qgeNq4mGkLpY4o5wOQvS5c9zQvT/zKk2yC";

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
                        if (newUserId) {
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
                            if (userId !== 1) {
                                // Never delete super admin
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
                            const name = formData.get("name");
                            const email = formData.get("email");
                            const phone = formData.get("phone") || null;
                            const designation =
                                formData.get("designation") || null;
                            const roleId = Number(formData.get("role_id")) || 3;
                            const status = formData.get("status") || "active";

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
                                    "VALUES (?, ?, ?, ?, ?, ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(title, type, dueAt, priority, notes, leadId)
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
                                                "VALUES (?, 'Follow-up', ?, 'High', ?, ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                        )
                                        .bind(
                                            nextAction || "Follow-up",
                                            nextActionAt,
                                            "Generated from outcome: " +
                                                outcome,
                                            leadId,
                                        )
                                        .run();
                                }

                                await db
                                    .prepare(
                                        "INSERT INTO activities (lead_id, user_id, type, title, description, performed_at, created_at, updated_at) " +
                                            "VALUES (?, 1, 'task_completed', ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        leadId,
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
                    refEco && refEco.includes("/toolkit")
                        ? "/toolkit?tab=ecosystem"
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
                        refEcoDel && refEcoDel.includes("/toolkit")
                            ? "/toolkit?tab=ecosystem"
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
                        refEcoPut && refEcoPut.includes("/toolkit")
                            ? "/toolkit?tab=ecosystem"
                            : "/ecosystem";
                    return Response.redirect(
                        new URL(ecoDestPut, request.url),
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
                                    "VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                            )
                            .bind(
                                leadId,
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
                                        "VALUES (?, 1, 'presentation', ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                )
                                .bind(
                                    leadId,
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
                                            "VALUES (?, 'Follow-up', ?, 'High', 'Follow up on presentation session', ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                                    )
                                    .bind(
                                        "Follow-up: Presentation discussion",
                                        nextFollowUpAt,
                                        leadId,
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
                        new URL(
                            "/presentations?deleted_pres=" + presId,
                            request.url,
                        ),
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
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
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
                if (delLeadsRes?.results)
                    deletedLeadIds = delLeadsRes.results.map((r) =>
                        Number(r.id),
                    );
                if (nodesRes?.results) liveNodes = nodesRes.results;
                if (contactsRes?.results) liveContacts = contactsRes.results;
                if (tasksRes?.results) liveTasks = tasksRes.results;
                if (ecoRes?.results) liveEcosystem = ecoRes.results;
                if (usersRes?.results) liveUsers = usersRes.results;
                if (presRes?.results) livePresentations = presRes.results;
                if (contentRes?.results) liveContentItems = contentRes.results;
                if (actRes?.results) liveActivities = actRes.results;
                if (rolesRes?.results) liveRoles = rolesRes.results;

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

        // 6. Select Page Template
        let html = null;

        if (path === "/" || path === "/dashboard") {
            html = PAGES.dashboard;
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

                const cleanWhatsapp = (
                    currentLead.whatsapp ||
                    currentLead.mobile ||
                    ""
                ).replace(/[^0-9]/g, "");

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
            }
            html = pageHtml;
        } else if (path === "/leads" || path === "/members") {
            const viewMode = url.searchParams.get("view");
            html = viewMode === "kanban" ? PAGES.kanban : PAGES.leads;
        } else if (path === "/profile") {
            html = PAGES.profile || PAGES.dashboard;
        } else if (path === "/presentations") {
            html = PAGES.presentations;
        } else if (path === "/toolkit") {
            html = PAGES.toolkit;
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
        } else if (path === "/ecosystem") {
            html = PAGES.ecosystem;
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
                    "</style>\n</head>",
            );
        }

        // 8. Inject Live Dynamic Edge Synchronization Script (STRICT PATH ISOLATION)
        if (responseHtml && responseHtml.includes("</body>")) {
            const syncDataPayload = {
                leads: liveLeads,
                deletedLeads: deletedLeadIds,
                nodes: liveNodes,
                deletedNodes: deletedNodeIds,
                contacts: liveContacts,
                tasks: liveTasks,
                ecosystem: liveEcosystem,
                users: liveUsers,
                deletedUsers: deletedUserIds,
                sources: sourcesMap,
                presentations: livePresentations,
                deletedPresentations: deletedPresIds,
                contentItems: liveContentItems,
                activities: liveActivities,
                roles: liveRoles,
            };

            const syncScript =
                '<script id="sbl-live-d1-sync">\n' +
                "const DATA = " +
                JSON.stringify(syncDataPayload) +
                ";\n" +
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
                "Cache-Control": "public, max-age=0, must-revalidate",
                "X-Powered-By":
                    "Cloudflare Workers Edge (Laravel Pixel-Perfect Edition)",
            },
        });
    },
};
