with open("src/client/App.tsx", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update officialPackages to the 3 exact official packages
old_pkg_start = "  const officialPackages = useMemo(\n    () => [\n      {"
old_pkg_end = "    ],\n    [lang],\n  );\n\n  // Ranks List"

new_packages_code = """  const officialPackages = useMemo(
    () => [
      {
        id: 'starter',
        name: lang === 'bn' ? 'স্টার্টার প্রজেক্ট' : 'Starter Project',
        priceBdt: 10000,
        badge: lang === 'bn' ? 'এন্ট্রি প্যাকেজ' : 'Entry Tier',
        badgeColor: 'bg-sky-500/20 text-sky-300 border-sky-500/30',
        bv: 100,
        capitalBdt: 10000,
        setupFeeBdt: 0,
        duration: '100 Weeks (24 Months)',
        dailyCap: 5000,
        returnRate: '১৫০ টাকা / সপ্তাহ (১০০ সপ্তাহে ১৫,০০০ টাকা রিটার্ন)',
        description:
          lang === 'bn'
            ? '১০০ সপ্তাহে ১৫,০০০ টাকা রিটার্ন (সপ্তাহে ১৫০ টাকা)। ফ্রি ফেসবুক পেজ সেটআপ ও অ্যাফিলিয়েট অ্যাকাউন্ট।'
            : '৳10,000 capital with guaranteed ৳15,000 return over 100 weeks (৳150/week).',
        benefits: [
          lang === 'bn' ? '১০০ সপ্তাহে মূলধনসহ ১৫,০০০ টাকা রিটার্ন (১৫০ টাকা/সপ্তাহ)' : '৳15,000 return over 100 weeks (৳150/week)',
          lang === 'bn' ? 'ফ্রি ফেসবুক পেজ সেটআপ ও টেকনিক্যাল সাপোর্ট' : 'Free professional Facebook business page setup',
          lang === 'bn' ? 'ফ্রি অ্যাফিলিয়েট অ্যাকাউন্ট ও ট্রেনিং' : 'Free affiliate account and orientation',
          lang === 'bn' ? 'আনলিমিটেড স্পনসর সুবিধা ও ফ্রি কনটেন্ট' : 'Unlimited direct sponsoring and content assets',
        ],
      },
      {
        id: 'national',
        name: lang === 'bn' ? 'ন্যাশনাল প্রজেক্ট' : 'National Project',
        priceBdt: 100000,
        badge: lang === 'bn' ? 'সবচেয়ে জনপ্রিয়' : 'Most Popular',
        badgeColor: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
        bv: 1000,
        capitalBdt: 100000,
        setupFeeBdt: 20000,
        duration: '100 Weeks + Lifetime Profit Sharing',
        dailyCap: 25000,
        returnRate: '১.৭৫% / সপ্তাহ (১০০ সপ্তাহে ১,৭৫,০০০ টাকা রিটার্ন)',
        description:
          lang === 'bn'
            ? '১,০০,০০০ - ৪,৯০,০০০ টাকা। প্রতি সপ্তাহে ১.৭৫% হিসেবে ১০০ সপ্তাহে ১,৭৫,০০০ টাকা গ্যারান্টিযুক্ত রিটার্ন ও আজীবন মাসিক মুনাফা শেয়ারিং।'
            : '৳1,00,000 - ৳4,90,000. 1.75%/week for 100 weeks (৳1,75,000 return) + lifetime profit sharing.',
        benefits: [
          lang === 'bn' ? '১০০ সপ্তাহে ১.৭৫%/সপ্তাহে মোট ১,৭৫,০০০ টাকা রিটার্ন' : '1.75%/week return for 100 weeks (৳175,000 total)',
          lang === 'bn' ? 'ওয়েবসাইট ডেভেলপমেন্ট ফি মাত্র ২০,০০০ টাকা' : 'Website development fee ৳20,000',
          lang === 'bn' ? '১০ লাখ টাকা পর্যন্ত ক্রাউডফান্ডিং সুবিধা' : 'Crowdfunding eligibility up to 10 Lac BDT',
          lang === 'bn' ? '১০০ সপ্তাহ পর আজীবন মাসিক ৫,০০০ - ২০,০০০ টাকা প্রফিট শেয়ারিং' : 'Lifetime profit sharing after 100 weeks (৳5k - ৳20k/mo)',
          lang === 'bn' ? 'ব্র্যান্ডেড শপিফাই স্টোর ও নিজস্ব প্যাকেজিং সাপোর্ট' : 'Branded Shopify eCommerce store and custom packaging',
        ],
      },
      {
        id: 'international',
        name: lang === 'bn' ? 'ইন্টারন্যাশনাল প্রজেক্ট' : 'International Project',
        priceBdt: 500000,
        badge: lang === 'bn' ? 'গ্লোবাল এন্টারপ্রাইজ' : 'Global Enterprise',
        badgeColor: 'bg-purple-500/20 text-purple-300 border-purple-500/30',
        bv: 5000,
        capitalBdt: 500000,
        setupFeeBdt: 50000,
        duration: '100 Weeks + Lifetime Profit Sharing',
        dailyCap: 50000,
        returnRate: '২.০% / সপ্তাহ (১০০ সপ্তাহে ১০,০০,০০০ টাকা রিটার্ন)',
        description:
          lang === 'bn'
            ? '৫,০০,০০০ - আনলিমিটেড। প্রতি সপ্তাহে ২.০% হিসেবে ১০০ সপ্তাহে ১০,০০,০০০ টাকা (৫ লাখে) গ্যারান্টিযুক্ত রিটার্ন ও আজীবন মাসিক ২৫,০০০-১,০০,০০০ টাকা প্রফিট শেয়ারিং।'
            : '৳5,00,000+. 2.0%/week for 100 weeks (৳10,00,000 return per 5L) + lifetime profit sharing.',
        benefits: [
          lang === 'bn' ? '১০০ সপ্তাহে ২.০%/সপ্তাহে মোট ১০,০০,০০০ টাকা রিটার্ন (৫ লাখে)' : '2.0%/week return for 100 weeks (৳10 Lac per 5 Lac)',
          lang === 'bn' ? 'আন্তর্জাতিক ওয়েবসাইট ও কনটেন্ট ফি ৫০,০০০ টাকা' : 'International website & video content fee ৳50,000',
          lang === 'bn' ? '৫০ লাখ টাকা পর্যন্ত ক্রাউডফান্ডিং সুবিধা' : 'Crowdfunding eligibility up to 50 Lac BDT',
          lang === 'bn' ? '১০০ সপ্তাহ পর আজীবন মাসিক ২৫,০০০ - ১,০০,০০০ টাকা প্রফিট শেয়ারিং' : 'Lifetime profit sharing after 100 weeks (৳25k - ৳1 Lac/mo)',
          lang === 'bn' ? 'ডেডিকেটেড প্রজেক্ট ম্যানেজমেন্ট টিম ও আনলিমিটেড ইউজিসি কনটেন্ট' : 'Dedicated project management team and unlimited UGC content',
        ],
      },
    ],
    [lang],
  );

  // Ranks List"""

p1 = content.find("  const officialPackages = useMemo(")
p2 = content.find("  // Ranks List", p1)
if p1 != -1 and p2 != -1:
    content = content[:p1] + new_packages_code + content[p2 + len("  // Ranks List"):]
    print("Replaced officialPackages successfully")
else:
    print("Warning: officialPackages bounds not found")

# 2. Update ranksList
old_ranks_start = "  const ranksList = ["
old_ranks_end = "  ];\n\n  // Objection scripts for Counseling Guide"

new_ranks_code = """  const ranksList = [
    {
      title: 'FME (Field Marketing Executive)',
      titleBn: 'এফএমই (Field Marketing Executive)',
      bvReq: lang === 'bn' ? 'ডিরেক্ট রেফারেন্স ১০ জন' : 'Direct Reference 10 Members',
      matchBonus: '১০%',
      reward: lang === 'bn' ? 'ক্যাশ ইনসেন্টিভ ৳৫,০০০' : 'Cash Incentive ৳5,000',
    },
    {
      title: 'SME (Senior Marketing Executive)',
      titleBn: 'এসএমই (Senior Marketing Executive)',
      bvReq: lang === 'bn' ? '৩০০ Pair Reward (ম্যাচিং)' : '300 Pair Reward Matches',
      matchBonus: '১২%',
      reward: lang === 'bn' ? 'ক্যাশ ইনসেন্টিভ ৳৫০,০০০' : 'Cash Incentive ৳50,000',
    },
    {
      title: 'PME (Promotional Marketing Executive)',
      titleBn: 'পিএমই (Promotional Marketing Executive)',
      bvReq: lang === 'bn' ? 'টিম: SME (লেফট ১৩ : রাইট ৭)' : 'Team: SME (Left 13 : Right 7)',
      matchBonus: '১৪%',
      reward: lang === 'bn' ? 'ক্যাশ ইনসেন্টিভ ৳১,০০,০০০' : 'Cash Incentive ৳1,00,000',
    },
    {
      title: 'BME (Brand Marketing Executive)',
      titleBn: 'বিএমই (Brand Marketing Executive)',
      bvReq: lang === 'bn' ? 'টিম: PME (লেফট ১০ : রাইট ৫)' : 'Team: PME (Left 10 : Right 5)',
      matchBonus: '১৫%',
      reward: lang === 'bn' ? 'ক্যাশ ইনসেন্টিভ ৳৫,০০,০০০' : 'Cash Incentive ৳5,00,000',
    },
    {
      title: 'GME (Global Marketing Executive)',
      titleBn: 'জিএমই (Global Marketing Executive)',
      bvReq: lang === 'bn' ? 'টিম: BME (লেফট ৮ : রাইট ৪)' : 'Team: BME (Left 8 : Right 4)',
      matchBonus: '১৬%',
      reward: lang === 'bn' ? 'ক্যাশ ইনসেন্টিভ ৳১০,০০,০০০' : 'Cash Incentive ৳10,00,000',
    },
    {
      title: 'ETD (Executive Team Director)',
      titleBn: 'ইটিডি (Executive Team Director)',
      bvReq: lang === 'bn' ? 'টিম: GME (লেফট ৭ : রাইট ৩)' : 'Team: GME (Left 7 : Right 3)',
      matchBonus: '১৮%',
      reward: lang === 'bn' ? 'ক্যাশ ইনসেন্টিভ ৳২০,০০,০০০ (মোট ৪০ লাখ টাকা পুরস্কার)' : 'Cash Incentive ৳20,00,000 (Total 40 Lac BDT)',
    },
  ];"""

r1 = content.find("  const ranksList = [")
r2 = content.find("  // Objection scripts for Counseling Guide", r1)
if r1 != -1 and r2 != -1:
    content = content[:r1] + new_ranks_code + "\n\n  " + content[r2:]
    print("Replaced ranksList successfully")
else:
    print("Warning: ranksList bounds not found")

# 3. Update Login Screen form labels and helpers
old_login_inputs = """            <div>
              <label className="block text-xs font-semibold text-slate-300 mb-1">
                {lang === 'bn' ? 'ইমেইল অ্যাড্রেস' : 'Email Address'}
              </label>
              <input
                type="email"
                value={loginEmail}
                onChange={(e) => setLoginEmail(e.target.value)}
                required
                className="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
              />
            </div>"""

new_login_inputs = """            <div>
              <label className="block text-xs font-semibold text-slate-300 mb-1">
                {lang === 'bn' ? 'মোবাইল নম্বর / ইউজারনেম' : 'Mobile Number / Username'}
              </label>
              <input
                type="text"
                value={loginEmail}
                onChange={(e) => setLoginEmail(e.target.value)}
                placeholder="017XXXXXXXX"
                required
                className="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono"
              />
            </div>"""

if old_login_inputs in content:
    content = content.replace(old_login_inputs, new_login_inputs)
    print("Replaced login inputs successfully")
else:
    print("Warning: old_login_inputs not found")

# 4. Update login footer credentials hint
old_login_hint = """          <div className="mt-6 p-3 rounded-xl bg-slate-800/40 border border-slate-800 text-xs text-slate-400 flex items-center justify-between">
            <div>
              <span className="font-bold text-slate-300">Demo Admin:</span> admin@sbl.test
            </div>
            <span className="text-slate-500 font-mono">password</span>
          </div>"""

new_login_hint = """          <div className="mt-6 p-3.5 rounded-2xl bg-slate-800/60 border border-slate-700/80 text-xs space-y-2">
            <div className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
              {lang === 'bn' ? 'দ্রুত লগিন টেস্ট অ্যাকাউন্টস:' : 'Quick Login Accounts:'}
            </div>
            <div className="flex items-center justify-between text-slate-300">
              <span className="font-bold text-orange-400">👑 Super Admin:</span>
              <button
                type="button"
                onClick={() => { setLoginEmail('01700000000'); setLoginPassword('password'); }}
                className="hover:underline font-mono text-[11px] bg-slate-800 px-2 py-0.5 rounded border border-slate-700"
              >
                01700000000 / password
              </button>
            </div>
            <div className="flex items-center justify-between text-slate-300">
              <span className="font-bold text-blue-400">👤 Member:</span>
              <button
                type="button"
                onClick={() => { setLoginEmail('01700000001'); setLoginPassword('password'); }}
                className="hover:underline font-mono text-[11px] bg-slate-800 px-2 py-0.5 rounded border border-slate-700"
              >
                01700000001 / password
              </button>
            </div>
            <div className="flex items-center justify-between text-slate-300">
              <span className="font-bold text-emerald-400">👀 Demo (Read-Only):</span>
              <button
                type="button"
                onClick={() => { setLoginEmail('01700000003'); setLoginPassword('password'); }}
                className="hover:underline font-mono text-[11px] bg-slate-800 px-2 py-0.5 rounded border border-slate-700"
              >
                01700000003 / password
              </button>
            </div>
          </div>"""

if old_login_hint in content:
    content = content.replace(old_login_hint, new_login_hint)
    print("Replaced login hint successfully")
else:
    print("Warning: old_login_hint not found")

# 5. Add Impersonation and Demo banners at the top of logged-in shell
old_shell_ribbon = """  // LOGGED IN APP SHELL
  return (
    <div className="min-h-screen bg-[#070b14] text-slate-100 flex flex-col antialiased">
      <div className="sbl-ribbon" />"""

new_shell_ribbon = """  // LOGGED IN APP SHELL
  return (
    <div className="min-h-screen bg-[#070b14] text-slate-100 flex flex-col antialiased">
      <div className="sbl-ribbon" />

      {/* Impersonation Return Banner */}
      {api.getBackupAdminToken() && (
        <div className="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 px-4 py-2 text-xs font-bold flex items-center justify-between sticky top-0 z-50 shadow-lg">
          <div className="flex items-center gap-2">
            <span className="text-base">⚠️</span>
            <span>
              {lang === 'bn'
                ? `আপনি বর্তমানে "${user?.name}" (মোবাইল: ${user?.phone || user?.username}) হিসেবে আছেন (Impersonated View)`
                : `Currently viewing account of "${user?.name}" (Mobile: ${user?.phone || user?.username})`}
            </span>
          </div>
          <button
            onClick={() => {
              const adminTok = api.getBackupAdminToken();
              if (adminTok) {
                api.setAuthToken(adminTok);
                api.clearBackupAdminToken();
                window.location.reload();
              }
            }}
            className="px-3 py-1 bg-slate-950 hover:bg-slate-900 text-amber-300 rounded-lg text-xs font-black shadow-sm transition-all"
          >
            {lang === 'bn' ? 'সুপার অ্যাডমিনে ফিরে যান ➔' : 'Back to Super Admin ➔'}
          </button>
        </div>
      )}

      {/* Demo Mode Read-Only Banner */}
      {user?.role === 'demo' && (
        <div className="bg-blue-600/20 border-b border-blue-500/30 text-blue-300 font-bold px-4 py-1.5 text-xs text-center">
          {lang === 'bn'
            ? '👀 ডেমো অ্যাকাউন্ট মোড: আপনি প্ল্যাটফর্মের সকল ফিচার দেখতে পারবেন, তবে ডাটা পরিবর্তন বা যোগ করার অনুমতি নেই (Read-Only Demo)'
            : '👀 Demo Mode: You are in read-only observation mode.'}
        </div>
      )}"""

if old_shell_ribbon in content:
    content = content.replace(old_shell_ribbon, new_shell_ribbon)
    print("Replaced shell ribbon with banners successfully")
else:
    print("Warning: old_shell_ribbon not found")

with open("src/client/App.tsx", "w", encoding="utf-8") as f:
    f.write(content)

print("Saved stage 3")

