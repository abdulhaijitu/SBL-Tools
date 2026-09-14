import { Hono } from "hono";
import { eq, desc } from "drizzle-orm";
import { Env, AuthUser } from "../types/env";
import { getDb } from "../lib/get-db";
import * as schema from "../db/schema";
import { authMiddleware } from "../middleware/auth";

export const financialsRouter = new Hono<{
    Bindings: Env;
    Variables: { user: AuthUser };
}>();

financialsRouter.use("*", authMiddleware);

// List investment plans
financialsRouter.get("/plans", async (c) => {
    const { db } = await getDb(c);
    const plans = await db
        .select()
        .from(schema.investmentPlans)
        .where(eq(schema.investmentPlans.isActive, true))
        .orderBy(schema.investmentPlans.minAmount);
    return c.json(plans);
});

// List user investments
financialsRouter.get("/my-investments", async (c) => {
    const authUser = c.get("user");
    const { db } = await getDb(c);

    const investments = await db
        .select({
            id: schema.investments.id,
            amount: schema.investments.amount,
            status: schema.investments.status,
            startDate: schema.investments.startDate,
            maturityDate: schema.investments.maturityDate,
            returnAmount: schema.investments.returnAmount,
            planName: schema.investmentPlans.name,
            returnRate: schema.investmentPlans.returnRate,
        })
        .from(schema.investments)
        .innerJoin(
            schema.investmentPlans,
            eq(schema.investments.planId, schema.investmentPlans.id),
        )
        .where(eq(schema.investments.userId, authUser.userId))
        .orderBy(desc(schema.investments.createdAt));

    return c.json(investments);
});

// Financial Package Calculator endpoint (BDT <-> USD at 1 USD = 120 BDT)
financialsRouter.post("/calculate", async (c) => {
    const { amountBdt, durationDays, ratePercent } = await c.req.json();
    const bdt = Number(amountBdt) || 0;
    const rate = Number(ratePercent) || 0;
    const days = Number(durationDays) || 365;

    const usd = bdt / 120;
    const projectedReturnBdt = bdt * (1 + (rate / 100) * (days / 365));
    const profitBdt = projectedReturnBdt - bdt;

    return c.json({
        amountBdt: bdt.toFixed(2),
        amountUsd: usd.toFixed(2),
        exchangeRate: "1 USD = 120 BDT",
        projectedReturnBdt: projectedReturnBdt.toFixed(2),
        profitBdt: profitBdt.toFixed(2),
    });
});
