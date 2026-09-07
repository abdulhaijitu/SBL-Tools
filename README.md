# SBL Growth Manager

Laravel CRM for leads, follow-ups, presentations, package tools, contacts and private team trees. Primary navigation: Dashboard, Leads, Plans & Toolkit, Contact, Team Explorer, Members, Roles & Permissions. Reports remain available from Dashboard; the existing marketing calendar is retained at `/marketing/content-calendar`.

## Run locally

Requires PHP 8.2+ (8.3 verified), Composer, Node 20.19+ or 22.12+, and SQLite or MySQL. On a **new checkout**:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm.cmd ci
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://localhost:8000. For frontend development, run `npm.cmd run dev` in another terminal. Use `npm.cmd` on Windows if PowerShell execution policy blocks `npm.ps1`.

For a **new local demo database only**, `php artisan db:seed` creates sample users, contacts, leads and trees. Do not reseed an existing working database to start the server. Seeded accounts have development passwords and must not be deployed as production accounts.

## Access and data

- Server middleware enforces role permissions and blocks inactive accounts.
- CRM records are scoped to their owner. Users with `leads.assign` can manage shared CRM data. Super Admin has access across workspaces.
- Team trees are private, including for CRM managers. Only Super Admin can switch tree owners.
- Members lists converted CRM leads. Team Explorer's Directory lists placement-tree members. User Management administers application login accounts.
- Registration assigns the Members role; an administrator configures its permissions. Members and Demo Members have no administrative access by default.
- Member passwords and TPINs use encrypted casts, are excluded from serialization, and load through an authorized, non-cacheable endpoint only after selecting Show.
- Preserve `APP_KEY` with database backups. **Do not regenerate it for an existing database**; stored member credentials depend on it. The encryption migration intentionally does not restore plaintext on rollback.
- Currency conversion uses a fixed demonstration rate of 1 USD = 120 BDT, not a live feed. Package calculators retain BDT inputs and model-based projections.

## Verify

```powershell
php artisan test --compact
php artisan view:cache
npm.cmd run build
```

Audit result, 2026-09-07: **95 tests / 342 assertions passed**. Chrome checks covered 320, 390, 768 and 1440px widths. See [PROJECT_AUDIT.md](PROJECT_AUDIT.md) for evidence and limits.

## Deployment

Laravel is the backend. Serve its `public` directory through a production PHP host; configure database, mail, HTTPS and sessions; set `APP_ENV=production` and `APP_DEBUG=false`; run migrations and compile assets. The existing Dockerfile runs `artisan serve` and is a preview runner, not a validated production server configuration.

The old Cloudflare Worker, generated authenticated HTML, D1 data export and duplicate CRUD backend were retired because they bypassed Laravel's security model. Original files are preserved in a private local archive under `storage/app/audit`, excluded from Docker context. **No remote deployment was changed.** Do not republish the retired Worker.

Online dependency advisory scans were blocked by automatic approval review because they transmit dependency metadata to a public registry. Dependency vulnerability status therefore remains unverified. Production load, external mail delivery, current financial package terms and formal accessibility compliance were not certified.
