import {
    pgTable,
    serial,
    varchar,
    text,
    boolean,
    integer,
    numeric,
    timestamp,
    date,
    jsonb,
    primaryKey,
    uniqueIndex,
    index,
} from "drizzle-orm/pg-core";
import { relations } from "drizzle-orm";

// ==========================================
// 1. Users & RBAC
// ==========================================

export const users = pgTable(
    "users",
    {
        id: serial("id").primaryKey(),
        name: varchar("name", { length: 255 }).notNull(),
        email: varchar("email", { length: 255 }).notNull().unique(),
        emailVerifiedAt: timestamp("email_verified_at", { withTimezone: true }),
        password: text("password").notNull(),
        phone: varchar("phone", { length: 50 }),
        designation: varchar("designation", { length: 100 }),
        status: varchar("status", { length: 50 }).notNull().default("active"),
        rememberToken: text("remember_token"),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        index("idx_users_email").on(table.email),
        index("idx_users_status").on(table.status),
    ],
);

export const roles = pgTable("roles", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 50 }).notNull().unique(), // super_admin, manager, agent, marketing, member, demo_member
    displayName: varchar("display_name", { length: 100 }).notNull(),
    description: text("description"),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const permissions = pgTable("permissions", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 100 }).notNull().unique(),
    displayName: varchar("display_name", { length: 150 }),
    groupName: varchar("group_name", { length: 50 }),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const userRoles = pgTable(
    "user_roles",
    {
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        roleId: integer("role_id")
            .notNull()
            .references(() => roles.id, { onDelete: "cascade" }),
    },
    (table) => [
        primaryKey({ columns: [table.userId, table.roleId] }),
        index("idx_user_roles_user_id").on(table.userId),
    ],
);

export const rolePermissions = pgTable(
    "role_permissions",
    {
        roleId: integer("role_id")
            .notNull()
            .references(() => roles.id, { onDelete: "cascade" }),
        permissionId: integer("permission_id")
            .notNull()
            .references(() => permissions.id, { onDelete: "cascade" }),
    },
    (table) => [primaryKey({ columns: [table.roleId, table.permissionId] })],
);

// ==========================================
// 2. CRM: Leads & Activities
// ==========================================

export const leadSources = pgTable("lead_sources", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 100 }).notNull(),
    isActive: boolean("is_active").notNull().default(true),
    sortOrder: integer("sort_order").notNull().default(0),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const leads = pgTable(
    "leads",
    {
        id: serial("id").primaryKey(),
        name: varchar("name", { length: 255 }).notNull(),
        mobile: varchar("mobile", { length: 50 }).notNull(),
        whatsapp: varchar("whatsapp", { length: 50 }),
        email: varchar("email", { length: 255 }),
        photoR2Key: text("photo_r2_key"), // Rule 9: Files stored in Cloudflare R2
        facebookUrl: varchar("facebook_url", { length: 255 }),
        location: varchar("location", { length: 255 }),
        professionOrBusiness: varchar("profession_or_business", {
            length: 255,
        }),
        leadSourceId: integer("lead_source_id").references(
            () => leadSources.id,
            { onDelete: "set null" },
        ),
        leadSourceDetail: varchar("lead_source_detail", { length: 255 }),
        interestTypes: jsonb("interest_types").default([]),
        leadTag: varchar("lead_tag", { length: 50 }),
        stage: varchar("stage", { length: 50 }).notNull().default("new"), // new, contacted, interested, presentation, follow_up, converted, lost
        temperature: varchar("temperature", { length: 50 })
            .notNull()
            .default("cold"), // cold, warm, hot, stale
        score: integer("score").notNull().default(0),
        isManualScore: boolean("is_manual_score").notNull().default(false),
        budgetRange: numeric("budget_range", { precision: 14, scale: 2 }), // Rule 8: NUMERIC for financials
        decisionTimeline: varchar("decision_timeline", { length: 100 }),
        ownerUserId: integer("owner_user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        assignedToUserId: integer("assigned_to_user_id").references(
            () => users.id,
            { onDelete: "set null" },
        ),
        nextActionType: varchar("next_action_type", { length: 100 }),
        nextActionAt: timestamp("next_action_at", { withTimezone: true }),
        lastContactAt: timestamp("last_contact_at", { withTimezone: true }),
        convertedAt: timestamp("converted_at", { withTimezone: true }),
        notes: text("notes"),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        deletedAt: timestamp("deleted_at", { withTimezone: true }),
    },
    (table) => [
        index("idx_leads_owner_user_id").on(table.ownerUserId),
        index("idx_leads_stage").on(table.stage),
        index("idx_leads_temperature").on(table.temperature),
        index("idx_leads_next_action_at").on(table.nextActionAt),
        index("idx_leads_deleted_at").on(table.deletedAt),
    ],
);

