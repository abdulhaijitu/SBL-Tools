// SBL Growth Manager - Cloudflare Worker Edge Application
// Serves the exact, 100% pixel-perfect compiled Laravel Blade views and handles real D1 CRUD on the edge.

const SBL_LOGO_BASE64 = __SBL_LOGO_BASE64__;
const CSS_CONTENT = __CSS_CONTENT__;
const JS_CONTENT = __JS_CONTENT__;
const CSS_PATH = __CSS_PATH__;
const JS_PATH = __JS_PATH__;
const PAGES = __PAGES__;

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

                        const insRes = await db
                            .prepare(
                                "INSERT INTO leads (name, mobile, whatsapp, email, location, profession_or_business, lead_source_id, interest_types, stage, temperature, score, is_manual_score, owner_user_id, next_action_type, next_action_at, last_contact_at, notes, created_at, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
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
                    return Response.redirect(
                        new URL("/leads?deleted_lead=" + leadId, request.url),
                        302,
                    );
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
                (path === "/binary" || path === "/binary/place" || path === "/team" || path === "/team/place") &&
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
                        const branch = (formData.get("branch") || formData.get("position") || "LEFT").toUpperCase();
                        const position = branch.toLowerCase();
                        const slotNumber = Number(formData.get("slot_number")) || 1;
                        let pointValue =
                            Number(formData.get("point_value")) || 100;
                        const leftTargetCount =
                            Number(formData.get("left_target_count")) || 5;
                        const rightTargetCount =
                            Number(formData.get("right_target_count")) || 5;
                        const isTarget = formData.get("is_target") ? 1 : 0;
                        const targetDate = formData.get("target_date") || null;
                        const targetNotes = formData.get("target_notes") || null;
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
                if (ref) return Response.redirect(new URL(ref, request.url), 302);
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
                if (ref) return Response.redirect(new URL(ref, request.url), 302);
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
                                    if (node.position === "left" || node.branch === "LEFT") {
                                        await db
                                            .prepare(
                                                "UPDATE binary_nodes SET left_count = MAX(0, left_count - 1), left_bv = MAX(0, left_bv - ?), carry_left = MAX(0, carry_left - ?) WHERE id = ?",
                                            )
                                            .bind(pv, pv, node.parent_id)
                                            .run();
                                    } else if (node.position === "right" || node.branch === "RIGHT") {
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
                    if (ref) return Response.redirect(new URL(ref, request.url), 302);
                    return Response.redirect(
                        new URL("/team?deleted_node=" + nodeId, request.url),
                        302,
                    );
                }

                if (effectiveMethod === "PUT" && nodeId && formData) {
                    if (db) {
                        try {
                            const memberName =
                                formData.get("member_name") || "Unnamed Member";
                            const memberCode =
                                formData.get("member_code") || null;
                            const phone = formData.get("phone") || null;
                            const email = formData.get("email") || null;
                            const passwordPlain =
                                formData.get("password_plain") ||
                                formData.get("password") ||
                                null;
                            const tpin = formData.get("tpin") || null;
                            const packageName =
                                formData.get("package_name") || "National 120k";
                            const rankName =
                                formData.get("rank_name") || "Member";
                            const sponsorName =
                                formData.get("sponsor_name") || null;
                            const sponsorId =
                                formData.get("sponsor_id") &&
                                formData.get("sponsor_id") !== ""
                                    ? Number(formData.get("sponsor_id"))
                                    : null;
                            const isTarget = formData.get("is_target") ? 1 : 0;
                            const targetDate = formData.get("target_date") || null;
                            const targetNotes = formData.get("target_notes") || null;
                            let contributions =
                                formData.get("contributions") || "[]";
                            let contributionsArr = [];
                            try {
                                contributionsArr =
                                    typeof contributions === "string"
                                        ? JSON.parse(contributions)
                                        : contributions;
                            } catch (e) {}
                            let pointValue =
                                formData.get("point_value") !== null &&
                                formData.get("point_value") !== ""
                                    ? Number(formData.get("point_value"))
                                    : 0;
                            if (
                                Array.isArray(contributionsArr) &&
                                contributionsArr.length > 0
                            ) {
                                pointValue = contributionsArr.reduce(
                                    (sum, c) => sum + (Number(c.amount) || 0),
                                    0,
                                );
                                contributions =
                                    JSON.stringify(contributionsArr);
                            } else if (typeof contributions !== "string") {
                                contributions = JSON.stringify(contributions);
                            }
                            const leftCount =
                                formData.get("left_count") !== null &&
                                formData.get("left_count") !== ""
                                    ? Number(formData.get("left_count"))
                                    : 0;
                            const rightCount =
                                formData.get("right_count") !== null &&
                                formData.get("right_count") !== ""
                                    ? Number(formData.get("right_count"))
                                    : 0;
                            const leftTargetCount =
                                formData.get("left_target_count") !== null &&
                                formData.get("left_target_count") !== ""
                                    ? Number(formData.get("left_target_count"))
                                    : 5;
                            const rightTargetCount =
                                formData.get("right_target_count") !== null &&
                                formData.get("right_target_count") !== ""
                                    ? Number(formData.get("right_target_count"))
                                    : 5;
                            const leftBv =
                                formData.get("left_bv") !== null &&
                                formData.get("left_bv") !== ""
                                    ? Number(formData.get("left_bv"))
                                    : 0;
                            const rightBv =
                                formData.get("right_bv") !== null &&
                                formData.get("right_bv") !== ""
                                    ? Number(formData.get("right_bv"))
                                    : 0;
                            const userId =
                                formData.get("user_id") &&
                                formData.get("user_id") !== ""
                                    ? Number(formData.get("user_id"))
                                    : null;
                            const isActive = formData.has("is_active") ? 1 : 0;

                            await db
                                .prepare(
                                    "UPDATE binary_nodes SET member_name = ?, member_code = ?, phone = ?, email = ?, password_plain = ?, tpin = ?, package_name = ?, rank_name = ?, sponsor_id = ?, sponsor_name = ?, point_value = ?, contributions = ?, is_target = ?, target_date = ?, target_notes = ?, left_count = ?, right_count = ?, left_target_count = ?, right_target_count = ?, left_bv = ?, right_bv = ?, user_id = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
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
                                    leftCount,
                                    rightCount,
                                    leftTargetCount,
                                    rightTargetCount,
                                    leftBv,
                                    rightBv,
                                    userId,
                                    isActive,
                                    nodeId,
                                )
                                .run();
                        } catch (e) {
                            console.error("D1 Binary update error:", e);
                        }
                    }
                    const ref = request.headers.get("referer");
                    if (ref) return Response.redirect(new URL(ref, request.url), 302);
                    return Response.redirect(
                        new URL("/team", request.url),
                        302,
                    );
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
                return Response.redirect(
                    new URL("/ecosystem", request.url),
                    302,
                );
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
                    return Response.redirect(
                        new URL("/ecosystem", request.url),
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
                    return Response.redirect(
                        new URL("/ecosystem", request.url),
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
                        `<div id="lead-show-avatar" class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs">${initialLetter}</div>`,
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
        } else if (path === "/leads") {
            const viewMode = url.searchParams.get("view");
            html = viewMode === "kanban" ? PAGES.kanban : PAGES.leads;
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
        } else if (path === "/contacts") {
            html = PAGES.contacts;
        } else if (
            path === "/binary" ||
            path.startsWith("/binary") ||
            path === "/team" ||
            path.startsWith("/team")
        ) {
            const viewMode = url.searchParams.get("view");
            html =
                viewMode === "table"
                    ? PAGES.binary_table || PAGES.binary
                    : PAGES.binary;
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

            const syncScript = `
<script id="sbl-live-d1-sync">
(function() {
  const DATA = ${JSON.stringify(syncDataPayload)};

  function syncEntityDropdowns() {
    if (!DATA) return;

    // A. Sync Lead Selects (<select name="lead_id">)
    document.querySelectorAll('select[name="lead_id"]').forEach(function(select) {
      if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
        DATA.deletedLeads.forEach(function(delId) {
          const opt = select.querySelector('option[value="' + delId + '"]');
          if (opt) opt.remove();
        });
      }
      if (DATA.leads && DATA.leads.length > 0) {
        DATA.leads.forEach(function(lead) {
          let opt = select.querySelector('option[value="' + lead.id + '"]');
          const stageLabel = (lead.stage || 'new').replace('_', ' ').toUpperCase();
          const text = lead.name + ' (' + lead.mobile + ') - ' + stageLabel;
          if (!opt) {
            opt = document.createElement('option');
            opt.value = lead.id;
            opt.setAttribute('data-lead-id', lead.id);
            opt.textContent = text;
            select.appendChild(opt);
          } else {
            opt.textContent = text;
          }
        });
      }
    });

    // B. Sync Related Lead Selects (<select name="related_lead_id">)
    document.querySelectorAll('select[name="related_lead_id"]').forEach(function(select) {
      if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
        DATA.deletedLeads.forEach(function(delId) {
          const opt = select.querySelector('option[value="' + delId + '"]');
          if (opt) opt.remove();
        });
      }
      if (DATA.leads && DATA.leads.length > 0) {
        DATA.leads.forEach(function(lead) {
          let opt = select.querySelector('option[value="' + lead.id + '"]');
          const text = lead.name + ' (' + lead.mobile + ')';
          if (!opt) {
            opt = document.createElement('option');
            opt.value = lead.id;
            opt.setAttribute('data-lead-id', lead.id);
            opt.textContent = text;
            select.appendChild(opt);
          } else {
            opt.textContent = text;
          }
        });
      }
    });

    // C. Sync User Selects (<select name="user_id">)
    document.querySelectorAll('select[name="user_id"]').forEach(function(select) {
      if (DATA.deletedUsers && DATA.deletedUsers.length > 0) {
        DATA.deletedUsers.forEach(function(delId) {
          const opt = select.querySelector('option[value="' + delId + '"]');
          if (opt) opt.remove();
        });
      }
      if (DATA.users && DATA.users.length > 0) {
        DATA.users.forEach(function(user) {
          let opt = select.querySelector('option[value="' + user.id + '"]');
          const text = user.name + (user.email ? ' (' + user.email + ')' : '');
          if (!opt) {
            opt = document.createElement('option');
            opt.value = user.id;
            opt.setAttribute('data-user-id', user.id);
            opt.textContent = text;
            select.appendChild(opt);
          }
        });
      }
    });

    // D. Sync Sponsor Selects (<select name="sponsor_id">)
    document.querySelectorAll('select[name="sponsor_id"]').forEach(function(select) {
      if (DATA.deletedNodes && DATA.deletedNodes.length > 0) {
        DATA.deletedNodes.forEach(function(delId) {
          const opt = select.querySelector('option[value="' + delId + '"]');
          if (opt) opt.remove();
        });
      }
      if (DATA.nodes && DATA.nodes.length > 0) {
        DATA.nodes.forEach(function(node) {
          let opt = select.querySelector('option[value="' + node.id + '"]');
          const text = node.member_name + ' (' + (node.member_code || ('SBL-' + node.id)) + ')';
          if (!opt) {
            opt = document.createElement('option');
            opt.value = node.id;
            opt.setAttribute('data-node-id', node.id);
            opt.textContent = text;
            select.appendChild(opt);
          }
        });
      }
    });
  }

  function runSync() {
    const curPath = window.location.pathname;

    // Run Universal Dropdowns Sync across all pages
    syncEntityDropdowns();

    // 1. DASHBOARD SYNC - ONLY on / or /dashboard!
    if (curPath === '/' || curPath === '/dashboard') {
      if (DATA.leads) {
        const totalLeads = DATA.leads.length;
        const totalEl = document.querySelector('[data-metric="total-leads"]');
        if (totalEl) totalEl.textContent = totalLeads;

        // Stage Funnel Counters
        const stages = ['new', 'contacted', 'qualified', 'presentation', 'interested', 'negotiation', 'converted', 'lost'];
        stages.forEach(function(s) {
          const count = DATA.leads.filter(function(l) { return (l.stage || 'new') === s; }).length;
          const el = document.querySelector('[data-funnel-count="' + s + '"]');
          if (el) el.textContent = count;
        });

        // Follow-ups & Overdue Calculations
        const now = new Date();
        const todayStr = now.toISOString().slice(0, 10);

        let overdueCount = 0;
        let followupsToday = 0;

        DATA.leads.forEach(function(lead) {
          if (lead.next_action_at) {
            const actDate = lead.next_action_at.slice(0, 10);
            if (actDate === todayStr) followupsToday++;
            if (new Date(lead.next_action_at) < now && (lead.stage !== 'converted' && lead.stage !== 'lost')) {
              overdueCount++;
            }
          }
        });

        if (DATA.tasks) {
          DATA.tasks.forEach(function(task) {
            if (task.status !== 'Completed' && task.due_at) {
              const dStr = task.due_at.slice(0, 10);
              if (dStr === todayStr) followupsToday++;
              if (new Date(task.due_at) < now) overdueCount++;
            }
          });
        }

        const dueEl = document.querySelector('[data-metric="followups-today"]');
        if (dueEl) dueEl.textContent = followupsToday;

        const overEl = document.querySelector('[data-metric="overdue-followups"]');
        if (overEl) overEl.textContent = overdueCount;

        // Presentations Today
        let presTodayCount = 0;
        if (DATA.presentations) {
          DATA.presentations.forEach(function(p) {
            if (p.date_time && p.date_time.slice(0, 10) === todayStr) presTodayCount++;
          });
        }
        const presEl = document.querySelector('[data-metric="presentations-today"]');
        if (presEl) presEl.textContent = presTodayCount;
      }
    }

    // 2. REPORTS SYNC - ONLY on /reports!
    if (curPath === '/reports' || curPath.startsWith('/reports?')) {
      if (DATA.leads && DATA.leads.length > 0) {
        const total = DATA.leads.length;
        const converted = DATA.leads.filter(function(l) { return l.stage === 'converted'; }).length;
        const rate = total > 0 ? Math.round((converted / total) * 100) : 0;

        const totEl = document.querySelector('[data-report-metric="total-leads"]');
        if (totEl) totEl.textContent = total;

        const convEl = document.querySelector('[data-report-metric="converted-leads"]');
        if (convEl) convEl.textContent = converted;

        const rateEl = document.querySelector('[data-report-metric="conversion-rate"]');
        if (rateEl) rateEl.textContent = rate + '%';

        // Update Funnel Breakdown
        const stages = ['new', 'contacted', 'qualified', 'presentation', 'interested', 'negotiation', 'converted', 'lost'];
        stages.forEach(function(s) {
          const count = DATA.leads.filter(function(l) { return (l.stage || 'new') === s; }).length;
          const pct = total > 0 ? Math.round((count / total) * 100) : 0;
          const txt = document.querySelector('[data-report-stage-text="' + s + '"]');
          if (txt) txt.textContent = count + ' leads (' + pct + '%)';
          const bar = document.querySelector('[data-report-stage-bar="' + s + '"]');
          if (bar) bar.style.width = pct + '%';
        });
      }
    }

    // 3. LEADS LIST SYNC - ONLY on /leads!
    if (curPath === '/leads' || curPath.startsWith('/leads?')) {
      if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
        DATA.deletedLeads.forEach(function(id) {
          document.querySelectorAll('[data-lead-id="' + id + '"]').forEach(function(el) { el.remove(); });
        });
      }

      if (DATA.leads && DATA.leads.length > 0) {
        const tableBody = document.querySelector('tbody.divide-y');
        const mobileStack = document.querySelector('div.divide-y[class*="md:hidden"]');

        DATA.leads.slice().reverse().forEach(function(lead) {
          let interests = [];
          try {
            interests = typeof lead.interest_types === 'string' ? JSON.parse(lead.interest_types) : (lead.interest_types || []);
          } catch(e) {}

          const sourceName = DATA.sources[lead.lead_source_id] || 'Direct';
          const initialLetter = (lead.name || 'L').charAt(0).toUpperCase();
          const stageLabel = (lead.stage || 'new').replace('_', ' ').toUpperCase();
          const cleanWhatsapp = (lead.whatsapp || lead.mobile || '').replace(/[^0-9]/g, '');

          const existingRow = document.querySelector('tr[data-lead-id="' + lead.id + '"]');
          if (tableBody) {
            const rowHtml = '<td class="py-3.5 px-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0">' + initialLetter + '</div><div><a href="/leads/' + lead.id + '" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">' + escapeHtml(lead.name) + '</a><div class="text-[11px] text-slate-400">' + escapeHtml(lead.location || 'No location') + (lead.profession_or_business ? ' • ' + escapeHtml(lead.profession_or_business) : '') + '</div></div></div></td><td class="py-3.5 px-4"><div class="font-medium text-slate-800">' + escapeHtml(lead.mobile) + '</div><div class="flex items-center gap-2 mt-1"><a href="tel:' + escapeHtml(lead.mobile) + '" title="Call" class="text-slate-400 hover:text-emerald-600 text-sm">📞</a><a href="https://wa.me/' + cleanWhatsapp + '" target="_blank" title="WhatsApp" class="text-slate-400 hover:text-emerald-600 text-sm">💬</a></div></td><td class="py-3.5 px-4"><span class="font-medium text-slate-800 block">' + escapeHtml(sourceName) + '</span><div class="flex flex-wrap gap-1 mt-1">' + interests.map(function(i) { return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-200/80">' + escapeHtml(i) + '</span>'; }).join('') + '</div></td><td class="py-3.5 px-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">' + stageLabel + '</span></td><td class="py-3.5 px-4"><div class="flex items-center gap-1.5"><span class="font-bold text-xs text-orange-600">' + (lead.score || 25) + '</span><span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-800">' + (lead.temperature || 'warm') + '</span></div></td><td class="py-3.5 px-4">' + (lead.next_action_at ? ('<div class="text-xs font-medium text-slate-700">' + escapeHtml(lead.next_action_type || 'Action') + '<br><span class="text-[11px] text-slate-400">' + escapeHtml(lead.next_action_at) + '</span></div>') : '<span class="text-[11px] text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-md">Needs Next Action</span>') + '</td><td class="py-3.5 px-4 text-right"><div class="flex items-center justify-end gap-1.5"><a href="/leads/' + lead.id + '" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs transition-colors">View</a><a href="/leads/' + lead.id + '/edit" class="px-2.5 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 font-semibold text-xs transition-colors">Edit</a><form action="/leads/' + lead.id + '" method="POST" onsubmit="return confirm(&quot;Are you sure you want to delete this lead?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs transition-colors">Delete</button></form></div></td>';
            if (existingRow) {
              existingRow.innerHTML = rowHtml;
            } else {
              const tr = document.createElement('tr');
              tr.setAttribute('data-lead-id', lead.id);
              tr.className = 'hover:bg-slate-50/70 transition-colors bg-orange-50/20';
              tr.innerHTML = rowHtml;
              tableBody.prepend(tr);
            }
          }

          const existingMobileCard = document.querySelector('div[data-lead-id="' + lead.id + '"]');
          if (mobileStack) {
            const cardHtml = '<div class="flex items-start justify-between gap-2"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-xs">' + initialLetter + '</div><div><a href="/leads/' + lead.id + '" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">' + escapeHtml(lead.name) + '</a><div class="text-xs text-slate-500">' + escapeHtml(lead.mobile) + '</div></div></div><span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-orange-700 border border-orange-200">' + stageLabel + '</span></div><div class="flex flex-wrap items-center gap-1 text-xs text-slate-500 pt-1"><span>' + escapeHtml(sourceName) + '</span>' + interests.map(function(i) { return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-200/80">' + escapeHtml(i) + '</span>'; }).join('') + '</div><div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs"><div class="flex items-center gap-2"><a href="tel:' + escapeHtml(lead.mobile) + '" class="text-emerald-600 font-semibold">📞 Call</a><a href="https://wa.me/' + cleanWhatsapp + '" target="_blank" class="text-emerald-600 font-semibold">💬 WhatsApp</a></div><div class="flex items-center gap-1.5"><a href="/leads/' + lead.id + '" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-semibold text-xs">View</a><a href="/leads/' + lead.id + '/edit" class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-semibold text-xs">Edit</a><form action="/leads/' + lead.id + '" method="POST" onsubmit="return confirm(&quot;Are you sure you want to delete this lead?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold text-xs">Delete</button></form></div></div>';
            if (existingMobileCard) {
              existingMobileCard.innerHTML = cardHtml;
            } else {
              const card = document.createElement('div');
              card.setAttribute('data-lead-id', lead.id);
              card.className = 'p-4 space-y-3 hover:bg-slate-50/50 transition-colors bg-orange-50/20';
              card.innerHTML = cardHtml;
              mobileStack.prepend(card);
            }
          }

          const kanbanCol = document.querySelector('.kanban-cards-container[data-stage="' + (lead.stage || 'new') + '"]');
          if (kanbanCol && !document.querySelector('.kanban-card[data-lead-id="' + lead.id + '"]')) {
            const kCard = document.createElement('div');
            kCard.setAttribute('data-lead-id', lead.id);
            kCard.className = 'bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-xs hover:border-orange-300 hover:shadow-md transition-all cursor-grab active:cursor-grabbing kanban-card bg-orange-50/20';
            kCard.innerHTML = '<div class="flex items-start justify-between gap-2"><a href="/leads/' + lead.id + '" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate block">' + escapeHtml(lead.name) + '</a><span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md border flex-shrink-0 bg-orange-100 text-orange-800 border-orange-200">' + (lead.temperature || 'warm') + '</span></div><div class="text-xs text-slate-600 mt-1 flex items-center justify-between"><span>' + escapeHtml(lead.mobile) + '</span><span class="text-[11px] text-slate-400">' + escapeHtml(sourceName) + '</span></div><div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs"><span class="font-bold text-orange-600">' + (lead.score || 25) + ' pts</span><div class="flex items-center gap-1"><a href="/leads/' + lead.id + '" class="p-1 text-slate-400 hover:text-slate-700" title="View">👁️</a><a href="/leads/' + lead.id + '/edit" class="p-1 text-slate-400 hover:text-orange-600" title="Edit">✏️</a></div></div>';
            kanbanCol.prepend(kCard);
          }
        });
      }
    }

    // 3b. LEAD DETAILS PAGE SYNC - ONLY on /leads/:id!
    const leadShowMatch = curPath.match(/^\/leads\/(\d+)$/);
    if (leadShowMatch) {
      const showLeadId = parseInt(leadShowMatch[1], 10);
      const lead = DATA.leads ? DATA.leads.find(function(l) { return Number(l.id) === showLeadId; }) : null;
      if (lead) {
        const stageLabel = (lead.stage || 'new').replace('_', ' ').toUpperCase();
        const initialLetter = (lead.name || 'L').charAt(0).toUpperCase();
        const sourceName = DATA.sources[lead.lead_source_id] || 'Direct';
        const score = lead.score || 25;
        const temp = (lead.temperature || 'warm').toUpperCase();
        const cleanWhatsapp = (lead.whatsapp || lead.mobile || '').replace(/[^0-9]/g, '');

        document.title = lead.name + ' - SBL Growth Manager';

        const pageTitleEl = document.getElementById('app-page-title');
        if (pageTitleEl) pageTitleEl.textContent = lead.name;

        const nameEl = document.getElementById('lead-show-name');
        if (nameEl) nameEl.textContent = lead.name;

        const avatarEl = document.getElementById('lead-show-avatar');
        if (avatarEl) avatarEl.textContent = initialLetter;

        const stageBadge = document.getElementById('lead-show-stage-badge');
        if (stageBadge) stageBadge.textContent = stageLabel;

        const tempBadge = document.getElementById('lead-show-temp-badge');
        if (tempBadge) tempBadge.textContent = temp;

        const mobileBtn = document.getElementById('lead-show-mobile-btn');
        if (mobileBtn) {
          mobileBtn.href = 'tel:' + lead.mobile;
          const mobTxt = document.getElementById('lead-show-mobile-text');
          if (mobTxt) mobTxt.textContent = lead.mobile;
        }

        const waBtn = document.getElementById('lead-show-wa-btn');
        if (waBtn) waBtn.href = 'https://wa.me/' + cleanWhatsapp;

        const locBox = document.getElementById('lead-show-location-container');
        const locTxt = document.getElementById('lead-show-location-text');
        if (locBox && locTxt) {
          if (lead.location) {
            locTxt.textContent = lead.location;
            locBox.style.display = '';
          } else {
            locBox.style.display = 'none';
          }
        }

        const scoreTxt = document.getElementById('lead-show-score-text');
        if (scoreTxt) scoreTxt.textContent = score + ' / 100';

        const scoreBar = document.getElementById('lead-show-score-bar');
        if (scoreBar) scoreBar.style.width = score + '%';

        const sourceTxt = document.getElementById('lead-show-source-text');
        if (sourceTxt) sourceTxt.textContent = sourceName;

        const nextActionEl = document.getElementById('lead-show-next-action-text');
        if (nextActionEl) {
          if (lead.next_action_at) {
            nextActionEl.textContent = (lead.next_action_type || 'Action') + ' (' + lead.next_action_at + ')';
            nextActionEl.className = 'mt-0.5 text-slate-800 font-semibold';
          } else {
            nextActionEl.textContent = 'Needs Next Action';
            nextActionEl.className = 'text-amber-600 font-semibold text-xs mt-0.5 block';
          }
        }

        const intList = document.getElementById('lead-show-interests-list');
        if (intList) {
          let interests = [];
          try {
            interests = typeof lead.interest_types === 'string' ? JSON.parse(lead.interest_types) : (lead.interest_types || []);
          } catch(e) {}
          if (interests.length > 0) {
            intList.innerHTML = interests.map(function(i) {
              return '<span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-medium">' + escapeHtml(i) + '</span>';
            }).join('');
          } else {
            intList.innerHTML = '<span class="text-slate-400">None specified</span>';
          }
        }

        const stageSelect = document.getElementById('lead-show-stage-select');
        if (stageSelect) stageSelect.value = lead.stage || 'new';

        const editLink = document.getElementById('lead-show-edit-link');
        if (editLink) editLink.href = '/leads/' + lead.id + '/edit';

        const stageForm = document.getElementById('lead-show-stage-form');
        if (stageForm) stageForm.action = '/leads/' + lead.id + '/stage';

        const convertForm = document.getElementById('lead-show-convert-form');
        if (convertForm) convertForm.action = '/leads/' + lead.id + '/convert';

        const deleteForm = document.getElementById('lead-show-delete-form');
        if (deleteForm) deleteForm.action = '/leads/' + lead.id;

        const actForm = document.getElementById('lead-show-activity-form');
        if (actForm) actForm.action = '/leads/' + lead.id + '/activities';

        // Timeline activities
        const timelineContainer = document.getElementById('lead-show-timeline-container');
        if (timelineContainer && DATA.activities) {
          const leadActivities = DATA.activities.filter(function(a) { return Number(a.lead_id) === Number(lead.id); });
          const countEl = document.getElementById('lead-activities-count');
          if (countEl) countEl.textContent = leadActivities.length + ' activities';

          if (leadActivities.length > 0) {
            timelineContainer.innerHTML = leadActivities.map(function(act) {
              let dotBg = 'bg-slate-500';
              if (act.type === 'conversion') dotBg = 'bg-emerald-500';
              else if (act.type === 'stage_change') dotBg = 'bg-blue-500';
              else if (act.type === 'call') dotBg = 'bg-orange-500';
              else if (act.type === 'presentation') dotBg = 'bg-purple-500';

              return '<div class="relative"><div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-white ' + dotBg + '"></div><div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 text-xs"><div class="flex items-center justify-between gap-2"><span class="font-bold text-slate-900 text-sm">' + escapeHtml(act.title) + '</span><span class="text-[11px] text-slate-400">' + escapeHtml(act.performed_at || '') + '</span></div>' + (act.description ? ('<p class="text-slate-600 mt-1 leading-relaxed">' + escapeHtml(act.description) + '</p>') : '') + '<div class="text-[10px] text-slate-400 mt-2">Logged by ' + escapeHtml(act.user_name || 'System') + '</div></div></div>';
            }).join('');
          } else {
            timelineContainer.innerHTML = '<div class="text-center py-8 text-slate-400 text-xs">No activity logged yet. Use the Quick Action bar above to log your first call or note.</div>';
          }
        }

        // Associated tasks
        const tasksContainer = document.getElementById('lead-show-tasks-container');
        if (tasksContainer && DATA.tasks) {
          const leadTasks = DATA.tasks.filter(function(t) { return Number(t.related_lead_id) === Number(lead.id); });
          const tCount = document.getElementById('lead-tasks-count');
          if (tCount) tCount.textContent = leadTasks.length + ' total';

          if (leadTasks.length > 0) {
            tasksContainer.innerHTML = leadTasks.map(function(task) {
              const isCompleted = task.status === 'Completed';
              return '<div class="p-3 rounded-xl border border-slate-100 text-xs hover:bg-slate-50 transition-colors"><div class="flex items-center justify-between"><span class="font-bold text-slate-800">' + escapeHtml(task.title) + '</span><span class="px-2 py-0.5 rounded text-[10px] font-semibold ' + (isCompleted ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700') + '">' + escapeHtml(task.status || 'Pending') + '</span></div><div class="text-slate-500 mt-1 flex items-center justify-between text-[11px]"><span>Due: ' + escapeHtml(task.due_at || '') + '</span><span class="font-semibold text-slate-700">' + escapeHtml(task.priority || 'Medium') + '</span></div></div>';
            }).join('');
          } else {
            tasksContainer.innerHTML = '<div class="text-center py-4 text-slate-400 text-xs">No pending tasks.</div>';
          }
        }

        // Associated presentations
        const presContainer = document.getElementById('lead-show-presentations-container');
        if (presContainer && DATA.presentations) {
          const leadPres = DATA.presentations.filter(function(p) { return Number(p.lead_id) === Number(lead.id); });
          const pCount = document.getElementById('lead-presentations-count');
          if (pCount) pCount.textContent = leadPres.length + ' sessions';

          if (leadPres.length > 0) {
            presContainer.innerHTML = leadPres.map(function(pres) {
              return '<div class="p-3 rounded-xl border border-purple-100 bg-purple-50/20 text-xs"><div class="flex items-center justify-between"><span class="font-bold text-purple-900">' + escapeHtml(pres.type || '1-on-1') + ' Session</span>' + (pres.outcome ? ('<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">' + escapeHtml(pres.outcome) + '</span>') : '') + '</div><div class="text-slate-600 mt-1">' + escapeHtml(pres.topic || 'SBL Ecosystem Presentation') + '</div><div class="text-slate-400 text-[11px] mt-1">' + escapeHtml(pres.date_time || '') + '</div></div>';
            }).join('');
          } else {
            presContainer.innerHTML = '<div class="text-center py-4 text-slate-400 text-xs">No presentations scheduled yet.</div>';
          }
        }
      }
    }

    // 3c. LEAD EDIT PAGE SYNC - ONLY on /leads/:id/edit!
    const leadEditMatch = curPath.match(/^\/leads\/(\d+)\/edit$/);
    if (leadEditMatch) {
      const editLeadId = parseInt(leadEditMatch[1], 10);
      const lead = DATA.leads ? DATA.leads.find(function(l) { return Number(l.id) === editLeadId; }) : null;
      if (lead) {
        document.title = 'Edit Lead: ' + lead.name + ' - SBL Growth Manager';

        const pageTitleEl = document.getElementById('app-page-title');
        if (pageTitleEl) pageTitleEl.textContent = 'Edit Lead: ' + lead.name;

        const titleEl = document.getElementById('lead-edit-title') || document.querySelector('h2.text-base.font-bold');
        if (titleEl) titleEl.textContent = 'Edit Lead: ' + lead.name;

        const editForm = document.getElementById('lead-edit-form') || document.querySelector('form[action*="/leads/"]');
        if (editForm) editForm.action = '/leads/' + lead.id;

        const backLink = document.getElementById('lead-edit-back-link');
        if (backLink) backLink.href = '/leads/' + lead.id;

        const cancelLink = document.getElementById('lead-edit-cancel-link');
        if (cancelLink) cancelLink.href = '/leads/' + lead.id;

        const delForm = document.getElementById('lead-edit-delete-form') || document.getElementById('delete-lead-form-' + lead.id);
        if (delForm) delForm.action = '/leads/' + lead.id;

        // Alpine x-data update
        const xDataContainer = document.querySelector('[x-data]');
        if (xDataContainer && xDataContainer._x_dataStack && xDataContainer._x_dataStack[0]) {
          xDataContainer._x_dataStack[0].name = lead.name || '';
          xDataContainer._x_dataStack[0].mobile = lead.mobile || '';
          xDataContainer._x_dataStack[0].whatsapp = lead.whatsapp || lead.mobile || '';
        }

        const nameInput = document.querySelector('input[name="name"]');
        if (nameInput) nameInput.value = lead.name || '';

        const mobileInput = document.querySelector('input[name="mobile"]');
        if (mobileInput) mobileInput.value = lead.mobile || '';

        const waInput = document.querySelector('input[name="whatsapp"]');
        if (waInput) waInput.value = lead.whatsapp || lead.mobile || '';

        const emailInput = document.querySelector('input[name="email"]');
        if (emailInput) emailInput.value = lead.email || '';

        const locInput = document.querySelector('input[name="location"]');
        if (locInput) locInput.value = lead.location || '';

        const profInput = document.querySelector('input[name="profession_or_business"]');
        if (profInput) profInput.value = lead.profession_or_business || '';

        const notesInput = document.querySelector('textarea[name="notes"]');
        if (notesInput) notesInput.value = lead.notes || '';

        const sourceSelect = document.querySelector('select[name="lead_source_id"]');
        if (sourceSelect) sourceSelect.value = String(lead.lead_source_id || 1);

        const stageSelect = document.querySelector('select[name="stage"]');
        if (stageSelect) stageSelect.value = lead.stage || 'new';

        let interests = [];
        try {
          interests = typeof lead.interest_types === 'string' ? JSON.parse(lead.interest_types) : (lead.interest_types || []);
        } catch(e) {}
        document.querySelectorAll('input[name="interest_types[]"]').forEach(function(chk) {
          chk.checked = interests.includes(chk.value);
        });
      }
    }

    // 4. TEAM & USERS SYNC - ONLY on /users!
    if (curPath === '/users' || curPath.startsWith('/users?')) {
      if (DATA.deletedUsers && DATA.deletedUsers.length > 0) {
        DATA.deletedUsers.forEach(function(id) {
          document.querySelectorAll('[data-user-id="' + id + '"]').forEach(function(el) { el.remove(); });
        });
      }

      if (DATA.users && DATA.users.length > 0) {
        const userTableBody = document.querySelector('tbody.divide-y');
        const userMobileContainer = document.querySelector('div.space-y-3[class*="md:hidden"]');

        DATA.users.forEach(function(user) {
          if (document.querySelector('[data-user-id="' + user.id + '"]')) return;

          const initialLetter = (user.name || 'U').charAt(0).toUpperCase();
          const roleLabel = user.role_name || 'Staff Member';

          if (userTableBody) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-user-id', user.id);
            tr.className = 'hover:bg-slate-50/60 transition-colors bg-orange-50/20';
            tr.innerHTML = '<td class="py-3 px-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">' + initialLetter + '</div><div><div class="font-semibold text-slate-900 flex items-center gap-2"><span>' + user.name + '</span></div><div class="text-xs text-slate-400">' + user.email + '</div></div></div></td><td class="py-3 px-4"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-purple-100 text-purple-800 border-purple-200">' + roleLabel + '</span></td><td class="py-3 px-4"><span class="text-slate-700 font-medium">' + (user.designation || 'Staff Member') + '</span></td><td class="py-3 px-4 text-xs">' + (user.phone ? ('<span>📞 ' + user.phone + '</span>') : '<span class="text-slate-400 italic">No phone set</span>') + '</td><td class="py-3 px-4 text-center"><div class="inline-flex items-center gap-2 text-xs"><span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md">👥 0</span><span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md">✅ 0</span></div></td><td class="py-3 px-4 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span></td><td class="py-3 px-4 text-right space-x-2"><button type="button" onclick="window.Alpine && window.Alpine.raw ? (function(){ var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].editingUser = { id: ' + user.id + ', name: \'' + user.name.replace(/'/g, "\\\'") + '\', email: \'' + user.email + '\', phone: \'' + (user.phone || '') + '\', designation: \'' + (user.designation || '') + '\', role_id: \'' + (user.role_id || '') + '\', status: \'active\' }; c._x_dataStack[0].editModalOpen = true; } })() : null" class="text-orange-600 hover:text-orange-800 font-semibold text-xs px-2 py-1 rounded hover:bg-orange-50 transition-colors">Edit</button><form action="/users/' + user.id + '" method="POST" class="inline" onsubmit="return confirm(&quot;Are you sure you want to delete member?&quot;);"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold text-xs px-2 py-1 rounded hover:bg-rose-50 transition-colors">Delete</button></form></td>';
            userTableBody.appendChild(tr);
          }

          if (userMobileContainer) {
            const mCard = document.createElement('div');
            mCard.setAttribute('data-user-id', user.id);
            mCard.className = 'bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3 bg-orange-50/20';
            mCard.innerHTML = '<div class="flex items-start justify-between gap-3"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700">' + initialLetter + '</div><div><div class="font-bold text-slate-900 text-sm flex items-center gap-1.5"><span>' + user.name + '</span></div><div class="text-xs text-slate-500">' + (user.designation || 'Staff Member') + '</div><div class="text-[11px] text-slate-400">' + user.email + '</div></div></div><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800">' + roleLabel + '</span></div><div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600"><div>' + (user.phone ? ('📞 ' + user.phone) : 'Active') + '</div><div><form action="/users/' + user.id + '" method="POST" onsubmit="return confirm(&quot;Delete?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="text-rose-600 font-semibold text-xs">Delete</button></form></div></div>';
            userMobileContainer.appendChild(mCard);
          }
        });
      }
    }

    // 5. BINARY TREE & DIRECTORY SYNC - ON /binary & /team!
    if (curPath === '/binary' || curPath.startsWith('/binary?') || curPath.startsWith('/binary/') || curPath === '/team' || curPath.startsWith('/team?') || curPath.startsWith('/team/')) {
      if (DATA.deletedNodes && DATA.deletedNodes.length > 0) {
        DATA.deletedNodes.forEach(function(id) {
          document.querySelectorAll('[data-node-id="' + id + '"]').forEach(function(el) { el.remove(); });
        });
      }

      if (DATA.nodes && DATA.nodes.length > 0) {
        const nodeMap = {};
        DATA.nodes.forEach(function(n) { nodeMap[n.id] = n; });

        // A. Sync Visual Tree Cards on Genealogy / Team Explorer Canvas
        DATA.nodes.forEach(function(node) {
          const cardEl = document.querySelector('div[data-node-id="' + node.id + '"]');
          if (cardEl) {
            let contributionsArr = [];
            try {
              contributionsArr = typeof node.contributions === 'string' ? JSON.parse(node.contributions) : (node.contributions || []);
            } catch(e) {}
            let pv = Number(node.point_value) || 0;
            if (Array.isArray(contributionsArr) && contributionsArr.length > 0) {
              pv = contributionsArr.reduce((sum, c) => sum + (Number(c.amount) || 0), 0);
            }
            const leftCount = Number(node.left_count) || 0;
            const rightCount = Number(node.right_count) || 0;
            const code = node.member_code || ('SBL-' + node.id);
            const username = node.username || (code.startsWith('@') ? code : ('@' + code.replace(/[^a-zA-Z0-9_]/g, '').toLowerCase()));
            const email = node.email || ('member' + node.id + '@gmail.com');
            const phone = node.phone || '';
            const password = node.password_plain || 'sbl123456';
            const tpin = node.tpin || '1234';
            const sponsorName = node.sponsor_name || (node.sponsor_id && nodeMap[node.sponsor_id] ? nodeMap[node.sponsor_id].member_name : (node.parent_id && nodeMap[node.parent_id] ? nodeMap[node.parent_id].member_name : 'Md. Samim'));

            // 1. Member Full Name
            const nameEl = cardEl.querySelector('h4') || cardEl.querySelector('h3 span') || cardEl.querySelector('h2');
            if (nameEl) nameEl.textContent = node.member_name;

            // 2. Member Username & Phone
            const userSpan = cardEl.querySelector('.font-mono span');
            if (userSpan) userSpan.textContent = username;

            // 3. Rank badge
            const rankBadge = cardEl.querySelector('span.rounded-full:not(.uppercase)');
            if (rankBadge) rankBadge.textContent = node.rank_name || 'Member';

            // 4. Currency Formatter Helper
            const activeCurr = localStorage.getItem('sbl_currency') || 'BDT';
            const fmtMoney = function(numVal) {
              const num = parseFloat(numVal) || 0;
              if (activeCurr === 'USD') {
                return '$' + Math.round(num / 120).toLocaleString();
              }
              return '৳ ' + Math.round(num).toLocaleString();
            };

            // 5. Total Investment / Point Value
            const invEl = cardEl.querySelector('.text-amber-300 span') || cardEl.querySelector('.text-amber-300');
            if (invEl) invEl.textContent = fmtMoney(pv);

            // 6. Direct Team counts
            const directTotalEl = cardEl.querySelector('div.font-black.text-white');
            if (directTotalEl) {
              directTotalEl.innerHTML = '<span>' + (leftCount + rightCount) + '/10</span><span class="text-[10px] text-slate-400 font-normal">(' + leftCount + 'L | ' + rightCount + 'R)</span>';
            }

            // 7. Rebind node object with fresh data
            const updatedNodeObj = {
              id: node.id,
              member_name: node.member_name,
              member_code: node.member_code,
              username: username,
              phone: phone,
              email: email,
              password_plain: password,
              tpin: tpin,
              sponsor_id: node.sponsor_id,
              sponsor_name: sponsorName,
              user_id: node.user_id,
              is_active: Boolean(node.is_active),
              is_target: Boolean(node.is_target),
              target_date: node.target_date || '',
              target_notes: node.target_notes || '',
              package_name: node.package_name || 'National 120k',
              point_value: pv,
              total_investment: pv,
              contributions: contributionsArr,
              rank_name: node.rank_name || 'Member',
              branch: node.branch || (node.position === 'left' ? 'LEFT' : 'RIGHT'),
              slot_number: node.slot_number || 1,
              position: node.position,
              left_count: leftCount,
              right_count: rightCount,
              parent_id: node.parent_id
            };

            // Rebind click to openDetailsModal or openEditModal
            const triggerDetails = function(e) {
              e.stopPropagation();
              const container = document.querySelector('[x-data]');
              if (container && container._x_dataStack) {
                container._x_dataStack[0].openDetailsModal(updatedNodeObj);
              }
            };
            const detailsBtn = cardEl.querySelector('button[title*="বিবরণ"], button[title*="Details"]');
            if (detailsBtn) detailsBtn.onclick = triggerDetails;
            if (nameEl) nameEl.onclick = triggerDetails;
          }
        });

        // B. Sync Member Directory Table
        const binaryTableBody = document.querySelector('tbody[data-binary-table-body]');
        if (binaryTableBody) {
          DATA.nodes.forEach(function(node) {
            const existingRow = binaryTableBody.querySelector('tr[data-node-id="' + node.id + '"]');
            if (existingRow) {
              const nameDiv = existingRow.querySelector('.font-bold.text-slate-900');
              if (nameDiv) nameDiv.textContent = node.member_name;
              const codeDiv = existingRow.querySelector('.font-mono');
              if (codeDiv) codeDiv.textContent = (node.member_code || ('SBL-' + node.id)) + (node.phone ? (' • ' + node.phone) : '');
            } else {
              const tr = document.createElement('tr');
              tr.setAttribute('data-node-id', node.id);
              tr.className = 'hover:bg-slate-50/60 transition-colors bg-orange-50/20';
              const initLetter = (node.member_name || 'M').charAt(0).toUpperCase();
              tr.innerHTML = '<td class="py-3.5 px-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-800 text-orange-400 font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-xs">' + initLetter + '</div><div><div class="font-bold text-slate-900 hover:text-orange-600 transition-colors">' + escapeHtml(node.member_name) + '</div><div class="text-[11px] text-slate-400 font-mono">' + escapeHtml(node.member_code || ('SBL-' + node.id)) + '</div></div></div></td><td class="py-3.5 px-4"><span class="font-semibold text-slate-800">' + (node.parent_id ? 'Node #' + node.parent_id : 'Top Root') + '</span><span class="block text-[10px] text-slate-400 uppercase">' + (node.position || 'Root') + '</span></td><td class="py-3.5 px-4"><span class="font-semibold text-slate-800 block">' + (node.package_name || 'National 120k') + '</span><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700">' + (node.rank_name || 'Member') + '</span></td><td class="py-3.5 px-4 text-center"><span class="font-bold text-emerald-700">' + (node.left_count || 0) + '</span><div class="text-[10px] text-slate-400 font-medium">' + (node.left_bv || 0) + ' BV</div></td><td class="py-3.5 px-4 text-center"><span class="font-bold text-blue-700">' + (node.right_count || 0) + '</span><div class="text-[10px] text-slate-400 font-medium">' + (node.right_bv || 0) + ' BV</div></td><td class="py-3.5 px-4 text-center font-bold text-orange-600">' + (node.matched_pairs || 0) + '</td><td class="py-3.5 px-4 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span></td><td class="py-3.5 px-4 text-right"><form action="/binary/' + node.id + '" method="POST" onsubmit="return confirm(&quot;Delete member?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold">Delete</button></form></td>';
              binaryTableBody.prepend(tr);
            }
          });
        }
      }
    }

    // 6. CONTACTS SYNC - ONLY on /contacts!
    if (curPath === '/contacts' || curPath.startsWith('/contacts?')) {
      if (DATA.contacts && DATA.contacts.length > 0) {
        const contactsGrid = document.querySelector('div[class*="grid-cols-1"][class*="lg:grid-cols-3"]');
        if (contactsGrid) {
          DATA.contacts.forEach(function(contact) {
            if (document.querySelector('[data-contact-id="' + contact.id + '"]')) return;
            const card = document.createElement('div');
            card.setAttribute('data-contact-id', contact.id);
            card.className = 'bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-emerald-300 transition-all group';
            card.innerHTML = '<div class="space-y-4"><div class="flex items-start justify-between gap-3"><div class="flex items-center gap-3"><div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center text-2xl flex-shrink-0">' + (contact.icon || '📞') + '</div><div><h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition-colors">' + contact.department + '</h3>' + (contact.contact_person ? ('<div class="text-xs font-medium text-slate-500 mt-0.5">' + contact.contact_person + '</div>') : '') + '</div></div>' + (contact.badge ? ('<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">' + contact.badge + '</span>') : '') + '</div><div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2 text-xs"><div class="flex items-center justify-between"><span class="text-slate-500 font-medium">ফোন:</span><span class="font-bold text-slate-900">' + contact.phone + '</span></div>' + (contact.whatsapp ? ('<div class="flex items-center justify-between pt-1.5 border-t border-slate-200/60"><span class="text-emerald-600 font-bold">WhatsApp:</span><span class="font-bold text-slate-900">' + contact.whatsapp + '</span></div>') : '') + '</div></div><div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between"><a href="tel:' + contact.phone + '" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700">📞 Call</a><form action="/contacts/' + contact.id + '" method="POST" onsubmit="return confirm(&quot;ডিলিট করতে চান?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold">Delete</button></form></div>';
            contactsGrid.prepend(card);
          });
        }
      }
    }

    // 7. PRESENTATIONS SYNC - ONLY on /presentations!
    if (curPath === '/presentations' || curPath.startsWith('/presentations?')) {
      if (DATA.deletedPresentations && DATA.deletedPresentations.length > 0) {
        DATA.deletedPresentations.forEach(function(id) {
          document.querySelectorAll('[data-presentation-id="' + id + '"]').forEach(function(el) { el.remove(); });
        });
      }
      if (DATA.presentations && DATA.presentations.length > 0) {
        const presGrid = document.querySelector('div[class*="grid-cols-1"][class*="lg:grid-cols-3"]');
        const emptyNotice = document.querySelector('.empty-presentations-notice, .col-span-full');
        if (emptyNotice) {
          emptyNotice.remove();
        }
        if (presGrid) {
          DATA.presentations.slice().reverse().forEach(function(pres) {
            if (document.querySelector('[data-presentation-id="' + pres.id + '"]')) return;
            const card = document.createElement('div');
            card.setAttribute('data-presentation-id', pres.id);
            card.className = 'bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-200 transition-all flex flex-col justify-between bg-orange-50/10';

            let outcomeBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Pending</span>';
            if (pres.outcome) {
              outcomeBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">' + pres.outcome + '</span>';
            }

            let leadBox = '';
            if (pres.lead_id && (pres.lead_name || pres.lead_mobile)) {
              leadBox = '<div class="text-xs text-slate-600 mt-2 p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px] uppercase font-semibold">Lead:</span><a href="/leads/' + pres.lead_id + '" class="font-bold text-orange-600 hover:underline">' + (pres.lead_name || 'Lead #' + pres.lead_id) + '</a>' + (pres.lead_mobile ? ('<span class="text-slate-500 text-[11px] block">📞 ' + pres.lead_mobile + '</span>') : '') + '</div>';
            }

            let qaBox = '';
            if (pres.questions || pres.objections) {
              qaBox = '<div class="mt-3 space-y-1 text-xs text-slate-600">' + (pres.questions ? ('<div><strong class="text-slate-800">Q:</strong> ' + pres.questions + '</div>') : '') + (pres.objections ? ('<div><strong class="text-rose-700">Objection:</strong> ' + pres.objections + '</div>') : '') + '</div>';
            }

            const dtStr = pres.date_time ? new Date(pres.date_time).toLocaleString('en-US', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Scheduled';

            card.innerHTML = '<div><div class="flex items-center justify-between gap-2 mb-2"><span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">' + (pres.type || '1-on-1') + '</span>' + outcomeBadge + '</div><h4 class="font-bold text-sm text-slate-900 mb-1">' + (pres.topic || 'SBL Ecosystem Presentation') + '</h4>' + leadBox + qaBox + '</div><div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400"><span>' + dtStr + '</span><div class="flex items-center gap-2"><span>By ' + (pres.user_name || 'Admin') + '</span><form action="/presentations/' + pres.id + '" method="POST" onsubmit="return confirm(&quot;Delete presentation record?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1 text-slate-400 hover:text-rose-600" title="Delete Presentation">🗑️</button></form></div></div>';
            presGrid.prepend(card);
          });
        }
      }
    }

    // 8. TASKS SYNC - ONLY on /tasks!
    if (curPath === '/tasks' || curPath.startsWith('/tasks?')) {
      if (DATA.tasks && DATA.tasks.length > 0) {
        const tasksContainer = document.querySelector('div.divide-y[class*="rounded-2xl"]');
        if (tasksContainer) {
          DATA.tasks.forEach(function(task) {
            let row = document.querySelector('[data-task-id="' + task.id + '"]');
            if (!row) {
              row = document.createElement('div');
              row.setAttribute('data-task-id', task.id);
              row.className = 'p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors bg-orange-50/10';
              const dtStr = task.due_at ? new Date(task.due_at).toLocaleString('en-US', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Scheduled';
              const isCompleted = task.status === 'Completed';

              const editDataJson = JSON.stringify({
                id: task.id,
                title: task.title || '',
                type: task.type || 'Follow-up',
                priority: task.priority || 'Medium',
                related_lead_id: task.related_lead_id || '',
                due_at: task.due_at ? task.due_at.slice(0, 16) : '',
                notes: task.notes || ''
              }).replace(/"/g, '&quot;');

              let actionsHtml = '<div class="flex items-center gap-2 self-end sm:self-center">';
              if (!isCompleted) {
                actionsHtml += '<button type="button" onclick="var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].completeTaskId = ' + task.id + '; c._x_dataStack[0].completeTaskTitle = \'' + (task.title || '').replace(/'/g, "\\'") + '\'; c._x_dataStack[0].completeModal = true; }" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">✓ Complete</button>';
              }
              actionsHtml += '<button type="button" onclick="var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openEditTask(' + JSON.stringify(task).replace(/"/g, '&quot;') + '); }" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-700 font-semibold text-xs transition-colors">Edit</button>';
              actionsHtml += '<form action="/tasks/' + task.id + '" method="POST" onsubmit="return confirm(&quot;Delete task?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg" title="Delete Task">🗑️</button></form></div>';

              row.innerHTML = '<div class="flex items-start gap-3"><span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200 flex-shrink-0 mt-0.5">' + (task.priority || 'Medium') + '</span><div><div class="text-sm font-bold text-slate-900 flex items-center gap-2"><span>' + task.title + '</span><span class="px-2 py-0.5 rounded text-[10px] font-medium border ' + (isCompleted ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200') + '">' + (task.status || 'Pending') + '</span></div><div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3"><span class="font-medium text-slate-700">' + (task.type || 'Follow-up') + '</span>' + (task.related_lead_id ? ('<span>•</span><a href="/leads/' + task.related_lead_id + '" class="text-orange-600 font-semibold hover:underline">Lead: ' + (task.lead_name || '#' + task.related_lead_id) + '</a>') : '') + '<span>•</span><span>Due: ' + dtStr + '</span></div>' + (task.notes ? ('<p class="text-xs text-slate-600 mt-1.5 bg-slate-50 p-2 rounded-lg border border-slate-100 inline-block">' + task.notes + '</p>') : '') + (task.outcome ? ('<div class="mt-2 text-xs text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100 inline-flex items-center gap-1.5"><span>✓ Outcome:</span><span class="font-medium">' + task.outcome + '</span></div>') : '') + '</div></div>' + actionsHtml;
              tasksContainer.prepend(row);
            }
          });
        }
      }
    }

    // 9. MARKETING CONTENT CALENDAR SYNC - ONLY on /marketing/content-calendar!
    if (curPath === '/marketing/content-calendar' || curPath.startsWith('/marketing/content-calendar?')) {
      if (DATA.contentItems && DATA.contentItems.length > 0) {
        const calContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3, div[class*="grid-cols-1"][class*="lg:grid-cols-3"]');
        if (calContainer) {
          DATA.contentItems.forEach(function(item) {
            if (document.querySelector('[data-content-id="' + item.id + '"]')) return;
            const cCard = document.createElement('div');
            cCard.setAttribute('data-content-id', item.id);
            cCard.className = 'bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-300 transition-all flex flex-col justify-between';
            const dtStr = item.scheduled_at ? new Date(item.scheduled_at).toLocaleString('en-US', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Scheduled';

            cCard.innerHTML = '<div><div class="flex items-center justify-between gap-2 mb-2"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200">' + (item.platform || 'Facebook Profile') + '</span><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">' + (item.status || 'Planned') + '</span></div><h4 class="font-bold text-slate-900 text-sm mb-1">' + item.title + '</h4>' + (item.topic ? ('<div class="text-xs text-slate-500 mb-2">Topic: <span class="font-medium text-slate-700">' + item.topic + '</span></div>') : '') + (item.caption ? ('<p class="text-xs text-slate-600 line-clamp-3 mb-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">' + item.caption + '</p>') : '') + (item.cta ? ('<div class="text-[11px] text-orange-700 bg-orange-50/70 px-2 py-1 rounded-lg border border-orange-200/60 mb-3"><span class="font-semibold">CTA:</span> ' + item.cta + '</div>') : '') + '<div class="grid grid-cols-3 gap-2 bg-slate-50/70 p-2 rounded-xl text-center text-xs"><div><span class="text-[10px] text-slate-400 block uppercase">Reach</span><span class="font-bold text-slate-800">' + (item.reach || 0) + '</span></div><div><span class="text-[10px] text-slate-400 block uppercase">Leads</span><span class="font-bold text-orange-600">' + (item.leads_generated || 0) + '</span></div><div><span class="text-[10px] text-slate-400 block uppercase">Converted</span><span class="font-bold text-emerald-600">' + (item.conversions || 0) + '</span></div></div></div><div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400"><span>' + dtStr + '</span><div class="flex items-center gap-1.5"><button type="button" onclick="var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openEdit(' + JSON.stringify(item).replace(/"/g, '&quot;') + '); }" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-700 rounded-lg text-xs font-semibold transition-colors">Edit</button><form action="/marketing/content-calendar/' + item.id + '" method="POST" onsubmit="return confirm(&quot;Remove content item?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg" title="Delete Item">🗑️</button></form></div></div>';
            calContainer.prepend(cCard);
          });
        }
      }
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runSync);
  } else {
    runSync();
  }

  // Re-sync dropdowns whenever user clicks to open any modal
  document.addEventListener('click', function() {
    setTimeout(syncEntityDropdowns, 50);
  });
})();
</script>
`;
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
