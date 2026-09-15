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

// List official ranks with rewards
financialsRouter.get("/ranks", async (c) => {
    const { db } = await getDb(c);
    const ranksList = await db
        .select()
        .from(schema.ranks)
        .orderBy(schema.ranks.levelOrder);
    return c.json(ranksList);
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

// Financial Package Calculator endpoint (100-Week SBL Model & USD Converter)
financialsRouter.post("/calculate", async (c) => {
    const { amountBdt, projectName, durationWeeks } = await c.req.json();
    const bdt = Number(amountBdt) || 10000;
    const weeks = Number(durationWeeks) || 100;

    let weeklyRate = 1.75; // default National
    if (projectName === "Starter" || bdt <= 25000) {
        weeklyRate = 1.5; // Starter 10k -> 15k over 100 weeks = 150/week = 1.5%
    } else if (projectName === "International" || bdt >= 500000) {
        weeklyRate = 2.0;
    }

    const weeklyReturnBdt = (bdt * weeklyRate) / 100;
    const totalReturnBdt = weeklyReturnBdt * weeks;
    const totalProfitBdt = totalReturnBdt - bdt;
    const usd = bdt / 100;

    // Projected lifetime monthly profit sharing after 100 weeks
    const monthlyLifetimeMin =
        weeklyRate >= 2.0 ? 25000 : weeklyRate >= 1.75 ? 5000 : 0;
    const monthlyLifetimeMax =
        weeklyRate >= 2.0 ? 100000 : weeklyRate >= 1.75 ? 20000 : 0;

    return c.json({
        amountBdt: bdt.toFixed(2),
        amountUsd: usd.toFixed(2),
        exchangeRate: "1 USD = 100 BDT",
        weeklyRatePercent: weeklyRate,
        weeklyReturnBdt: weeklyReturnBdt.toFixed(2),
        durationWeeks: weeks,
        durationMonths: Math.round(weeks / 4.16),
        totalReturnBdt: totalReturnBdt.toFixed(2),
        totalProfitBdt: totalProfitBdt.toFixed(2),
        monthlyLifetimeMin,
        monthlyLifetimeMax,
    });
});
