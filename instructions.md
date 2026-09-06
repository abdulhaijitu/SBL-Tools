# Antigravity Bootstrap Prompt

Build **SBL Growth Manager**, a mobile-first personal CRM + activity + lead + income management system for managing SBL Ecosystem business operations.

Use this file as the source of truth. Implement in phases. Do not over-engineer. Prioritize fast data entry, clear follow-up workflow, lead pipeline visibility, and real received income tracking.

Preferred stack:
- Laravel 12+
- PHP 8.3+
- MySQL 8+
- Blade
- Tailwind CSS
- Alpine.js
- Laravel Breeze or equivalent simple auth
- Chart.js only where needed
- No SPA unless clearly necessary

Start with Phase 1 MVP only. Keep architecture modular so later phases can be added without rewrites.

---

# 1. Project Name

**SBL Growth Manager**

Subtitle:

**Personal CRM + Activity + Lead + Income Management System**

Primary user:
- Owner / Admin
- Initially single-user
- Future team support must remain possible

---

# 2. Core Goal

The system must answer these questions every day:

1. Whom do I need to contact today?
2. Which leads are most important?
3. What is the next action for each lead?
4. Which content/campaign generates leads?
5. Which leads reached presentation or conversion?
6. How much income is earned?
7. How much is withdrawable?
8. How much money is actually received?
9. What capital is still unrecovered?
10. What activities are producing results?

Primary business flow:

`Marketing → Lead → Qualification → Follow-up → Presentation → Conversion → Customer/Member → Sale/Commission → Withdrawal → Actual Received`

Secondary workflow:

`Daily Action → Task → Result → Next Action`

---

# 3. Main Modules

## 3.1 Dashboard
Show:

### Today
- Today's Tasks
- Follow-ups Due
- Overdue Follow-ups
- New Leads
- Presentations Today
- Meetings Today
- Today's Sales
- Today's Actual Received Income

### Funnel Summary
- Total Leads
- New
- Contacted
- Interested
- Qualified
- Presentation
- Follow-up
- Converted

### Financial Summary
- Product Sales
- Affiliate/Referral Commission
- Team/Rank Commission
- Investment/Business Return
- Withdrawable
- Withdrawal Pending
- Successfully Received

### Lead Priority
- Hot Leads
- Warm Leads
- Cold Leads
- Stale Leads

---

# 4. Sidebar / Navigation

## Dashboard

## CRM
- Leads
- Contacts
- Customers
- Members

## Activities
- My Tasks
- Follow-ups
- Meetings
- Presentations

## Marketing
- Content Calendar
- Campaigns
- Lead Sources

## Business
- Product Sales
- Investments
- Commissions
- Withdrawals

## Team
- Members
- Ranks

## Reports

## Settings

Keep sidebar simple and collapsible.

---

# 5. Lead CRM

## Lead Fields
- id
- name
- mobile
- whatsapp
- email nullable
- facebook_url nullable
- location nullable
- profession_or_business nullable
- lead_source_id
- lead_source_detail nullable
- interest_types JSON or pivot
- lead_tag nullable
- stage
- temperature
- score
- budget_range nullable
- decision_timeline nullable
- owner_user_id
- next_action_type nullable
- next_action_at nullable
- last_contact_at nullable
- created_at
- updated_at

## Interest Types
Allow multiple:
- Product
- E-commerce
- Dropshipping
- Affiliate
- Network
- Investment
- Partnership

## Quick Tags
- P1 = Product
- E1 = E-commerce
- A1 = Affiliate/Network
- I1 = Investment
- B1 = Business Partnership

## Lead Pipeline
Use these stages:

`new`
`contacted`
`interested`
`qualified`
`presentation`
`follow_up`
`decision`
`converted`
`not_now`
`lost`
`not_suitable`

Build both:
- Table view
- Kanban view

Allow drag-and-drop stage movement.

---

# 6. Lead Profile

Each lead profile must show:

## Header
- Name
- Main contact
- Interest
- Stage
- Temperature
- Score
- Next Action
- Assigned owner

## Timeline
Show all activities chronologically:
- Lead created
- Calls
- Messenger
- WhatsApp
- Notes
- Meetings
- Presentations
- Follow-ups
- Stage changes
- Conversion
- Sale
- Commission
- Withdrawal-related references if linked

