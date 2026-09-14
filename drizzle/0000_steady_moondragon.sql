CREATE TABLE `abbreviations` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`abbreviation` text NOT NULL,
	`term` text NOT NULL,
	`definition` text NOT NULL,
	`category` text,
	`sort_order` integer DEFAULT 0,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `abbreviations_abbreviation_unique` ON `abbreviations` (`abbreviation`);--> statement-breakpoint
CREATE TABLE `activities` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`lead_id` integer,
	`user_id` integer,
	`type` text NOT NULL,
	`details` text,
	`scheduled_at` integer,
	`completed_at` integer,
	`created_at` integer NOT NULL,
	FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE set null
);
--> statement-breakpoint
CREATE INDEX `idx_activities_lead` ON `activities` (`lead_id`);--> statement-breakpoint
CREATE INDEX `idx_activities_user` ON `activities` (`user_id`);--> statement-breakpoint
CREATE TABLE `binary_nodes` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`user_id` integer NOT NULL,
	`parent_id` integer,
	`placement_position` integer NOT NULL,
	`sponsor_id` integer,
	`sponsor_name` text,
	`member_id` text,
	`member_name` text NOT NULL,
	`phone` text,
	`password_encrypted` text,
	`tpin_encrypted` text,
	`rank` text DEFAULT 'Associate',
	`package_name` text DEFAULT 'Basic Starter',
	`status` text DEFAULT 'active' NOT NULL,
	`notes` text,
	`created_at` integer NOT NULL,
	`updated_at` integer NOT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`sponsor_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE set null
);
--> statement-breakpoint
CREATE UNIQUE INDEX `idx_binary_parent_position` ON `binary_nodes` (`parent_id`,`placement_position`);--> statement-breakpoint
CREATE INDEX `idx_binary_user` ON `binary_nodes` (`user_id`);--> statement-breakpoint
CREATE INDEX `idx_binary_sponsor` ON `binary_nodes` (`sponsor_id`);--> statement-breakpoint
CREATE INDEX `idx_binary_member_id` ON `binary_nodes` (`member_id`);--> statement-breakpoint
CREATE TABLE `campaigns` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`title` text NOT NULL,
	`platform` text NOT NULL,
	`status` text DEFAULT 'active' NOT NULL,
	`budget` numeric,
	`spent_amount` numeric,
	`start_date` integer,
	`end_date` integer,
	`target_leads_count` integer,
	`acquired_leads_count` integer DEFAULT 0,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `commission_types` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`rate_percentage` numeric,
	`description` text,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `commission_types_name_unique` ON `commission_types` (`name`);--> statement-breakpoint
CREATE TABLE `content_items` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`title` text NOT NULL,
	`type` text NOT NULL,
	`language` text DEFAULT 'bn' NOT NULL,
	`file_r2_key` text,
	`external_url` text,
	`text_content` text,
	`category` text,
	`sort_order` integer DEFAULT 0,
	`is_active` integer DEFAULT true NOT NULL,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `daily_activity_logs` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`user_id` integer NOT NULL,
	`log_date` text NOT NULL,
	`calls_count` integer DEFAULT 0 NOT NULL,
	`meetings_count` integer DEFAULT 0 NOT NULL,
	`presentations_count` integer DEFAULT 0 NOT NULL,
	`follow_ups_count` integer DEFAULT 0 NOT NULL,
	`new_leads_count` integer DEFAULT 0 NOT NULL,
	`notes` text,
	`created_at` integer NOT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE UNIQUE INDEX `idx_daily_activity_user_date` ON `daily_activity_logs` (`user_id`,`log_date`);--> statement-breakpoint
CREATE TABLE `ecosystem_links` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`title` text NOT NULL,
	`url` text NOT NULL,
	`category` text DEFAULT 'General' NOT NULL,
	`description` text,
	`sort_order` integer DEFAULT 0,
	`is_active` integer DEFAULT true NOT NULL,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `investment_plans` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`min_amount` numeric NOT NULL,
	`max_amount` numeric,
	`return_rate` numeric,
	`duration_days` integer,
	`description` text,
	`is_active` integer DEFAULT true NOT NULL,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `investment_plans_name_unique` ON `investment_plans` (`name`);--> statement-breakpoint
CREATE TABLE `investments` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`user_id` integer NOT NULL,
	`plan_id` integer NOT NULL,
	`amount` numeric NOT NULL,
	`return_amount` numeric,
	`status` text DEFAULT 'active' NOT NULL,
	`start_date` integer NOT NULL,
	`maturity_date` integer,
	`created_at` integer NOT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`plan_id`) REFERENCES `investment_plans`(`id`) ON UPDATE no action ON DELETE restrict
);
--> statement-breakpoint
CREATE INDEX `idx_investments_user` ON `investments` (`user_id`);--> statement-breakpoint
CREATE TABLE `lead_interests` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`lead_id` integer NOT NULL,
	`interest_type` text NOT NULL,
	`details` text,
	`created_at` integer NOT NULL,
	FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE TABLE `lead_sources` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`description` text,
	`is_active` integer DEFAULT true NOT NULL,
	`sort_order` integer DEFAULT 0 NOT NULL,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `lead_sources_name_unique` ON `lead_sources` (`name`);--> statement-breakpoint
CREATE TABLE `leads` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`mobile` text NOT NULL,
	`whatsapp` text,
	`email` text,
	`photo_r2_key` text,
	`facebook_url` text,
	`location` text,
	`profession_or_business` text,
	`lead_source_id` integer,
	`lead_source_detail` text,
	`stage` text DEFAULT 'new' NOT NULL,
	`temperature` text DEFAULT 'cold' NOT NULL,
	`score` integer DEFAULT 20 NOT NULL,
	`budget_range` text,
	`decision_timeline` text,
	`owner_user_id` integer,
	`next_action_type` text,
	`next_action_at` integer,
	`last_contact_at` integer,
	`notes` text,
	`created_at` integer NOT NULL,
	`updated_at` integer NOT NULL,
	`deleted_at` integer,
	FOREIGN KEY (`lead_source_id`) REFERENCES `lead_sources`(`id`) ON UPDATE no action ON DELETE set null,
	FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE set null
);
--> statement-breakpoint
CREATE INDEX `idx_leads_mobile` ON `leads` (`mobile`);--> statement-breakpoint
CREATE INDEX `idx_leads_stage` ON `leads` (`stage`);--> statement-breakpoint
CREATE INDEX `idx_leads_temperature` ON `leads` (`temperature`);--> statement-breakpoint
CREATE INDEX `idx_leads_owner` ON `leads` (`owner_user_id`);--> statement-breakpoint
CREATE INDEX `idx_leads_next_action` ON `leads` (`next_action_at`);--> statement-breakpoint
CREATE TABLE `marketing_resources` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`title` text NOT NULL,
	`category` text NOT NULL,
	`file_r2_key` text NOT NULL,
	`download_count` integer DEFAULT 0,
	`file_size` integer,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `permissions` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`display_name` text NOT NULL,
	`module` text NOT NULL,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `permissions_name_unique` ON `permissions` (`name`);--> statement-breakpoint
CREATE TABLE `presentations` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`lead_id` integer NOT NULL,
	`presenter_user_id` integer,
	`type` text DEFAULT 'one_to_one' NOT NULL,
	`scheduled_at` integer NOT NULL,
	`status` text DEFAULT 'scheduled' NOT NULL,
	`outcome_notes` text,
	`created_at` integer NOT NULL,
	FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`presenter_user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE set null
);
--> statement-breakpoint
CREATE TABLE `ranks` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`level_order` integer NOT NULL,
	`target_sales_amount` numeric,
	`commission_bonus_rate` numeric,
	`badge_icon` text,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `ranks_name_unique` ON `ranks` (`name`);--> statement-breakpoint
