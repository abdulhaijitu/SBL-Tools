import { migrate } from "drizzle-orm/node-postgres/migrator";
import { drizzle } from "drizzle-orm/node-postgres";
import { Client } from "pg";
import * as dotenv from "dotenv";

dotenv.config();

async function runMigrations() {
    const connectionString = process.env.DATABASE_URL;

    if (!connectionString) {
        console.error(
            "❌ Error: DATABASE_URL is not set in environment or .env file.",
        );
        process.exit(1);
    }

    console.log("🔄 Connecting to PostgreSQL database...");
    const client = new Client({ connectionString });
    await client.connect();

    try {
        const db = drizzle(client);
        console.log("🚀 Running pending Drizzle migrations from ./drizzle...");
        await migrate(db, { migrationsFolder: "./drizzle" });
        console.log("✅ Migrations completed successfully!");
    } catch (err) {
        console.error("❌ Migration failed:", err);
        process.exit(1);
    } finally {
        await client.end();
    }
}

runMigrations();
