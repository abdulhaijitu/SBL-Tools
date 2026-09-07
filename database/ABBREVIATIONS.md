# Abbreviation CRUD

Laravel: the additive migration preserves the original glossary and seeds it once. Reading remains available to active authenticated users; changing terms requires the existing users.manage permission. Run php artisan migrate when updating another Laravel installation.

Cloudflare: before deploying the rebuilt worker.js, apply database/abbreviations_d1.sql to the target D1 database. This file is an initial migration; apply it once, because rerunning it can restore deleted default terms. Configure the Worker secret ABBREVIATIONS_ADMIN_PASSWORD with a strong ASCII password. The glossary then uses browser HTTP Basic authentication (username admin) over HTTPS. Without this secret, the glossary remains read-only. No production migration, secret configuration, or deployment has been performed.

The Worker has no existing session authentication; this separate gate protects the newly added glossary write endpoints. It does not secure unrelated Worker endpoints.

Validation: php artisan test --filter=AbbreviationCrudTest; node --test tests/Feature/abbreviation-worker.test.mjs; npm run build; php render_pages.php; node build_worker.cjs.
