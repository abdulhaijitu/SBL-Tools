import React, { useState, useEffect } from 'react';
import { api, setAuthToken, clearAuthToken, getAuthToken } from './lib/api';
import {
  Users,
  LayoutDashboard,
  Network,
  Wrench,
  Calculator,
  LogOut,
  Plus,
  Phone,
  MessageSquare,
  Search,
  Filter,
  CheckCircle2,
  Clock,
  AlertCircle,
  TrendingUp,
  ExternalLink,
  ShieldAlert,
} from 'lucide-react';

export function App() {
  const [token, setToken] = useState<string | null>(getAuthToken());
  const [user, setUser] = useState<any>(null);
  const [activeTab, setActiveTab] = useState<'dashboard' | 'leads' | 'tree' | 'toolkit' | 'calculator'>('dashboard');

  // Auth states
  const [loginEmail, setLoginEmail] = useState('admin@sbl.test');
  const [loginPassword, setLoginPassword] = useState('password');
  const [loginError, setLoginError] = useState('');
  const [isLoggingIn, setIsLoggingIn] = useState(false);

  // Data states
  const [dashboardData, setDashboardData] = useState<any>(null);
  const [leads, setLeads] = useState<any[]>([]);
  const [leadFilter, setLeadFilter] = useState({ stage: '', temperature: '', search: '' });
  const [showAddLeadModal, setShowAddLeadModal] = useState(false);
  const [newLead, setNewLead] = useState({
    name: '',
    mobile: '',
    whatsapp: '',
    email: '',
    location: '',
    professionOrBusiness: '',
    stage: 'new',
    temperature: 'warm',
    budgetRange: '50000',
    decisionTimeline: 'Within 15 Days',
    notes: '',
  });

  // Toolkit & Tree states
  const [links, setLinks] = useState<any[]>([]);
  const [contacts, setContacts] = useState<any[]>([]);
  const [abbreviations, setAbbreviations] = useState<any[]>([]);
  const [treeNodes, setTreeNodes] = useState<any[]>([]);

  // Calculator states
  const [calcAmount, setCalcAmount] = useState('50000');
  const [calcDays, setCalcDays] = useState('365');
  const [calcRate, setCalcRate] = useState('12');
  const [calcResult, setCalcResult] = useState<any>(null);

  // Check auth and fetch current user
  useEffect(() => {
    if (token) {
      api
        .getMe()
        .then((res) => setUser(res.user))
        .catch(() => {
          clearAuthToken();
          setToken(null);
        });
    }
  }, [token]);

  // Load active tab data
  useEffect(() => {
    if (!token) return;

    if (activeTab === 'dashboard') {
      api.getDashboardSummary().then(setDashboardData).catch(console.error);
    } else if (activeTab === 'leads') {
      api.getLeads(leadFilter).then(setLeads).catch(console.error);
    } else if (activeTab === 'tree') {
      api.getTreeNodes().then(setTreeNodes).catch(console.error);
    } else if (activeTab === 'toolkit') {
      Promise.all([api.getLinks(), api.getContacts(), api.getAbbreviations()])
        .then(([l, c, a]) => {
          setLinks(l);
          setContacts(c);
          setAbbreviations(a);
        })
        .catch(console.error);
    }
  }, [token, activeTab, leadFilter]);

  // Handle Login
  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoginError('');
    setIsLoggingIn(true);
    try {
      const res = await api.login({ email: loginEmail, password: loginPassword });
      setAuthToken(res.token);
      setToken(res.token);
      setUser(res.user);
    } catch (err: any) {
      setLoginError(err.message || 'Login failed');
    } finally {
      setIsLoggingIn(false);
    }
  };

  const handleLogout = () => {
    clearAuthToken();
    setToken(null);
    setUser(null);
  };

  // Handle Add Lead
  const handleAddLead = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.createLead(newLead);
      setShowAddLeadModal(false);
      setNewLead({
        name: '',
        mobile: '',
        whatsapp: '',
        email: '',
        location: '',
        professionOrBusiness: '',
        stage: 'new',
        temperature: 'warm',
        budgetRange: '50000',
        decisionTimeline: 'Within 15 Days',
        notes: '',
      });
      api.getLeads(leadFilter).then(setLeads);
    } catch (err: any) {
      alert(err.message || 'Failed to create lead');
    }
  };

  // Handle Calculator
  const handleCalculate = async () => {
    try {
      const res = await api.calculatePackage({
        amountBdt: Number(calcAmount),
        durationDays: Number(calcDays),
        ratePercent: Number(calcRate),
      });
      setCalcResult(res);
    } catch (err: any) {
      alert(err.message);
    }
  };

  // Auth Screen
  if (!token) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950 p-4">
        <div className="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
          <div className="text-center mb-8">
            <div className="inline-flex p-3 rounded-2xl bg-indigo-500/10 text-indigo-400 mb-3">
              <TrendingUp className="w-8 h-8" />
            </div>
            <h1 className="text-2xl font-bold text-white">SBL Growth Manager</h1>
            <p className="text-slate-400 text-sm mt-1">Cloudflare Workers + PostgreSQL + Drizzle ORM</p>
          </div>

          {loginError && (
            <div className="mb-4 p-3 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
              <ShieldAlert className="w-4 h-4 shrink-0" />
              <span>{loginError}</span>
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4">
            <div>
              <label className="block text-xs font-medium text-slate-300 mb-1">Email Address</label>
              <input
                type="email"
                value={loginEmail}
                onChange={(e) => setLoginEmail(e.target.value)}
                required
                className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-300 mb-1">Password</label>
              <input
                type="password"
                value={loginPassword}
                onChange={(e) => setLoginPassword(e.target.value)}
                required
                className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <button
              type="submit"
              disabled={isLoggingIn}
              className="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
            >
              {isLoggingIn ? 'Signing In...' : 'Sign In to Dashboard'}
            </button>
          </form>

          <div className="mt-6 p-3 rounded-lg bg-slate-800/50 border border-slate-700/50 text-xs text-slate-400">
            <span className="font-semibold text-slate-300">Demo Admin:</span> admin@sbl.test / password
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
      {/* Top Navbar */}
      <header className="border-b border-slate-800 bg-slate-900/60 backdrop-blur sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-xl bg-indigo-600 text-white">
              <TrendingUp className="w-5 h-5" />
            </div>
            <div>
              <span className="font-bold text-white text-base">SBL Growth Manager</span>
              <span className="hidden sm:inline-block ml-2 px-2 py-0.5 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                Edge + PostgreSQL
              </span>
            </div>
          </div>

          <div className="flex items-center gap-4">
            <div className="text-right hidden sm:block">
              <div className="text-sm font-medium text-white">{user?.name || 'Administrator'}</div>
              <div className="text-xs text-slate-400 capitalize">{user?.role || 'Admin'}</div>
            </div>
            <button
              onClick={handleLogout}
              className="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
              title="Logout"
            >
              <LogOut className="w-5 h-5" />
            </button>
          </div>
        </div>
      </header>

      {/* Main Container */}
      <div className="flex-1 max-w-7xl w-full mx-auto px-4 py-6 flex flex-col md:flex-row gap-6">
        {/* Sidebar Nav */}
        <nav className="w-full md:w-60 shrink-0 space-y-1">
          <button
            onClick={() => setActiveTab('dashboard')}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
              activeTab === 'dashboard'
                ? 'bg-indigo-600 text-white'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            }`}
          >
            <LayoutDashboard className="w-4 h-4" />
            Dashboard
          </button>
          <button
            onClick={() => setActiveTab('leads')}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
              activeTab === 'leads'
                ? 'bg-indigo-600 text-white'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            }`}
          >
            <Users className="w-4 h-4" />
            Leads & Pipeline
          </button>
          <button
            onClick={() => setActiveTab('tree')}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
              activeTab === 'tree'
                ? 'bg-indigo-600 text-white'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            }`}
          >
            <Network className="w-4 h-4" />
            Team Explorer (10-Slot)
          </button>
          <button
            onClick={() => setActiveTab('toolkit')}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
              activeTab === 'toolkit'
                ? 'bg-indigo-600 text-white'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            }`}
          >
            <Wrench className="w-4 h-4" />
            SBL Toolkit
          </button>
          <button
            onClick={() => setActiveTab('calculator')}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
              activeTab === 'calculator'
                ? 'bg-indigo-600 text-white'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            }`}
          >
            <Calculator className="w-4 h-4" />
            Package Calculator
          </button>
        </nav>

        {/* Tab Content Area */}
        <main className="flex-1 min-w-0">
          {/* TAB 1: DASHBOARD */}
          {activeTab === 'dashboard' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <h2 className="text-xl font-bold text-white">Daily Operations Dashboard</h2>
                <span className="text-xs text-slate-400">PostgreSQL Live Data</span>
              </div>

              {/* Priority Counters */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <span className="text-xs text-rose-400 font-semibold uppercase">Hot Leads</span>
                  <div className="text-2xl font-bold text-white mt-1">
                    {dashboardData?.temperatures?.find((t: any) => t.temperature === 'hot')?.count || 0}
                  </div>
                </div>
                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <span className="text-xs text-amber-400 font-semibold uppercase">Warm Leads</span>
                  <div className="text-2xl font-bold text-white mt-1">
                    {dashboardData?.temperatures?.find((t: any) => t.temperature === 'warm')?.count || 0}
                  </div>
                </div>
                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <span className="text-xs text-emerald-400 font-semibold uppercase">Follow-ups Today</span>
                  <div className="text-2xl font-bold text-white mt-1">
                    {dashboardData?.todayFollowUps?.length || 0}
                  </div>
                </div>
                <div className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                  <span className="text-xs text-indigo-400 font-semibold uppercase">Pending Tasks</span>
                  <div className="text-2xl font-bold text-white mt-1">
                    {dashboardData?.tasks?.length || 0}
                  </div>
                </div>
              </div>

              {/* Follow-up Alerts */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                  <div className="flex items-center gap-2 mb-4 text-emerald-400 font-semibold text-sm">
                    <Clock className="w-4 h-4" /> Today's Follow-ups Due
                  </div>
                  <div className="space-y-2">
                    {dashboardData?.todayFollowUps?.length === 0 && (
                      <p className="text-xs text-slate-500 py-4 text-center">No follow-ups due today.</p>
                    )}
                    {dashboardData?.todayFollowUps?.map((f: any) => (
                      <div key={f.id} className="p-3 rounded-lg bg-slate-800/60 flex items-center justify-between">
                        <div>
                          <div className="text-sm font-medium text-white">{f.name}</div>
                          <div className="text-xs text-slate-400">{f.mobile} • {f.nextActionType || 'Follow-up'}</div>
                        </div>
                        <span className="px-2 py-0.5 text-xs rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 capitalize">
                          {f.temperature}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                  <div className="flex items-center gap-2 mb-4 text-rose-400 font-semibold text-sm">
                    <AlertCircle className="w-4 h-4" /> Overdue Follow-ups
                  </div>
                  <div className="space-y-2">
                    {dashboardData?.overdueFollowUps?.length === 0 && (
                      <p className="text-xs text-slate-500 py-4 text-center">No overdue follow-ups.</p>
                    )}
                    {dashboardData?.overdueFollowUps?.map((f: any) => (
                      <div key={f.id} className="p-3 rounded-lg bg-slate-800/60 flex items-center justify-between">
                        <div>
                          <div className="text-sm font-medium text-white">{f.name}</div>
                          <div className="text-xs text-slate-400">{f.mobile} • {f.nextActionType || 'Pending'}</div>
                        </div>
                        <span className="px-2 py-0.5 text-xs rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 capitalize">
                          Overdue
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: LEADS */}
          {activeTab === 'leads' && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <h2 className="text-xl font-bold text-white">Leads Pipeline</h2>
                <button
                  onClick={() => setShowAddLeadModal(true)}
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium flex items-center gap-2 transition-colors"
                >
                  <Plus className="w-4 h-4" /> Add New Lead
                </button>
              </div>

              {/* Filters */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div className="relative">
                  <Search className="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Search name, phone..."
                    value={leadFilter.search}
                    onChange={(e) => setLeadFilter({ ...leadFilter, search: e.target.value })}
                    className="w-full pl-9 pr-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
                <select
                  value={leadFilter.stage}
                  onChange={(e) => setLeadFilter({ ...leadFilter, stage: e.target.value })}
                  className="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-sm text-white focus:outline-none"
                >
                  <option value="">All Stages</option>
                  <option value="new">New</option>
                  <option value="contacted">Contacted</option>
                  <option value="interested">Interested</option>
                  <option value="presentation">Presentation</option>
                  <option value="follow_up">Follow Up</option>
                  <option value="converted">Converted</option>
                </select>
                <select
                  value={leadFilter.temperature}
                  onChange={(e) => setLeadFilter({ ...leadFilter, temperature: e.target.value })}
                  className="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-sm text-white focus:outline-none"
                >
                  <option value="">All Temperatures</option>
                  <option value="hot">Hot</option>
                  <option value="warm">Warm</option>
                  <option value="cold">Cold</option>
                </select>
              </div>

              {/* Leads Table */}
              <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <table className="w-full text-left text-sm">
                  <thead className="bg-slate-800/50 text-slate-400 text-xs uppercase border-b border-slate-800">
                    <tr>
                      <th className="px-4 py-3">Lead Info</th>
                      <th className="px-4 py-3">Location & Business</th>
                      <th className="px-4 py-3">Stage</th>
                      <th className="px-4 py-3">Priority</th>
                      <th className="px-4 py-3">Budget</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800">
                    {leads.length === 0 ? (
                      <tr>
                        <td colSpan={5} className="px-4 py-8 text-center text-slate-500">
                          No leads found matching criteria.
                        </td>
                      </tr>
                    ) : (
                      leads.map((l) => (
                        <tr key={l.id} className="hover:bg-slate-800/30 transition-colors">
                          <td className="px-4 py-3">
                            <div className="font-semibold text-white">{l.name}</div>
                            <div className="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                              <Phone className="w-3 h-3" /> {l.mobile}
                            </div>
                          </td>
                          <td className="px-4 py-3">
                            <div className="text-slate-200">{l.professionOrBusiness || '—'}</div>
                            <div className="text-xs text-slate-400">{l.location || '—'}</div>
                          </td>
                          <td className="px-4 py-3">
                            <span className="px-2.5 py-1 text-xs rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 capitalize font-medium">
                              {l.stage}
                            </span>
                          </td>
                          <td className="px-4 py-3">
                            <span
                              className={`px-2 py-0.5 text-xs rounded font-medium capitalize ${
                                l.temperature === 'hot'
                                  ? 'bg-rose-500/10 text-rose-400'
                                  : l.temperature === 'warm'
                                  ? 'bg-amber-500/10 text-amber-400'
                                  : 'bg-slate-700 text-slate-300'
                              }`}
                            >
                              {l.temperature} ({l.score} pts)
                            </span>
                          </td>
                          <td className="px-4 py-3 font-mono text-slate-300">
                            {l.budgetRange ? `${Number(l.budgetRange).toLocaleString()} BDT` : '—'}
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* TAB 3: TREE (10-SLOT) */}
          {activeTab === 'tree' && (
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-xl font-bold text-white">Team Explorer (10-Slot Placement Network)</h2>
                  <p className="text-xs text-slate-400 mt-1">
                    Structured placement hierarchy stored directly in PostgreSQL
                  </p>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {treeNodes.map((node) => (
                  <div key={node.id} className="p-4 rounded-xl bg-slate-900 border border-slate-800">
                    <div className="flex items-center justify-between mb-2">
                      <span className="px-2 py-0.5 rounded text-xs bg-indigo-500/10 text-indigo-400 font-mono">
                        Slot #{node.placementPosition}
                      </span>
                      <span className="text-xs text-emerald-400 font-semibold">{node.rank || 'Associate'}</span>
                    </div>
                    <div className="font-bold text-white text-base">{node.memberName}</div>
                    <div className="text-xs text-slate-400 mt-1">ID: {node.memberId || 'N/A'} • {node.phone || 'No phone'}</div>
                    <div className="text-xs text-slate-500 mt-2">Package: {node.packageName || 'Basic'}</div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 4: TOOLKIT */}
          {activeTab === 'toolkit' && (
            <div className="space-y-6">
              <h2 className="text-xl font-bold text-white">SBL Operations Toolkit</h2>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Ecosystem Links */}
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                  <h3 className="font-semibold text-white mb-3 text-sm">Verified Ecosystem Links</h3>
                  <div className="space-y-2">
                    {links.map((link) => (
                      <a
                        key={link.id}
                        href={link.url}
                        target="_blank"
                        rel="noreferrer"
                        className="p-3 rounded-lg bg-slate-800/60 hover:bg-slate-800 flex items-center justify-between text-sm transition-colors"
                      >
                        <div>
                          <div className="font-medium text-white">{link.title}</div>
                          <div className="text-xs text-slate-400">{link.category || 'General'}</div>
                        </div>
                        <ExternalLink className="w-4 h-4 text-slate-400" />
                      </a>
                    ))}
                  </div>
                </div>

                {/* SBL Contacts */}
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                  <h3 className="font-semibold text-white mb-3 text-sm">Official Contacts Directory</h3>
                  <div className="space-y-2">
                    {contacts.map((contact) => (
                      <div key={contact.id} className="p-3 rounded-lg bg-slate-800/60 flex items-center justify-between text-sm">
                        <div>
                          <div className="font-medium text-white">{contact.name}</div>
                          <div className="text-xs text-slate-400">{contact.designation} • {contact.phone}</div>
                        </div>
                        {contact.whatsapp && (
                          <a
                            href={`https://wa.me/${contact.whatsapp}`}
                            target="_blank"
                            rel="noreferrer"
                            className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20"
                          >
                            <MessageSquare className="w-4 h-4" />
                          </a>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Abbreviations */}
              <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                <h3 className="font-semibold text-white mb-3 text-sm">Glossary & Terms</h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  {abbreviations.map((abbr) => (
                    <div key={abbr.id} className="p-3 rounded-lg bg-slate-800/40">
                      <div className="font-bold text-indigo-400">{abbr.abbreviation} - {abbr.term}</div>
                      <div className="text-xs text-slate-300 mt-1">{abbr.definition}</div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* TAB 5: PACKAGE CALCULATOR */}
          {activeTab === 'calculator' && (
            <div className="max-w-xl space-y-6">
              <h2 className="text-xl font-bold text-white">Investment & Package Calculator</h2>
              <div className="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">Package Amount (BDT)</label>
                  <input
                    type="number"
                    value={calcAmount}
                    onChange={(e) => setCalcAmount(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">Annual Return Rate (%)</label>
                  <input
                    type="number"
                    value={calcRate}
                    onChange={(e) => setCalcRate(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">Duration (Days)</label>
                  <input
                    type="number"
                    value={calcDays}
                    onChange={(e) => setCalcDays(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white"
                  />
                </div>

                <button
                  onClick={handleCalculate}
                  className="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg text-sm"
                >
                  Calculate Projected Returns
                </button>

                {calcResult && (
                  <div className="mt-4 p-4 rounded-xl bg-slate-800/80 border border-slate-700 space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span className="text-slate-400">Equivalent USD (1 USD = 120 BDT):</span>
                      <span className="font-bold text-white">${calcResult.amountUsd}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-slate-400">Projected Total Return:</span>
                      <span className="font-bold text-emerald-400">{Number(calcResult.projectedReturnBdt).toLocaleString()} BDT</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-slate-400">Net Profit Yield:</span>
                      <span className="font-bold text-indigo-400">{Number(calcResult.profitBdt).toLocaleString()} BDT</span>
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
        </main>
      </div>

      {/* Add Lead Modal */}
      {showAddLeadModal && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 className="text-lg font-bold text-white mb-4">Add New Lead</h3>
            <form onSubmit={handleAddLead} className="space-y-3">
              <div>
                <label className="block text-xs text-slate-400 mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  value={newLead.name}
                  onChange={(e) => setNewLead({ ...newLead, name: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs text-slate-400 mb-1">Mobile Phone *</label>
                  <input
                    type="text"
                    required
                    value={newLead.mobile}
                    onChange={(e) => setNewLead({ ...newLead, mobile: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                  />
                </div>
                <div>
                  <label className="block text-xs text-slate-400 mb-1">WhatsApp</label>
                  <input
                    type="text"
                    value={newLead.whatsapp}
                    onChange={(e) => setNewLead({ ...newLead, whatsapp: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                  />
                </div>
              </div>
              <div>
                <label className="block text-xs text-slate-400 mb-1">Location</label>
                <input
                  type="text"
                  value={newLead.location}
                  onChange={(e) => setNewLead({ ...newLead, location: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs text-slate-400 mb-1">Stage</label>
                  <select
                    value={newLead.stage}
                    onChange={(e) => setNewLead({ ...newLead, stage: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                  >
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="interested">Interested</option>
                    <option value="presentation">Presentation</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs text-slate-400 mb-1">Temperature</label>
                  <select
                    value={newLead.temperature}
                    onChange={(e) => setNewLead({ ...newLead, temperature: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                  >
                    <option value="hot">Hot</option>
                    <option value="warm">Warm</option>
                    <option value="cold">Cold</option>
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-xs text-slate-400 mb-1">Budget Range (BDT)</label>
                <input
                  type="number"
                  value={newLead.budgetRange}
                  onChange={(e) => setNewLead({ ...newLead, budgetRange: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                />
              </div>
              <div>
                <label className="block text-xs text-slate-400 mb-1">Notes</label>
                <textarea
                  rows={2}
                  value={newLead.notes}
                  onChange={(e) => setNewLead({ ...newLead, notes: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddLeadModal(false)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium"
                >
                  Save Lead
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

