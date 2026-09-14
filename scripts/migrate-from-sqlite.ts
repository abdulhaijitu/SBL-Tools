import { Client } from "pg";
import * as dotenv from "dotenv";
import * as fs from "fs";
import * as path from "path";

dotenv.config();

async function importExistingData() {
    const connectionString = process.env.DATABASE_URL;

    if (!connectionString) {
        console.error(
            "❌ Error: DATABASE_URL is not set in environment or .env file.",
        );
        process.exit(1);
    }

    const sqlFilePath = path.resolve(process.cwd(), "d1_schema.sql");
    if (!fs.existsSync(sqlFilePath)) {
        console.error(`❌ Error: d1_schema.sql not found at ${sqlFilePath}`);
        process.exit(1);
    }

    console.log("🔄 Connecting to PostgreSQL database...");
    const client = new Client({ connectionString });
    await client.connect();

    try {
        console.log("📖 Reading existing data from d1_schema.sql...");
        const rawSql = fs.readFileSync(sqlFilePath, "utf8");

        // Extract all INSERT lines
        const lines = rawSql.split("\n");
        const insertLines = lines.filter((l) =>
            l.trim().startsWith("INSERT OR IGNORE INTO "),
        );

        console.log(
            `Found ${insertLines.length} insert statements to migrate.`,
        );

        let successCount = 0;
        let skippedCount = 0;

        for (const line of insertLines) {
            // Transform SQLite "INSERT OR IGNORE INTO ..." -> PostgreSQL "INSERT INTO ... ON CONFLICT DO NOTHING"
            // Replace table name quotes and clean syntax
            let pgSql = line.trim();
            pgSql = pgSql.replace(
                /^INSERT\s+OR\s+IGNORE\s+INTO\s+/i,
                "INSERT INTO ",
            );

            // If ends with semicolon, remove it before appending ON CONFLICT
            if (pgSql.endsWith(";")) {
                pgSql = pgSql.slice(0, -1);
            }
            pgSql += " ON CONFLICT DO NOTHING;";

            // Skip sessions table as PostgreSQL doesn't need legacy PHP sessions
            if (
                pgSql.includes('INSERT INTO "sessions"') ||
                pgSql.includes("INSERT INTO 'sessions'")
            ) {
                skippedCount++;
                continue;
            }

            try {
                await client.query(pgSql);
                successCount++;
            } catch (err: any) {
                // Log warning for data mismatches but keep transferring
                console.warn(`⚠️ Skipped row [${err.message.slice(0, 80)}...]`);
                skippedCount++;
            }
        }

        // Reset primary key sequences for serial columns
        console.log("🔄 Syncing PostgreSQL serial sequences...");
        const tables = [
            "users",
            "lead_sources",
            "leads",
            "lead_interests",
            "activities",
            "tasks",
            "presentations",
            "campaigns",
            "content_items",
            "daily_activity_logs",
            "ranks",
            "investment_plans",
            "commission_types",
            "roles",
            "permissions",
            "ecosystem_links",
            "sbl_contacts",
            "binary_nodes",
            "abbreviations",
        ];

        for (const table of tables) {
            try {
                await client.query(`
          SELECT setval(
            pg_get_serial_sequence('"${table}"', 'id'),
            COALESCE((SELECT MAX(id) + 1 FROM "${table}"), 1),
            false
          );
        `);
            } catch {
                // Table might have custom PK or no rows
            }
        }

        console.log(
            `✅ Data migration completed! Successfully imported: ${successCount}, Skipped: ${skippedCount}`,
        );
    } catch (err) {
        console.error("❌ Data migration failed:", err);
        process.exit(1);
    } finally {
        await client.end();
    }
}

importExistingData();