export const leadInterests = pgTable(
    "lead_interests",
    {
        id: serial("id").primaryKey(),
        leadId: integer("lead_id")
            .notNull()
            .references(() => leads.id, { onDelete: "cascade" }),
        interest: varchar("interest", { length: 100 }).notNull(),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [index("idx_lead_interests_lead_id").on(table.leadId)],
);

export const activities = pgTable(
    "activities",
    {
        id: serial("id").primaryKey(),
        leadId: integer("lead_id")
            .notNull()
            .references(() => leads.id, { onDelete: "cascade" }),
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        type: varchar("type", { length: 50 }).notNull(), // call, meeting, message, email, note
        details: text("details"),
        scheduledAt: timestamp("scheduled_at", { withTimezone: true }),
        completedAt: timestamp("completed_at", { withTimezone: true }),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        index("idx_activities_lead_id").on(table.leadId),
        index("idx_activities_user_id").on(table.userId),
    ],
);

export const tasks = pgTable(
    "tasks",
    {
        id: serial("id").primaryKey(),
        title: varchar("title", { length: 255 }).notNull(),
        description: text("description"),
        dueDate: timestamp("due_date", { withTimezone: true }),
        priority: varchar("priority", { length: 20 })
            .notNull()
            .default("medium"), // low, medium, high, urgent
        status: varchar("status", { length: 20 }).notNull().default("pending"), // pending, completed, cancelled
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        leadId: integer("lead_id").references(() => leads.id, {
            onDelete: "set null",
        }),
        completedAt: timestamp("completed_at", { withTimezone: true }),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        index("idx_tasks_user_id").on(table.userId),
        index("idx_tasks_status").on(table.status),
        index("idx_tasks_due_date").on(table.dueDate),
    ],
);

export const presentations = pgTable(
    "presentations",
    {
        id: serial("id").primaryKey(),
        leadId: integer("lead_id")
            .notNull()
            .references(() => leads.id, { onDelete: "cascade" }),
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        title: varchar("title", { length: 255 }).notNull(),
        presentationType: varchar("presentation_type", { length: 50 }), // online, 1-on-1, seminar, group
        scheduledAt: timestamp("scheduled_at", {
            withTimezone: true,
        }).notNull(),
        status: varchar("status", { length: 50 })
            .notNull()
            .default("scheduled"), // scheduled, completed, cancelled, no_show
        outcomeNotes: text("outcome_notes"),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        index("idx_presentations_lead_id").on(table.leadId),
        index("idx_presentations_user_id").on(table.userId),
        index("idx_presentations_scheduled_at").on(table.scheduledAt),
    ],
);

// ==========================================
// 3. Marketing & Activity Tracking
// ==========================================

export const campaigns = pgTable("campaigns", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 255 }).notNull(),
    channel: varchar("channel", { length: 100 }), // facebook, youtube, tiktok, offline, etc.
    budget: numeric("budget", { precision: 14, scale: 2 })
        .notNull()
        .default("0.00"), // Rule 8: NUMERIC
    status: varchar("status", { length: 50 }).notNull().default("active"),
    startDate: timestamp("start_date", { withTimezone: true }),
    endDate: timestamp("end_date", { withTimezone: true }),
    notes: text("notes"),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const contentItems = pgTable(
    "content_items",
    {
        id: serial("id").primaryKey(),
        campaignId: integer("campaign_id").references(() => campaigns.id, {
            onDelete: "set null",
        }),
        title: varchar("title", { length: 255 }).notNull(),
        contentType: varchar("content_type", { length: 50 }), // reel, post, video, story, blog
        url: varchar("url", { length: 500 }),
        publishDate: timestamp("publish_date", { withTimezone: true }),
        status: varchar("status", { length: 50 }).default("published"),
        performanceMetrics: jsonb("performance_metrics").default({}),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [index("idx_content_items_campaign_id").on(table.campaignId)],
);

export const dailyActivityLogs = pgTable(
    "daily_activity_logs",
    {
        id: serial("id").primaryKey(),
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        logDate: date("log_date").notNull(),
        callsCount: integer("calls_count").notNull().default(0),
        messagesCount: integer("messages_count").notNull().default(0),
        meetingsCount: integer("meetings_count").notNull().default(0),
        presentationsCount: integer("presentations_count").notNull().default(0),
        newLeadsCount: integer("new_leads_count").notNull().default(0),
        notes: text("notes"),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        uniqueIndex("idx_daily_activity_logs_user_date").on(
            table.userId,
            table.logDate,
        ),
    ],
);

// ==========================================
// 4. Financial Plans, Ranks & Commissions
// ==========================================

export const ranks = pgTable("ranks", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 100 }).notNull(),
    level: integer("level").notNull(),
    minTeamVolume: numeric("min_team_volume", { precision: 14, scale: 2 })
        .notNull()
        .default("0.00"), // Rule 8: NUMERIC
    minDirectMembers: integer("min_direct_members").notNull().default(0),
    rewardDescription: text("reward_description"),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const investmentPlans = pgTable("investment_plans", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 100 }).notNull(),
    minAmount: numeric("min_amount", { precision: 14, scale: 2 })
        .notNull()
        .default("0.00"), // Rule 8
    maxAmount: numeric("max_amount", { precision: 14, scale: 2 })
        .notNull()
        .default("0.00"), // Rule 8
    returnRate: numeric("return_rate", { precision: 6, scale: 2 })
        .notNull()
        .default("0.00"), // Rule 8
    durationDays: integer("duration_days").notNull().default(0),
    riskLevel: varchar("risk_level", { length: 50 }).default("medium"),
    description: text("description"),
    isActive: boolean("is_active").notNull().default(true),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const commissionTypes = pgTable("commission_types", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 100 }).notNull(),
    calculationType: varchar("calculation_type", { length: 50 }).notNull(),
    percentageOrFixed: numeric("percentage_or_fixed", {
        precision: 10,
        scale: 2,
    })
        .notNull()
        .default("0.00"), // Rule 8
    isActive: boolean("is_active").notNull().default(true),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const investments = pgTable(
    "investments",
    {
        id: serial("id").primaryKey(),
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        leadId: integer("lead_id").references(() => leads.id, {
            onDelete: "set null",
        }),
        planId: integer("plan_id")
            .notNull()
            .references(() => investmentPlans.id, { onDelete: "cascade" }),
        amount: numeric("amount", { precision: 14, scale: 2 })
            .notNull()
            .default("0.00"), // Rule 8
        status: varchar("status", { length: 50 }).notNull().default("active"), // active, matured, withdrawn, cancelled
        startDate: timestamp("start_date", { withTimezone: true })
            .notNull()
            .defaultNow(),
        maturityDate: timestamp("maturity_date", { withTimezone: true }),
        returnAmount: numeric("return_amount", {
            precision: 14,
            scale: 2,
        }).default("0.00"), // Rule 8
        notes: text("notes"),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        index("idx_investments_user_id").on(table.userId),
        index("idx_investments_plan_id").on(table.planId),
    ],
);