## Quick Actions
- Add Note
- Add Call
- Add Follow-up
- Schedule Presentation
- Add Meeting
- Convert Lead
- Add Sale
- Add Task

---

# 7. Lead Score

Use a simple configurable scoring model.

Default:
- Strong interest: +20
- Budget available: +20
- Presentation attended: +20
- Asked follow-up question: +15
- Decision timeline <= 30 days: +15
- Responds regularly: +10

Temperature:
- 80–100 = Hot
- 50–79 = Warm
- 0–49 = Cold

Allow manual score override.

---

# 8. Activity / Task Manager

## Task Types
- Call
- Messenger
- WhatsApp
- Follow-up
- Presentation
- Meeting
- Content
- Product Follow-up
- Payment Follow-up
- Member Support
- Training
- Other

## Priority
- High
- Medium
- Low

## Status
- Pending
- In Progress
- Completed
- Cancelled

## Task Fields
- title
- type
- related_lead_id nullable
- related_customer_id nullable
- related_member_id nullable
- due_at
- priority
- status
- notes
- completed_at nullable
- outcome nullable
- next_action nullable
- next_action_at nullable

The system must make "Next Action" highly visible.

---

# 9. Follow-up Workflow

Every meaningful interaction should support:
- Outcome
- Next action
- Next follow-up date/time

Rules:
- Overdue follow-ups appear on Dashboard
- No completed follow-up should disappear without an outcome
- If a lead has no future follow-up and is not closed, show as "Needs Next Action"
- Mark stale leads if no activity for configurable number of days

Default stale threshold:
- 7 days for Hot/Warm
- 14 days for Cold

---

# 10. Presentation Manager

## Fields
- lead_id
- date_time
- type
- topic
- interest_focus
- questions
- objections
- outcome
- next_follow_up_at
- notes

## Presentation Type
- Online
- Offline
- Group
- 1-to-1

## Outcome
- Hot
- Warm
- Cold
- Converted
- Not Interested

Presentations must appear in:
- Lead timeline
- Calendar
- Dashboard upcoming list

---

# 11. Contacts / Customers / Members

A lead may convert into one or more types.

## Contact Types
- Product Customer
- E-commerce Client/Partner
- Affiliate Member
- Network Member
- Investor
- Business Partner

Use a contact/person master record where possible to avoid duplicates.

---

# 12. Member / Team Basics

Phase 1 or early Phase 3 only needs:
- member_code nullable
- person/contact
- sponsor_member_id nullable
- join_date
- current_rank_id nullable
- side_position nullable
- status
- notes

## Member Status
- Active
- Inactive
- Pending
- Closed

Do not build complex genealogy initially.

Future-ready fields:
- left/right position
- sponsor tree
- team metrics

---

# 13. Rank Management

Ranks must be configurable, not hard-coded.

Initial known sequence may include:
- FME
- SME
- PME
- BME
- GME
- ETD

Rank fields:
- code
- name
- order
- requirement_text
- incentive_amount nullable
- active

Do not assume rank rules unless entered by Admin.

---

# 14. Marketing / Content Calendar

## Content Status
- Idea
- Planned
- Design
- Ready
- Published
- Cancelled

## Fields
- title
- platform
- content_type
- topic
- caption
- creative_path nullable
- scheduled_at
- published_at nullable
- cta
- campaign_id nullable
- reach nullable
- engagement nullable
- inbox_count nullable
- leads_generated nullable
- conversions nullable
- notes

## Platforms
- Facebook Profile
- Facebook Page
- Facebook Story
- Reel
- Messenger
- WhatsApp
- TikTok
- YouTube
- Offline
- Other

Main report:
`Content/Campaign → Leads → Presentations → Conversions → Received Income`

---

# 15. Lead Sources

Configurable sources:
- Facebook Profile
- Facebook Page
- Reel
- Story
- Messenger
- WhatsApp
- Referral
- Offline Meeting
- Event
- Existing Client
- Personal Network
- Other

Track:
- total leads
- qualified
- presentations
- conversions
- conversion rate
- received income

---

# 16. Product Sales

## Product
- name
- sku nullable
- category nullable
- cost_price nullable
- selling_price nullable
- affiliate_commission nullable
- active

