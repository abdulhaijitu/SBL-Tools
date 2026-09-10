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
            // Helper for Asia/Dhaka YYYY-MM-DD
            function getDhakaYmd() {
                try {
                    return new Intl.DateTimeFormat("en-CA", {
                        timeZone: "Asia/Dhaka",
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                    }).format(new Date());
                } catch (e) {
                    const d = new Date();
                    return (
                        d.getFullYear() +
                        "-" +
                        String(d.getMonth() + 1).padStart(2, "0") +
                        "-" +
                        String(d.getDate()).padStart(2, "0")
                    );
                }
            }

            // Hydrate current date in Asia/Dhaka locale
            const dateEl = document.getElementById("dashboard-current-date");
            if (dateEl) {
                try {
                    dateEl.textContent = new Intl.DateTimeFormat("en-US", {
                        timeZone: "Asia/Dhaka",
                        weekday: "long",
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                    }).format(new Date());
                } catch (e) {}
            }

            const nonDeletedLeads = (DATA.leads || []).filter(function (l) {
                return !l.deleted_at;
            });
            const activeLeads = nonDeletedLeads.filter(function (l) {
                return (
                    l.stage !== "converted" &&
                    l.stage !== "lost" &&
                    l.stage !== "not_suitable"
                );
            });

            // 1.1 Total Active Leads & Added Today
            const activeLeadsEl =
                document.getElementById("active-leads") ||
                document.querySelector('[data-metric="total-leads"]');
            const localYmd = getDhakaYmd();
            if (activeLeadsEl) {
                activeLeadsEl.textContent = activeLeads.length;
                const smallEl = activeLeadsEl.parentElement
                    ? activeLeadsEl.parentElement.querySelector("small")
                    : null;
                if (smallEl) {
                    const addedToday = nonDeletedLeads.filter(function (l) {
                        return (
                            l.created_at &&
                            l.created_at.slice(0, 10) === localYmd
                        );
                    }).length;
                    smallEl.textContent = addedToday + " added today";
                }
            }

            // 1.2 Today's Followups & Overdue Follow-ups (ONLY Leads)
            const now = new Date();
            let followupsToday = 0;
            let overdueLeads = [];

            nonDeletedLeads.forEach(function (lead) {
                if (
                    lead.next_action_at &&
                    lead.stage !== "converted" &&
                    lead.stage !== "lost" &&
                    lead.stage !== "not_suitable"
                ) {
                    const actDate = lead.next_action_at.slice(0, 10);
                    if (actDate === localYmd) {
                        followupsToday++;
                    }
                    if (new Date(lead.next_action_at) < now) {
                        overdueLeads.push(lead);
                    }
                }
            });

            const dueEl =
                document.getElementById("today-followup") ||
                document.querySelector('[data-metric="followups-today"]');
            if (dueEl) dueEl.textContent = followupsToday;

            // 1.3 Today's Tasks Calculation
            let tasksTodayList = [];
            if (DATA.tasks) {
                DATA.tasks.forEach(function (task) {
                    if (
                        task.status !== "Completed" &&
                        task.status !== "Cancelled" &&
                        task.due_at
                    ) {
                        const dStr = task.due_at.slice(0, 10);
                        if (dStr === localYmd) {
                            tasksTodayList.push(task);
                        }
                    }
                });
            }
            const tasksKpiEl = document.getElementById("today-tasks-kpi");
            if (tasksKpiEl) tasksKpiEl.textContent = tasksTodayList.length;

            // 1.4 Total Presentations
            const totalPres = (DATA.presentations || []).filter(function (p) {
                return !p.deleted_at;
            }).length;
            const presEl =
                document.getElementById("total-presentations") ||
                document.querySelector('[data-metric="presentations-today"]');
            if (presEl) presEl.textContent = totalPres;

            // 1.5 Stage Funnel Counters & Percentage Bars
            const funnelStages = [
                "new",
                "contacted",
                "interested",
                "qualified",
                "presentation",
                "follow_up",
                "decision",
                "converted",
            ];
            const totalPipelineLeads = nonDeletedLeads.length;

            funnelStages.forEach(function (s) {
                const count = nonDeletedLeads.filter(function (l) {
                    return (l.stage || "new") === s;
                }).length;
                const pct =
                    totalPipelineLeads > 0
                        ? Math.min(
                              100,
                              Math.round((count / totalPipelineLeads) * 100),
                          )
                        : 0;

                const el = document.querySelector(
                    '[data-funnel-count="' + s + '"]',
                );
                if (el) el.textContent = count;

                const barEl = document.querySelector(
                    '[data-funnel-bar="' + s + '"]',
                );
                if (barEl) barEl.style.width = pct + "%";

                const pctEl = document.querySelector(
                    '[data-funnel-pct="' + s + '"]',
                );
                if (pctEl) pctEl.textContent = pct + "% share";
            });

            // 1.6 Overdue Follow-ups List Hydration (Dual-State DOM)
            const overdueBadge =
                document.getElementById("dashboard-overdue-badge") ||
                document.querySelector('[data-metric="overdue-followups"]');
            if (overdueBadge) {
                overdueBadge.textContent = overdueLeads.length + " Overdue";
            }
            const overdueContainer = document.getElementById(
                "dashboard-overdue-container",
            );
            const overdueEmpty = document.getElementById(
                "dashboard-overdue-empty",
            );

            if (overdueLeads.length === 0) {
                if (overdueEmpty) overdueEmpty.style.display = "";
                if (overdueContainer) {
                    overdueContainer.style.display = "none";
                    overdueContainer.innerHTML = "";
                }
            } else {
                if (overdueEmpty) overdueEmpty.style.display = "none";
                if (overdueContainer) {
                    overdueContainer.style.display = "";
                    // Remove elements that are no longer overdue
                    overdueContainer
                        .querySelectorAll("[data-lead-id]")
                        .forEach(function (el) {
                            const lid = Number(el.getAttribute("data-lead-id"));
                            if (
                                !overdueLeads.some(function (ol) {
                                    return Number(ol.id) === lid;
                                })
                            ) {
                                el.remove();
                            }
                        });
                    // Add overdue leads not already in the container
                    overdueLeads.forEach(function (lead) {
                        if (
                            !overdueContainer.querySelector(
                                '[data-lead-id="' + lead.id + '"]',
                            )
                        ) {
                            const cleanWa = (
                                lead.whatsapp ||
                                lead.mobile ||
                                ""
                            ).replace(/\D/g, "");
                            const tempClasses =
                                lead.temperature === "hot"
                                    ? "bg-rose-100 text-rose-800"
                                    : lead.temperature === "warm"
                                      ? "bg-amber-100 text-amber-800"
                                      : "bg-blue-100 text-blue-800";
                            const tempLabel = lead.temperature
                                ? lead.temperature.charAt(0).toUpperCase() +
                                  lead.temperature.slice(1)
                                : "Warm";
                            const initial = (lead.name || "L")
                                .charAt(0)
                                .toUpperCase();

                            const item = document.createElement("div");
                            item.setAttribute("data-lead-id", lead.id);
                            item.className =
                                "p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors";
                            item.innerHTML =
                                '<div class="flex items-start gap-3 min-w-0">' +
                                '<div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-black flex items-center justify-center text-sm flex-shrink-0 shadow-xs">' +
                                initial +
                                "</div>" +
                                '<div class="min-w-0">' +
                                '<div class="flex items-center gap-2 flex-wrap">' +
                                '<a href="/leads/' +
                                lead.id +
                                '" class="font-bold text-sm text-slate-900 hover:text-orange-600 truncate">' +
                                (lead.name || "") +
                                "</a>" +
                                '<span class="text-xs font-mono text-slate-400 font-normal">#' +
                                lead.id +
                                "</span>" +
                                '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full ' +
                                tempClasses +
                                '">' +
                                tempLabel +
                                "</span>" +
                                "</div>" +
                                '<div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">' +
                                '<span class="font-medium text-slate-700">📞 ' +
                                (lead.mobile || "") +
                                "</span>" +
                                '<span class="text-rose-600 font-bold bg-rose-50 px-1.5 py-0.2 rounded text-[10px]">Overdue Action</span>' +
                                "</div>" +
                                (lead.next_action_type
                                    ? '<div class="text-[11px] font-semibold text-slate-600 mt-0.5">Action: <span class="text-orange-600 font-bold">' +
                                      lead.next_action_type +
                                      "</span></div>"
                                    : "") +
                                "</div>" +
                                "</div>" +
                                '<div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">' +
                                (cleanWa
                                    ? '<a href="https://wa.me/' +
                                      cleanWa +
                                      '" target="_blank" title="WhatsApp Message" aria-label="WhatsApp ' +
                                      (lead.name || "") +
                                      '" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors text-xs font-bold flex items-center gap-1"><span>💬</span></a>'
                                    : "") +
                                (lead.mobile
                                    ? '<a href="tel:' +
                                      lead.mobile +
                                      '" title="Phone Call" aria-label="Call ' +
                                      (lead.name || "") +
                                      '" class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors text-xs font-bold flex items-center gap-1"><span>📞</span></a>'
                                    : "") +
                                '<a href="/leads/' +
                                lead.id +
                                '" class="px-3 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs transition-all active:scale-95">Take Action</a>' +
                                "</div>";
                            overdueContainer.appendChild(item);
                        }
                    });
                }
            }

            // 1.7 Today's Tasks Hydration (Dual-State DOM)
            const tasksContainer = document.getElementById(
                "dashboard-tasks-container",
            );
            const tasksEmpty = document.getElementById("dashboard-tasks-empty");

            if (tasksTodayList.length === 0) {
                if (tasksEmpty) tasksEmpty.style.display = "";
                if (tasksContainer) {
                    tasksContainer.style.display = "none";
                    tasksContainer.innerHTML = "";
                }
            } else {
                if (tasksEmpty) tasksEmpty.style.display = "none";
                if (tasksContainer) {
                    tasksContainer.style.display = "";
                    // Remove tasks no longer due today
                    tasksContainer
                        .querySelectorAll("[data-task-id]")
                        .forEach(function (el) {
                            const tid = Number(el.getAttribute("data-task-id"));
                            if (
                                !tasksTodayList.some(function (tt) {
                                    return Number(tt.id) === tid;
                                })
                            ) {
                                el.remove();
                            }
                        });
                    // Add tasks not already present
                    tasksTodayList.forEach(function (task) {
                        if (
                            !tasksContainer.querySelector(
                                '[data-task-id="' + task.id + '"]',
                            )
                        ) {
                            const prio = task.priority || "Medium";
                            const prioClass =
                                prio === "Urgent"
                                    ? "bg-rose-100 text-rose-800"
                                    : prio === "High"
                                      ? "bg-orange-100 text-orange-800"
                                      : "bg-slate-100 text-slate-800";
                            const dueTime = task.due_at
                                ? task.due_at.slice(11, 16)
                                : "";
                            const leadName = task.lead
                                ? task.lead.name
                                : task.lead_name || "";
                            const leadId = task.lead
                                ? task.lead.id
                                : task.lead_id || "";

                            const item = document.createElement("div");
                            item.setAttribute("data-task-id", task.id);
                            item.className =
                                "p-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors";
                            item.innerHTML =
                                '<div class="flex items-center gap-3 min-w-0">' +
                                '<span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider ' +
                                prioClass +
                                ' flex-shrink-0">' +
                                prio +
                                "</span>" +
                                '<div class="min-w-0">' +
                                '<div class="text-sm font-bold text-slate-900 truncate">' +
                                (task.title || "") +
                                "</div>" +
                                '<div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">' +
                                '<span class="font-semibold text-slate-700">' +
                                (task.type || "Task") +
                                "</span>" +
                                (leadName
                                    ? '<span>•</span><a href="/leads/' +
                                      leadId +
                                      '" class="text-orange-600 hover:underline font-bold truncate">' +
                                      leadName +
                                      ' <span class="text-slate-400 font-normal">#' +
                                      leadId +
                                      "</span></a>"
                                    : "") +
                                (dueTime
                                    ? "<span>• Due " + dueTime + "</span>"
                                    : "") +
                                "</div>" +
                                "</div>" +
                                "</div>" +
                                '<form action="/tasks/' +
                                task.id +
                                '/complete" method="POST" class="flex-shrink-0">' +
                                '<input type="hidden" name="outcome" value="Completed successfully as planned">' +
                                '<button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-xs font-bold text-slate-700 transition-colors shadow-2xs">✓ Done</button>' +
                                "</form>";
                            tasksContainer.appendChild(item);
                        }
                    });
                }
            }

            // 1.8 Hot Priority Leads Hydration
            const hotLeads = nonDeletedLeads.filter(function (l) {
                return (
                    l.stage !== "converted" &&
                    l.stage !== "lost" &&
                    l.stage !== "not_suitable" &&
                    (Number(l.score) >= 80 || l.temperature === "hot")
                );
            });
            const hotBadge = document.getElementById("dashboard-hot-badge");
            if (hotBadge) {
                hotBadge.textContent = hotLeads.length + " hot";
            }
            const hotContainer = document.getElementById(
                "dashboard-hot-container",
            );
            const hotEmpty = document.getElementById("dashboard-hot-empty");
            if (hotContainer) {
                if (hotLeads.length === 0) {
                    if (hotEmpty) hotEmpty.style.display = "";
                    hotContainer.style.display = "none";
                    hotContainer.innerHTML = "";
                } else {
                    if (hotEmpty) hotEmpty.style.display = "none";
                    hotContainer.style.display = "";
                    hotContainer
                        .querySelectorAll("[data-lead-id]")
                        .forEach(function (el) {
                            const lid = Number(el.getAttribute("data-lead-id"));
                            if (
                                !hotLeads.some(function (hl) {
                                    return Number(hl.id) === lid;
                                })
                            ) {
                                el.remove();
                            }
                        });
                    hotLeads.forEach(function (lead) {
                        if (
                            !hotContainer.querySelector(
                                '[data-lead-id="' + lead.id + '"]',
                            )
                        ) {
                            const item = document.createElement("a");
                            item.setAttribute("data-lead-id", lead.id);
                            item.setAttribute("href", "/leads/" + lead.id);
                            item.className =
                                "block p-3 rounded-xl border border-slate-100 hover:border-orange-300 hover:bg-orange-50/30 transition-all shadow-2xs group";
                            item.innerHTML =
                                '<div class="flex items-center justify-between">' +
                                '<span class="text-xs font-bold text-slate-900 group-hover:text-orange-700 truncate">' +
                                (lead.name || "") +
                                ' <span class="font-mono text-slate-400 font-normal">#' +
                                lead.id +
                                "</span>" +
                                "</span>" +
                                '<span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-orange-600 text-white shadow-2xs">' +
                                (lead.score || 0) +
                                " pts" +
                                "</span>" +
                                "</div>" +
                                '<div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">' +
                                '<span class="font-medium text-slate-600">' +
                                (lead.stage || "new") +
                                "</span>" +
                                "<span>" +
                                (lead.mobile || "") +
                                "</span>" +
                                "</div>";
                            hotContainer.appendChild(item);
                        }
                    });
                }
            }

            // 1.9 Stale Leads Hydration (Disambiguate duplicates via #ID & mobile)
            const staleLeads = nonDeletedLeads.filter(function (l) {
                if (
                    l.stage === "converted" ||
                    l.stage === "lost" ||
                    l.stage === "not_suitable"
                )
                    return false;
                const contactDate = l.last_contact_at || l.created_at;
                if (!contactDate) return true;
                const daysDiff =
                    (now - new Date(contactDate)) / (1000 * 60 * 60 * 24);
                return daysDiff >= 7;
            });
            const staleContainer = document.getElementById(
                "dashboard-stale-container",
            );
            const staleEmpty = document.getElementById("dashboard-stale-empty");
            if (staleContainer) {
                if (staleLeads.length === 0) {
                    if (staleEmpty) staleEmpty.style.display = "";
                    staleContainer.style.display = "none";
                    staleContainer.innerHTML = "";
                } else {
                    if (staleEmpty) staleEmpty.style.display = "none";
                    staleContainer.style.display = "";
                    staleContainer
                        .querySelectorAll("[data-lead-id]")
                        .forEach(function (el) {
                            const lid = Number(el.getAttribute("data-lead-id"));
                            if (
                                !staleLeads.some(function (sl) {
                                    return Number(sl.id) === lid;
                                })
                            ) {
                                el.remove();
                            }
                        });
                    staleLeads.forEach(function (lead) {
                        if (
                            !staleContainer.querySelector(
                                '[data-lead-id="' + lead.id + '"]',
                            )
                        ) {
                            const item = document.createElement("div");
                            item.setAttribute("data-lead-id", lead.id);
                            item.className =
                                "flex items-center justify-between p-2.5 bg-white border border-purple-100 rounded-xl text-xs shadow-2xs";
                            item.innerHTML =
                                '<div class="min-w-0 pr-2">' +
                                '<a href="/leads/' +
                                lead.id +
                                '" class="font-bold text-slate-900 hover:text-purple-700 block truncate">' +
                                (lead.name || "") +
                                ' <span class="text-[11px] font-mono text-purple-600 font-normal">#' +
                                lead.id +
                                "</span>" +
                                "</a>" +
                                '<div class="text-[10px] text-slate-400 mt-0.5">📞 ' +
                                (lead.mobile || "") +
                                " • ID #" +
                                lead.id +
                                "</div>" +
                                "</div>" +
                                '<a href="/leads/' +
                                lead.id +
                                '" class="text-[11px] font-bold text-purple-700 hover:underline flex-shrink-0">Re-engage →</a>';
                            staleContainer.appendChild(item);
                        }
                    });
                }
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

            const tableBody =
                document.querySelector("#desktop-leads-tbody") ||
                document.querySelector("tbody.divide-y");
            const mobileStack =
                document.querySelector("#mobile-leads-stack") ||
                document.querySelector('div.divide-y[class*="md:hidden"]');

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

            const callIconSvg =
                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z"/></svg>';
            const waIconSvg =
                '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.196 8.196 0 01-5.82 2.42c-1.45 0-2.87-.38-4.12-1.11l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 01-1.25-4.42c0-4.54 3.7-8.24 8.23-8.24m4.52 11.66c-.25.7-.72 1.29-1.37 1.63-.52.27-1.18.42-2.12.06-.94-.37-1.92-.99-2.73-1.8-.81-.81-1.43-1.79-1.8-2.73-.36-.94-.21-1.6.06-2.12.34-.65.93-1.12 1.63-1.37.22-.08.45-.04.62.1l1.3 1.6c.14.17.17.41.07.61l-.6 1.2c-.1.2-.06.45.1.61.62.62 1.36 1.12 2.19 1.48.2.09.43.05.57-.1l.98-.98c.18-.18.44-.22.66-.1l1.96.98c.22.11.35.34.33.59-.02.26-.14.5-.32.67z"/></svg>';
            const fbIconSvg =
                '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';

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
                const initialLetter = escapeHtml(
                    (lead.name || "?").charAt(0).toUpperCase(),
                );
                const stageClass = getStageBadgeClass(lead.stage || "new");
                const stageLabel = (lead.stage || "new")
                    .replace(/_/g, " ")
                    .toUpperCase();
                const tempClass = getTempBadgeClass(lead.temperature || "warm");
                const tempLabel = (lead.temperature || "warm").toUpperCase();
                const sourceName =
                    (DATA.sources && DATA.sources[lead.lead_source_id]) ||
                    "Direct";
                const cleanMobile = (lead.mobile || "").replace(/[^\d+]/g, "");
                let cleanWhatsapp = (
                    lead.whatsapp ||
                    lead.mobile ||
                    ""
                ).replace(/[^\d]/g, "");
                if (
                    cleanWhatsapp.startsWith("01") &&
                    cleanWhatsapp.length === 11
                ) {
                    cleanWhatsapp = "88" + cleanWhatsapp;
                }

                const callBtn = cleanMobile
                    ? '<a href="tel:' +
                      cleanMobile +
                      '" class="py-2.5 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-semibold text-xs text-center flex items-center justify-center gap-1.5 active:scale-95 transition-all">' +
                      callIconSvg +
                      "<span>Call</span></a>"
                    : "";

                const waBtn = cleanWhatsapp
                    ? '<a href="https://wa.me/' +
                      cleanWhatsapp +
                      '" target="_blank" rel="noopener noreferrer" class="py-2.5 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all">' +
                      waIconSvg +
                      "<span>WhatsApp</span></a>"
                    : "";

                const detailsBtn =
                    '<a href="/leads/' +
                    lead.id +
                    '" class="py-2.5 px-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs text-center flex items-center justify-center gap-1 active:scale-95 transition-all"><span>Details</span><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>';

                const nextActionHtml = lead.next_action_at
                    ? '<div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-between text-xs border border-slate-100"><div class="flex items-center gap-1.5"><span>⏰</span><div><span class="font-semibold text-slate-700">' +
                      escapeHtml(lead.next_action_type || "Action") +
                      '</span><span class="text-[11px] text-slate-400"> • ' +
                      escapeHtml(lead.next_action_at) +
                      "</span></div></div></div>"
                    : '<div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-between text-xs border border-slate-100"><div class="flex items-center gap-1.5"><span>⏰</span><span class="text-amber-600 font-semibold text-[11px]">Needs Next Action</span></div></div>';

                return (
                    '<div class="flex items-start justify-between gap-2">' +
                    '<div class="flex items-center gap-3">' +
                    (lead.photo
                        ? '<div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-xs border border-orange-200/50"><img src="' +
                          escapeHtml(lead.photo) +
                          '" class="w-full h-full object-cover"></div>'
                        : '<div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-xs border border-orange-200/50">' +
                          initialLetter +
                          "</div>") +
                    "<div>" +
                    '<div class="flex items-center gap-1.5">' +
                    '<a href="/leads/' +
                    lead.id +
                    '" class="font-bold text-slate-900 hover:text-orange-600 text-sm block">' +
                    escapeHtml(lead.name) +
                    "</a>" +
                    '<span class="text-xs font-mono text-slate-400 font-normal">#' +
                    lead.id +
                    "</span>" +
                    "</div>" +
                    '<div class="text-[11px] text-slate-400 mt-0.5">' +
                    escapeHtml(lead.location || "No location") +
                    (lead.profession_or_business
                        ? " • " + escapeHtml(lead.profession_or_business)
                        : "") +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border flex-shrink-0 ' +
                    stageClass +
                    '">' +
                    stageLabel +
                    "</span>" +
                    "</div>" +
                    '<div class="flex flex-wrap items-center gap-1.5 text-[11px]">' +
                    '<span class="px-2 py-0.5 rounded-full font-semibold border ' +
                    tempClass +
                    '">' +
                    tempLabel +
                    (lead.score ? " (" + lead.score + " pts)" : "") +
                    "</span>" +
                    '<span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">' +
                    escapeHtml(sourceName) +
                    "</span>" +
                    (lead.lead_tag
                        ? '<span class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 font-bold">' +
                          escapeHtml(lead.lead_tag) +
                          "</span>"
                        : "") +
                    "</div>" +
                    nextActionHtml +
                    '<div class="grid grid-cols-3 gap-2 pt-1">' +
                    callBtn +
                    waBtn +
                    detailsBtn +
                    "</div>" +
                    '<div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">' +
                    '<a href="/leads/' +
                    lead.id +
                    '/edit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>Edit</a>' +
                    '<form action="/leads/' +
                    lead.id +
                    '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this lead?\');" class="inline">' +
                    '<input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>Delete</button>' +
                    "</form>" +
                    "</div>"
                );
            }

            function renderKanbanCard(lead) {
                const sourceName =
                    (DATA.sources && DATA.sources[lead.lead_source_id]) ||
                    "Direct";
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

            window.filterLeadsLive = function (query) {
                query = (query || "").toLowerCase().trim();
                const rows = document.querySelectorAll(
                    "#desktop-leads-tbody tr[data-lead-id], tbody.divide-y tr[data-lead-id]",
                );
                const cards = document.querySelectorAll(
                    "#mobile-leads-stack > div[data-lead-id], div.divide-y[class*='md:hidden'] > div[data-lead-id]",
                );
                const kCards = document.querySelectorAll(
                    ".kanban-cards-container .kanban-card[data-lead-id]",
                );
                let count = 0;

                function checkMatch(el) {
                    if (!query) {
                        el.style.display = "";
                        return true;
                    }
                    const text = (el.textContent || "").toLowerCase();
                    const matches = text.includes(query);
                    el.style.display = matches ? "" : "none";
                    return matches;
                }

                rows.forEach(function (r) {
                    if (checkMatch(r)) count++;
                });
                cards.forEach(function (c) {
                    checkMatch(c);
                });
                kCards.forEach(function (k) {
                    checkMatch(k);
                });

                const counter = document.getElementById("leads-live-counter");
                if (counter) {
                    if (query) {
                        counter.textContent = count + " found";
                        counter.classList.remove("hidden");
                    } else {
                        counter.classList.add("hidden");
                    }
                }
            };
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
                    (DATA.sources && DATA.sources[lead.lead_source_id]) ||
                    "Direct";
                const score = lead.score || 25;
                const temp = (lead.temperature || "warm").toUpperCase();
                let cleanWhatsapp = (
                    lead.whatsapp ||
                    lead.mobile ||
                    ""
                ).replace(/[^0-9]/g, "");
                if (cleanWhatsapp.startsWith("01") && cleanWhatsapp.length === 11) {
                    cleanWhatsapp = "88" + cleanWhatsapp;
                }

                document.title = lead.name + " - SBL Growth Manager";

                const pageTitleEl = document.getElementById("app-page-title");
                if (pageTitleEl) pageTitleEl.textContent = lead.name;

                const nameEl = document.getElementById("lead-show-name");
                if (nameEl) nameEl.textContent = lead.name;

                const idEl = document.getElementById("lead-show-id");
                if (idEl) idEl.textContent = "#" + lead.id;

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
                    const loc = (lead.location || "").trim();
                    const isValidLoc =
                        loc &&
                        !loc.startsWith("http://") &&
                        !loc.startsWith("https://") &&
                        !loc.includes("/leads/");
                    if (isValidLoc) {
                        locTxt.textContent = loc;
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
            const contactsMobileCards = document.getElementById(
                "contacts-mobile-cards",
            );

            if (DATA.contacts && Array.isArray(DATA.contacts)) {
                // Desktop Table Rendering
                if (contactsTbody) {
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
                                /[^0-9+]/g,
                                "",
                            );
                            let cleanWa = (c.whatsapp || c.phone || "").replace(
                                /[^0-9]/g,
                                "",
                            );
                            if (cleanWa.startsWith("01")) {
                                cleanWa = "88" + cleanWa;
                            }

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
                                    '<button type="button" onclick="window.copyContactToClipboard(\'' +
                                    escapeHtml(c.phone) +
                                    '\', \'Phone number\')" title="Copy Phone" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors cursor-pointer">' +
                                    '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' +
                                    "</button>" +
                                    '<a href="tel:' +
                                    cleanPhone +
                                    '" title="সরাসরি ফোন কল করুন" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-black text-emerald-400 text-xs font-semibold shadow-xs transition-colors active:scale-95">' +
                                    '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>' +
                                    "<span>কল</span>" +
                                    "</a>" +
                                    "</div>";
                            }

                            const targetWa = c.whatsapp || c.phone;
                            let waHtml =
                                '<span class="text-slate-400 text-xs">—</span>';
                            if (targetWa) {
                                waHtml =
                                    '<div class="flex items-center gap-2">' +
                                    '<span class="font-bold text-slate-900 text-sm font-mono">' +
                                    escapeHtml(targetWa) +
                                    "</span>" +
                                    '<button type="button" onclick="window.copyContactToClipboard(\'' +
                                    escapeHtml(targetWa) +
                                    '\', \'WhatsApp number\')" title="Copy WhatsApp" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors cursor-pointer">' +
                                    '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' +
                                    "</button>" +
                                    '<a href="https://wa.me/' +
                                    cleanWa +
                                    "?text=" +
                                    encodeURIComponent(
                                        "আসসালামু আলাইকুম, এসবিএল সংক্রান্ত বিষয়ে যোগাযোগ করতে চাচ্ছি।",
                                    ) +
                                    '" target="_blank" rel="noopener noreferrer" title="হোয়াটসঅ্যাপে মেসেজ পাঠান" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors active:scale-95">' +
                                    '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.769.815 2.796.815 3.182 0 5.768-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm3.385 8.163c-.143.402-.832.744-1.144.789-.312.046-.713.064-2.032-.477-.735-.302-1.396-.757-1.93-1.288-.535-.53-.992-1.19-1.295-1.924-.543-1.319-.525-1.72-.479-2.032.045-.312.387-1.001.789-1.144.135-.048.277-.024.38.064l.872 1.071c.092.113.109.269.043.4l-.391.783c-.066.131-.038.29.068.396.406.407.886.732 1.413.957.147.063.315.029.426-.083l.635-.634c.121-.122.302-.152.455-.075l1.28.639c.143.072.224.223.199.381l-.105.794z"/></svg>' +
                                    "<span>মেসেজ</span>" +
                                    "</a>" +
                                    '<a href="https://wa.me/' +
                                    cleanWa +
                                    '" target="_blank" rel="noopener noreferrer" title="হোয়াটসঅ্যাপে কল / ডায়াল করুন" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-semibold transition-colors active:scale-95">' +
                                    "<span>📱 কল</span>" +
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
                                (c.department || "") +
                                " " +
                                (c.contact_person || "") +
                                " " +
                                (c.phone || "") +
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

                // Mobile Cards Rendering
                if (contactsMobileCards) {
                    contactsMobileCards.innerHTML = DATA.contacts
                        .map(function (c) {
                            const isPrimary = Number(c.is_primary) === 1;
                            const iconBg = isPrimary
                                ? "bg-emerald-50 border border-emerald-200 text-emerald-700"
                                : "bg-slate-100 border border-slate-200 text-slate-700";
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
                                ? '<p class="text-xs text-slate-600 bg-slate-50/80 p-2.5 rounded-xl border border-slate-100 leading-relaxed">' +
                                  escapeHtml(c.description) +
                                  "</p>"
                                : "";
                            const cleanPhone = (c.phone || "").replace(
                                /[^0-9+]/g,
                                "",
                            );
                            let cleanWa = (c.whatsapp || c.phone || "").replace(
                                /[^0-9]/g,
                                "",
                            );
                            if (cleanWa.startsWith("01")) {
                                cleanWa = "88" + cleanWa;
                            }

                            const personHtml = c.contact_person
                                ? '<p class="text-xs text-slate-600 mt-0.5 flex items-center gap-1"><span>👤</span> ' +
                                  escapeHtml(c.contact_person) +
                                  "</p>"
                                : "";

                            const searchData = (
                                (c.department || "") +
                                " " +
                                (c.contact_person || "") +
                                " " +
                                (c.phone || "") +
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
                                '<div data-contact-id="' +
                                c.id +
                                '" data-search="' +
                                escapeHtml(searchData) +
                                '" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3.5 transition-all">' +
                                '<div class="flex items-start justify-between gap-3">' +
                                '<div class="flex items-center gap-3">' +
                                '<div class="w-11 h-11 rounded-2xl ' +
                                iconBg +
                                ' flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">' +
                                escapeHtml(c.icon || "📞") +
                                "</div>" +
                                "<div>" +
                                '<div class="flex items-center gap-1.5 flex-wrap">' +
                                '<h3 class="font-bold text-slate-900 text-sm">' +
                                escapeHtml(c.department) +
                                "</h3>" +
                                badgeHtml +
                                "</div>" +
                                personHtml +
                                "</div>" +
                                "</div>" +
                                '<div class="flex items-center gap-1">' +
                                '<button type="button" onclick="window.openContactEditModalById(' +
                                c.id +
                                ')" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Edit">' +
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>' +
                                "</button>" +
                                '<form action="/contacts/' +
                                c.id +
                                '" method="POST" onsubmit="return confirm(\'Delete this contact hotline?\');" class="inline">' +
                                '<input type="hidden" name="_method" value="DELETE">' +
                                '<button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">' +
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>' +
                                "</button>" +
                                "</form>" +
                                "</div>" +
                                "</div>" +
                                descHtml +
                                '<div class="flex items-center justify-between text-xs text-slate-500 pt-1 border-t border-slate-100 flex-wrap gap-2">' +
                                '<div class="flex items-center gap-1.5">' +
                                '<span class="font-bold text-slate-900 font-mono">' +
                                escapeHtml(c.phone) +
                                "</span>" +
                                '<button type="button" onclick="window.copyContactToClipboard(\'' +
                                escapeHtml(c.phone) +
                                '\', \'Phone number\')" title="Copy Phone" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100">' +
                                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' +
                                "</button>" +
                                "</div>" +
                                '<span class="text-[11px] bg-slate-100 px-2 py-0.5 rounded-md text-slate-600 font-medium">🕒 ' +
                                escapeHtml(
                                    c.available_hours || "10:00 AM - 08:00 PM",
                                ) +
                                "</span>" +
                                "</div>" +
                                '<div class="grid grid-cols-3 gap-2 pt-1">' +
                                '<a href="tel:' +
                                cleanPhone +
                                '" title="Direct Phone Call" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-900 hover:bg-black text-emerald-400 shadow-xs active:scale-95 transition-all text-center">' +
                                '<svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>' +
                                '<span class="text-[11px] font-bold">ফোন কল</span>' +
                                "</a>" +
                                '<a href="https://wa.me/' +
                                cleanWa +
                                "?text=" +
                                encodeURIComponent(
                                    "আসসালামু আলাইকুম, এসবিএল সংক্রান্ত বিষয়ে যোগাযোগ করতে চাচ্ছি।",
                                ) +
                                '" target="_blank" rel="noopener noreferrer" title="WhatsApp Message" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs active:scale-95 transition-all text-center">' +
                                '<svg class="w-4 h-4 mb-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.769.815 2.796.815 3.182 0 5.768-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm3.385 8.163c-.143.402-.832.744-1.144.789-.312.046-.713.064-2.032-.477-.735-.302-1.396-.757-1.93-1.288-.535-.53-.992-1.19-1.295-1.924-.543-1.319-.525-1.72-.479-2.032.045-.312.387-1.001.789-1.144.135-.048.277-.024.38.064l.872 1.071c.092.113.109.269.043.4l-.391.783c-.066.131-.038.29.068.396.406.407.886.732 1.413.957.147.063.315.029.426-.083l.635-.634c.121-.122.302-.152.455-.075l1.28.639c.143.072.224.223.199.381l-.105.794z"/></svg>' +
                                '<span class="text-[11px] font-bold">হোয়াটসঅ্যাপ</span>' +
                                "</a>" +
                                '<a href="https://wa.me/' +
                                cleanWa +
                                '" target="_blank" rel="noopener noreferrer" title="WhatsApp Call / Direct" class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 active:scale-95 transition-all text-center">' +
                                '<span class="text-base mb-0.5 leading-tight">📱</span>' +
                                '<span class="text-[11px] font-bold">ডায়াল / কল</span>' +
                                "</a>" +
                                "</div>" +
                                "</div>"
                            );
                        })
                        .join("");
                }
            }

            // Live search filter on input (filters BOTH table rows and mobile cards)
            const contactSearchInput = document.getElementById(
                "contacts-search-input",
            );
            if (contactSearchInput && !contactSearchInput.__hasListener) {
                contactSearchInput.__hasListener = true;
                contactSearchInput.addEventListener("input", function (e) {
                    const q = (e.target.value || "").toLowerCase().trim();
                    const items = document.querySelectorAll(
                        "#contacts-table-body tr[data-contact-id], #contacts-mobile-cards div[data-contact-id]",
                    );
                    items.forEach(function (el) {
                        const txt = (
                            el.getAttribute("data-search") ||
                            el.textContent ||
                            ""
                        ).toLowerCase();
                        el.style.display = !q || txt.includes(q) ? "" : "none";
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
            const presGrid = document.querySelector(
                'div[class*="grid-cols-1"][class*="lg:grid-cols-3"]',
            );
            if (DATA.presentations && Array.isArray(DATA.presentations)) {
                const livePresMap = new Map();
                DATA.presentations.forEach(function (p) {
                    livePresMap.set(String(p.id), p);
                });

                // Remove any cards that are not in live presentations
                document
                    .querySelectorAll("[data-presentation-id]")
                    .forEach(function (el) {
                        const pid = el.getAttribute("data-presentation-id");
                        if (!livePresMap.has(pid)) {
                            el.remove();
                        }
                    });

                // Update total count
                const totalEl = document.getElementById("total-presentations");
                if (totalEl) totalEl.textContent = DATA.presentations.length;

                if (DATA.presentations.length === 0) {
                    if (presGrid && !document.querySelector(".empty-presentations-notice")) {
                        const emptyNotice = document.createElement("div");
                        emptyNotice.className =
                            "col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400 text-xs empty-presentations-notice";
                        emptyNotice.textContent =
                            'No presentations recorded yet. Click "Record Presentation" above to log a session.';
                        presGrid.appendChild(emptyNotice);
                    }
                } else {
                    const emptyNotice = document.querySelector(
                        ".empty-presentations-notice",
                    );
                    if (emptyNotice) emptyNotice.remove();

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
                                    "bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-orange-200 transition-all flex flex-col justify-between";

                                let outcomeBadge =
                                    '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Pending</span>';
                                if (pres.outcome) {
                                    outcomeBadge =
                                        '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">' +
                                        escapeHtml(pres.outcome) +
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
                                        escapeHtml(
                                            pres.lead_name ||
                                                "Lead #" + pres.lead_id,
                                        ) +
                                        "</a>" +
                                        (pres.lead_mobile
                                            ? '<span class="text-slate-500 text-[11px] block">📞 ' +
                                              escapeHtml(pres.lead_mobile) +
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
                                              escapeHtml(pres.questions) +
                                              "</div>"
                                            : "") +
                                        (pres.objections
                                            ? '<div><strong class="text-rose-700">Objection:</strong> ' +
                                              escapeHtml(pres.objections) +
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
                                    escapeHtml(pres.type || "1-on-1") +
                                    "</span>" +
                                    outcomeBadge +
                                    '</div><h4 class="font-bold text-sm text-slate-900 mb-1">' +
                                    escapeHtml(
                                        pres.topic ||
                                            "SBL Ecosystem Presentation",
                                    ) +
                                    "</h4>" +
                                    leadBox +
                                    qaBox +
                                    '</div><div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400"><span>' +
                                    dtStr +
                                    '</span><div class="flex items-center gap-2"><span>By ' +
                                    escapeHtml(pres.user_name || "Admin") +
                                    '</span><form action="/presentations/' +
                                    pres.id +
                                    '" method="POST" onsubmit="return confirm(&quot;Delete presentation record?&quot;);" class="inline"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors" title="Delete Presentation"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form></div></div>';
                                presGrid.prepend(card);
                            });
                    }
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
        }

        // 10. AUTH USERS SYNC - ONLY on /users!
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
                const tbody = document.querySelector("table tbody.divide-y");
                DATA.users.forEach(function (u) {
                    const row = document.querySelector(
                        'tr[data-user-id="' + u.id + '"]',
                    );
                    if (row) {
                        const statusCell = row.children[5];
                        if (statusCell) {
                            const isAct = u.status === "active";
                            statusCell.innerHTML =
                                '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ' +
                                (isAct
                                    ? "bg-emerald-100 text-emerald-800"
                                    : "bg-rose-100 text-rose-800") +
                                '">' +
                                (isAct ? "Active" : "Inactive") +
                                "</span>";
                        }
                        const phoneSpan = row.querySelector(".font-mono span");
                        if (phoneSpan && u.phone) {
                            phoneSpan.textContent = u.phone;
                        }
                    } else if (tbody) {
                        const tr = document.createElement("tr");
                        tr.setAttribute("data-user-id", u.id);
                        tr.className = "hover:bg-slate-50/60 transition-colors";
                        const isAct = u.status === "active";
                        const initial = (u.name || "U").charAt(0).toUpperCase();

                        tr.innerHTML =
                            '<td class="py-3 px-4"><div class="flex items-center gap-3">' +
                            '<div class="w-9 h-9 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">' +
                            initial +
                            '</div><div><div class="font-semibold text-slate-900 flex items-center gap-2"><span>' +
                            (u.name || "User") +
                            '</span></div><div class="text-xs text-slate-500 font-mono">📱 Login: <span class="font-bold text-slate-700">' +
                            (u.phone || "None") +
                            "</span></div></div></div></td>" +
                            '<td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-100 text-emerald-800 border-emerald-200">Member</span></td>' +
                            '<td class="py-3 px-4 text-slate-700 font-medium">' +
                            (u.designation || "Affiliate Partner") +
                            "</td>" +
                            '<td class="py-3 px-4 text-xs font-mono text-slate-600">' +
                            (u.phone ||
                                '<span class="text-slate-400 italic">No phone set</span>') +
                            "</td>" +
                            '<td class="py-3 px-4 text-center"><div class="inline-flex items-center gap-2 text-xs"><span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md">👥 0</span><span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md">✅ 0</span></div></td>' +
                            '<td class="py-3 px-4 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ' +
                            (isAct
                                ? "bg-emerald-100 text-emerald-800"
                                : "bg-rose-100 text-rose-800") +
                            '">' +
                            (isAct ? "Active" : "Inactive") +
                            "</span></td>" +
                            '<td class="py-3 px-4 text-right space-x-2"><button type="button" class="text-orange-600 hover:text-orange-800 font-semibold text-xs px-2 py-1 rounded hover:bg-orange-50 transition-colors">Edit</button>' +
                            '<form action="/users/' +
                            u.id +
                            '" method="POST" class="inline" onsubmit="return confirm(&quot;Are you sure?&quot;);"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold text-xs px-2 py-1 rounded hover:bg-rose-50 transition-colors">Delete</button></form></td>';

                        const editBtn = tr.querySelector("button");
                        if (editBtn) {
                            editBtn.addEventListener("click", function () {
                                const alpine =
                                    document.querySelector("[x-data]");
                                if (alpine && alpine._x_dataStack) {
                                    alpine._x_dataStack[0].editingUser = {
                                        id: u.id,
                                        name: u.name || "",
                                        email: u.email || "",
                                        phone: u.phone || "",
                                        designation: u.designation || "",
                                        role_id: u.role_id || "2",
                                        status: u.status || "active",
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

    function checkLeadSavedToast() {
        try {
            const url = new URL(window.location.href);
            if (url.searchParams.get("saved") === "1") {
                try {
                    localStorage.removeItem("sbl_lead_create_draft");
                } catch (e) {}

                const toast = document.createElement("div");
                toast.className =
                    "fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl shadow-emerald-600/30 font-bold text-sm transform transition-all duration-300 translate-y-10 opacity-0";
                toast.innerHTML =
                    "<span>✅</span> <span>নতুন লিড সফলভাবে সংরক্ষিত হয়েছে!</span>";
                document.body.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove("translate-y-10", "opacity-0");
                });

                setTimeout(() => {
                    toast.classList.add("translate-y-10", "opacity-0");
                    setTimeout(() => toast.remove(), 400);
                }, 4000);

                url.searchParams.delete("saved");
                url.searchParams.delete("lead_id");
                window.history.replaceState(
                    {},
                    document.title,
                    url.pathname + (url.search ? url.search : ""),
                );
            }
        } catch (e) {}
    }

    function applyLanguage() {
        try {
            const lang = localStorage.getItem("sbl_lang") || "en";
            document.documentElement.lang = lang;
            document.documentElement.dataset.lang = lang;
            document.querySelectorAll("[data-en][data-bn]").forEach(function(el) {
                const text = lang === "bn" ? el.getAttribute("data-bn") : el.getAttribute("data-en");
                if (text !== null && text !== undefined) {
                    el.textContent = text;
                }
            });
            document.querySelectorAll("[data-lang-content]").forEach(function(el) {
                el.style.display = el.getAttribute("data-lang-content") === lang ? "" : "none";
            });
        } catch (e) {}
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            applyLanguage();
            runSync();
            checkLeadSavedToast();
        });
    } else {
        applyLanguage();
        runSync();
        checkLeadSavedToast();
    }

    window.addEventListener("lang-changed", function() {
        applyLanguage();
    });

    // Re-sync dropdowns whenever user clicks to open any modal
    document.addEventListener("click", function () {
        setTimeout(syncEntityDropdowns, 50);
    });
})();
