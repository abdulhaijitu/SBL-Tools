(function () {
    // DATA provided globally by edge

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    window.DATA = typeof DATA !== "undefined" ? DATA : {};

    window.openContactEditModalById = function (contactId) {
        const list = (window.DATA && window.DATA.contacts) || [];
        const contact = list.find(function (c) {
            return Number(c.id) === Number(contactId);
        });
        if (contact) {
            const root =
                document.querySelector('[x-data*="editingContact"]') ||
                document.querySelector(".space-y-6[x-data]");
            if (root && root._x_dataStack && root._x_dataStack[0]) {
                root._x_dataStack[0].editingContact = Object.assign(
                    {},
                    contact,
                );
                root._x_dataStack[0].editModalOpen = true;
            }
        }
    };

    window.openEditWebsiteModalById = function (linkId) {
        const list = (window.DATA && window.DATA.ecosystem) || [];
        const link = list.find(function (l) {
            return Number(l.id) === Number(linkId);
        });
        if (link) {
            const root =
                document.querySelector('[x-data*="editingWebsite"]') ||
                document.querySelector(".space-y-6[x-data]");
            if (root && root._x_dataStack && root._x_dataStack[0]) {
                root._x_dataStack[0].editingWebsite = Object.assign({}, link);
                root._x_dataStack[0].editWebsiteModalOpen = true;
            } else {
                const rootEco = document.querySelector(
                    '[x-data*="editingLink"]',
                );
                if (
                    rootEco &&
                    rootEco._x_dataStack &&
                    rootEco._x_dataStack[0]
                ) {
                    rootEco._x_dataStack[0].editingLink = Object.assign(
                        {},
                        link,
                    );
                    rootEco._x_dataStack[0].editModalOpen = true;
                }
            }
        }
    };

    window.copyContactToClipboard = function (text, title) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                window.dispatchEvent(
                    new CustomEvent("notify", {
                        detail: {
                            message: title + " copied to clipboard!",
                            type: "success",
                        },
                    }),
                );
            });
        }
    };

    window.openUserEditById = function (userId) {
        if (!DATA || !DATA.users) return;
        const user = DATA.users.find(function (u) {
            return Number(u.id) === Number(userId);
        });
        if (user) {
            var c = document.querySelector("[x-data]");
            if (c && c._x_dataStack) {
                c._x_dataStack[0].editingUser = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    phone: user.phone || "",
                    designation: user.designation || "",
                    role_id: user.role_id || "",
                    status: "active",
                };
                c._x_dataStack[0].editModalOpen = true;
            }
        }
    };

    window.openPlacementModalById = function (parentId, branch, slotNumber) {
        if (!DATA || !DATA.nodes) return;
        const p = DATA.nodes.find(function (n) {
            return Number(n.id) === Number(parentId);
        });
        const pName = p ? p.member_name : "";
        const pCode = p ? p.member_code || "SBL-" + p.id : "";
        var c = document.querySelector("[x-data]");
        if (c && c._x_dataStack) {
            c._x_dataStack[0].openPlacementModal(
                parentId,
                pName,
                pCode,
                branch,
                slotNumber,
            );
        }
    };

    window.openEditTaskById = function (taskId) {
        if (!DATA || !DATA.tasks) return;
        const task = DATA.tasks.find(function (t) {
            return Number(t.id) === Number(taskId);
        });
        if (task) {
            var c = document.querySelector("[x-data]");
            if (c && c._x_dataStack) {
                c._x_dataStack[0].openEditTask(task);
            }
        }
    };

    window.openContentCalendarEditById = function (itemId) {
        if (!DATA || !DATA.contentItems) return;
        const item = DATA.contentItems.find(function (ci) {
            return Number(ci.id) === Number(itemId);
        });
        if (item) {
            var c = document.querySelector("[x-data]");
            if (c && c._x_dataStack) {
                c._x_dataStack[0].openEdit(item);
            }
        }
    };

    function syncEntityDropdowns() {
        if (!DATA) return;

        // A. Sync Lead Selects (<select name="lead_id">)
        document
            .querySelectorAll('select[name="lead_id"]')
            .forEach(function (select) {
                if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
                    DATA.deletedLeads.forEach(function (delId) {
                        const opt = select.querySelector(
                            'option[value="' + delId + '"]',
                        );
                        if (opt) opt.remove();
                    });
                }
                if (DATA.leads && DATA.leads.length > 0) {
                    const validLeadIds = new Set(
                        DATA.leads.map(function (l) {
                            return String(l.id);
                        }),
                    );
                    Array.from(select.options).forEach(function (opt) {
                        if (opt.value && !validLeadIds.has(opt.value)) {
                            opt.remove();
                        }
                    });
                    DATA.leads.forEach(function (lead) {
                        let opt = select.querySelector(
                            'option[value="' + lead.id + '"]',
                        );
                        const stageLabel = (lead.stage || "new")
                            .replace("_", " ")
                            .toUpperCase();
                        const text =
                            lead.name +
                            " (" +
                            lead.mobile +
                            ") - " +
                            stageLabel;
                        if (!opt) {
                            opt = document.createElement("option");
                            opt.value = lead.id;
                            opt.setAttribute("data-lead-id", lead.id);
                            opt.textContent = text;
                            select.appendChild(opt);
                        } else {
                            opt.textContent = text;
                        }
                    });
                }
            });

        // B. Sync Related Lead Selects (<select name="related_lead_id">)
        document
            .querySelectorAll('select[name="related_lead_id"]')
            .forEach(function (select) {
                if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
                    DATA.deletedLeads.forEach(function (delId) {
                        const opt = select.querySelector(
                            'option[value="' + delId + '"]',
                        );
                        if (opt) opt.remove();
                    });
                }
                if (DATA.leads && DATA.leads.length > 0) {
                    const validLeadIds = new Set(
                        DATA.leads.map(function (l) {
                            return String(l.id);
                        }),
                    );
                    Array.from(select.options).forEach(function (opt) {
                        if (opt.value && !validLeadIds.has(opt.value)) {
                            opt.remove();
                        }
                    });
                    DATA.leads.forEach(function (lead) {
                        let opt = select.querySelector(
                            'option[value="' + lead.id + '"]',
                        );
                        const text = lead.name + " (" + lead.mobile + ")";
                        if (!opt) {
                            opt = document.createElement("option");
                            opt.value = lead.id;
                            opt.setAttribute("data-lead-id", lead.id);
                            opt.textContent = text;
                            select.appendChild(opt);
                        } else {
                            opt.textContent = text;
                        }
                    });
                }
            });

        // C. Sync User Selects (<select name="user_id">)
        document
            .querySelectorAll('select[name="user_id"]')
            .forEach(function (select) {
                if (DATA.deletedUsers && DATA.deletedUsers.length > 0) {
                    DATA.deletedUsers.forEach(function (delId) {
                        const opt = select.querySelector(
                            'option[value="' + delId + '"]',
                        );
                        if (opt) opt.remove();
                    });
                }
                if (DATA.users && DATA.users.length > 0) {
                    DATA.users.forEach(function (user) {
                        let opt = select.querySelector(
                            'option[value="' + user.id + '"]',
                        );
                        const text =
                            user.name +
                            (user.email ? " (" + user.email + ")" : "");
                        if (!opt) {
                            opt = document.createElement("option");
                            opt.value = user.id;
                            opt.setAttribute("data-user-id", user.id);
                            opt.textContent = text;
                            select.appendChild(opt);
                        }
                    });
                }
            });

        // D. Sync Sponsor Selects (<select name="sponsor_id">)
        document
            .querySelectorAll('select[name="sponsor_id"]')
            .forEach(function (select) {
                if (DATA.deletedNodes && DATA.deletedNodes.length > 0) {
                    DATA.deletedNodes.forEach(function (delId) {
                        const opt = select.querySelector(
                            'option[value="' + delId + '"]',
                        );
                        if (opt) opt.remove();
                    });
                }
                if (DATA.nodes && DATA.nodes.length > 0) {
                    DATA.nodes.forEach(function (node) {
                        let opt = select.querySelector(
                            'option[value="' + node.id + '"]',
                        );
                        const text =
                            node.member_name +
                            " (" +
                            (node.member_code || "SBL-" + node.id) +
                            ")";
                        if (!opt) {
                            opt = document.createElement("option");
                            opt.value = node.id;
                            opt.setAttribute("data-node-id", node.id);
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
        if (curPath === "/" || curPath === "/dashboard") {
            if (DATA.leads) {
                const totalLeads = DATA.leads.length;
                const totalEl = document.querySelector(
                    '[data-metric="total-leads"]',
                );
                if (totalEl) totalEl.textContent = totalLeads;

                // Stage Funnel Counters
                const stages = [
                    "new",
                    "contacted",
                    "qualified",
                    "presentation",
                    "interested",
                    "negotiation",
                    "converted",
                    "lost",
                ];
                stages.forEach(function (s) {
                    const count = DATA.leads.filter(function (l) {
                        return (l.stage || "new") === s;
                    }).length;
                    const el = document.querySelector(
                        '[data-funnel-count="' + s + '"]',
                    );
                    if (el) el.textContent = count;
                });

                // Follow-ups & Overdue Calculations
                const now = new Date();
                const todayStr = now.toISOString().slice(0, 10);

                let overdueCount = 0;
                let followupsToday = 0;

                DATA.leads.forEach(function (lead) {
                    if (lead.next_action_at) {
                        const actDate = lead.next_action_at.slice(0, 10);
                        if (actDate === todayStr) followupsToday++;
                        if (
                            new Date(lead.next_action_at) < now &&
                            lead.stage !== "converted" &&
                            lead.stage !== "lost"
                        ) {
                            overdueCount++;
                        }
                    }
                });

                if (DATA.tasks) {
                    DATA.tasks.forEach(function (task) {
                        if (task.status !== "Completed" && task.due_at) {
                            const dStr = task.due_at.slice(0, 10);
                            if (dStr === todayStr) followupsToday++;
                            if (new Date(task.due_at) < now) overdueCount++;
                        }
                    });
                }

                const dueEl = document.querySelector(
                    '[data-metric="followups-today"]',
                );
                if (dueEl) dueEl.textContent = followupsToday;

                const overEl = document.querySelector(
                    '[data-metric="overdue-followups"]',
                );
                if (overEl) overEl.textContent = overdueCount;

                // Presentations Today
                let presTodayCount = 0;
                if (DATA.presentations) {
                    DATA.presentations.forEach(function (p) {
                        if (
                            p.date_time &&
                            p.date_time.slice(0, 10) === todayStr
                        )
                            presTodayCount++;
                    });
                }
                const presEl = document.querySelector(
                    '[data-metric="presentations-today"]',
                );
                if (presEl) presEl.textContent = presTodayCount;
            }
        }

        // 2. REPORTS SYNC - ONLY on /reports!
        if (curPath === "/reports" || curPath.startsWith("/reports?")) {
            if (DATA.leads && DATA.leads.length > 0) {
                const total = DATA.leads.length;
                const converted = DATA.leads.filter(function (l) {
                    return l.stage === "converted";
                }).length;
                const rate =
                    total > 0 ? Math.round((converted / total) * 100) : 0;

                const totEl = document.querySelector(
                    '[data-report-metric="total-leads"]',
                );
                if (totEl) totEl.textContent = total;

                const convEl = document.querySelector(
                    '[data-report-metric="converted-leads"]',
                );
                if (convEl) convEl.textContent = converted;

                const rateEl = document.querySelector(
                    '[data-report-metric="conversion-rate"]',
                );
                if (rateEl) rateEl.textContent = rate + "%";

                // Update Funnel Breakdown
                const stages = [
                    "new",
                    "contacted",
                    "qualified",
                    "presentation",
                    "interested",
                    "negotiation",
                    "converted",
                    "lost",
                ];
                stages.forEach(function (s) {
                    const count = DATA.leads.filter(function (l) {
                        return (l.stage || "new") === s;
                    }).length;
                    const pct =
                        total > 0 ? Math.round((count / total) * 100) : 0;
                    const txt = document.querySelector(
                        '[data-report-stage-text="' + s + '"]',
                    );
                    if (txt) txt.textContent = count + " leads (" + pct + "%)";
                    const bar = document.querySelector(
                        '[data-report-stage-bar="' + s + '"]',
                    );
                    if (bar) bar.style.width = pct + "%";
                });
            }
        }

        // 3. LEADS LIST SYNC - ONLY on /leads!
        if (curPath === "/leads" || curPath.startsWith("/leads?")) {
            if (DATA.deletedLeads && DATA.deletedLeads.length > 0) {
                DATA.deletedLeads.forEach(function (id) {
                    document
                        .querySelectorAll('[data-lead-id="' + id + '"]')
                        .forEach(function (el) {
                            el.remove();
                        });
                });
            }

            const tableBody = document.querySelector("tbody.divide-y");
            const mobileStack = document.querySelector(
                'div.divide-y[class*="md:hidden"]',
            );

            function getStageBadgeClass(stage) {
                switch (stage) {
                    case "new":
                        return "bg-blue-50 text-blue-700 border-blue-200";
                    case "contacted":
                        return "bg-sky-50 text-sky-700 border-sky-200";
                    case "qualified":
                        return "bg-indigo-50 text-indigo-700 border-indigo-200";
                    case "presentation":
                        return "bg-purple-50 text-purple-700 border-purple-200";
                    case "interested":
                        return "bg-amber-50 text-amber-700 border-amber-200";
                    case "negotiation":
                        return "bg-orange-50 text-orange-700 border-orange-200";
                    case "converted":
                        return "bg-emerald-50 text-emerald-700 border-emerald-200";
                    case "lost":
                        return "bg-slate-100 text-slate-700 border-slate-200";
                    default:
                        return "bg-slate-50 text-slate-700 border-slate-200";
                }
            }

            function getTempBadgeClass(temp) {
                switch (temp) {
                    case "hot":
                        return "bg-red-50 text-red-700 border-red-200";
                    case "warm":
                        return "bg-amber-50 text-amber-700 border-amber-200";
                    case "cold":
                        return "bg-blue-50 text-blue-700 border-blue-200";
                    default:
                        return "bg-slate-50 text-slate-700 border-slate-200";
                }
            }

            function renderLeadRow(lead) {
                const initialLetter = (lead.name || "L")
                    .charAt(0)
                    .toUpperCase();
                const stageLabel = (lead.stage || "new")
                    .replace("_", " ")
                    .toUpperCase();
                let cleanWhatsapp = (
                    lead.whatsapp ||
                    lead.mobile ||
                    ""
                ).replace(/[^0-9]/g, "");
                if (
                    cleanWhatsapp.startsWith("01") &&
                    cleanWhatsapp.length === 11
                ) {
                    cleanWhatsapp = "88" + cleanWhatsapp;
                }
                const stageClass = getStageBadgeClass(lead.stage || "new");
                const fbUrl = lead.facebook_url || "";

                const callIconSvg =
                    '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>';
                const waIconSvg =
                    '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>';
                const fbIconSvg =
                    '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';

                const callBtn =
                    '<a href="tel:' +
                    escapeHtml(lead.mobile) +
                    '" title="Call ' +
                    escapeHtml(lead.mobile) +
                    '" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white transition-all shadow-2xs active:scale-95" aria-label="Call ' +
                    escapeHtml(lead.mobile) +
                    '">' +
                    callIconSvg +
                    "</a>";
                const waBtn = cleanWhatsapp
                    ? '<a href="https://wa.me/' +
                      cleanWhatsapp +
                      '" target="_blank" rel="noopener noreferrer" title="WhatsApp (' +
                      escapeHtml(lead.whatsapp || lead.mobile) +
                      ')" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white transition-all shadow-2xs active:scale-95" aria-label="WhatsApp (' +
                      escapeHtml(lead.whatsapp || lead.mobile) +
                      ')">' +
                      waIconSvg +
                      "</a>"
                    : '<span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 text-slate-300" title="No WhatsApp">' +
                      waIconSvg +
                      "</span>";
                const fbBtn = fbUrl
                    ? '<a href="' +
                      escapeHtml(fbUrl) +
                      '" target="_blank" rel="noopener noreferrer" title="Facebook Profile" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white transition-all shadow-2xs active:scale-95" aria-label="Facebook Profile">' +
                      fbIconSvg +
                      "</a>"
                    : '<span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 text-slate-300" title="No Facebook URL">' +
                      fbIconSvg +
                      "</span>";

                return (
                    '<td class="py-3.5 px-4">' +
                    '<div class="flex items-center gap-3">' +
                    (lead.photo
                        ? '<div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center overflow-hidden flex-shrink-0 border border-orange-200/50"><img src="' +
                          escapeHtml(lead.photo) +
                          '" class="w-full h-full object-cover"></div>'
                        : '<div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0">' +
                          initialLetter +
                          "</div>") +
                    "<div>" +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">' +
                    escapeHtml(lead.name) +
                    "</a>" +
                    '<div class="text-[11px] text-slate-400">' +
                    escapeHtml(lead.location || "No location") +
                    (lead.profession_or_business
                        ? " • " + escapeHtml(lead.profession_or_business)
                        : "") +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    "</td>" +
                    '<td class="py-3.5 px-4 whitespace-nowrap">' +
                    '<div class="font-semibold text-slate-900 text-xs font-mono tracking-wide">' +
                    escapeHtml(lead.mobile) +
                    "</div>" +
                    '<div class="flex items-center gap-1.5 mt-1.5">' +
                    callBtn +
                    waBtn +
                    fbBtn +
                    "</div>" +
                    "</td>" +
                    '<td class="py-3.5 px-4 whitespace-nowrap">' +
                    '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ' +
                    stageClass +
                    '">' +
                    stageLabel +
                    "</span>" +
                    "</td>" +
                    '<td class="py-3.5 px-4 whitespace-nowrap">' +
                    (lead.next_action_at
                        ? '<div class="text-xs font-medium text-slate-700">' +
                          escapeHtml(lead.next_action_type || "Action") +
                          '<br><span class="text-[11px] text-slate-400">' +
                          escapeHtml(lead.next_action_at) +
                          "</span></div>"
                        : '<span class="text-[11px] text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-md">Needs Next Action</span>') +
                    "</td>" +
                    '<td class="py-3.5 px-4 text-right">' +
                    '<div class="flex items-center justify-end gap-1.5">' +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-all active:scale-95 shadow-xs border border-slate-200/60" title="View Lead Profile" aria-label="View Lead Profile">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' +
                    '<span class="sr-only">View</span>' +
                    "</a>" +
                    '<a href="/leads/' +
                    lead.id +
                    '/edit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-orange-50 hover:bg-orange-100 text-orange-600 hover:text-orange-800 transition-all active:scale-95 shadow-xs border border-orange-200/60" title="Edit Lead" aria-label="Edit Lead">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' +
                    '<span class="sr-only">Edit</span>' +
                    "</a>" +
                    '<form action="/leads/' +
                    lead.id +
                    '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this lead?\');" class="inline">' +
                    '<input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition-all active:scale-95 shadow-xs border border-rose-200/60 cursor-pointer" title="Delete Lead" aria-label="Delete Lead">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
                    '<span class="sr-only">Delete</span>' +
                    "</button>" +
                    "</form>" +
                    "</div>" +
                    "</td>"
                );
            }

            function renderMobileCard(lead) {
                const initialLetter = (lead.name || "L")
                    .charAt(0)
                    .toUpperCase();
                const stageLabel = (lead.stage || "new")
                    .replace("_", " ")
                    .toUpperCase();
                const cleanWhatsapp = (
                    lead.whatsapp ||
                    lead.mobile ||
                    ""
                ).replace(/[^0-9]/g, "");
                const stageClass = getStageBadgeClass(lead.stage || "new");
                const fbUrl = lead.facebook_url || "";

                const callIconSvg =
                    '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>';
                const waIconSvg =
                    '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>';
                const fbIconSvg =
                    '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';

                const callBtn =
                    '<a href="tel:' +
                    escapeHtml(lead.mobile) +
                    '" class="inline-flex items-center gap-1 text-emerald-600 font-semibold">' +
                    callIconSvg +
                    " Call</a>";
                const waBtn = cleanWhatsapp
                    ? '<a href="https://wa.me/' +
                      cleanWhatsapp +
                      '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-emerald-600 font-semibold">' +
                      waIconSvg +
                      " WhatsApp</a>"
                    : "";
                const fbBtn = fbUrl
                    ? '<a href="' +
                      escapeHtml(fbUrl) +
                      '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-600 font-semibold">' +
                      fbIconSvg +
                      " Facebook</a>"
                    : "";

                return (
                    '<div class="flex items-start justify-between gap-2">' +
                    '<div class="flex items-center gap-3">' +
                    (lead.photo
                        ? '<div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-xs border border-orange-200/50"><img src="' +
                          escapeHtml(lead.photo) +
                          '" class="w-full h-full object-cover"></div>'
                        : '<div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-xs">' +
                          initialLetter +
                          "</div>") +
                    "<div>" +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">' +
                    escapeHtml(lead.name) +
                    "</a>" +
                    '<div class="text-xs text-slate-500">' +
                    escapeHtml(lead.mobile) +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold ' +
                    stageClass +
                    '">' +
                    stageLabel +
                    "</span>" +
                    "</div>" +
                    '<div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">' +
                    '<div class="flex items-center gap-3">' +
                    callBtn +
                    waBtn +
                    fbBtn +
                    "</div>" +
                    '<div class="flex items-center gap-1.5">' +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-semibold text-xs">View</a>' +
                    '<a href="/leads/' +
                    lead.id +
                    '/edit" class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-semibold text-xs">Edit</a>' +
                    '<form action="/leads/' +
                    lead.id +
                    '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this lead?\');" class="inline">' +
                    '<input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold text-xs">Delete</button>' +
                    "</form>" +
                    "</div>" +
                    "</div>"
                );
            }

            function renderKanbanCard(lead) {
                const sourceName =
                    DATA.sources[lead.lead_source_id] || "Direct";
                const tempClass = getTempBadgeClass(lead.temperature || "warm");
                const tempLabel = (lead.temperature || "warm").toUpperCase();

                return (
                    '<div class="flex items-start justify-between gap-2">' +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate block">' +
                    escapeHtml(lead.name) +
                    "</a>" +
                    '<span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md border flex-shrink-0 ' +
                    tempClass +
                    '">' +
                    tempLabel +
                    "</span>" +
                    "</div>" +
                    '<div class="text-xs text-slate-500 mt-1 flex items-center justify-between">' +
                    "<span>📞 " +
                    escapeHtml(lead.mobile) +
                    "</span>" +
                    '<span class="font-bold text-slate-700">' +
                    (lead.score || 25) +
                    " pts</span>" +
                    "</div>" +
                    '<div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">' +
                    '<div class="flex items-center gap-1.5">' +
                    (lead.next_action_at
                        ? '<span class="font-medium text-slate-600">' +
                          escapeHtml(lead.next_action_type || "Action") +
                          "</span>"
                        : '<span class="text-amber-600 font-semibold">No Action</span>') +
                    "</div>" +
                    '<div class="flex items-center gap-1">' +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition-colors" title="View Lead Profile">👁️</a>' +
                    '<a href="/leads/' +
                    lead.id +
                    '/edit" class="text-slate-400 hover:text-orange-600 p-1 rounded hover:bg-orange-50 transition-colors" title="Edit Lead">✏️</a>' +
                    '<form action="/leads/' +
                    lead.id +
                    '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this lead?\');" class="inline">' +
                    '<input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded" title="Delete Lead">🗑️</button>' +
                    "</form>" +
                    "</div>" +
                    "</div>"
                );
            }

            if (DATA.leads) {
                if (tableBody) {
                    tableBody.innerHTML = "";
                    if (DATA.leads.length === 0) {
                        tableBody.innerHTML =
                            '<tr><td colspan="5" class="py-12 text-center text-slate-400"><div class="text-base font-semibold text-slate-700">No leads found</div><div class="text-xs text-slate-500 mt-1">Try adjusting your filters or add a new lead.</div><a href="/leads/create" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-semibold">+ Add New Lead</a></td></tr>';
                    } else {
                        DATA.leads.forEach(function (lead) {
                            const tr = document.createElement("tr");
                            tr.setAttribute("data-lead-id", lead.id);
                            tr.className =
                                "hover:bg-slate-50/70 transition-colors";
                            tr.innerHTML = renderLeadRow(lead);
                            tableBody.appendChild(tr);
                        });
                    }
                }

                if (mobileStack) {
                    mobileStack.innerHTML = "";
                    if (DATA.leads.length === 0) {
                        mobileStack.innerHTML =
                            '<div class="py-12 text-center text-slate-400"><div class="text-base font-semibold text-slate-700">No leads found</div><a href="/leads/create" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-semibold">+ Add New Lead</a></div>';
                    } else {
                        DATA.leads.forEach(function (lead) {
                            const card = document.createElement("div");
                            card.setAttribute("data-lead-id", lead.id);
                            card.className =
                                "p-4 space-y-3 hover:bg-slate-50/50 transition-colors";
                            card.innerHTML = renderMobileCard(lead);
                            mobileStack.appendChild(card);
                        });
                    }
                }

                const kanbanContainers = document.querySelectorAll(
                    ".kanban-cards-container[data-stage]",
                );
                if (kanbanContainers.length > 0) {
                    kanbanContainers.forEach(function (col) {
                        const stage = col.getAttribute("data-stage");
                        col.innerHTML = "";
                        const stageLeads = DATA.leads.filter(function (l) {
                            return (l.stage || "new") === stage;
                        });

                        const colHeader = document.querySelector(
                            "#kanban-col-" +
                                stage +
                                " span.text-xs.font-bold.text-slate-500",
                        );
                        if (colHeader)
                            colHeader.textContent = stageLeads.length;

                        stageLeads.forEach(function (lead) {
                            const kCard = document.createElement("div");
                            kCard.setAttribute("data-lead-id", lead.id);
                            kCard.className =
                                "bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-xs hover:border-orange-300 hover:shadow-md transition-all cursor-grab active:cursor-grabbing kanban-card";
                            kCard.innerHTML = renderKanbanCard(lead);
                            col.appendChild(kCard);
                        });
                    });
                }
            }
        }

        // 3b. LEAD DETAILS PAGE SYNC - ONLY on /leads/:id!
        const leadShowParts = curPath.split("?")[0].split("/").filter(Boolean);
        const isLeadShow =
            leadShowParts.length === 2 &&
            leadShowParts[0] === "leads" &&
            !isNaN(Number(leadShowParts[1]));
        if (isLeadShow) {
            const showLeadId = parseInt(leadShowParts[1], 10);
            const lead = DATA.leads
                ? DATA.leads.find(function (l) {
                      return Number(l.id) === showLeadId;
                  })
                : null;
            if (lead) {
                const stageLabel = (lead.stage || "new")
                    .replace("_", " ")
                    .toUpperCase();
                const initialLetter = (lead.name || "L")
                    .charAt(0)
                    .toUpperCase();
                const sourceName =
                    DATA.sources[lead.lead_source_id] || "Direct";
                const score = lead.score || 25;
                const temp = (lead.temperature || "warm").toUpperCase();
                const cleanWhatsapp = (
                    lead.whatsapp ||
                    lead.mobile ||
                    ""
                ).replace(/[^0-9]/g, "");

                document.title = lead.name + " - SBL Growth Manager";

                const pageTitleEl = document.getElementById("app-page-title");
                if (pageTitleEl) pageTitleEl.textContent = lead.name;

                const nameEl = document.getElementById("lead-show-name");
                if (nameEl) nameEl.textContent = lead.name;

                const avatarEl = document.getElementById("lead-show-avatar");
                if (avatarEl) {
                    if (lead.photo) {
                        avatarEl.className =
                            "w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs overflow-hidden border border-orange-200/50";
                        avatarEl.innerHTML =
                            '<img src="' +
                            escapeHtml(lead.photo) +
                            '" class="w-full h-full object-cover">';
                    } else {
                        avatarEl.className =
                            "w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-xs";
                        avatarEl.textContent = initialLetter;
                    }
                }

                const stageBadge = document.getElementById(
                    "lead-show-stage-badge",
                );
                if (stageBadge) stageBadge.textContent = stageLabel;

                const tempBadge = document.getElementById(
                    "lead-show-temp-badge",
                );
                if (tempBadge) tempBadge.textContent = temp;

                const mobileBtn = document.getElementById(
                    "lead-show-mobile-btn",
                );
                if (mobileBtn) {
                    mobileBtn.href = "tel:" + lead.mobile;
                    const mobTxt = document.getElementById(
                        "lead-show-mobile-text",
                    );
                    if (mobTxt) mobTxt.textContent = lead.mobile;
                }

                const waBtn = document.getElementById("lead-show-wa-btn");
                if (waBtn) waBtn.href = "https://wa.me/" + cleanWhatsapp;

                const locBox = document.getElementById(
                    "lead-show-location-container",
                );
                const locTxt = document.getElementById(
                    "lead-show-location-text",
                );
                if (locBox && locTxt) {
                    if (lead.location) {
                        locTxt.textContent = lead.location;
                        locBox.style.display = "";
                    } else {
                        locBox.style.display = "none";
                    }
                }

                const scoreTxt = document.getElementById(
                    "lead-show-score-text",
                );
                if (scoreTxt) scoreTxt.textContent = score + " / 100";

                const scoreBar = document.getElementById("lead-show-score-bar");
                if (scoreBar) scoreBar.style.width = score + "%";

                const sourceTxt = document.getElementById(
                    "lead-show-source-text",
                );
                if (sourceTxt) sourceTxt.textContent = sourceName;

                const nextActionEl = document.getElementById(
                    "lead-show-next-action-text",
                );
                if (nextActionEl) {
                    if (lead.next_action_at) {
                        nextActionEl.textContent =
                            (lead.next_action_type || "Action") +
                            " (" +
                            lead.next_action_at +
                            ")";
                        nextActionEl.className =
                            "mt-0.5 text-slate-800 font-semibold";
                    } else {
                        nextActionEl.textContent = "Needs Next Action";
                        nextActionEl.className =
                            "text-amber-600 font-semibold text-xs mt-0.5 block";
                    }
                }

                const intList = document.getElementById(
                    "lead-show-interests-list",
                );
                if (intList) {
                    let interests = [];
                    try {
                        interests =
                            typeof lead.interest_types === "string"
                                ? JSON.parse(lead.interest_types)
                                : lead.interest_types || [];
                    } catch (e) {}
                    if (interests.length > 0) {
                        intList.innerHTML = interests
                            .map(function (i) {
                                return (
                                    '<span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-medium">' +
                                    escapeHtml(i) +
                                    "</span>"
                                );
                            })
                            .join("");
                    } else {
                        intList.innerHTML =
                            '<span class="text-slate-400">None specified</span>';
                    }
                }

                const stageSelect = document.getElementById(
                    "lead-show-stage-select",
                );
                if (stageSelect) stageSelect.value = lead.stage || "new";

                const editLink = document.getElementById("lead-show-edit-link");
                if (editLink) editLink.href = "/leads/" + lead.id + "/edit";

                const stageForm = document.getElementById(
                    "lead-show-stage-form",
                );
                if (stageForm)
                    stageForm.action = "/leads/" + lead.id + "/stage";

                const convertForm = document.getElementById(
                    "lead-show-convert-form",
                );
                if (convertForm)
                    convertForm.action = "/leads/" + lead.id + "/convert";

                const deleteForm = document.getElementById(
                    "lead-show-delete-form",
                );
                if (deleteForm) deleteForm.action = "/leads/" + lead.id;

                const actForm = document.getElementById(
                    "lead-show-activity-form",
                );
                if (actForm)
                    actForm.action = "/leads/" + lead.id + "/activities";

                // Timeline activities
                const timelineContainer = document.getElementById(
                    "lead-show-timeline-container",
                );
                if (timelineContainer && DATA.activities) {
                    const leadActivities = DATA.activities.filter(function (a) {
                        return Number(a.lead_id) === Number(lead.id);
                    });
                    const countEl = document.getElementById(
                        "lead-activities-count",
                    );
                    if (countEl)
                        countEl.textContent =
                            leadActivities.length + " activities";

                    if (leadActivities.length > 0) {
                        timelineContainer.innerHTML = leadActivities
                            .map(function (act) {
                                let dotBg = "bg-slate-500";
                                if (act.type === "conversion")
                                    dotBg = "bg-emerald-500";
                                else if (act.type === "stage_change")
                                    dotBg = "bg-blue-500";
                                else if (act.type === "call")
                                    dotBg = "bg-orange-500";
                                else if (act.type === "presentation")
                                    dotBg = "bg-purple-500";

                                return (
                                    '<div class="relative"><div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-white ' +
                                    dotBg +
                                    '"></div><div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 text-xs"><div class="flex items-center justify-between gap-2"><span class="font-bold text-slate-900 text-sm">' +
                                    escapeHtml(act.title) +
                                    '</span><span class="text-[11px] text-slate-400">' +
                                    escapeHtml(act.performed_at || "") +
                                    "</span></div>" +
                                    (act.description
                                        ? '<p class="text-slate-600 mt-1 leading-relaxed">' +
                                          escapeHtml(act.description) +
                                          "</p>"
                                        : "") +
                                    '<div class="text-[10px] text-slate-400 mt-2">Logged by ' +
                                    escapeHtml(act.user_name || "System") +
                                    "</div></div></div>"
                                );
                            })
                            .join("");
                    } else {
                        timelineContainer.innerHTML =
                            '<div class="text-center py-8 text-slate-400 text-xs">No activity logged yet. Use the Quick Action bar above to log your first call or note.</div>';
                    }
                }

                // Associated tasks
                const tasksContainer = document.getElementById(
                    "lead-show-tasks-container",
                );
                if (tasksContainer && DATA.tasks) {
                    const leadTasks = DATA.tasks.filter(function (t) {
                        return Number(t.related_lead_id) === Number(lead.id);
                    });
                    const tCount = document.getElementById("lead-tasks-count");
                    if (tCount)
                        tCount.textContent = leadTasks.length + " total";

                    if (leadTasks.length > 0) {
                        tasksContainer.innerHTML = leadTasks
                            .map(function (task) {
                                const isCompleted = task.status === "Completed";
                                return (
                                    '<div class="p-3 rounded-xl border border-slate-100 text-xs hover:bg-slate-50 transition-colors"><div class="flex items-center justify-between"><span class="font-bold text-slate-800">' +
                                    escapeHtml(task.title) +
                                    '</span><span class="px-2 py-0.5 rounded text-[10px] font-semibold ' +
                                    (isCompleted
                                        ? "bg-emerald-50 text-emerald-700"
                                        : "bg-blue-50 text-blue-700") +
                                    '">' +
                                    escapeHtml(task.status || "Pending") +
                                    '</span></div><div class="text-slate-500 mt-1 flex items-center justify-between text-[11px]"><span>Due: ' +
                                    escapeHtml(task.due_at || "") +
                                    '</span><span class="font-semibold text-slate-700">' +
                                    escapeHtml(task.priority || "Medium") +
                                    "</span></div></div>"
                                );
                            })
                            .join("");
                    } else {
                        tasksContainer.innerHTML =
                            '<div class="text-center py-4 text-slate-400 text-xs">No pending tasks.</div>';
                    }
                }

                // Associated presentations
                const presContainer = document.getElementById(
                    "lead-show-presentations-container",
                );
                if (presContainer && DATA.presentations) {
                    const leadPres = DATA.presentations.filter(function (p) {
                        return Number(p.lead_id) === Number(lead.id);
                    });
                    const pCount = document.getElementById(
                        "lead-presentations-count",
                    );
                    if (pCount)
                        pCount.textContent = leadPres.length + " sessions";

                    if (leadPres.length > 0) {
                        presContainer.innerHTML = leadPres
                            .map(function (pres) {
                                return (
                                    '<div class="p-3 rounded-xl border border-purple-100 bg-purple-50/20 text-xs"><div class="flex items-center justify-between"><span class="font-bold text-purple-900">' +
                                    escapeHtml(pres.type || "1-on-1") +
                                    " Session</span>" +
                                    (pres.outcome
                                        ? '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">' +
                                          escapeHtml(pres.outcome) +
                                          "</span>"
                                        : "") +
                                    '</div><div class="text-slate-600 mt-1">' +
                                    escapeHtml(
                                        pres.topic ||
                                            "SBL Ecosystem Presentation",
                                    ) +
                                    '</div><div class="text-slate-400 text-[11px] mt-1">' +
                                    escapeHtml(pres.date_time || "") +
                                    "</div></div>"
                                );
                            })
                            .join("");
                    } else {
                        presContainer.innerHTML =
                            '<div class="text-center py-4 text-slate-400 text-xs">No presentations scheduled yet.</div>';
                    }
                }
            }
        }

        // 3c. LEAD EDIT PAGE SYNC - ONLY on /leads/:id/edit!
        const isLeadEdit =
            leadShowParts.length === 3 &&
            leadShowParts[0] === "leads" &&
            !isNaN(Number(leadShowParts[1])) &&
            leadShowParts[2] === "edit";
        if (isLeadEdit) {
            const editLeadId = parseInt(leadShowParts[1], 10);
            const lead = DATA.leads
                ? DATA.leads.find(function (l) {
                      return Number(l.id) === editLeadId;
                  })
                : null;
            if (lead) {
                document.title =
                    "Edit Lead: " + lead.name + " - SBL Growth Manager";

                const pageTitleEl = document.getElementById("app-page-title");
                if (pageTitleEl)
                    pageTitleEl.textContent = "Edit Lead: " + lead.name;

                const titleEl =
                    document.getElementById("lead-edit-title") ||
                    document.querySelector("h2.text-base.font-bold");
                if (titleEl) titleEl.textContent = "Edit Lead: " + lead.name;

                const editForm =
                    document.getElementById("lead-edit-form") ||
                    document.querySelector('form[action*="/leads/"]');
                if (editForm) editForm.action = "/leads/" + lead.id;

                const backLink = document.getElementById("lead-edit-back-link");
                if (backLink) backLink.href = "/leads/" + lead.id;

                const cancelLink = document.getElementById(
                    "lead-edit-cancel-link",
                );
                if (cancelLink) cancelLink.href = "/leads/" + lead.id;

                const delForm =
                    document.getElementById("lead-edit-delete-form") ||
                    document.getElementById("delete-lead-form-" + lead.id);
                if (delForm) delForm.action = "/leads/" + lead.id;

                // Alpine x-data update
                const xDataContainer = document.querySelector("[x-data]");
                if (
                    xDataContainer &&
                    xDataContainer._x_dataStack &&
                    xDataContainer._x_dataStack[0]
                ) {
                    xDataContainer._x_dataStack[0].name = lead.name || "";
                    xDataContainer._x_dataStack[0].mobile = lead.mobile || "";
                    xDataContainer._x_dataStack[0].whatsapp =
                        lead.whatsapp || lead.mobile || "";
                    xDataContainer._x_dataStack[0].photoData = lead.photo || "";
                }
                const photoInput = document.querySelector(
                    'input[name="photo"]',
                );
                if (photoInput) photoInput.value = lead.photo || "";

                const nameInput = document.querySelector('input[name="name"]');
                if (nameInput) nameInput.value = lead.name || "";

                const mobileInput = document.querySelector(
                    'input[name="mobile"]',
                );
                if (mobileInput) mobileInput.value = lead.mobile || "";

                const waInput = document.querySelector(
                    'input[name="whatsapp"]',
                );
                if (waInput) waInput.value = lead.whatsapp || lead.mobile || "";

                const emailInput = document.querySelector(
                    'input[name="email"]',
                );
                if (emailInput) emailInput.value = lead.email || "";

                const locInput = document.querySelector(
                    'input[name="location"]',
                );
                if (locInput) locInput.value = lead.location || "";

                const profInput = document.querySelector(
                    'input[name="profession_or_business"]',
                );
                if (profInput)
                    profInput.value = lead.profession_or_business || "";

                const notesInput = document.querySelector(
                    'textarea[name="notes"]',
                );
                if (notesInput) notesInput.value = lead.notes || "";

                const sourceSelect = document.querySelector(
                    'select[name="lead_source_id"]',
                );
                if (sourceSelect)
                    sourceSelect.value = String(lead.lead_source_id || 1);

                const stageSelect = document.querySelector(
                    'select[name="stage"]',
                );
                if (stageSelect) stageSelect.value = lead.stage || "new";

                let interests = [];
                try {
                    interests =
                        typeof lead.interest_types === "string"
                            ? JSON.parse(lead.interest_types)
                            : lead.interest_types || [];
                } catch (e) {}
                document
                    .querySelectorAll('input[name="interest_types[]"]')
                    .forEach(function (chk) {
                        chk.checked = interests.includes(chk.value);
                    });
            }
        }

        // 4. TEAM & USERS SYNC - ONLY on /users!
        if (curPath === "/users" || curPath.startsWith("/users?")) {
            if (DATA.deletedUsers && DATA.deletedUsers.length > 0) {
                DATA.deletedUsers.forEach(function (id) {
                    document
                        .querySelectorAll('[data-user-id="' + id + '"]')
                        .forEach(function (el) {
                            el.remove();
                        });
                });
            }

            if (DATA.users && DATA.users.length > 0) {
                const userTableBody = document.querySelector("tbody.divide-y");
                const userMobileContainer = document.querySelector(
                    'div.space-y-3[class*="md:hidden"]',
                );

                DATA.users.forEach(function (user) {
                    if (
                        document.querySelector(
                            '[data-user-id="' + user.id + '"]',
                        )
                    )
                        return;

                    const initialLetter = (user.name || "U")
                        .charAt(0)
                        .toUpperCase();
                    const roleLabel = user.role_name || "Staff Member";

                    if (userTableBody) {
                        const tr = document.createElement("tr");
                        tr.setAttribute("data-user-id", user.id);
                        tr.className =
                            "hover:bg-slate-50/60 transition-colors bg-orange-50/20";
                        tr.innerHTML =
                            '<td class="py-3 px-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">' +
                            initialLetter +
                            '</div><div><div class="font-semibold text-slate-900 flex items-center gap-2"><span>' +
                            user.name +
                            '</span></div><div class="text-xs text-slate-400">' +
                            user.email +
                            '</div></div></div></td><td class="py-3 px-4"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-purple-100 text-purple-800 border-purple-200">' +
                            roleLabel +
                            '</span></td><td class="py-3 px-4"><span class="text-slate-700 font-medium">' +
                            (user.designation || "Staff Member") +
                            '</span></td><td class="py-3 px-4 text-xs">' +
                            (user.phone
                                ? "<span>📞 " + user.phone + "</span>"
                                : '<span class="text-slate-400 italic">No phone set</span>') +
                            '</td><td class="py-3 px-4 text-center"><div class="inline-flex items-center gap-2 text-xs"><span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md">👥 0</span><span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md">✅ 0</span></div></td><td class="py-3 px-4 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span></td><td class="py-3 px-4 text-right space-x-2"><button type="button" onclick="window.Alpine && window.Alpine.raw ? (function(){ var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].editingUser = { id: ' +
                            user.id +
                            ", name: '" +
                            user.name.replace(/'/g, "\\\'") +
                            "', email: '" +
                            user.email +
                            "', phone: '" +
                            (user.phone || "") +
                            "', designation: '" +
                            (user.designation || "") +
                            "', role_id: '" +
                            (user.role_id || "") +
                            '\', status: \'active\' }; c._x_dataStack[0].editModalOpen = true; } })() : null" class="text-orange-600 hover:text-orange-800 font-semibold text-xs px-2 py-1 rounded hover:bg-orange-50 transition-colors">Edit</button><form action="/users/' +
                            user.id +
                            '" method="POST" class="inline" onsubmit="return confirm(&quot;Are you sure you want to delete member?&quot;);"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold text-xs px-2 py-1 rounded hover:bg-rose-50 transition-colors">Delete</button></form></td>';
                        userTableBody.appendChild(tr);
                    }

                    if (userMobileContainer) {
                        const mCard = document.createElement("div");
                        mCard.setAttribute("data-user-id", user.id);
                        mCard.className =
                            "bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3 bg-orange-50/20";
                        mCard.innerHTML =
                            '<div class="flex items-start justify-between gap-3"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700">' +
                            initialLetter +
                            '</div><div><div class="font-bold text-slate-900 text-sm flex items-center gap-1.5"><span>' +
                            user.name +
                            '</span></div><div class="text-xs text-slate-500">' +
                            (user.designation || "Staff Member") +
                            '</div><div class="text-[11px] text-slate-400">' +
                            user.email +
                            '</div></div></div><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800">' +
                            roleLabel +
                            '</span></div><div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600"><div>' +
                            (user.phone ? "📞 " + user.phone : "Active") +
                            '</div><div><form action="/users/' +
                            user.id +
                            '" method="POST" onsubmit="return confirm(&quot;Delete?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="text-rose-600 font-semibold text-xs">Delete</button></form></div></div>';
                        userMobileContainer.appendChild(mCard);
                    }
                });
            }
        }

        // 5. TEAM EXPLORER (5 LEFT + 5 RIGHT) & DIRECTORY SYNC - ON /binary & /team!
        if (
            curPath === "/binary" ||
            curPath.startsWith("/binary?") ||
            curPath.startsWith("/binary/") ||
            curPath === "/team" ||
            curPath.startsWith("/team?") ||
            curPath.startsWith("/team/")
        ) {
            if (DATA.deletedNodes && DATA.deletedNodes.length > 0) {
                DATA.deletedNodes.forEach(function (id) {
                    document
                        .querySelectorAll('[data-node-id="' + id + '"]')
                        .forEach(function (el) {
                            el.remove();
                        });
                });
            }

            if (DATA.nodes && DATA.nodes.length > 0) {
                const nodeMap = {};
                DATA.nodes.forEach(function (n) {
                    nodeMap[n.id] = n;
                });

                // Calculate dynamic direct left and right counts for every node
                DATA.nodes.forEach(function (n) {
                    const directL = DATA.nodes.filter(function (c) {
                        return (
                            Number(c.parent_id) === Number(n.id) &&
                            (c.branch === "LEFT" || c.position === "left")
                        );
                    }).length;
                    const directR = DATA.nodes.filter(function (c) {
                        return (
                            Number(c.parent_id) === Number(n.id) &&
                            (c.branch === "RIGHT" || c.position === "right")
                        );
                    }).length;
                    n.direct_left_count = directL;
                    n.direct_right_count = directR;
                    n.direct_total_count = directL + directR;
                    n.is_fme = directL >= 5 && directR >= 5;
                });

                // Determine currently viewed member
                let viewedMemberId = null;
                const bParts = curPath.split("?")[0].split("/").filter(Boolean);
                if (
                    (bParts[0] === "team" || bParts[0] === "binary") &&
                    bParts[1] &&
                    !isNaN(Number(bParts[1]))
                ) {
                    viewedMemberId = parseInt(bParts[1], 10);
                }
                let currentMember = null;
                if (viewedMemberId) {
                    currentMember = nodeMap[viewedMemberId];
                }
                if (!currentMember) {
                    const roots = DATA.nodes.filter(function (n) {
                        return !n.parent_id;
                    });
                    currentMember = roots.length > 0 ? roots[0] : DATA.nodes[0];
                }

                const activeCurr =
                    localStorage.getItem("sbl_currency") || "BDT";
                const fmtMoney = function (numVal) {
                    const num = parseFloat(numVal) || 0;
                    if (activeCurr === "USD")
                        return "$" + Math.round(num / 120).toLocaleString();
                    return "৳ " + Math.round(num).toLocaleString();
                };

                // A. Sync Top Current Member Summary Card
                if (currentMember) {
                    let contribs = [];
                    try {
                        contribs =
                            typeof currentMember.contributions === "string"
                                ? JSON.parse(currentMember.contributions)
                                : currentMember.contributions || [];
                    } catch (e) {}
                    let pv = Number(currentMember.point_value) || 0;
                    if (Array.isArray(contribs) && contribs.length > 0) {
                        pv = contribs.reduce(function (sum, c) {
                            return sum + (Number(c.amount) || 0);
                        }, 0);
                    }
                    const code =
                        currentMember.member_code || "SBL-" + currentMember.id;
                    const username =
                        currentMember.username ||
                        (code.startsWith("@")
                            ? code
                            : "@" +
                              code.replace(/[^a-zA-Z0-9_]/g, "").toLowerCase());
                    const sponsorName =
                        currentMember.sponsor_name ||
                        (currentMember.sponsor_id &&
                        nodeMap[currentMember.sponsor_id]
                            ? nodeMap[currentMember.sponsor_id].member_name
                            : currentMember.parent_id &&
                                nodeMap[currentMember.parent_id]
                              ? nodeMap[currentMember.parent_id].member_name
                              : "Md. Samim");

                    const initEl = document.querySelector(
                        "[data-current-member-initial]",
                    );
                    if (initEl)
                        initEl.textContent = (currentMember.member_name || "M")
                            .charAt(0)
                            .toUpperCase();

                    const nameEl = document.querySelector(
                        "[data-current-member-name]",
                    );
                    if (nameEl) nameEl.textContent = currentMember.member_name;

                    const rankEl = document.querySelector(
                        "[data-current-member-rank]",
                    );
                    if (rankEl)
                        rankEl.textContent =
                            currentMember.rank_name || "Member";

                    const userEl = document.querySelector(
                        "[data-current-member-username]",
                    );
                    if (userEl) userEl.textContent = username;

                    const codeEl = document.querySelector(
                        "[data-current-member-code]",
                    );
                    if (codeEl)
                        codeEl.textContent =
                            currentMember.member_code ||
                            "SBL-" + currentMember.id;

                    const phoneEl = document.querySelector(
                        "[data-current-member-phone]",
                    );
                    const phoneSepEl = document.querySelector(
                        "[data-current-member-phone-sep]",
                    );
                    if (phoneEl) {
                        phoneEl.textContent = currentMember.phone || "";
                        if (phoneSepEl)
                            phoneSepEl.style.display = currentMember.phone
                                ? ""
                                : "none";
                        phoneEl.style.display = currentMember.phone
                            ? ""
                            : "none";
                    }

                    const sponEl = document.querySelector(
                        "[data-current-member-sponsor]",
                    );
                    if (sponEl) sponEl.textContent = sponsorName;

                    const dTotEl = document.querySelector(
                        "[data-current-direct-total]",
                    );
                    if (dTotEl)
                        dTotEl.textContent =
                            currentMember.direct_total_count + "/10";

                    const dSubEl = document.querySelector(
                        "[data-current-direct-sub]",
                    );
                    if (dSubEl)
                        dSubEl.textContent = currentMember.direct_total_count;

                    const dSplitEl = document.querySelector(
                        "[data-current-direct-split]",
                    );
                    if (dSplitEl)
                        dSplitEl.innerHTML =
                            '<span class="text-emerald-400">👈 Left: ' +
                            currentMember.direct_left_count +
                            '/5</span><span class="text-slate-600">|</span><span class="text-blue-400">👉 Right: ' +
                            currentMember.direct_right_count +
                            "/5</span>";

                    const ownInvEl = document.querySelector(
                        "[data-current-own-investment]",
                    );
                    if (ownInvEl) ownInvEl.textContent = fmtMoney(pv);

                    const invCountEl = document.querySelector(
                        "[data-current-investment-count]",
                    );
                    if (invCountEl)
                        invCountEl.textContent =
                            contribs.length + " Investment Records";

                    const lHdrCount = document.querySelector(
                        "[data-left-header-count]",
                    );
                    if (lHdrCount)
                        lHdrCount.textContent =
                            (currentMember.direct_left_count || 0) +
                            " Active / 5 Max";

                    const rHdrCount = document.querySelector(
                        "[data-right-header-count]",
                    );
                    if (rHdrCount)
                        rHdrCount.textContent =
                            (currentMember.direct_right_count || 0) +
                            " Active / 5 Max";

                    const btnDetails = document.querySelector(
                        "[data-btn-full-details]",
                    );
                    if (btnDetails) {
                        btnDetails.onclick = function () {
                            var c = document.querySelector("[x-data]");
                            if (c && c._x_dataStack)
                                c._x_dataStack[0].openDetailsModal(
                                    currentMember.id,
                                );
                        };
                    }
                    const btnEdit = document.querySelector(
                        "[data-btn-edit-member]",
                    );
                    if (btnEdit) {
                        btnEdit.onclick = function () {
                            var c = document.querySelector("[x-data]");
                            if (c && c._x_dataStack)
                                c._x_dataStack[0].openEditModal(currentMember);
                        };
                    }

                    // Render Slot Card Helper
                    function renderSlotCard(
                        node,
                        branch,
                        slotNumber,
                        parentId,
                        parentName,
                        parentCode,
                    ) {
                        const isLeft = branch === "LEFT";
                        const slotLabel = (isLeft ? "L-" : "R-") + slotNumber;
                        if (!node || node.is_vacant) {
                            return (
                                '<div class="p-4 rounded-2xl border-2 border-dashed ' +
                                (isLeft
                                    ? "border-emerald-300/80 bg-emerald-50/20 hover:bg-emerald-50/60"
                                    : "border-blue-300/80 bg-blue-50/20 hover:bg-blue-50/60") +
                                ' transition-all flex items-center justify-between gap-3 group">' +
                                '<div class="flex items-center gap-3">' +
                                '<div class="w-10 h-10 rounded-xl ' +
                                (isLeft
                                    ? "bg-emerald-100 text-emerald-700 border-emerald-200"
                                    : "bg-blue-100 text-blue-700 border-blue-200") +
                                ' font-bold flex items-center justify-center text-xs border">' +
                                slotLabel +
                                "</div>" +
                                "<div>" +
                                '<div class="text-xs font-bold text-slate-700">Slot ' +
                                slotLabel +
                                " (খালি রয়েছে)</div>" +
                                '<div class="text-[11px] text-slate-400">নতুন মেম্বারকে এই পজিশনে বসান</div>' +
                                "</div>" +
                                "</div>" +
                                '<button type="button" onclick="window.Alpine && window.Alpine.raw ? (function(){ var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openPlacementModal(' +
                                parentId +
                                ", '" +
                                (parentName || "").replace(/'/g, "\\'") +
                                "', '" +
                                (parentCode || "") +
                                "', '" +
                                branch +
                                "', " +
                                slotNumber +
                                '); } })() : null" class="px-3.5 py-2 rounded-xl ' +
                                (isLeft
                                    ? "bg-emerald-600 hover:bg-emerald-700"
                                    : "bg-blue-600 hover:bg-blue-700") +
                                ' text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1 cursor-pointer">' +
                                "<span>➕</span> <span>Place Member</span>" +
                                "</button>" +
                                "</div>"
                            );
                        }

                        const isTarget = Boolean(Number(node.is_target));
                        const cCode = node.member_code || "SBL-" + node.id;
                        let cPv = Number(node.point_value) || 100;
                        let cleanPhone = (node.phone || "").replace(
                            /[^0-9]/g,
                            "",
                        );
                        let waNum = cleanPhone;
                        if (waNum.startsWith("01") && waNum.length === 11) {
                            waNum = "88" + waNum;
                        }

                        let phoneIconsHtml = "";
                        if (node.phone) {
                            phoneIconsHtml +=
                                '<a href="tel:' +
                                escapeHtml(node.phone) +
                                '" title="Call ' +
                                escapeHtml(node.phone) +
                                '" class="w-7 h-7 rounded-lg ' +
                                (isLeft
                                    ? "bg-emerald-50 hover:bg-emerald-600 text-emerald-600"
                                    : "bg-blue-50 hover:bg-blue-600 text-blue-600") +
                                ' hover:text-white flex items-center justify-center transition-all shadow-2xs">' +
                                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>' +
                                "</a>";
                            if (waNum) {
                                phoneIconsHtml +=
                                    '<a href="https://wa.me/' +
                                    waNum +
                                    '" target="_blank" title="WhatsApp" class="w-7 h-7 rounded-lg ' +
                                    (isLeft
                                        ? "bg-emerald-50 hover:bg-emerald-600 text-emerald-600"
                                        : "bg-blue-50 hover:bg-blue-600 text-blue-600") +
                                    ' hover:text-white flex items-center justify-center transition-all shadow-2xs">' +
                                    '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>' +
                                    "</a>";
                            }
                        }

                        return (
                            '<div data-node-id="' +
                            node.id +
                            '" class="p-4 rounded-2xl bg-white border border-slate-200 hover:' +
                            (isLeft
                                ? "border-emerald-400/80"
                                : "border-blue-400/80") +
                            ' shadow-xs hover:shadow-md transition-all space-y-3">' +
                            '<div class="flex items-start justify-between gap-3">' +
                            '<div class="flex items-center gap-3">' +
                            '<div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-orange-400 font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0 border border-slate-700">' +
                            escapeHtml(
                                (node.member_name || "M")
                                    .charAt(0)
                                    .toUpperCase(),
                            ) +
                            "</div>" +
                            "<div>" +
                            '<div class="flex items-center gap-2">' +
                            '<span class="px-2 py-0.5 rounded-md ' +
                            (isLeft
                                ? "bg-emerald-100 text-emerald-800"
                                : "bg-blue-100 text-blue-800") +
                            ' font-black text-[10px]">' +
                            "SLOT " +
                            slotLabel +
                            "</span>" +
                            '<span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">GEN 1</span>' +
                            (isTarget
                                ? '<span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded">🎯 Target</span>'
                                : "") +
                            "</div>" +
                            '<div class="font-black text-slate-900 text-sm mt-0.5 hover:text-orange-600 cursor-pointer" onclick="window.Alpine && window.Alpine.raw ? (function(){ var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openDetailsModal(' +
                            node.id +
                            '); } })() : null">' +
                            escapeHtml(node.member_name) +
                            "</div>" +
                            '<div class="flex items-center gap-1.5 text-xs text-slate-500 font-mono mt-0.5">' +
                            "<span>" +
                            escapeHtml(cCode) +
                            "</span>" +
                            "</div>" +
                            "</div>" +
                            "</div>" +
                            '<div class="flex items-center gap-1">' +
                            phoneIconsHtml +
                            "</div>" +
                            "</div>" +
                            '<div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">' +
                            '<div class="flex items-center gap-2">' +
                            '<span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[11px]">' +
                            escapeHtml(node.rank_name || "Member") +
                            "</span>" +
                            '<span class="font-black ' +
                            (isLeft
                                ? "text-emerald-700 bg-emerald-50 border-emerald-200/80"
                                : "text-blue-700 bg-blue-50 border-blue-200/80") +
                            ' px-2 py-0.5 rounded border text-[11px]">' +
                            cPv +
                            " BV</span>" +
                            '<span class="text-slate-400 text-[11px]">' +
                            escapeHtml(node.package_name || "National") +
                            "</span>" +
                            "</div>" +
                            '<div class="flex items-center gap-1.5">' +
                            '<a href="/team/' +
                            node.id +
                            '" class="px-2.5 py-1 rounded-lg bg-orange-50 hover:bg-orange-600 text-orange-700 hover:text-white font-bold text-xs transition-all flex items-center gap-1 border border-orange-200/60 shadow-2xs"><span>👥</span> <span>Explore Team</span></a>' +
                            '<button type="button" onclick="window.Alpine && window.Alpine.raw ? (function(){ var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openDetailsModal(' +
                            node.id +
                            '); } })() : null" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition-colors cursor-pointer" title="View Details">👁️</button>' +
                            "</div>" +
                            "</div>" +
                            "</div>"
                        );
                    }

                    // B. Sync 5 LEFT Slots
                    const leftContainer = document.querySelector(
                        "[data-left-slots-container]",
                    );
                    if (leftContainer) {
                        let leftHtml = "";
                        for (let s = 1; s <= 5; s++) {
                            const child = DATA.nodes.find(function (n) {
                                return (
                                    Number(n.parent_id) ===
                                        Number(currentMember.id) &&
                                    (n.branch === "LEFT" ||
                                        n.position === "left") &&
                                    Number(n.slot_number || 1) === s
                                );
                            });
                            leftHtml += renderSlotCard(
                                child,
                                "LEFT",
                                s,
                                currentMember.id,
                                currentMember.member_name,
                                currentMember.member_code ||
                                    "SBL-" + currentMember.id,
                            );
                        }
                        leftContainer.innerHTML = leftHtml;
                    }

                    // C. Sync 5 RIGHT Slots
                    const rightContainer = document.querySelector(
                        "[data-right-slots-container]",
                    );
                    if (rightContainer) {
                        let rightHtml = "";
                        for (let s = 1; s <= 5; s++) {
                            const child = DATA.nodes.find(function (n) {
                                return (
                                    Number(n.parent_id) ===
                                        Number(currentMember.id) &&
                                    (n.branch === "RIGHT" ||
                                        n.position === "right") &&
                                    Number(n.slot_number || 1) === s
                                );
                            });
                            rightHtml += renderSlotCard(
                                child,
                                "RIGHT",
                                s,
                                currentMember.id,
                                currentMember.member_name,
                                currentMember.member_code ||
                                    "SBL-" + currentMember.id,
                            );
                        }
                        rightContainer.innerHTML = rightHtml;
                    }
                }

                // D. Sync Member Directory Table
                const binaryTableBody = document.querySelector(
                    "tbody[data-binary-table-body]",
                );
                if (binaryTableBody) {
                    DATA.nodes.forEach(function (node) {
                        const existingRow = binaryTableBody.querySelector(
                            'tr[data-node-id="' + node.id + '"]',
                        );
                        if (existingRow) {
                            const nameDiv = existingRow.querySelector(
                                ".font-bold.text-slate-900",
                            );
                            if (nameDiv) {
                                nameDiv.textContent = node.member_name;
                                nameDiv.onclick = function () {
                                    var c = document.querySelector("[x-data]");
                                    if (c && c._x_dataStack)
                                        c._x_dataStack[0].openDetailsModal(
                                            node.id,
                                        );
                                };
                            }
                            const codeDiv =
                                existingRow.querySelector(".font-mono");
                            if (codeDiv) {
                                codeDiv.innerHTML =
                                    "<span>" +
                                    escapeHtml(
                                        node.member_code || "SBL-" + node.id,
                                    ) +
                                    "</span>" +
                                    (node.phone
                                        ? "<span> • </span><span>" +
                                          escapeHtml(node.phone) +
                                          "</span>"
                                        : "") +
                                    (node.notes
                                        ? ' <span title="' +
                                          escapeHtml(node.notes) +
                                          '" class="cursor-help text-amber-600">📝</span>'
                                        : "");
                            }
                        } else {
                            const tr = document.createElement("tr");
                            tr.setAttribute("data-node-id", node.id);
                            tr.className =
                                "hover:bg-slate-50/60 transition-colors bg-orange-50/20";
                            const initLetter = (node.member_name || "M")
                                .charAt(0)
                                .toUpperCase();
                            tr.innerHTML =
                                '<td class="py-3.5 px-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-800 text-orange-400 font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-xs">' +
                                initLetter +
                                '</div><div><div class="font-bold text-slate-900 hover:text-orange-600 transition-colors">' +
                                escapeHtml(node.member_name) +
                                '</div><div class="text-[11px] text-slate-400 font-mono">' +
                                escapeHtml(
                                    node.member_code || "SBL-" + node.id,
                                ) +
                                '</div></div></div></td><td class="py-3.5 px-4"><span class="font-semibold text-slate-800">' +
                                (node.parent_id
                                    ? "Node #" + node.parent_id
                                    : "Top Root") +
                                '</span><span class="block text-[10px] text-slate-400 uppercase">' +
                                (node.position || "Root") +
                                '</span></td><td class="py-3.5 px-4"><span class="font-semibold text-slate-800 block">' +
                                (node.package_name || "National 120k") +
                                '</span><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700">' +
                                (node.rank_name || "Member") +
                                '</span></td><td class="py-3.5 px-4 text-center"><span class="font-bold text-emerald-700">' +
                                (node.left_count || 0) +
                                '</span><div class="text-[10px] text-slate-400 font-medium">' +
                                (node.left_bv || 0) +
                                ' BV</div></td><td class="py-3.5 px-4 text-center"><span class="font-bold text-blue-700">' +
                                (node.right_count || 0) +
                                '</span><div class="text-[10px] text-slate-400 font-medium">' +
                                (node.right_bv || 0) +
                                ' BV</div></td><td class="py-3.5 px-4 text-center font-bold text-orange-600">' +
                                (node.matched_pairs || 0) +
                                '</td><td class="py-3.5 px-4 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span></td><td class="py-3.5 px-4 text-right"><form action="/team/' +
                                node.id +
                                '" method="POST" onsubmit="return confirm(&quot;Delete member?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold">Delete</button></form></td>';
                            binaryTableBody.prepend(tr);
                        }
                    });
                }
            }
        }

        // 6. CONTACTS SYNC - ONLY on /contacts!
        if (curPath === "/contacts" || curPath.startsWith("/contacts?")) {
            const contactsTbody = document.getElementById(
                "contacts-table-body",
            );
            if (
                contactsTbody &&
                DATA.contacts &&
                Array.isArray(DATA.contacts)
            ) {
                contactsTbody.innerHTML = DATA.contacts
                    .map(function (c) {
                        const isPrimary = Number(c.is_primary) === 1;
                        const iconBg = isPrimary
                            ? "bg-emerald-50 border border-emerald-200 text-emerald-700"
                            : "bg-slate-100 border border-slate-200/80 text-slate-700";
                        const badgeHtml = c.badge
                            ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ' +
                              (isPrimary
                                  ? "bg-emerald-100 text-emerald-800 border border-emerald-200"
                                  : "bg-slate-100 text-slate-700 border border-slate-200") +
                              '">' +
                              escapeHtml(c.badge) +
                              "</span>"
                            : "";
                        const descHtml = c.description
                            ? '<p class="text-xs text-slate-500 line-clamp-1 mt-0.5 max-w-xs" title="' +
                              escapeHtml(c.description) +
                              '">' +
                              escapeHtml(c.description) +
                              "</p>"
                            : "";
                        const cleanPhone = (c.phone || "").replace(
                            /[^0-9]/g,
                            "",
                        );
                        const cleanWa = (c.whatsapp || "").replace(
                            /[^0-9]/g,
                            "",
                        );

                        let personHtml =
                            '<span class="text-slate-400 text-xs">—</span>';
                        if (c.contact_person) {
                            personHtml =
                                '<div class="flex items-center gap-2 text-sm text-slate-800 font-medium">' +
                                '<svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>' +
                                "<span>" +
                                escapeHtml(c.contact_person) +
                                "</span>" +
                                "</div>";
                        }

                        let phoneHtml =
                            '<span class="text-slate-400 text-xs">—</span>';
                        if (c.phone) {
                            phoneHtml =
                                '<div class="flex items-center gap-2">' +
                                '<span class="font-bold text-slate-900 text-sm font-mono">' +
                                escapeHtml(c.phone) +
                                "</span>" +
                                '<button type="button" onclick="window.copyContactToClipboard(\' ' +
                                escapeHtml(c.phone) +
                                '\', \'Phone number\')" title="Copy Phone" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors cursor-pointer">' +
                                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' +
                                "</button>" +
                                '<a href="tel:' +
                                cleanPhone +
                                '" title="Direct Call" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-900 hover:bg-black text-emerald-400 shadow-xs transition-colors active:scale-95">' +
                                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>' +
                                "</a>" +
                                "</div>";
                        }

                        let waHtml =
                            '<span class="text-slate-400 text-xs">—</span>';
                        if (c.whatsapp) {
                            waHtml =
                                '<div class="flex items-center gap-2">' +
                                '<span class="font-bold text-slate-900 text-sm font-mono">' +
                                escapeHtml(c.whatsapp) +
                                "</span>" +
                                '<button type="button" onclick="window.copyContactToClipboard(\' ' +
                                escapeHtml(c.whatsapp) +
                                '\', \'WhatsApp number\')" title="Copy WhatsApp" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors cursor-pointer">' +
                                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' +
                                "</button>" +
                                '<a href="https://wa.me/' +
                                cleanWa +
                                "?text=" +
                                encodeURIComponent(
                                    "Hello, I would like to connect with SBL Helpdesk.",
                                ) +
                                '" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors active:scale-95">' +
                                '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.769.815 2.796.815 3.182 0 5.768-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm3.385 8.163c-.143.402-.832.744-1.144.789-.312.046-.713.064-2.032-.477-.735-.302-1.396-.757-1.93-1.288-.535-.53-.992-1.19-1.295-1.924-.543-1.319-.525-1.72-.479-2.032.045-.312.387-1.001.789-1.144.135-.048.277-.024.38.064l.872 1.071c.092.113.109.269.043.4l-.391.783c-.066.131-.038.29.068.396.406.407.886.732 1.413.957.147.063.315.029.426-.083l.635-.634c.121-.122.302-.152.455-.075l1.28.639c.143.072.224.223.199.381l-.105.794z"/></svg>' +
                                "</a>" +
                                "</div>";
                        }

                        let emailHtml =
                            '<span class="text-slate-400 text-xs">—</span>';
                        if (c.email) {
                            emailHtml =
                                '<a href="mailto:' +
                                escapeHtml(c.email) +
                                '" class="text-xs text-slate-700 hover:text-emerald-600 font-medium truncate max-w-[160px] inline-flex items-center gap-1.5" title="' +
                                escapeHtml(c.email) +
                                '">' +
                                '<svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' +
                                "<span>" +
                                escapeHtml(c.email) +
                                "</span>" +
                                "</a>";
                        }

                        const hoursHtml =
                            '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-slate-600 text-xs font-medium">' +
                            '<svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' +
                            "<span>" +
                            escapeHtml(
                                c.available_hours || "10:00 AM - 08:00 PM",
                            ) +
                            "</span>" +
                            "</span>";

                        const searchData = (
                            c.department +
                            " " +
                            (c.contact_person || "") +
                            " " +
                            c.phone +
                            " " +
                            (c.whatsapp || "") +
                            " " +
                            (c.email || "") +
                            " " +
                            (c.badge || "") +
                            " " +
                            (c.description || "")
                        ).toLowerCase();

                        return (
                            '<tr data-contact-id="' +
                            c.id +
                            '" data-search="' +
                            escapeHtml(searchData) +
                            '" class="hover:bg-slate-50/75 transition-colors group">' +
                            '<td class="py-4 px-4 align-middle">' +
                            '<div class="flex items-center gap-3">' +
                            '<div class="w-10 h-10 rounded-xl ' +
                            iconBg +
                            ' flex items-center justify-center text-xl flex-shrink-0">' +
                            escapeHtml(c.icon || "📞") +
                            "</div>" +
                            '<div class="min-w-0">' +
                            '<div class="flex items-center gap-2 flex-wrap">' +
                            '<span class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors">' +
                            escapeHtml(c.department) +
                            "</span>" +
                            badgeHtml +
                            "</div>" +
                            descHtml +
                            "</div>" +
                            "</div>" +
                            "</td>" +
                            '<td class="py-4 px-4 align-middle whitespace-nowrap">' +
                            personHtml +
                            "</td>" +
                            '<td class="py-4 px-4 align-middle whitespace-nowrap">' +
                            phoneHtml +
                            "</td>" +
                            '<td class="py-4 px-4 align-middle whitespace-nowrap">' +
                            waHtml +
                            "</td>" +
                            '<td class="py-4 px-4 align-middle whitespace-nowrap">' +
                            emailHtml +
                            "</td>" +
                            '<td class="py-4 px-4 align-middle whitespace-nowrap">' +
                            hoursHtml +
                            "</td>" +
                            '<td class="py-4 px-4 align-middle text-right whitespace-nowrap">' +
                            '<div class="inline-flex items-center gap-1.5">' +
                            '<button type="button" onclick="window.openContactEditModalById(' +
                            c.id +
                            ')" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer" title="Edit Contact">' +
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>' +
                            "</button>" +
                            '<form action="/contacts/' +
                            c.id +
                            '" method="POST" onsubmit="return confirm(\'Delete this contact hotline?\');" class="inline">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Delete Contact">' +
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>' +
                            "</button>" +
                            "</form>" +
                            "</div>" +
                            "</td>" +
                            "</tr>"
                        );
                    })
                    .join("");
            }

            // Live search filter on input
            const contactSearchInput = document.getElementById(
                "contacts-search-input",
            );
            if (contactSearchInput && !contactSearchInput.__hasListener) {
                contactSearchInput.__hasListener = true;
                contactSearchInput.addEventListener("input", function (e) {
                    const q = (e.target.value || "").toLowerCase().trim();
                    const rows = document.querySelectorAll(
                        "#contacts-table-body tr[data-contact-id]",
                    );
                    rows.forEach(function (row) {
                        const txt = (
                            row.getAttribute("data-search") ||
                            row.textContent ||
                            ""
                        ).toLowerCase();
                        row.style.display = !q || txt.includes(q) ? "" : "none";
                    });
                });
            }
        }

        // 7. ECOSYSTEM WEBSITES SYNC
        if (
            curPath === "/toolkit" ||
            curPath.startsWith("/toolkit?") ||
            curPath === "/ecosystem" ||
            curPath.startsWith("/ecosystem?")
        ) {
            const toolkitGrid = document.getElementById(
                "toolkit-websites-grid",
            );
            if (
                toolkitGrid &&
                DATA.ecosystem &&
                Array.isArray(DATA.ecosystem)
            ) {
                toolkitGrid.innerHTML = DATA.ecosystem
                    .map(function (el) {
                        const badgeHtml = el.badge
                            ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">' +
                              escapeHtml(el.badge) +
                              "</span>"
                            : "";
                        return (
                            '<div data-link-id="' +
                            el.id +
                            '" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md hover:border-orange-200 transition-all group">' +
                            '<div class="space-y-3">' +
                            '<div class="flex items-start justify-between gap-3">' +
                            '<div class="flex items-center gap-3">' +
                            '<div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 group-hover:bg-orange-50 flex items-center justify-center text-xl flex-shrink-0">' +
                            escapeHtml(el.icon || "🌐") +
                            "</div>" +
                            "<div>" +
                            '<h4 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors">' +
                            escapeHtml(el.title) +
                            "</h4>" +
                            '<span class="text-[10px] font-semibold text-slate-400">' +
                            escapeHtml(el.category) +
                            "</span>" +
                            "</div>" +
                            "</div>" +
                            badgeHtml +
                            "</div>" +
                            '<p class="text-xs text-slate-600 leading-relaxed">' +
                            escapeHtml(el.description || "") +
                            "</p>" +
                            '<div class="text-[11px] font-mono text-slate-400 truncate">' +
                            escapeHtml(el.url) +
                            "</div>" +
                            "</div>" +
                            '<div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2">' +
                            '<a href="' +
                            escapeHtml(el.url) +
                            '" target="_blank" rel="noopener noreferrer" class="flex-1 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-xs">' +
                            "<span>Visit Website</span>" +
                            '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>' +
                            "</a>" +
                            '<button type="button" onclick="window.openEditWebsiteModalById(' +
                            el.id +
                            ')" class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors cursor-pointer" title="Edit Website">✏️</button>' +
                            '<form action="/ecosystem/' +
                            el.id +
                            '" method="POST" onsubmit="return confirm(\x27Remove ' +
                            escapeHtml(el.title).replace(/'/g, "\\'") +
                            '?\x27);" class="inline">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer" title="Delete Website">🗑️</button>' +
                            "</form>" +
                            "</div>" +
                            "</div>"
                        );
                    })
                    .join("");
            }
        }

        // 7. PRESENTATIONS SYNC - ONLY on /presentations!
        if (
            curPath === "/presentations" ||
            curPath.startsWith("/presentations?")
        ) {
            if (
                DATA.deletedPresentations &&
                DATA.deletedPresentations.length > 0
            ) {
                DATA.deletedPresentations.forEach(function (id) {
                    document
                        .querySelectorAll('[data-presentation-id="' + id + '"]')
                        .forEach(function (el) {
                            el.remove();
                        });
                });
            }
            if (DATA.presentations && DATA.presentations.length > 0) {
                const presGrid = document.querySelector(
                    'div[class*="grid-cols-1"][class*="lg:grid-cols-3"]',
                );
                const emptyNotice = document.querySelector(
                    ".empty-presentations-notice, .col-span-full",
                );
                if (emptyNotice) {
                    emptyNotice.remove();
                }
                if (presGrid) {
                    DATA.presentations
                        .slice()
                        .reverse()
                        .forEach(function (pres) {
                            if (
                                document.querySelector(
                                    '[data-presentation-id="' + pres.id + '"]',
                                )
                            )
                                return;
                            const card = document.createElement("div");
                            card.setAttribute("data-presentation-id", pres.id);
                            card.className =
                                "bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-200 transition-all flex flex-col justify-between bg-orange-50/10";

                            let outcomeBadge =
                                '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Pending</span>';
                            if (pres.outcome) {
                                outcomeBadge =
                                    '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">' +
                                    pres.outcome +
                                    "</span>";
                            }

                            let leadBox = "";
                            if (
                                pres.lead_id &&
                                (pres.lead_name || pres.lead_mobile)
                            ) {
                                leadBox =
                                    '<div class="text-xs text-slate-600 mt-2 p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px] uppercase font-semibold">Lead:</span><a href="/leads/' +
                                    pres.lead_id +
                                    '" class="font-bold text-orange-600 hover:underline">' +
                                    (pres.lead_name ||
                                        "Lead #" + pres.lead_id) +
                                    "</a>" +
                                    (pres.lead_mobile
                                        ? '<span class="text-slate-500 text-[11px] block">📞 ' +
                                          pres.lead_mobile +
                                          "</span>"
                                        : "") +
                                    "</div>";
                            }

                            let qaBox = "";
                            if (pres.questions || pres.objections) {
                                qaBox =
                                    '<div class="mt-3 space-y-1 text-xs text-slate-600">' +
                                    (pres.questions
                                        ? '<div><strong class="text-slate-800">Q:</strong> ' +
                                          pres.questions +
                                          "</div>"
                                        : "") +
                                    (pres.objections
                                        ? '<div><strong class="text-rose-700">Objection:</strong> ' +
                                          pres.objections +
                                          "</div>"
                                        : "") +
                                    "</div>";
                            }

                            const dtStr = pres.date_time
                                ? new Date(pres.date_time).toLocaleString(
                                      "en-US",
                                      {
                                          day: "2-digit",
                                          month: "short",
                                          year: "numeric",
                                          hour: "2-digit",
                                          minute: "2-digit",
                                      },
                                  )
                                : "Scheduled";

                            card.innerHTML =
                                '<div><div class="flex items-center justify-between gap-2 mb-2"><span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">' +
                                (pres.type || "1-on-1") +
                                "</span>" +
                                outcomeBadge +
                                '</div><h4 class="font-bold text-sm text-slate-900 mb-1">' +
                                (pres.topic || "SBL Ecosystem Presentation") +
                                "</h4>" +
                                leadBox +
                                qaBox +
                                '</div><div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400"><span>' +
                                dtStr +
                                '</span><div class="flex items-center gap-2"><span>By ' +
                                (pres.user_name || "Admin") +
                                '</span><form action="/presentations/' +
                                pres.id +
                                '" method="POST" onsubmit="return confirm(&quot;Delete presentation record?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1 text-slate-400 hover:text-rose-600" title="Delete Presentation">🗑️</button></form></div></div>';
                            presGrid.prepend(card);
                        });
                }
            }
        }

        // 8. TASKS SYNC - ONLY on /tasks!
        if (curPath === "/tasks" || curPath.startsWith("/tasks?")) {
            if (DATA.tasks && DATA.tasks.length > 0) {
                const tasksContainer = document.querySelector(
                    'div.divide-y[class*="rounded-2xl"]',
                );
                if (tasksContainer) {
                    DATA.tasks.forEach(function (task) {
                        let row = document.querySelector(
                            '[data-task-id="' + task.id + '"]',
                        );
                        if (!row) {
                            row = document.createElement("div");
                            row.setAttribute("data-task-id", task.id);
                            row.className =
                                "p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition-colors bg-orange-50/10";
                            const dtStr = task.due_at
                                ? new Date(task.due_at).toLocaleString(
                                      "en-US",
                                      {
                                          day: "2-digit",
                                          month: "short",
                                          year: "numeric",
                                          hour: "2-digit",
                                          minute: "2-digit",
                                      },
                                  )
                                : "Scheduled";
                            const isCompleted = task.status === "Completed";

                            const editDataJson = JSON.stringify({
                                id: task.id,
                                title: task.title || "",
                                type: task.type || "Follow-up",
                                priority: task.priority || "Medium",
                                related_lead_id: task.related_lead_id || "",
                                due_at: task.due_at
                                    ? task.due_at.slice(0, 16)
                                    : "",
                                notes: task.notes || "",
                            }).replace(/"/g, "&quot;");

                            let actionsHtml =
                                '<div class="flex items-center gap-2 self-end sm:self-center">';
                            if (!isCompleted) {
                                actionsHtml +=
                                    '<button type="button" onclick="var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].completeTaskId = ' +
                                    task.id +
                                    "; c._x_dataStack[0].completeTaskTitle = '" +
                                    (task.title || "").replace(/'/g, "\\'") +
                                    '\'; c._x_dataStack[0].completeModal = true; }" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">✓ Complete</button>';
                            }
                            actionsHtml +=
                                '<button type="button" onclick="var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openEditTask(' +
                                JSON.stringify(task).replace(/"/g, "&quot;") +
                                '); }" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-700 font-semibold text-xs transition-colors">Edit</button>';
                            actionsHtml +=
                                '<form action="/tasks/' +
                                task.id +
                                '" method="POST" onsubmit="return confirm(&quot;Delete task?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg" title="Delete Task">🗑️</button></form></div>';

                            row.innerHTML =
                                '<div class="flex items-start gap-3"><span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200 flex-shrink-0 mt-0.5">' +
                                (task.priority || "Medium") +
                                '</span><div><div class="text-sm font-bold text-slate-900 flex items-center gap-2"><span>' +
                                task.title +
                                '</span><span class="px-2 py-0.5 rounded text-[10px] font-medium border ' +
                                (isCompleted
                                    ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                    : "bg-blue-50 text-blue-700 border-blue-200") +
                                '">' +
                                (task.status || "Pending") +
                                '</span></div><div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3"><span class="font-medium text-slate-700">' +
                                (task.type || "Follow-up") +
                                "</span>" +
                                (task.related_lead_id
                                    ? '<span>•</span><a href="/leads/' +
                                      task.related_lead_id +
                                      '" class="text-orange-600 font-semibold hover:underline">Lead: ' +
                                      (task.lead_name ||
                                          "#" + task.related_lead_id) +
                                      "</a>"
                                    : "") +
                                "<span>•</span><span>Due: " +
                                dtStr +
                                "</span></div>" +
                                (task.notes
                                    ? '<p class="text-xs text-slate-600 mt-1.5 bg-slate-50 p-2 rounded-lg border border-slate-100 inline-block">' +
                                      task.notes +
                                      "</p>"
                                    : "") +
                                (task.outcome
                                    ? '<div class="mt-2 text-xs text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100 inline-flex items-center gap-1.5"><span>✓ Outcome:</span><span class="font-medium">' +
                                      task.outcome +
                                      "</span></div>"
                                    : "") +
                                "</div></div>" +
                                actionsHtml;
                            tasksContainer.prepend(row);
                        }
                    });
                }
            }
        }

        // 9. MARKETING CONTENT CALENDAR SYNC - ONLY on /marketing/content-calendar!
        if (
            curPath === "/marketing/content-calendar" ||
            curPath.startsWith("/marketing/content-calendar?")
        ) {
            if (DATA.contentItems && DATA.contentItems.length > 0) {
                const calContainer = document.querySelector(
                    '.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3, div[class*="grid-cols-1"][class*="lg:grid-cols-3"]',
                );
                if (calContainer) {
                    DATA.contentItems.forEach(function (item) {
                        if (
                            document.querySelector(
                                '[data-content-id="' + item.id + '"]',
                            )
                        )
                            return;
                        const cCard = document.createElement("div");
                        cCard.setAttribute("data-content-id", item.id);
                        cCard.className =
                            "bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-300 transition-all flex flex-col justify-between";
                        const dtStr = item.scheduled_at
                            ? new Date(item.scheduled_at).toLocaleString(
                                  "en-US",
                                  {
                                      day: "2-digit",
                                      month: "short",
                                      year: "numeric",
                                      hour: "2-digit",
                                      minute: "2-digit",
                                  },
                              )
                            : "Scheduled";

                        cCard.innerHTML =
                            '<div><div class="flex items-center justify-between gap-2 mb-2"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200">' +
                            (item.platform || "Facebook Profile") +
                            '</span><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">' +
                            (item.status || "Planned") +
                            '</span></div><h4 class="font-bold text-slate-900 text-sm mb-1">' +
                            item.title +
                            "</h4>" +
                            (item.topic
                                ? '<div class="text-xs text-slate-500 mb-2">Topic: <span class="font-medium text-slate-700">' +
                                  item.topic +
                                  "</span></div>"
                                : "") +
                            (item.caption
                                ? '<p class="text-xs text-slate-600 line-clamp-3 mb-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">' +
                                  item.caption +
                                  "</p>"
                                : "") +
                            (item.cta
                                ? '<div class="text-[11px] text-orange-700 bg-orange-50/70 px-2 py-1 rounded-lg border border-orange-200/60 mb-3"><span class="font-semibold">CTA:</span> ' +
                                  item.cta +
                                  "</div>"
                                : "") +
                            '<div class="grid grid-cols-3 gap-2 bg-slate-50/70 p-2 rounded-xl text-center text-xs"><div><span class="text-[10px] text-slate-400 block uppercase">Reach</span><span class="font-bold text-slate-800">' +
                            (item.reach || 0) +
                            '</span></div><div><span class="text-[10px] text-slate-400 block uppercase">Leads</span><span class="font-bold text-orange-600">' +
                            (item.leads_generated || 0) +
                            '</span></div><div><span class="text-[10px] text-slate-400 block uppercase">Converted</span><span class="font-bold text-emerald-600">' +
                            (item.conversions || 0) +
                            '</span></div></div></div><div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400"><span>' +
                            dtStr +
                            '</span><div class="flex items-center gap-1.5"><button type="button" onclick="var c = document.querySelector(\'[x-data]\'); if (c && c._x_dataStack) { c._x_dataStack[0].openEdit(' +
                            JSON.stringify(item).replace(/"/g, "&quot;") +
                            '); }" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 text-slate-700 hover:text-orange-700 rounded-lg text-xs font-semibold transition-colors">Edit</button><form action="/marketing/content-calendar/' +
                            item.id +
                            '" method="POST" onsubmit="return confirm(&quot;Remove content item?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg" title="Delete Item">🗑️</button></form></div></div>';
                        calContainer.prepend(cCard);
                    });
                }
            }
        // 10. AUTH USERS SYNC - ONLY on /users!
        if (curPath === "/users" || curPath.startsWith("/users?")) {
            if (DATA.deletedUsers && DATA.deletedUsers.length > 0) {
                DATA.deletedUsers.forEach(function (id) {
                    document.querySelectorAll('[data-user-id="' + id + '"]').forEach(function (el) {
                        el.remove();
                    });
                });
            }
            if (DATA.users && DATA.users.length > 0) {
                const tbody = document.querySelector("table tbody.divide-y");
                DATA.users.forEach(function (u) {
                    const row = document.querySelector('tr[data-user-id="' + u.id + '"]');
                    if (row) {
                        const statusCell = row.children[5];
                        if (statusCell) {
                            const isAct = u.status === "active";
                            statusCell.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ' +
                                (isAct ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') + '">' +
                                (isAct ? 'Active' : 'Inactive') + '</span>';
                        }
                        const phoneSpan = row.querySelector('.font-mono span');
                        if (phoneSpan && u.phone) {
                            phoneSpan.textContent = u.phone;
                        }
                    } else if (tbody) {
                        const tr = document.createElement("tr");
                        tr.setAttribute("data-user-id", u.id);
                        tr.className = "hover:bg-slate-50/60 transition-colors";
                        const isAct = u.status === "active";
                        const initial = (u.name || "U").charAt(0).toUpperCase();

                        tr.innerHTML = '<td class="py-3 px-4"><div class="flex items-center gap-3">' +
                            '<div class="w-9 h-9 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">' +
                            initial + '</div><div><div class="font-semibold text-slate-900 flex items-center gap-2"><span>' +
                            (u.name || "User") + '</span></div><div class="text-xs text-slate-500 font-mono">📱 Login: <span class="font-bold text-slate-700">' +
                            (u.phone || "None") + '</span></div></div></div></td>' +
                            '<td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-100 text-emerald-800 border-emerald-200">Member</span></td>' +
                            '<td class="py-3 px-4 text-slate-700 font-medium">' + (u.designation || "Affiliate Partner") + '</td>' +
                            '<td class="py-3 px-4 text-xs font-mono text-slate-600">' + (u.phone || '<span class="text-slate-400 italic">No phone set</span>') + '</td>' +
                            '<td class="py-3 px-4 text-center"><div class="inline-flex items-center gap-2 text-xs"><span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md">👥 0</span><span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md">✅ 0</span></div></td>' +
                            '<td class="py-3 px-4 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ' +
                            (isAct ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') + '">' + (isAct ? 'Active' : 'Inactive') + '</span></td>' +
                            '<td class="py-3 px-4 text-right space-x-2"><button type="button" class="text-orange-600 hover:text-orange-800 font-semibold text-xs px-2 py-1 rounded hover:bg-orange-50 transition-colors">Edit</button>' +
                            '<form action="/users/' + u.id + '" method="POST" class="inline" onsubmit="return confirm(&quot;Are you sure?&quot;);"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold text-xs px-2 py-1 rounded hover:bg-rose-50 transition-colors">Delete</button></form></td>';

                        const editBtn = tr.querySelector("button");
                        if (editBtn) {
                            editBtn.addEventListener("click", function () {
                                const alpine = document.querySelector("[x-data]");
                                if (alpine && alpine._x_dataStack) {
                                    alpine._x_dataStack[0].editingUser = {
                                        id: u.id,
                                        name: u.name || "",
                                        email: u.email || "",
                                        phone: u.phone || "",
                                        designation: u.designation || "",
                                        role_id: u.role_id || "2",
                                        status: u.status || "active"
                                    };
                                    alpine._x_dataStack[0].editModalOpen = true;
                                }
                            });
                        }
                        tbody.prepend(tr);
                    }
                });
            }
        }
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", runSync);
    } else {
        runSync();
    }

    // Re-sync dropdowns whenever user clicks to open any modal
    document.addEventListener("click", function () {
        setTimeout(syncEntityDropdowns, 50);
    });
})();
