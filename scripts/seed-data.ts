import { drizzle } from "drizzle-orm/node-postgres";
import { Client } from "pg";
import * as dotenv from "dotenv";
import * as bcrypt from "bcryptjs";
import * as schema from "../src/db/schema";
import { eq } from "drizzle-orm";

dotenv.config();

async function seed() {
    const connectionString = process.env.DATABASE_URL;

    if (!connectionString) {
        console.error(
            "❌ Error: DATABASE_URL is not set in environment or .env file.",
        );
        process.exit(1);
    }

    console.log("🔄 Connecting to PostgreSQL database for seeding...");
    const client = new Client({ connectionString });
    await client.connect();

    try {
        const db = drizzle(client, { schema });

        console.log("🌱 Seeding Roles...");
        const defaultRoles = [
            {
                name: "super_admin",
                displayName: "Super Administrator",
                description: "Full cross-workspace administrative access",
            },
            {
                name: "manager",
                displayName: "Sales / Branch Manager",
                description:
                    "Can view and assign team leads and monitor operations",
            },
            {
                name: "agent",
                displayName: "Sales Agent",
                description: "Can manage personal leads, activities, and tasks",
            },
            {
                name: "marketing",
                displayName: "Growth Marketing",
                description: "Can manage campaigns, content, and lead channels",
            },
            {
                name: "member",
                displayName: "Registered Member",
                description:
                    "Standard ecosystem member with private tree access",
            },
            {
                name: "demo_member",
                displayName: "Demo Member",
                description: "Preview-only access without data modification",
            },
        ];

        for (const r of defaultRoles) {
            const existing = await db
                .select()
                .from(schema.roles)
                .where(eq(schema.roles.name, r.name))
                .limit(1);
            if (existing.length === 0) {
                await db.insert(schema.roles).values(r);
            }
        }

        console.log("🌱 Seeding Default Admin User...");
        const existingAdmin = await db
            .select()
            .from(schema.users)
            .where(eq(schema.users.email, "admin@sbl.test"))
            .limit(1);
        let adminUserId = existingAdmin[0]?.id;

        if (!adminUserId) {
            const hashedPassword = await bcrypt.hash("password", 10);
            const [newAdmin] = await db
                .insert(schema.users)
                .values({
                    name: "SBL Admin",
                    email: "admin@sbl.test",
                    password: hashedPassword,
                    phone: "01700000000",
                    designation: "System Administrator",
                    status: "active",
                    emailVerifiedAt: new Date(),
                })
                .returning();
            adminUserId = newAdmin.id;

            // Assign super_admin role
            const [superRole] = await db
                .select()
                .from(schema.roles)
                .where(eq(schema.roles.name, "super_admin"))
                .limit(1);
            if (superRole) {
                await db.insert(schema.userRoles).values({
                    userId: adminUserId,
                    roleId: superRole.id,
                });
            }
        }

        console.log("🌱 Seeding Lead Sources...");
        const defaultSources = [
            { name: "Facebook Profile", sortOrder: 1 },
            { name: "Facebook Page", sortOrder: 2 },
            { name: "Reel", sortOrder: 3 },
            { name: "Story", sortOrder: 4 },
            { name: "Messenger", sortOrder: 5 },
            { name: "WhatsApp", sortOrder: 6 },
            { name: "Referral", sortOrder: 7 },
            { name: "Offline Meeting", sortOrder: 8 },
            { name: "Event", sortOrder: 9 },
            { name: "Existing Client", sortOrder: 10 },
            { name: "Personal Network", sortOrder: 11 },
            { name: "Other", sortOrder: 12 },
        ];

        for (const s of defaultSources) {
            const existing = await db
                .select()
                .from(schema.leadSources)
                .where(eq(schema.leadSources.name, s.name))
                .limit(1);
            if (existing.length === 0) {
                await db.insert(schema.leadSources).values({
                    name: s.name,
                    sortOrder: s.sortOrder,
                    isActive: true,
                });
            }
        }

        console.log("🌱 Seeding Investment Plans...");
        const defaultPlans = [
            {
                name: "Basic Starter",
                minAmount: "10000.00",
                maxAmount: "49999.00",
                returnRate: "8.50",
                durationDays: 180,
                riskLevel: "low",
            },
            {
                name: "Growth Builder",
                minAmount: "50000.00",
                maxAmount: "199999.00",
                returnRate: "12.00",
                durationDays: 365,
                riskLevel: "moderate",
            },
            {
                name: "Executive Pro",
                minAmount: "200000.00",
                maxAmount: "999999.00",
                returnRate: "15.50",
                durationDays: 365,
                riskLevel: "moderate",
            },
            {
                name: "Ecosystem Elite",
                minAmount: "1000000.00",
                maxAmount: "5000000.00",
                returnRate: "18.00",
                durationDays: 730,
                riskLevel: "high",
            },
        ];

        for (const p of defaultPlans) {
            const existing = await db
                .select()
                .from(schema.investmentPlans)
                .where(eq(schema.investmentPlans.name, p.name))
                .limit(1);
            if (existing.length === 0) {
                await db.insert(schema.investmentPlans).values(p);
            }
        }

        console.log("🌱 Seeding Standard Abbreviations...");
        const defaultAbbreviations = [
            {
                term: "Direct Sponsor",
                abbreviation: "DS",
                definition:
                    "The person who directly invited and registered the member.",
                category: "Network",
            },
            {
                term: "Placement Upline",
                abbreviation: "PU",
                definition:
                    "The immediate parent node in the 10-slot network tree.",
                category: "Network",
            },
            {
                term: "Team Volume",
                abbreviation: "TV",
                definition:
                    "The cumulative investment or sales volume generated by a team subtree.",
                category: "Finance",
            },
            {
                term: "Transaction PIN",
                abbreviation: "TPIN",
                definition:
                    "Secure 4-6 digit numeric code required for fund withdrawals and transfers.",
                category: "Security",
            },
            {
                term: "Return on Investment",
                abbreviation: "ROI",
                definition:
                    "Expected financial yield on active ecosystem packages.",
                category: "Finance",
            },
            {
                term: "Lead Conversion Rate",
                abbreviation: "LCR",
                definition:
                    "Percentage of prospects transitioning from new to converted.",
                category: "CRM",
            },
        ];

        for (const a of defaultAbbreviations) {
            const existing = await db
                .select()
                .from(schema.abbreviations)
                .where(eq(schema.abbreviations.abbreviation, a.abbreviation))
                .limit(1);
            if (existing.length === 0) {
                await db.insert(schema.abbreviations).values(a);
            }
        }

        console.log("✅ Seed data successfully populated into PostgreSQL!");
    } catch (err) {
        console.error("❌ Seeding failed:", err);
        process.exit(1);
    } finally {
        await client.end();
    }
}

seed();
