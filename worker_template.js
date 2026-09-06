// SBL Growth Manager - Cloudflare Worker Edge Application
// Serves the exact, 100% pixel-perfect compiled Laravel Blade views and handles real D1 CRUD on the edge.

const SBL_LOGO_BASE64 = __SBL_LOGO_BASE64__;
const CSS_CONTENT = __CSS_CONTENT__;
const JS_CONTENT = __JS_CONTENT__;
const CSS_PATH = __CSS_PATH__;
const JS_PATH = __JS_PATH__;
const PAGES = __PAGES__;

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
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
          "Cache-Control": "public, max-age=31536000, immutable"
        }
      });
    }

    // 2. Compiled Vite JS
    if (path === JS_PATH) {
      return new Response(JS_CONTENT, {
        headers: {
          "Content-Type": "application/javascript; charset=utf-8",
          "Cache-Control": "public, max-age=31536000, immutable"
        }
      });
    }

    // 3. Logo & Static Images
    if (path === "/images/sbl-logo.png" || path === "/favicon.png" || path === "/apple-touch-icon.png") {
      const binaryString = atob(SBL_LOGO_BASE64);
      const len = binaryString.length;
      const bytes = new Uint8Array(len);
      for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
      }
      return new Response(bytes.buffer, {
        headers: {
          "Content-Type": "image/png",
          "Cache-Control": "public, max-age=31536000, immutable"
        }
      });
    }

    if (path === "/ping") {
      return new Response("pong", { status: 200 });
    }

    const db = env.DB || env.sbl_database;

    // 4. Handle POST, PUT, PATCH, DELETE Form Actions on Cloudflare D1
    if (request.method === "POST" || request.method === "PUT" || request.method === "PATCH" || request.method === "DELETE") {
      let effectiveMethod = request.method;
      let formData = null;
      const contentType = request.headers.get("content-type") || "";

      if (contentType.includes("form") || contentType.includes("multipart") || contentType.includes("urlencoded")) {
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

      // 4a. Leads Handlers
      if (path === "/leads" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const name = formData.get("name") || "Unnamed Lead";
            const mobile = formData.get("mobile") || "";
            const whatsapp = formData.get("whatsapp") || null;
            const email = formData.get("email") || null;
            const location = formData.get("location") || null;
            const profession = formData.get("profession_or_business") || null;
            const sourceId = Number(formData.get("lead_source_id")) || 1;
            const stage = formData.get("stage") || "new";
            const interests = formData.getAll("interest_types[]") || [];
            const interestsJson = JSON.stringify(interests);
            const nextActionType = formData.get("next_action_type") || null;
            const nextActionAt = formData.get("next_action_at") || null;
            const notes = formData.get("notes") || null;
            const score = (interests.length > 0 ? 20 : 0) + (nextActionAt ? 15 : 0);
            const temperature = score >= 50 ? "hot" : (score >= 25 ? "warm" : "cold");

            const insRes = await db.prepare(
              "INSERT INTO leads (name, mobile, whatsapp, email, location, profession_or_business, lead_source_id, interest_types, stage, temperature, score, is_manual_score, owner_user_id, next_action_type, next_action_at, last_contact_at, notes, created_at, updated_at) " +
              "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(name, mobile, whatsapp, email, location, profession, sourceId, interestsJson, stage, temperature, score, nextActionType, nextActionAt, notes).run();

            const newLeadId = insRes?.meta?.last_row_id;
            if (nextActionAt && newLeadId) {
              await db.prepare(
                "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
                "VALUES (?, ?, ?, 'Medium', ?, ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
              ).bind(nextActionType ? (nextActionType + ": " + name) : ("Follow-up: " + name), nextActionType || "Follow-up", nextActionAt, notes, newLeadId).run();
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
              await db.prepare("UPDATE leads SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?").bind(leadId).run();
            } catch (e) {
              console.error("D1 Leads delete error:", e);
            }
          }
          return Response.redirect(new URL("/leads?deleted_lead=" + leadId, request.url), 302);
        }

        if (effectiveMethod === "PUT" && leadId && formData) {
          if (db) {
            try {
              const name = formData.get("name") || "Unnamed Lead";
              const mobile = formData.get("mobile") || "";
              const whatsapp = formData.get("whatsapp") || null;
              const email = formData.get("email") || null;
              const location = formData.get("location") || null;
              const profession = formData.get("profession_or_business") || null;
              const sourceId = Number(formData.get("lead_source_id")) || 1;
              const stage = formData.get("stage") || "new";
              const interests = formData.getAll("interest_types[]") || [];
              const interestsJson = JSON.stringify(interests);
              const notes = formData.get("notes") || null;
              const score = formData.get("score") ? Number(formData.get("score")) : 30;

              await db.prepare(
                "UPDATE leads SET name = ?, mobile = ?, whatsapp = ?, email = ?, location = ?, profession_or_business = ?, lead_source_id = ?, interest_types = ?, stage = ?, notes = ?, score = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
              ).bind(name, mobile, whatsapp, email, location, profession, sourceId, interestsJson, stage, notes, score, leadId).run();
            } catch (e) {
              console.error("D1 Leads update error:", e);
            }
          }
          return Response.redirect(new URL("/leads/" + leadId, request.url), 302);
        }

        if (parts[3] === "stage" && effectiveMethod === "POST" && leadId && formData) {
          if (db) {
            try {
              const stage = formData.get("stage") || "new";
              await db.prepare("UPDATE leads SET stage = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?").bind(stage, leadId).run();
            } catch (e) {
              console.error("D1 Leads stage update error:", e);
            }
          }
          return Response.redirect(new URL("/leads", request.url), 302);
        }

        if (parts[3] === "convert" && effectiveMethod === "POST" && leadId) {
          if (db) {
            try {
              await db.prepare("UPDATE leads SET stage = 'converted', converted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?").bind(leadId).run();
            } catch (e) {
              console.error("D1 Leads convert error:", e);
            }
          }
          return Response.redirect(new URL("/leads/" + leadId, request.url), 302);
        }
      }

      // 4b. Binary Tree Handlers
      if (path === "/binary" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const memberName = formData.get("member_name") || "New Member";
            const phone = formData.get("phone") || null;
            const email = formData.get("email") || null;
            const packageName = formData.get("package_name") || "National 120k";
            const rankName = formData.get("rank_name") || "Member";
            const parentId = Number(formData.get("parent_id")) || 1;
            const position = formData.get("position") || "left";
            const pointValue = Number(formData.get("point_value")) || 100;

            await db.prepare(
              "INSERT INTO binary_nodes (member_name, phone, email, package_name, rank_name, parent_id, position, point_value, is_active, left_count, right_count, left_bv, right_bv, carry_left, carry_right, matched_pairs, created_at, updated_at) " +
              "VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 0, 0, 0, 0, 0, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(memberName, phone, email, packageName, rankName, parentId, position, pointValue).run();

            if (position === "left") {
              await db.prepare("UPDATE binary_nodes SET left_count = left_count + 1, left_bv = left_bv + ?, carry_left = carry_left + ? WHERE id = ?").bind(pointValue, pointValue, parentId).run();
            } else if (position === "right") {
              await db.prepare("UPDATE binary_nodes SET right_count = right_count + 1, right_bv = right_bv + ?, carry_right = carry_right + ? WHERE id = ?").bind(pointValue, pointValue, parentId).run();
            }
          } catch (e) {
            console.error("D1 Binary create error:", e);
          }
        }
        return Response.redirect(new URL("/binary", request.url), 302);
      }

      if (path.startsWith("/binary/")) {
        const parts = path.split("/");
        const nodeId = parseInt(parts[2], 10);
        if (effectiveMethod === "DELETE" && nodeId) {
          if (db) {
            try {
              const check = await db.prepare("SELECT count(*) as count FROM binary_nodes WHERE parent_id = ?").bind(nodeId).first();
              if (!check || check.count === 0) {
                const node = await db.prepare("SELECT * FROM binary_nodes WHERE id = ?").bind(nodeId).first();
                if (node && node.parent_id) {
                  const pv = Number(node.point_value) || 0;
                  if (node.position === "left") {
                    await db.prepare("UPDATE binary_nodes SET left_count = MAX(0, left_count - 1), left_bv = MAX(0, left_bv - ?), carry_left = MAX(0, carry_left - ?) WHERE id = ?").bind(pv, pv, node.parent_id).run();
                  } else if (node.position === "right") {
                    await db.prepare("UPDATE binary_nodes SET right_count = MAX(0, right_count - 1), right_bv = MAX(0, right_bv - ?), carry_right = MAX(0, carry_right - ?) WHERE id = ?").bind(pv, pv, node.parent_id).run();
                  }
                }
                await db.prepare("DELETE FROM binary_nodes WHERE id = ?").bind(nodeId).run();
              }
            } catch (e) {
              console.error("D1 Binary delete error:", e);
            }
          }
          return Response.redirect(new URL("/binary?deleted_node=" + nodeId, request.url), 302);
        }

        if (effectiveMethod === "PUT" && nodeId && formData) {
          if (db) {
            try {
              const memberName = formData.get("member_name");
              const phone = formData.get("phone") || null;
              const email = formData.get("email") || null;
              const packageName = formData.get("package_name");
              const rankName = formData.get("rank_name");
              const isActive = formData.has("is_active") ? 1 : 0;
              await db.prepare("UPDATE binary_nodes SET member_name = ?, phone = ?, email = ?, package_name = ?, rank_name = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                .bind(memberName, phone, email, packageName, rankName, isActive, nodeId)
                .run();
            } catch (e) {
              console.error("D1 Binary update error:", e);
            }
          }
          return Response.redirect(new URL("/binary", request.url), 302);
        }
      }

      // 4c. Contacts Handlers
      if (path === "/contacts" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const department = formData.get("department") || "General Support";
            const contactPerson = formData.get("contact_person") || null;
            const phone = formData.get("phone") || "";
            const whatsapp = formData.get("whatsapp") || null;
            const email = formData.get("email") || null;
            const availableHours = formData.get("available_hours") || "10:00 AM - 08:00 PM";
            const description = formData.get("description") || null;
            const icon = formData.get("icon") || "📞";
            const badge = formData.get("badge") || null;
            const isPrimary = formData.has("is_primary") ? 1 : 0;

            await db.prepare(
              "INSERT INTO sbl_contacts (department, contact_person, phone, whatsapp, email, available_hours, description, icon, badge, is_primary, sort_order, created_at, updated_at) " +
              "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(department, contactPerson, phone, whatsapp, email, availableHours, description, icon, badge, isPrimary).run();
          } catch (e) {
            console.error("D1 Contacts create error:", e);
          }
        }
        return Response.redirect(new URL("/contacts", request.url), 302);
      }

      if (path.startsWith("/contacts/")) {
        const parts = path.split("/");
        const contactId = parseInt(parts[2], 10);
        if (effectiveMethod === "DELETE" && contactId) {
          if (db) {
            try {
              await db.prepare("DELETE FROM sbl_contacts WHERE id = ?").bind(contactId).run();
            } catch (e) {
              console.error("D1 Contacts delete error:", e);
            }
          }
          return Response.redirect(new URL("/contacts?deleted_contact=" + contactId, request.url), 302);
        }

        if (effectiveMethod === "PUT" && contactId && formData) {
          if (db) {
            try {
              const department = formData.get("department") || "General Support";
              const contactPerson = formData.get("contact_person") || null;
              const phone = formData.get("phone") || "";
              const whatsapp = formData.get("whatsapp") || null;
              const email = formData.get("email") || null;
              const availableHours = formData.get("available_hours") || "10:00 AM - 08:00 PM";
              const description = formData.get("description") || null;
              const icon = formData.get("icon") || "📞";
              const badge = formData.get("badge") || null;
              const isPrimary = formData.has("is_primary") ? 1 : 0;

              await db.prepare(
                "UPDATE sbl_contacts SET department = ?, contact_person = ?, phone = ?, whatsapp = ?, email = ?, available_hours = ?, description = ?, icon = ?, badge = ?, is_primary = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
              ).bind(department, contactPerson, phone, whatsapp, email, availableHours, description, icon, badge, isPrimary, contactId).run();
            } catch (e) {
              console.error("D1 Contacts update error:", e);
            }
          }
          return Response.redirect(new URL("/contacts", request.url), 302);
        }
      }

      // 4d. Tasks Handlers
      if (path === "/tasks" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const title = formData.get("title") || "New Task";
            const type = formData.get("type") || "Follow-up";
            const dueAt = formData.get("due_at") || new Date().toISOString();
            const priority = formData.get("priority") || "Medium";
            const notes = formData.get("notes") || null;
            const leadId = formData.get("related_lead_id") ? Number(formData.get("related_lead_id")) : null;

            await db.prepare(
              "INSERT INTO tasks (title, type, due_at, priority, notes, related_lead_id, user_id, status, created_at, updated_at) " +
              "VALUES (?, ?, ?, ?, ?, ?, 1, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(title, type, dueAt, priority, notes, leadId).run();
          } catch (e) {
            console.error("D1 Tasks create error:", e);
          }
        }
        return Response.redirect(new URL("/tasks", request.url), 302);
      }

      if (path.startsWith("/tasks/")) {
        const parts = path.split("/");
        const taskId = parseInt(parts[2], 10);
        if (parts[3] === "complete" && effectiveMethod === "POST" && taskId) {
          if (db) {
            try {
              await db.prepare("UPDATE tasks SET status = 'Completed', completed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?").bind(taskId).run();
            } catch (e) {
              console.error("D1 Tasks complete error:", e);
            }
          }
          return Response.redirect(new URL("/tasks", request.url), 302);
        }
        if (effectiveMethod === "DELETE" && taskId) {
          if (db) {
            try {
              await db.prepare("DELETE FROM tasks WHERE id = ?").bind(taskId).run();
            } catch (e) {
              console.error("D1 Tasks delete error:", e);
            }
          }
          return Response.redirect(new URL("/tasks", request.url), 302);
        }
      }

      // 4e. Ecosystem Handlers
      if (path === "/ecosystem" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const title = formData.get("title") || "New Portal";
            const urlVal = formData.get("url") || "#";
            const category = formData.get("category") || "Official Portals";
            const badge = formData.get("badge") || null;
            const description = formData.get("description") || null;
            const icon = formData.get("icon") || "🌐";
            const isActive = formData.has("is_active") ? 1 : 0;

            await db.prepare(
              "INSERT INTO ecosystem_links (title, url, category, badge, description, icon, is_active, sort_order, created_at, updated_at) " +
              "VALUES (?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(title, urlVal, category, badge, description, icon, isActive).run();
          } catch (e) {
            console.error("D1 Ecosystem create error:", e);
          }
        }
        return Response.redirect(new URL("/ecosystem", request.url), 302);
      }

      if (path.startsWith("/ecosystem/")) {
        const parts = path.split("/");
        const linkId = parseInt(parts[2], 10);
        if (effectiveMethod === "DELETE" && linkId) {
          if (db) {
            try {
              await db.prepare("DELETE FROM ecosystem_links WHERE id = ?").bind(linkId).run();
            } catch (e) {
              console.error("D1 Ecosystem delete error:", e);
            }
          }
          return Response.redirect(new URL("/ecosystem", request.url), 302);
        }
        if (effectiveMethod === "PUT" && linkId && formData) {
          if (db) {
            try {
              const title = formData.get("title") || "New Portal";
              const urlVal = formData.get("url") || "#";
              const category = formData.get("category") || "Official Portals";
              const badge = formData.get("badge") || null;
              const description = formData.get("description") || null;
              const icon = formData.get("icon") || "🌐";
              const isActive = formData.has("is_active") ? 1 : 0;

              await db.prepare(
                "UPDATE ecosystem_links SET title = ?, url = ?, category = ?, badge = ?, description = ?, icon = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
              ).bind(title, urlVal, category, badge, description, icon, isActive, linkId).run();
            } catch (e) {
              console.error("D1 Ecosystem update error:", e);
            }
          }
          return Response.redirect(new URL("/ecosystem", request.url), 302);
        }
      }

      // 4f. Presentations Handlers
      if (path === "/presentations" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const leadId = Number(formData.get("lead_id")) || null;
            const type = formData.get("type") || "1-on-1 In-person";
            const outcome = formData.get("outcome") || "Interested";
            const notes = formData.get("notes") || null;
            const presAt = formData.get("presentation_at") || new Date().toISOString();

            await db.prepare(
              "INSERT INTO presentations (lead_id, user_id, type, presentation_at, outcome, notes, created_at, updated_at) " +
              "VALUES (?, 1, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(leadId, type, presAt, outcome, notes).run();

            if (leadId) {
              await db.prepare("UPDATE leads SET stage = 'presentation', updated_at = CURRENT_TIMESTAMP WHERE id = ?").bind(leadId).run();
            }
          } catch (e) {
            console.error("D1 Presentation error:", e);
          }
        }
        return Response.redirect(new URL("/presentations", request.url), 302);
      }

      // 4g. Content Calendar Handlers
      if (path === "/marketing/content-calendar" && effectiveMethod === "POST" && formData) {
        if (db) {
          try {
            const title = formData.get("title") || "New Post";
            const platform = formData.get("platform") || "Facebook";
            const status = formData.get("status") || "Draft";
            const scheduledAt = formData.get("scheduled_at") || new Date().toISOString();
            const copyText = formData.get("copy_text") || null;
            const mediaUrl = formData.get("media_url") || null;

            await db.prepare(
              "INSERT INTO content_items (title, platform, status, scheduled_at, copy_text, media_url, created_at, updated_at) " +
              "VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            ).bind(title, platform, status, scheduledAt, copyText, mediaUrl).run();
          } catch (e) {
            console.error("D1 Content Calendar create error:", e);
          }
        }
        return Response.redirect(new URL("/marketing/content-calendar", request.url), 302);
      }

      if (path.startsWith("/marketing/content-calendar/")) {
        const parts = path.split("/");
        const itemId = parseInt(parts[3], 10);
        if (effectiveMethod === "DELETE" && itemId) {
          if (db) {
            try {
              await db.prepare("DELETE FROM content_items WHERE id = ?").bind(itemId).run();
            } catch (e) {
              console.error("D1 Content Calendar delete error:", e);
            }
          }
          return Response.redirect(new URL("/marketing/content-calendar", request.url), 302);
        }
      }

      // Default redirect
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
    let sourcesMap = { 1: "Direct Inbound", 2: "Facebook Page", 3: "LinkedIn Outreach", 4: "Referral / Team", 5: "Website / Landing Page", 6: "Seminar / Workshop", 7: "Investor Network", 8: "Cold Calling" };

    if (db) {
      try {
        const [leadsRes, delLeadsRes, nodesRes, contactsRes, tasksRes, ecoRes, sourcesRes] = await Promise.all([
          db.prepare("SELECT * FROM leads WHERE deleted_at IS NULL ORDER BY id DESC").all(),
          db.prepare("SELECT id FROM leads WHERE deleted_at IS NOT NULL").all(),
          db.prepare("SELECT * FROM binary_nodes ORDER BY id ASC").all(),
          db.prepare("SELECT * FROM sbl_contacts ORDER BY sort_order ASC, id DESC").all(),
          db.prepare("SELECT * FROM tasks ORDER BY id DESC").all(),
          db.prepare("SELECT * FROM ecosystem_links ORDER BY sort_order ASC, id DESC").all(),
          db.prepare("SELECT id, name FROM lead_sources").all()
        ]);

        if (leadsRes?.results) liveLeads = leadsRes.results;
        if (delLeadsRes?.results) deletedLeadIds = delLeadsRes.results.map(r => Number(r.id));
        if (nodesRes?.results) liveNodes = nodesRes.results;
        if (contactsRes?.results) liveContacts = contactsRes.results;
        if (tasksRes?.results) liveTasks = tasksRes.results;
        if (ecoRes?.results) liveEcosystem = ecoRes.results;

        if (sourcesRes?.results) {
          for (const s of sourcesRes.results) {
            sourcesMap[s.id] = s.name;
          }
        }

        const existingNodeIds = new Set(liveNodes.map(r => Number(r.id)));
        const allKnownNodeIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        deletedNodeIds = allKnownNodeIds.filter(id => !existingNodeIds.has(id));
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

      // Dynamically populate lead data into edit template
      const currentLead = liveLeads.find(l => Number(l.id) === leadId);
      if (currentLead && pageHtml) {
        pageHtml = pageHtml
          .replace(/action="[^"]*\/leads\/\d+"/g, `action="/leads/${currentLead.id}"`)
          .replace(/id="delete-lead-form-\d+"/g, `id="delete-lead-form-${currentLead.id}"`)
          .replace(/value="Rafiqul Islam"/g, `value="${escapeHtml(currentLead.name)}"`)
          .replace(/value="01711001122"/g, `value="${escapeHtml(currentLead.mobile)}"`)
          .replace(/value="rafiq@example.com"/g, `value="${escapeHtml(currentLead.email || '')}"`)
          .replace(/value="Dhaka, Mirpur"/g, `value="${escapeHtml(currentLead.location || '')}"`)
          .replace(/value="Retail Shop Owner"/g, `value="${escapeHtml(currentLead.profession_or_business || '')}"`)
          .replace(/Looking to expand his retail business to online dropshipping\./g, escapeHtml(currentLead.notes || ''));
      }
      html = pageHtml;
    } else if (path.match(/^\/leads\/\d+$/)) {
      const parts = path.split("/");
      const leadId = parseInt(parts[2], 10);
      let pageHtml = PAGES.leads_show || PAGES.leads;

      // Dynamically populate lead data into show template
      const currentLead = liveLeads.find(l => Number(l.id) === leadId);
      if (currentLead && pageHtml) {
        pageHtml = pageHtml
          .replace(/action="[^"]*\/leads\/\d+\/stage"/g, `action="/leads/${currentLead.id}/stage"`)
          .replace(/action="[^"]*\/leads\/\d+\/convert"/g, `action="/leads/${currentLead.id}/convert"`)
          .replace(/action="[^"]*\/leads\/\d+"/g, `action="/leads/${currentLead.id}"`)
          .replace(/href="[^"]*\/leads\/\d+\/edit"/g, `href="/leads/${currentLead.id}/edit"`)
          .replace(/id="delete-lead-form-\d+"/g, `id="delete-lead-form-${currentLead.id}"`)
          .replace(/Rafiqul Islam/g, escapeHtml(currentLead.name))
          .replace(/01711001122/g, escapeHtml(currentLead.mobile))
          .replace(/rafiq@example\.com/g, escapeHtml(currentLead.email || 'N/A'))
          .replace(/Dhaka, Mirpur/g, escapeHtml(currentLead.location || 'N/A'))
          .replace(/Retail Shop Owner/g, escapeHtml(currentLead.profession_or_business || 'N/A'))
          .replace(/Looking to expand his retail business to online dropshipping\./g, escapeHtml(currentLead.notes || 'No initial notes.'));
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
    } else if (path === "/users") {
      html = PAGES.users;
    } else if (path === "/roles" || path.startsWith("/roles/")) {
      html = PAGES.roles;
    } else if (path === "/ecosystem") {
      html = PAGES.ecosystem;
    } else if (path === "/contacts") {
      html = PAGES.contacts;
    } else if (path === "/binary" || path.startsWith("/binary")) {
      const viewMode = url.searchParams.get("view");
      html = viewMode === "table" ? (PAGES.binary_table || PAGES.binary) : PAGES.binary;
    } else {
      html = PAGES.dashboard;
    }

    let responseHtml = html;

    // 7. Inject Edge Styles
    if (responseHtml && responseHtml.includes("</head>")) {
      let syncStyles = "";
      if (deletedLeadIds.length > 0) {
        syncStyles += deletedLeadIds.map(id => '[data-lead-id="' + id + '"]').join(', ') + ' { display: none !important; }\n';
      }
      if (deletedNodeIds.length > 0) {
        syncStyles += deletedNodeIds.map(id => '[data-node-id="' + id + '"]').join(', ') + ' { display: none !important; }\n';
      }

      responseHtml = responseHtml.replace("</head>", () => 
        "<style id=\"sbl-edge-styles\">\n" + CSS_CONTENT + "\n" + syncStyles + "</style>\n</head>"
      );
    }

    // 8. Inject Live Dynamic Edge Synchronization Script
    if (responseHtml && responseHtml.includes("</body>")) {
      const syncDataPayload = {
        leads: liveLeads,
        deletedLeads: deletedLeadIds,
        nodes: liveNodes,
        deletedNodes: deletedNodeIds,
        contacts: liveContacts,
        tasks: liveTasks,
        ecosystem: liveEcosystem,
        sources: sourcesMap
      };

      const syncScript = `
<script id="sbl-live-d1-sync">
(function() {
  const DATA = ${JSON.stringify(syncDataPayload)};
  
  function runSync() {
    // 1. Leads DOM Sync
    if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
      DATA.deletedLeads.forEach(function(id) {
        document.querySelectorAll('[data-lead-id="' + id + '"]').forEach(function(el) { el.remove(); });
      });
    }

    if (DATA.leads && DATA.leads.length > 0) {
      const tableBody = document.querySelector('tbody.divide-y');
      const mobileStack = document.querySelector('.block.md\\\\:hidden.divide-y, div.divide-y.block.md\\\\:hidden');

      // Sort newest first
      DATA.leads.slice().reverse().forEach(function(lead) {
        // If already rendered in DOM, update stage/status if needed
        const existingRow = document.querySelector('tr[data-lead-id="' + lead.id + '"]');
        if (existingRow) return;

        // Otherwise, this is a newly created lead in D1 - inject it!
        let interests = [];
        try {
          interests = typeof lead.interest_types === 'string' ? JSON.parse(lead.interest_types) : (lead.interest_types || []);
        } catch(e) {}

        const sourceName = DATA.sources[lead.lead_source_id] || 'Direct';
        const initialLetter = (lead.name || 'L').charAt(0).toUpperCase();
        const stageLabel = (lead.stage || 'new').replace('_', ' ').toUpperCase();
        const cleanWhatsapp = (lead.whatsapp || lead.mobile || '').replace(/[^0-9]/g, '');

        // Desktop Table Row
        if (tableBody) {
          const tr = document.createElement('tr');
          tr.setAttribute('data-lead-id', lead.id);
          tr.className = 'hover:bg-slate-50/70 transition-colors bg-orange-50/20';
          tr.innerHTML = \`
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                  \${initialLetter}
                </div>
                <div>
                  <a href="/leads/\${lead.id}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                    \${lead.name}
                  </a>
                  <div class="text-[11px] text-slate-400">
                    \${lead.location || 'No location'} \${lead.profession_or_business ? '• ' + lead.profession_or_business : ''}
                  </div>
                </div>
              </div>
            </td>
            <td class="py-3.5 px-4">
              <div class="font-medium text-slate-800">\${lead.mobile}</div>
              <div class="flex items-center gap-2 mt-1">
                <a href="tel:\${lead.mobile}" title="Call" class="text-slate-400 hover:text-emerald-600 text-sm">📞</a>
                <a href="https://wa.me/\${cleanWhatsapp}" target="_blank" title="WhatsApp" class="text-slate-400 hover:text-emerald-600 text-sm">💬</a>
              </div>
            </td>
            <td class="py-3.5 px-4">
              <span class="font-medium text-slate-800 block">\${sourceName}</span>
              <div class="flex flex-wrap gap-1 mt-1">
                \${interests.map(function(i) { return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-200/80">' + i + '</span>'; }).join('')}
              </div>
            </td>
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                \${stageLabel}
              </span>
            </td>
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-1.5">
                <span class="font-bold text-xs text-orange-600">\${lead.score || 25}</span>
                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-800">\${lead.temperature || 'warm'}</span>
              </div>
            </td>
            <td class="py-3.5 px-4">
              \${lead.next_action_at ? '<div class="text-xs font-medium text-slate-700">' + (lead.next_action_type || 'Action') + '<br><span class="text-[11px] text-slate-400">' + lead.next_action_at + '</span></div>' : '<span class="text-[11px] text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-md">Needs Next Action</span>'}
            </td>
            <td class="py-3.5 px-4 text-right">
              <div class="flex items-center justify-end gap-1.5">
                <a href="/leads/\${lead.id}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs transition-colors">View</a>
                <a href="/leads/\${lead.id}/edit" class="px-2.5 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 font-semibold text-xs transition-colors">Edit</a>
                <form action="/leads/\${lead.id}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                  <input type="hidden" name="_method" value="DELETE">
                  <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs transition-colors">Delete</button>
                </form>
              </div>
            </td>
          \`;
          tableBody.prepend(tr);
        }

        // Mobile Card Stack
        if (mobileStack) {
          const card = document.createElement('div');
          card.setAttribute('data-lead-id', lead.id);
          card.className = 'p-4 space-y-3 hover:bg-slate-50/50 transition-colors bg-orange-50/20';
          card.innerHTML = \`
            <div class="flex items-start justify-between gap-2">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-xs">
                  \${initialLetter}
                </div>
                <div>
                  <a href="/leads/\${lead.id}" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">
                    \${lead.name}
                  </a>
                  <div class="text-xs text-slate-500">\${lead.mobile}</div>
                </div>
              </div>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                \${stageLabel}
              </span>
            </div>
            <div class="flex flex-wrap items-center gap-1 text-xs text-slate-500 pt-1">
              <span>\${sourceName}</span>
              \${interests.map(function(i) { return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-200/80">' + i + '</span>'; }).join('')}
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
              <div class="flex items-center gap-2">
                <a href="tel:\${lead.mobile}" class="text-emerald-600 font-semibold">📞 Call</a>
                <a href="https://wa.me/\${cleanWhatsapp}" target="_blank" class="text-emerald-600 font-semibold">💬 WhatsApp</a>
              </div>
              <div class="flex items-center gap-1.5">
                <a href="/leads/\${lead.id}" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-semibold text-xs">View</a>
                <a href="/leads/\${lead.id}/edit" class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-semibold text-xs">Edit</a>
                <form action="/leads/\${lead.id}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="inline">
                  <input type="hidden" name="_method" value="DELETE">
                  <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold text-xs">Delete</button>
                </form>
              </div>
            </div>
          \`;
          mobileStack.prepend(card);
        }

        // Kanban Board Card
        const kanbanCol = document.querySelector('.kanban-cards-container[data-stage="' + (lead.stage || 'new') + '"]');
        if (kanbanCol && !document.querySelector('.kanban-card[data-lead-id="' + lead.id + '"]')) {
          const kCard = document.createElement('div');
          kCard.setAttribute('data-lead-id', lead.id);
          kCard.className = 'bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-xs hover:border-orange-300 hover:shadow-md transition-all cursor-grab active:cursor-grabbing kanban-card bg-orange-50/20';
          kCard.innerHTML = \`
            <div class="flex items-start justify-between gap-2">
              <a href="/leads/\${lead.id}" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate block">
                \${lead.name}
              </a>
              <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md border flex-shrink-0 bg-orange-100 text-orange-800 border-orange-200">
                \${lead.temperature || 'warm'}
              </span>
            </div>
            <div class="text-xs text-slate-600 mt-1 flex items-center justify-between">
              <span>\${lead.mobile}</span>
              <span class="text-[11px] text-slate-400">\${sourceName}</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
              <span class="font-bold text-orange-600">\${lead.score || 25} pts</span>
              <div class="flex items-center gap-1">
                <a href="/leads/\${lead.id}" class="p-1 text-slate-400 hover:text-slate-700" title="View">👁️</a>
                <a href="/leads/\${lead.id}/edit" class="p-1 text-slate-400 hover:text-orange-600" title="Edit">✏️</a>
              </div>
            </div>
          \`;
          kanbanCol.prepend(kCard);
        }
      });
    }

    // 2. Binary Tree DOM Sync
    if (DATA.deletedNodes && DATA.deletedNodes.length > 0) {
      DATA.deletedNodes.forEach(function(id) {
        document.querySelectorAll('[data-node-id="' + id + '"]').forEach(function(el) { el.remove(); });
      });
    }

    // 3. Contacts DOM Sync
    if (DATA.contacts && DATA.contacts.length > 0) {
      const contactsGrid = document.querySelector('.grid.grid-cols-1.md\\\\:grid-cols-2.lg\\\\:grid-cols-3');
      if (contactsGrid) {
        DATA.contacts.forEach(function(contact) {
          if (document.querySelector('[data-contact-id="' + contact.id + '"]')) return;
          const card = document.createElement('div');
          card.setAttribute('data-contact-id', contact.id);
          card.className = 'bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-emerald-300 transition-all group';
          card.innerHTML = \`
            <div class="space-y-4">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center text-2xl flex-shrink-0">
                    \${contact.icon || '📞'}
                  </div>
                  <div>
                    <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition-colors">\${contact.department}</h3>
                    \${contact.contact_person ? '<div class="text-xs font-medium text-slate-500 mt-0.5">' + contact.contact_person + '</div>' : ''}
                  </div>
                </div>
                \${contact.badge ? '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">' + contact.badge + '</span>' : ''}
              </div>
              <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-slate-500 font-medium">ফোন:</span>
                  <span class="font-bold text-slate-900">\${contact.phone}</span>
                </div>
                \${contact.whatsapp ? '<div class="flex items-center justify-between pt-1.5 border-t border-slate-200/60"><span class="text-emerald-600 font-bold">WhatsApp:</span><span class="font-bold text-slate-900">' + contact.whatsapp + '</span></div>' : ''}
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
              <a href="tel:\${contact.phone}" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700">📞 Call</a>
              <form action="/contacts/\${contact.id}" method="POST" onsubmit="return confirm('ডিলিট করতে চান?');" class="inline">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold">Delete</button>
              </form>
            </div>
          \`;
          contactsGrid.prepend(card);
        });
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runSync);
  } else {
    runSync();
  }
})();
</script>
`;
      responseHtml = responseHtml.replace("</body>", () => syncScript + "</body>");
    }

    return new Response(responseHtml, {
      headers: {
        "Content-Type": "text/html; charset=utf-8",
        "Cache-Control": "public, max-age=0, must-revalidate",
        "X-Powered-By": "Cloudflare Workers Edge (Laravel Pixel-Perfect Edition)"
      }
    });
  }
};