## Sale
- customer_id
- sale_date
- subtotal
- discount
- delivery_charge
- total
- payment_status
- delivery_status
- notes

## Sale Items
- sale_id
- product_id
- qty
- unit_price
- cost_price
- line_profit

## Payment Status
- Unpaid
- Partial
- Paid
- Refunded

## Delivery Status
- Pending
- Processing
- Shipped
- Delivered
- Returned
- Cancelled

Support repeat-purchase follow-up.

---

# 17. Investment / Capital Tracker

Keep this module completely separate from commissions.

## Fields
- title
- plan_name
- start_date
- principal_amount
- duration_weeks nullable
- projected_return nullable
- projected_notes nullable
- actual_received
- capital_recovered_amount
- remaining_capital
- status
- notes

## Status
- Testing
- Active
- Capital Recovering
- Capital Recovered
- Profit Stage
- Closed
- Suspended

Important:
Projected return must NEVER count as actual income.

---

# 18. Commission Manager

Commission types must be configurable.

Initial examples:
- Product Profit
- Spot Commission
- Referral / Refer Return
- Pair Reward
- UDR
- Rank Reward
- Investment / Business Return
- Other

## Commission Fields
- source_type
- source_reference nullable
- member_id nullable
- earned_date
- amount
- status
- withdrawable_date nullable
- notes

## Commission Status
- Earned
- Pending
- Withdrawable
- Withdrawal Requested
- Received
- Reversed

---

# 19. Withdrawal Tracker

## Fields
- withdrawal_no
- amount_requested
- request_date
- method
- account_reference nullable
- status
- approved_date nullable
- received_date nullable
- actual_amount_received nullable
- charge_amount nullable
- transaction_reference nullable
- notes

## Status
- Requested
- Processing
- Approved
- Received
- Rejected
- Cancelled

Rule:
Only `Received` withdrawals can contribute to Actual Received Income.

---

# 20. Financial Principle

This rule is mandatory across the whole system:

`Displayed/Earned Profit → Withdrawable Profit → Withdrawal Requested → Successfully Received`

Reports must prioritize:
**Successfully Received Amount**

Do not label projected, pending, or dashboard-only numbers as "Actual Income".

---

# 21. Daily Activity Log

Daily summary fields:
- date
- calls
- messenger_contacts
- whatsapp_contacts
- new_leads
- follow_ups
- presentations
- meetings
- conversions
- product_sales_count
- received_income
- note

Allow optional auto-generation from activity records.

---

# 22. Reports

Minimum reports:

## Lead Funnel Report
- Stage counts
- Stage conversion
- Date filter
- Source filter

## Activity Report
- Calls
- Follow-ups
- Meetings
- Presentations
- Completed tasks

## Marketing Report
- Content
- Campaign
- Leads generated
- Conversion

## Sales Report
- Sales
- Profit
- Repeat customers
- Product performance

## Income Report
Breakdown:
- Product
- Affiliate
- Team
- Rank
- Investment/business return
- Other

Show separately:
- Earned
- Withdrawable
- Pending Withdrawal
- Successfully Received

## Capital Report
- Total Invested
- Capital Recovered
- Remaining Capital
- Net Realized Profit

Date filters:
- Today
- This Week
- This Month
- This Year
- Custom

---

# 23. Dashboard UI Rules

Design:
- Mobile-first
- Clean
- Compact
- Low cognitive load
- Corporate
- Black / White / Orange accent
- Avoid excessive cards
- Keep key numbers above the fold

Dashboard sections:
1. Today's actions
2. Lead pipeline
3. Hot leads
4. Follow-up overdue
5. Financial snapshot
6. Recent activity

Use charts sparingly.

---

# 24. Mobile UX

Critical requirement.

Bottom or floating quick action:
`+`

Options:
- Add Lead
- Add Follow-up
- Add Task
- Add Sale
- Add Income/Commission
- Add Note

New lead should be savable in under 30 seconds.

Forms:
- Compact
- Minimal required fields
- Mobile keyboard friendly
- No unnecessary tabs
- Use dropdowns/selects where practical

---

# 25. Authentication / Roles

Phase 1:
- Super Admin / Owner only

Future roles:
- Admin
- Team Member
- Viewer