// ==========================================
// 5. Team Tree / Binary Nodes (Ten-Slot Structure)
// ==========================================

export const binaryNodes = pgTable(
    "binary_nodes",
    {
        id: serial("id").primaryKey(),
        userId: integer("user_id")
            .notNull()
            .references(() => users.id, { onDelete: "cascade" }),
        parentId: integer("parent_id").references((): any => binaryNodes.id, {
            onDelete: "set null",
        }),
        placementPosition: integer("placement_position").notNull(), // 1 to 10
        sponsorId: integer("sponsor_id").references((): any => binaryNodes.id, {
            onDelete: "set null",
        }),
        sponsorName: varchar("sponsor_name", { length: 255 }),
        memberId: varchar("member_id", { length: 100 }),
        memberName: varchar("member_name", { length: 255 }).notNull(),
        phone: varchar("phone", { length: 50 }),
        passwordEncrypted: text("password_encrypted"), // Encrypted for private vault
        tpinEncrypted: text("tpin_encrypted"), // Encrypted TPIN
        rank: varchar("rank", { length: 100 }),
        packageName: varchar("package_name", { length: 100 }),
        status: varchar("status", { length: 50 }).notNull().default("active"),
        notes: text("notes"),
        createdAt: timestamp("created_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
        updatedAt: timestamp("updated_at", { withTimezone: true })
            .notNull()
            .defaultNow(),
    },
    (table) => [
        index("idx_binary_nodes_user_id").on(table.userId),
        index("idx_binary_nodes_parent_id").on(table.parentId),
        uniqueIndex("idx_binary_nodes_placement_unique").on(
            table.userId,
            table.parentId,
            table.placementPosition,
        ),
    ],
);

// ==========================================
// 6. SBL Toolkit: Links, Resources, Contacts, Abbreviations
// ==========================================

export const ecosystemLinks = pgTable("ecosystem_links", {
    id: serial("id").primaryKey(),
    title: varchar("title", { length: 255 }).notNull(),
    url: varchar("url", { length: 500 }).notNull(),
    category: varchar("category", { length: 100 }),
    icon: varchar("icon", { length: 100 }),
    isActive: boolean("is_active").notNull().default(true),
    isVerified: boolean("is_verified").notNull().default(true),
    verificationNotes: text("verification_notes"),
    verifiedAt: timestamp("verified_at", { withTimezone: true }),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const marketingResources = pgTable("marketing_resources", {
    id: serial("id").primaryKey(),
    title: varchar("title", { length: 255 }).notNull(),
    category: varchar("category", { length: 100 }),
    mediaType: varchar("media_type", { length: 50 }).notNull(), // image, video, pdf, document, template
    fileR2Key: text("file_r2_key").notNull(), // Rule 9: Files in R2
    description: text("description"),
    isVerified: boolean("is_verified").notNull().default(true),
    verifiedAt: timestamp("verified_at", { withTimezone: true }),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const sblContacts = pgTable("sbl_contacts", {
    id: serial("id").primaryKey(),
    name: varchar("name", { length: 255 }).notNull(),
    designation: varchar("designation", { length: 100 }),
    department: varchar("department", { length: 100 }),
    phone: varchar("phone", { length: 50 }),
    email: varchar("email", { length: 255 }),
    whatsapp: varchar("whatsapp", { length: 50 }),
    telegram: varchar("telegram", { length: 100 }),
    address: text("address"),
    isEmergency: boolean("is_emergency").notNull().default(false),
    isVerified: boolean("is_verified").notNull().default(true),
    bilingualInfo: jsonb("bilingual_info").default({}),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

export const abbreviations = pgTable("abbreviations", {
    id: serial("id").primaryKey(),
    term: varchar("term", { length: 100 }).notNull(),
    abbreviation: varchar("abbreviation", { length: 50 }).notNull(),
    definition: text("definition").notNull(),
    category: varchar("category", { length: 100 }),
    createdAt: timestamp("created_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true })
        .notNull()
        .defaultNow(),
});

// ==========================================
// Drizzle Relations
// ==========================================

export const usersRelations = relations(users, ({ many }) => ({
    userRoles: many(userRoles),
    leads: many(leads),
    tasks: many(tasks),
    activities: many(activities),
    presentations: many(presentations),
    dailyActivityLogs: many(dailyActivityLogs),
    investments: many(investments),
    binaryNodes: many(binaryNodes),
}));

export const rolesRelations = relations(roles, ({ many }) => ({
    userRoles: many(userRoles),
    rolePermissions: many(rolePermissions),
}));

export const userRolesRelations = relations(userRoles, ({ one }) => ({
    user: one(users, {
        fields: [userRoles.userId],
        references: [users.id],
    }),
    role: one(roles, {
        fields: [userRoles.roleId],
        references: [roles.id],
    }),
}));

export const leadsRelations = relations(leads, ({ one, many }) => ({
    owner: one(users, {
        fields: [leads.ownerUserId],
        references: [users.id],
    }),
    assignedTo: one(users, {
        fields: [leads.assignedToUserId],
        references: [users.id],
    }),
    source: one(leadSources, {
        fields: [leads.leadSourceId],
        references: [leadSources.id],
    }),
    interests: many(leadInterests),
    activities: many(activities),
    tasks: many(tasks),
    presentations: many(presentations),
    investments: many(investments),
}));

export const tasksRelations = relations(tasks, ({ one }) => ({
    user: one(users, {
        fields: [tasks.userId],
        references: [users.id],
    }),
    lead: one(leads, {
        fields: [tasks.leadId],
        references: [leads.id],
    }),
}));

export const activitiesRelations = relations(activities, ({ one }) => ({
    lead: one(leads, {
        fields: [activities.leadId],
        references: [leads.id],
    }),
    user: one(users, {
        fields: [activities.userId],
        references: [users.id],
    }),
}));

export const presentationsRelations = relations(presentations, ({ one }) => ({
    lead: one(leads, {
        fields: [presentations.leadId],
        references: [leads.id],
    }),
    user: one(users, {
        fields: [presentations.userId],
        references: [users.id],
    }),
}));

export const binaryNodesRelations = relations(binaryNodes, ({ one, many }) => ({
    user: one(users, {
        fields: [binaryNodes.userId],
        references: [users.id],
    }),
    parent: one(binaryNodes, {
        fields: [binaryNodes.parentId],
        references: [binaryNodes.id],
        relationName: "treeHierarchy",
    }),
    children: many(binaryNodes, {
        relationName: "treeHierarchy",
    }),
    sponsor: one(binaryNodes, {
        fields: [binaryNodes.sponsorId],
        references: [binaryNodes.id],
        relationName: "sponsorHierarchy",
    }),
}));
