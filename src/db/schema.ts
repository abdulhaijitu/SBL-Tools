import { sqliteTable, text, integer, numeric, index, uniqueIndex } from 'drizzle-orm/sqlite-core';
import { relations } from 'drizzle-orm';

// ==========================================
// 1. AUTH & RBAC (Roles & Permissions)
// ==========================================

export const users = sqliteTable(
  'users',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    name: text('name').notNull(),
    email: text('email').notNull().unique(),
    phone: text('phone'),
    designation: text('designation'),
    password: text('password').notNull(),
    status: text('status').notNull().default('active'), // active, inactive, suspended
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
    updatedAt: integer('updated_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
  },
  (table) => [
    uniqueIndex('idx_users_email').on(table.email),
    index('idx_users_phone').on(table.phone),
  ]
);

export const roles = sqliteTable('roles', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull().unique(), // super_admin, admin, manager, member
  displayName: text('display_name').notNull(),
  description: text('description'),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const permissions = sqliteTable('permissions', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull().unique(),
  displayName: text('display_name').notNull(),
  module: text('module').notNull(),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const userRoles = sqliteTable(
  'user_roles',
  {
    userId: integer('user_id')
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    roleId: integer('role_id')
      .notNull()
      .references(() => roles.id, { onDelete: 'cascade' }),
  },
  (table) => [
    uniqueIndex('idx_user_roles_unique').on(table.userId, table.roleId),
  ]
);

export const rolePermissions = sqliteTable(
  'role_permissions',
  {
    roleId: integer('role_id')
      .notNull()
      .references(() => roles.id, { onDelete: 'cascade' }),
    permissionId: integer('permission_id')
      .notNull()
      .references(() => permissions.id, { onDelete: 'cascade' }),
  },
  (table) => [
    uniqueIndex('idx_role_permissions_unique').on(table.roleId, table.permissionId),
  ]
);

// ==========================================
// 2. LEADS, PIPELINE & CRM
// ==========================================

export const leadSources = sqliteTable('lead_sources', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull().unique(),
  description: text('description'),
  isActive: integer('is_active', { mode: 'boolean' }).notNull().default(true),
  sortOrder: integer('sort_order').notNull().default(0),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const leads = sqliteTable(
  'leads',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    name: text('name').notNull(),
    mobile: text('mobile').notNull(),
    whatsapp: text('whatsapp'),
    email: text('email'),
    photoR2Key: text('photo_r2_key'), // Database Rule: Store file keys in Cloudflare R2
    facebookUrl: text('facebook_url'),
    location: text('location'),
    professionOrBusiness: text('profession_or_business'),
    leadSourceId: integer('lead_source_id').references(() => leadSources.id, { onDelete: 'set null' }),
    leadSourceDetail: text('lead_source_detail'),
    stage: text('stage').notNull().default('new'), // new, contacted, interested, presentation, follow_up, converted, lost
    temperature: text('temperature').notNull().default('cold'), // hot, warm, cold, stale
    score: integer('score').notNull().default(20),
    budgetRange: text('budget_range'),
    decisionTimeline: text('decision_timeline'),
    ownerUserId: integer('owner_user_id').references(() => users.id, { onDelete: 'set null' }),
    nextActionType: text('next_action_type'),
    nextActionAt: integer('next_action_at', { mode: 'timestamp' }),
    lastContactAt: integer('last_contact_at', { mode: 'timestamp' }),
    notes: text('notes'),
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
    updatedAt: integer('updated_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
    deletedAt: integer('deleted_at', { mode: 'timestamp' }),
  },
  (table) => [
    index('idx_leads_mobile').on(table.mobile),
    index('idx_leads_stage').on(table.stage),
    index('idx_leads_temperature').on(table.temperature),
    index('idx_leads_owner').on(table.ownerUserId),
    index('idx_leads_next_action').on(table.nextActionAt),
  ]
);

export const leadInterests = sqliteTable('lead_interests', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  leadId: integer('lead_id')
    .notNull()
    .references(() => leads.id, { onDelete: 'cascade' }),
  interestType: text('interest_type').notNull(),
  details: text('details'),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const activities = sqliteTable(
  'activities',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    leadId: integer('lead_id').references(() => leads.id, { onDelete: 'cascade' }),
    userId: integer('user_id').references(() => users.id, { onDelete: 'set null' }),
    type: text('type').notNull(), // call, meeting, note, whatsapp, email
    details: text('details'),
    scheduledAt: integer('scheduled_at', { mode: 'timestamp' }),
    completedAt: integer('completed_at', { mode: 'timestamp' }),
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
  },
  (table) => [
    index('idx_activities_lead').on(table.leadId),
    index('idx_activities_user').on(table.userId),
  ]
);

export const tasks = sqliteTable(
  'tasks',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    leadId: integer('lead_id').references(() => leads.id, { onDelete: 'cascade' }),
    userId: integer('user_id')
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    title: text('title').notNull(),
    description: text('description'),
    dueDate: integer('due_date', { mode: 'timestamp' }),
    priority: text('priority').notNull().default('medium'), // low, medium, high, urgent
    status: text('status').notNull().default('pending'), // pending, completed, overdue, cancelled
    completedAt: integer('completed_at', { mode: 'timestamp' }),
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
    updatedAt: integer('updated_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
  },
  (table) => [
    index('idx_tasks_user_status').on(table.userId, table.status),
    index('idx_tasks_due_date').on(table.dueDate),
  ]
);

export const presentations = sqliteTable('presentations', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  leadId: integer('lead_id')
    .notNull()
    .references(() => leads.id, { onDelete: 'cascade' }),
  presenterUserId: integer('presenter_user_id').references(() => users.id, { onDelete: 'set null' }),
  type: text('type').notNull().default('one_to_one'), // one_to_one, group, online_zoom
  scheduledAt: integer('scheduled_at', { mode: 'timestamp' }).notNull(),
  status: text('status').notNull().default('scheduled'), // scheduled, completed, missed, rescheduled
  outcomeNotes: text('outcome_notes'),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

// ==========================================
// 3. CAMPAIGNS & MARKETING CONTENT
// ==========================================

export const campaigns = sqliteTable('campaigns', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  title: text('title').notNull(),
  platform: text('platform').notNull(), // facebook, whatsapp, youtube, offline
  status: text('status').notNull().default('active'),
  budget: numeric('budget'), // Financial values stored with precision
  spentAmount: numeric('spent_amount'),
  startDate: integer('start_date', { mode: 'timestamp' }),
  endDate: integer('end_date', { mode: 'timestamp' }),
  targetLeadsCount: integer('target_leads_count'),
  acquiredLeadsCount: integer('acquired_leads_count').default(0),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const contentItems = sqliteTable('content_items', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  title: text('title').notNull(),
  type: text('type').notNull(), // copy, poster, video, script, banner
  language: text('language').notNull().default('bn'), // bn, en
  fileR2Key: text('file_r2_key'), // Storage in Cloudflare R2
  externalUrl: text('external_url'),
  textContent: text('text_content'),
  category: text('category'),
  sortOrder: integer('sort_order').default(0),
  isActive: integer('is_active', { mode: 'boolean' }).notNull().default(true),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const dailyActivityLogs = sqliteTable(
  'daily_activity_logs',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    userId: integer('user_id')
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    logDate: text('log_date').notNull(), // YYYY-MM-DD
    callsCount: integer('calls_count').notNull().default(0),
    meetingsCount: integer('meetings_count').notNull().default(0),
    presentationsCount: integer('presentations_count').notNull().default(0),
    followUpsCount: integer('follow_ups_count').notNull().default(0),
    newLeadsCount: integer('new_leads_count').notNull().default(0),
    notes: text('notes'),
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
  },
  (table) => [
    uniqueIndex('idx_daily_activity_user_date').on(table.userId, table.logDate),
  ]
);

// ==========================================
// 4. FINANCIALS, RANKS & COMMISSIONS
// ==========================================

export const ranks = sqliteTable('ranks', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull().unique(), // Associate, Silver, Gold, Platinum, Diamond, Crown
  levelOrder: integer('level_order').notNull(),
  targetSalesAmount: numeric('target_sales_amount'),
  commissionBonusRate: numeric('commission_bonus_rate'),
  badgeIcon: text('badge_icon'),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const investmentPlans = sqliteTable('investment_plans', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull().unique(),
  minAmount: numeric('min_amount').notNull(),
  maxAmount: numeric('max_amount'),
  returnRate: numeric('return_rate'),
  durationDays: integer('duration_days'),
  description: text('description'),
  isActive: integer('is_active', { mode: 'boolean' }).notNull().default(true),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const commissionTypes = sqliteTable('commission_types', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull().unique(), // Direct Referral, Binary Match, Leadership Pool, Generation Bonus
  ratePercentage: numeric('rate_percentage'),
  description: text('description'),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const investments = sqliteTable(
  'investments',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    userId: integer('user_id')
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    planId: integer('plan_id')
      .notNull()
      .references(() => investmentPlans.id, { onDelete: 'restrict' }),
    amount: numeric('amount').notNull(),
    returnAmount: numeric('return_amount'),
    status: text('status').notNull().default('active'), // active, matured, cancelled
    startDate: integer('start_date', { mode: 'timestamp' }).notNull(),
    maturityDate: integer('maturity_date', { mode: 'timestamp' }),
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
  },
  (table) => [
    index('idx_investments_user').on(table.userId),
  ]
);

// ==========================================
// 5. BINARY / 10-SLOT NETWORK TREE
// ==========================================

export const binaryNodes = sqliteTable(
  'binary_nodes',
  {
    id: integer('id').primaryKey({ autoIncrement: true }),
    userId: integer('user_id')
      .notNull()
      .references(() => users.id, { onDelete: 'cascade' }),
    parentId: integer('parent_id'),
    placementPosition: integer('placement_position').notNull(), // 1 through 10 slot positions
    sponsorId: integer('sponsor_id').references(() => users.id, { onDelete: 'set null' }),
    sponsorName: text('sponsor_name'),
    memberId: text('member_id'),
    memberName: text('member_name').notNull(),
    phone: text('phone'),
    passwordEncrypted: text('password_encrypted'),
    tpinEncrypted: text('tpin_encrypted'),
    rank: text('rank').default('Associate'),
    packageName: text('package_name').default('Basic Starter'),
    status: text('status').notNull().default('active'),
    notes: text('notes'),
    createdAt: integer('created_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
    updatedAt: integer('updated_at', { mode: 'timestamp' })
      .notNull()
      .$defaultFn(() => new Date()),
  },
  (table) => [
    uniqueIndex('idx_binary_parent_position').on(table.parentId, table.placementPosition),
    index('idx_binary_user').on(table.userId),
    index('idx_binary_sponsor').on(table.sponsorId),
    index('idx_binary_member_id').on(table.memberId),
  ]
);

// ==========================================
// 6. TOOLKIT, DIRECTORY & GLOSSARY
// ==========================================

export const ecosystemLinks = sqliteTable('ecosystem_links', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  title: text('title').notNull(),
  url: text('url').notNull(),
  category: text('category').notNull().default('General'),
  description: text('description'),
  sortOrder: integer('sort_order').default(0),
  isActive: integer('is_active', { mode: 'boolean' }).notNull().default(true),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const marketingResources = sqliteTable('marketing_resources', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  title: text('title').notNull(),
  category: text('category').notNull(), // PDF, Presentation, Video, Logo
  fileR2Key: text('file_r2_key').notNull(), // Cloudflare R2 file storage
  downloadCount: integer('download_count').default(0),
  fileSize: integer('file_size'),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const sblContacts = sqliteTable('sbl_contacts', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull(),
  designation: text('designation').notNull(),
  department: text('department'),
  phone: text('phone').notNull(),
  whatsapp: text('whatsapp'),
  email: text('email'),
  notes: text('notes'),
  sortOrder: integer('sort_order').default(0),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

export const abbreviations = sqliteTable('abbreviations', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  abbreviation: text('abbreviation').notNull().unique(),
  term: text('term').notNull(),
  definition: text('definition').notNull(),
  category: text('category'),
  sortOrder: integer('sort_order').default(0),
  createdAt: integer('created_at', { mode: 'timestamp' })
    .notNull()
    .$defaultFn(() => new Date()),
});

// ==========================================
// DRIZZLE RELATIONS
// ==========================================

export const usersRelations = relations(users, ({ many }) => ({
  userRoles: many(userRoles),
  leads: many(leads),
  tasks: many(tasks),
  activities: many(activities),
  investments: many(investments),
}));

export const leadsRelations = relations(leads, ({ one, many }) => ({
  owner: one(users, {
    fields: [leads.ownerUserId],
    references: [users.id],
  }),
  source: one(leadSources, {
    fields: [leads.leadSourceId],
    references: [leadSources.id],
  }),
  activities: many(activities),
  tasks: many(tasks),
  interests: many(leadInterests),
  presentations: many(presentations),
}));

export const rolesRelations = relations(roles, ({ many }) => ({
  userRoles: many(userRoles),
  rolePermissions: many(rolePermissions),
}));