Use Laravel policies/permissions so future expansion is easy.

Do not expose another user's private leads unless permission is granted.

---

# 26. Notifications / Automation Roadmap

Future Phase 4:
- Follow-up reminders
- Presentation reminders
- Stale lead alerts
- Product repeat-purchase reminders
- Withdrawal pending alerts
- Daily action summary
- Hot lead alerts

Do not build external WhatsApp automation in MVP.

---

# 27. Database Design Guidance

Use normalized relational tables.

Suggested tables:
- users
- contacts
- leads
- lead_interests
- lead_sources
- activities
- tasks
- presentations
- customers
- members
- ranks
- content_items
- campaigns
- products
- sales
- sale_items
- investments
- commission_types
- commissions
- withdrawals
- daily_activity_logs
- settings

Use:
- foreign keys
- timestamps
- soft deletes where business history matters
- indexes on stage, status, due_at, lead_source_id, member_id, dates

Keep JSON only for flexible low-value metadata.
Prefer pivot tables for multi-select business relations.

---

# 28. Coding Conventions

- Follow Laravel conventions
- Use service classes only when logic becomes non-trivial
- Keep controllers thin
- Use Form Requests for validation
- Use Enums for stable status fields where practical
- Use Policies for authorization
- Use Events/Listeners only when useful
- Avoid premature abstraction
- Avoid unnecessary repositories
- Use database transactions for money-related writes
- Never use floating point for money
- Store money as DECIMAL(18,2)
- Store timestamps consistently
- Write migrations cleanly
- Seed essential statuses/config
- Add feature tests for critical workflows

---

# 29. Phase Plan

## Phase 1: MVP
Build first:
- Authentication
- Dashboard
- Leads CRM
- Lead table + Kanban
- Lead profile timeline
- Tasks
- Follow-ups
- Meetings
- Presentations
- Lead source tracking
- Content calendar
- Basic reports

Acceptance:
- Can add lead in <30 seconds
- Can assign next action
- Can see overdue follow-ups
- Can move lead through pipeline
- Can record presentation
- Can see lead source performance
- Can see today's tasks

## Phase 2: Business & Money
Add:
- Product sales
- Commissions
- Investments
- Withdrawals
- Actual Received
- Financial reports

## Phase 3: Team
Add:
- Members
- Sponsor relationships
- Ranks
- Team performance
- Optional network visualization

## Phase 4: Automation
Add:
- Reminders
- Notifications
- Stale lead rules
- Smart lead scoring
- Advanced analytics

---

# 30. Business / Safety Rules

The system must never automatically present projected returns as guaranteed income.

Use labels such as:
- Projected Return
- Expected Return
- Earned
- Withdrawable
- Received

Never use:
- Guaranteed Profit
- Risk-free Income
- Guaranteed Return

For all SBL financial claims:
- Keep source/reference field where useful
- Allow Admin notes
- Distinguish documented company claim from verified personal result

Capital protection rule:
`Capital Protection > Cash Flow > Profit > Growth`

Reinvestment should not be automated without explicit user action.

---

# 31. Acceptance Criteria

The MVP is successful if the user can:

1. Add a lead quickly
2. Assign interest and lead source
3. Set stage and score
4. Schedule next action
5. See follow-ups due today
6. See overdue leads
7. Record presentation outcome
8. Convert lead
9. Track source-to-conversion
10. Manage 30-day content plan
11. Use comfortably from mobile
12. View a clean dashboard without clutter

---

# 32. Build Order for Antigravity

Follow this order:

1. Project setup
2. Auth
3. Database schema
4. Seeders + enums
5. Base layout/sidebar
6. Lead CRM
7. Lead profile + timeline
8. Tasks/follow-ups
9. Presentations
10. Dashboard
11. Content calendar
12. Reports
13. Tests
14. Mobile QA
15. Phase 1 validation

Do not start Phase 2 until Phase 1 is stable.

---

# 33. Final Antigravity Instruction

Before writing code:
1. Read this file fully
2. Inspect current repository
3. Do not destroy existing working features
4. Create a concise implementation plan
5. Build one phase at a time
6. Validate database, UI, mobile layout, and workflows
7. Keep UI simple and production-ready
8. Ask only when a requirement is truly blocking