CREATE TABLE `role_permissions` (
	`role_id` integer NOT NULL,
	`permission_id` integer NOT NULL,
	FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE UNIQUE INDEX `idx_role_permissions_unique` ON `role_permissions` (`role_id`,`permission_id`);--> statement-breakpoint
CREATE TABLE `roles` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`display_name` text NOT NULL,
	`description` text,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `roles_name_unique` ON `roles` (`name`);--> statement-breakpoint
CREATE TABLE `sbl_contacts` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`designation` text NOT NULL,
	`department` text,
	`phone` text NOT NULL,
	`whatsapp` text,
	`email` text,
	`notes` text,
	`sort_order` integer DEFAULT 0,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `tasks` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`lead_id` integer,
	`user_id` integer NOT NULL,
	`title` text NOT NULL,
	`description` text,
	`due_date` integer,
	`priority` text DEFAULT 'medium' NOT NULL,
	`status` text DEFAULT 'pending' NOT NULL,
	`completed_at` integer,
	`created_at` integer NOT NULL,
	`updated_at` integer NOT NULL,
	FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE INDEX `idx_tasks_user_status` ON `tasks` (`user_id`,`status`);--> statement-breakpoint
CREATE INDEX `idx_tasks_due_date` ON `tasks` (`due_date`);--> statement-breakpoint
CREATE TABLE `user_roles` (
	`user_id` integer NOT NULL,
	`role_id` integer NOT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE no action ON DELETE cascade,
	FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON UPDATE no action ON DELETE cascade
);
--> statement-breakpoint
CREATE UNIQUE INDEX `idx_user_roles_unique` ON `user_roles` (`user_id`,`role_id`);--> statement-breakpoint
CREATE TABLE `users` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`email` text NOT NULL,
	`phone` text,
	`designation` text,
	`password` text NOT NULL,
	`status` text DEFAULT 'active' NOT NULL,
	`created_at` integer NOT NULL,
	`updated_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `users_email_unique` ON `users` (`email`);--> statement-breakpoint
CREATE UNIQUE INDEX `idx_users_email` ON `users` (`email`);--> statement-breakpoint
CREATE INDEX `idx_users_phone` ON `users` (`phone`);