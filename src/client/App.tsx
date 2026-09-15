import React, { useState, useEffect, useMemo } from 'react';
import {
  api,
  setAuthToken,
  clearAuthToken,
  getAuthToken,
  getBackupAdminToken,
  setBackupAdminToken,
  clearBackupAdminToken,
} from './lib/api';
import {
  Users,
  UserPlus,
  LayoutDashboard,
  Network,
  Package,
  Award,
  BookOpen,
  Calculator,
  Link2,
  FolderDown,
  PhoneCall,
  FileText,
  UserCheck,
  Shield,
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
  Copy,
  ChevronRight,
  Eye,
  Download,
  Share2,
  Menu,
  X,
  Sparkles,
  ArrowRight,
  HelpCircle,
  Edit3,
  Trash2,
  Calendar,
  DollarSign,
  MapPin,
  Briefcase,
  Mail,
  Check,
  Activity as ActivityIcon,
  Send,
} from 'lucide-react';

// Types
type Currency = 'BDT' | 'USD';
type Language = 'en' | 'bn';

export function App() {
  const [token, setToken] = useState<string | null>(getAuthToken());
  const [user, setUser] = useState<any>(null);
  const [currency, setCurrency] = useState<Currency>('BDT');
  const [lang, setLang] = useState<Language>('bn');
  const [sidebarOpen, setSidebarOpen] = useState(false);

  // Active Navigation
  const [activeTab, setActiveTab] = useState<
    | 'dashboard'
    | 'leads'
    | 'tree'
    | 'packages'
    | 'ranks'
    | 'counseling'
    | 'commission'
    | 'links'
    | 'resources'
    | 'contacts'
    | 'glossary'
    | 'users'
  >('dashboard');

  // Leads Sub-view: table or kanban
  const [leadsView, setLeadsView] = useState<'table' | 'kanban'>('table');
  // Tree Sub-view: slots or mindmap
  const [treeView, setTreeView] = useState<'slots' | 'mindmap'>('slots');

  // Auth states
  const [loginEmail, setLoginEmail] = useState('admin@sbl.test');
  const [loginPassword, setLoginPassword] = useState('password');
  const [loginError, setLoginError] = useState('');
  const [isLoggingIn, setIsLoggingIn] = useState(false);

  // Data states
  const [dashboardData, setDashboardData] = useState<any>(null);
  const [leads, setLeads] = useState<any[]>([]);
  const [leadSources, setLeadSources] = useState<any[]>([]);
  const [leadFilter, setLeadFilter] = useState({ stage: '', temperature: '', search: '' });

  // Leads CRUD Modals State
  const [showAddLeadModal, setShowAddLeadModal] = useState(false);
  const [showEditLeadModal, setShowEditLeadModal] = useState(false);
  const [showDetailLeadModal, setShowDetailLeadModal] = useState(false);
  const [deleteConfirmLead, setDeleteConfirmLead] = useState<any | null>(null);
  const [activeLeadDetail, setActiveLeadDetail] = useState<any | null>(null);

  // Form State for Add / Edit Lead
  const initialLeadForm = {
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
    leadSourceId: '',
    notes: '',
  };
  const [leadFormData, setLeadFormData] = useState<any>(initialLeadForm);
  const [editingLeadId, setEditingLeadId] = useState<number | null>(null);

  // Activity Log State (Inside Lead Details Modal)
  const [activityNote, setActivityNote] = useState('');
  const [activityType, setActivityType] = useState('note');
  const [isLoggingActivity, setIsLoggingActivity] = useState(false);

  // Toolkit & Tree states
  const [links, setLinks] = useState<any[]>([]);
  const [contacts, setContacts] = useState<any[]>([]);
  const [abbreviations, setAbbreviations] = useState<any[]>([]);
  const [treeNodes, setTreeNodes] = useState<any[]>([]);
  const [showAddNodeModal, setShowAddNodeModal] = useState(false);
  const [newNode, setNewNode] = useState({
    placementPosition: 1,
    memberName: '',
    phone: '',
    sponsorName: '',
    password: 'password123',
    tpin: '1234',
    packageName: 'Starter',
    amountBdt: 10000,
  });

  // Tree recursive drill-down navigation & project allocation
  const [treePath, setTreePath] = useState<Array<{ id: number | null; name: string }>>([
    { id: null, name: 'হোম ট্রি (Root)' },
  ]);
  const [showAddProjectModal, setShowAddProjectModal] = useState(false);
  const [selectedMemberForProject, setSelectedMemberForProject] = useState<any | null>(null);
  const [projectFormData, setProjectFormData] = useState({
    projectName: 'Starter',
    amountBdt: 10000,
    referenceNote: '',
  });

  // Super Admin Users Management
  const [usersList, setUsersList] = useState<any[]>([]);
  const [isLoadingUsers, setIsLoadingUsers] = useState(false);
  const [showAddUserModal, setShowAddUserModal] = useState(false);
  const [newUserFormData, setNewUserFormData] = useState({
    name: '',
    phone: '',
    password: '',
    role: 'member',
    designation: 'Associate Member',
  });

  // Package presentation brochure modal
  const [presentationOpen, setPresentationOpen] = useState(false);
  const [presentationIndex, setPresentationIndex] = useState(0);
  const [showQrModal, setShowQrModal] = useState(false);

  // Social Share & Referral Modal
  const [showShareModal, setShowShareModal] = useState(false);
  const [sharePackageData, setSharePackageData] = useState<any | null>(null);

  // Toast feedback
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3000);
  };

  // Currency Formatter Helper (Fixed baseline: 1 USD = 120 BDT)
  const formatMoney = (amountBdt: number) => {
    if (currency === 'USD') {
      const usd = Math.round(amountBdt / 120);
      return `$${usd.toLocaleString()}`;
    }
    return `৳${amountBdt.toLocaleString()}`;
  };

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
      loadLeads();
      api.getLeadSources().then(setLeadSources).catch(console.error);
    } else if (activeTab === 'tree') {
      const currentParentId = treePath[treePath.length - 1]?.id;
      api.getTreeNodes(currentParentId || undefined).then(setTreeNodes).catch(console.error);
    } else if (activeTab === 'users') {
      if (user?.role === 'super_admin') {
        loadUsers();
      }
    } else if (activeTab === 'links' || activeTab === 'contacts' || activeTab === 'glossary') {
      Promise.all([api.getLinks(), api.getContacts(), api.getAbbreviations()])
        .then(([l, c, a]) => {
          setLinks(l || []);
          setContacts(c || []);
          setAbbreviations(a || []);
        })
        .catch(console.error);
    }
  }, [token, activeTab, leadFilter]);

  const loadLeads = () => {
    api.getLeads(leadFilter).then(setLeads).catch(console.error);
  };

  // Load tree nodes for parent
  const loadTreeNodesFor = (parentId?: number | null) => {
    api.getTreeNodes(parentId || undefined).then(setTreeNodes).catch(console.error);
  };

  // Tree recursive drill-down navigation
  const handleDrillDownTree = (node: any) => {
    setTreePath((prev) => [...prev, { id: node.id, name: node.memberName }]);
    loadTreeNodesFor(node.id);
  };

  const handleNavigateTreeBreadcrumb = (index: number) => {
    const target = treePath[index];
    const newPath = treePath.slice(0, index + 1);
    setTreePath(newPath);
    loadTreeNodesFor(target?.id);
  };

  // Add Project to Member Node
  const handleOpenAddProject = (node: any) => {
    setSelectedMemberForProject(node);
    setProjectFormData({
      projectName: 'Starter',
      amountBdt: 10000,
      referenceNote: '',
    });
    setShowAddProjectModal(true);
  };

  const handleSaveMemberProject = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedMemberForProject) return;
    try {
      await api.addProjectToNode({
        nodeId: selectedMemberForProject.id,
        projectName: projectFormData.projectName,
        amountBdt: Number(projectFormData.amountBdt),
        referenceNote: projectFormData.referenceNote,
      });
      setShowAddProjectModal(false);
      showToast(lang === 'bn' ? 'প্রজেক্ট সফলভাবে যুক্ত হয়েছে!' : 'Project added successfully!');
      const currentParentId = treePath[treePath.length - 1]?.id;
      loadTreeNodesFor(currentParentId);
    } catch (err: any) {
      alert(err.message || 'Failed to add project');
    }
  };

  // Super Admin: Load & Manage Users
  const loadUsers = () => {
    setIsLoadingUsers(true);
    api.getUsers()
      .then((data) => setUsersList(data || []))
      .catch(console.error)
      .finally(() => setIsLoadingUsers(false));
  };

  const handleCreateUser = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.createUser(newUserFormData);
      setShowAddUserModal(false);
      setNewUserFormData({
        name: '',
        phone: '',
        password: '',
        role: 'member',
        designation: 'Associate Member',
      });
      showToast(lang === 'bn' ? 'নতুন ইউজার সফলভাবে তৈরি হয়েছে!' : 'User created successfully!');
      loadUsers();
    } catch (err: any) {
      alert(err.message || 'Failed to create user');
    }
  };

  const handleImpersonate = async (targetUser: any) => {
    if (!confirm(lang === 'bn' ? `আপনি কি "${targetUser.name}" এর অ্যাকাউন্টে প্রবেশ করতে চান?` : `Switch into ${targetUser.name}'s account?`)) {
      return;
    }
    try {
      // Backup current admin token if not already backed up
      if (!getBackupAdminToken() && token) {
        setBackupAdminToken(token);
      }
      const res = await api.impersonateUser(targetUser.id);
      setAuthToken(res.token);
      setToken(res.token);
      setUser(res.user);
      setActiveTab('dashboard');
      showToast(lang === 'bn' ? `"${res.user.name}" অ্যাকাউন্টে সফলভাবে প্রবেশ করা হয়েছে!` : `Switched to ${res.user.name}`);
    } catch (err: any) {
      alert(err.message || 'Impersonation failed');
    }
  };

  // Handle Login (Supports Mobile Number as Username)
  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoggingIn(true);
    setLoginError('');

    try {
      const res = await api.login({ identifier: loginEmail, email: loginEmail, password: loginPassword });
      setAuthToken(res.token);
      setToken(res.token);
      setUser(res.user);
    } catch (err: any) {
      setLoginError(err.message || 'লগিন ব্যর্থ হয়েছে। সঠিক মোবাইল নম্বর ও পাসওয়ার্ড দিন।');
    } finally {
      setIsLoggingIn(false);
    }
  };

  const handleLogout = () => {
    clearAuthToken();
    setToken(null);
    setUser(null);
  };

  // ==========================================
  // LEADS CRUD HANDLERS
  // ==========================================

  // Open Create Lead Modal
  const handleOpenAddLead = () => {
    setLeadFormData(initialLeadForm);
    setShowAddLeadModal(true);
  };

  // Submit Create Lead
  const handleCreateLead = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.createLead(leadFormData);
      setShowAddLeadModal(false);
      showToast(lang === 'bn' ? 'লিড সফলভাবে তৈরি হয়েছে!' : 'Lead created successfully!');
      loadLeads();
    } catch (err: any) {
      alert(err.message || 'Failed to create lead');
    }
  };

  // Open Edit Lead Modal
  const handleOpenEditLead = (lead: any) => {
    setEditingLeadId(lead.id);
    setLeadFormData({
      name: lead.name || '',
      mobile: lead.mobile || '',
      whatsapp: lead.whatsapp || '',
      email: lead.email || '',
      location: lead.location || '',
      professionOrBusiness: lead.professionOrBusiness || '',
      stage: lead.stage || 'new',
      temperature: lead.temperature || 'warm',
      budgetRange: lead.budgetRange || '50000',
      decisionTimeline: lead.decisionTimeline || 'Within 15 Days',
      leadSourceId: lead.leadSourceId || '',
      notes: lead.notes || '',
    });
    setShowEditLeadModal(true);
  };

  // Submit Edit Lead
  const handleUpdateLead = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingLeadId) return;
    try {
      await api.updateLead(editingLeadId, leadFormData);
      setShowEditLeadModal(false);
      setEditingLeadId(null);
      showToast(lang === 'bn' ? 'লিড তথ্য সফলভাবে আপডেট হয়েছে!' : 'Lead updated successfully!');
      loadLeads();
      if (activeLeadDetail && activeLeadDetail.id === editingLeadId) {
        openLeadDetails(editingLeadId);
      }
    } catch (err: any) {
      alert(err.message || 'Failed to update lead');
    }
  };

  // Quick Stage Update (e.g. from Kanban or Table)
  const handleQuickStageChange = async (leadId: number, newStage: string) => {
    try {
      await api.updateLead(leadId, { stage: newStage });
      showToast(lang === 'bn' ? `স্টেজ পরিবর্তিত হয়েছে: ${newStage}` : `Stage updated to ${newStage}`);
      loadLeads();
      if (activeLeadDetail && activeLeadDetail.id === leadId) {
        setActiveLeadDetail({ ...activeLeadDetail, stage: newStage });
      }
    } catch (err: any) {
      alert(err.message || 'Failed to update stage');
    }
  };

  // Open Lead Details Modal
  const openLeadDetails = async (leadId: number) => {
    try {
      const details = await api.getLead(leadId);
      setActiveLeadDetail(details);
      setShowDetailLeadModal(true);
    } catch (err: any) {
      alert(err.message || 'Failed to fetch lead details');
    }
  };

  // Submit New Activity / Note to Lead
  const handleAddActivity = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeLeadDetail || !activityNote.trim()) return;
    setIsLoggingActivity(true);
    try {
      await api.addActivity(activeLeadDetail.id, {
        type: activityType,
        details: activityNote,
      });
      setActivityNote('');
      showToast(lang === 'bn' ? 'কার্যক্রম নোট সংরক্ষণ করা হয়েছে!' : 'Activity note saved!');
      openLeadDetails(activeLeadDetail.id);
    } catch (err: any) {
      alert(err.message || 'Failed to save activity note');
    } finally {
      setIsLoggingActivity(false);
    }
  };

  // Confirm Delete Lead
  const handleDeleteLead = async () => {
    if (!deleteConfirmLead) return;
    try {
      await api.deleteLead(deleteConfirmLead.id);
      setDeleteConfirmLead(null);
      setShowDetailLeadModal(false);
      showToast(lang === 'bn' ? 'লিড সফলভাবে মুছে ফেলা হয়েছে!' : 'Lead deleted successfully!');
      loadLeads();
    } catch (err: any) {
      alert(err.message || 'Failed to delete lead');
    }
  };

  // ==========================================
  // TREE NODE HANDLER
  // ==========================================
  const handleCreateNode = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const currentParentId = treePath[treePath.length - 1]?.id || undefined;
      await api.addTreeNode({
        ...newNode,
        parentId: currentParentId,
        userId: user?.id || 1,
      });
      setShowAddNodeModal(false);
      showToast(lang === 'bn' ? 'টিম মেম্বার যুক্ত হয়েছে!' : 'Team member placed successfully!');
      loadTreeNodesFor(currentParentId);
    } catch (err: any) {
      alert(err.message || 'Failed to place member');
    }
  };

  // Official SBL Packages (Matching sbl-tools.onrender.com)
  const officialPackages = useMemo(
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

  // Ranks List
  const ranksList = [
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
  ];

    // Objection scripts for Counseling Guide
  const objections = [
    {
      qEn: '"I do not have enough money to start right now."',
      qBn: '“আমার কাছে এখন শুরু করার মতো পর্যাপ্ত টাকা নেই।”',
      answerBn:
        '“ভাই/আপু, আমি আপনার অবস্থানটা পুরোপুরি বুঝতে পারছি। সত্যি বলতে, টাকা নেই বলেই তো আমরা অতিরিক্ত আয়ের সুযোগ খুঁজছি। আপনি মাত্র ৳১৫,০০০ এর বেসিক স্টার্টার দিয়েও নিজের বিজনেস শুরু করতে পারেন। প্রথম মাসে মাত্র ২ জন পার্টনার যুক্ত করলেই আপনার মূলধনের একটি বড় অংশ উঠে আসবে।”',
      answerEn:
        '"I completely understand. In fact, that is the exact reason we explore extra income channels. You can begin with our ৳15,000 Basic Starter. Sponsoring your first two partners helps recover your initial step rapidly."',
    },
    {
      qEn: '"I don\'t have enough free time for this."',
      qBn: '“আমার তো চাকরির পর কোনো বাড়তি সময় থাকে না।”',
      answerBn:
        '“দারুণ প্রশ্ন! এসবিএল টুলস এমনভাবে ডিজাইন করা হয়েছে যাতে দিনে মাত্র ১-২ ঘণ্টা স্মার্টফোন দিয়ে লিড ম্যানেজ করা যায়। আমাদের স্বয়ংক্রিয় ব্রোশিউর ও প্রেজেন্টেশন স্লাইড প্রসপেক্টদের পাঠিয়ে দিলে সিস্টেমই আপনার কাজ অর্ধেক করে দেবে।”',
      answerEn:
        '"Great question! SBL Tools is designed to work in just 1-2 hours daily on your smartphone. Simply share our digital brochure and presentation deck to let the tools handle the explanation for you."',
    },
    {
      qEn: '"Is this a traditional MLM or a real business?"',
      qBn: '“এটা কি সাধারণ কোনো এমএলএম নাকি সত্যিকারের ব্যবসা?”',
      answerBn:
        '“এসবিএল স্মার্ট বিজনেস লজিস্টিকস এবং ড্রপশিপিং ভিত্তিক একটি নির্ভরযোগ্য ট্রেডিং ইকোসিস্টেম। এখানে প্রতি টাকার বিপরীতে রিয়েল পয়েন্ট ভলিউম (BV) এবং পার্টনারশিপ শেয়ারিং রয়েছে যা সরকারের ডিজিটাল কমার্স নীতিমালার সম্পূর্ণ অনুসারী।”',
      answerEn:
        '"SBL is built on smart logistics and verified dropshipping commerce. Every transaction is tied to genuine product/service point volumes (BV) complying with transparent commerce standards."',
    },
    {
      qEn: '"I need to discuss with my family first."',
      qBn: '“আমাকে পরিবারের সাথে আলোচনা করতে হবে।”',
      answerBn:
        '“অবশ্যই, পরিবারের মতামত অত্যন্ত গুরুত্বপূর্ণ। তবে আপনি কি পরিবারের সামনে একাই বোঝাবেন, নাকি আমাদের অফিসিয়াল পিডিএফ লিফলেট এবং আজকের প্রেজেন্টেশন ভিডিও তাদের একসাথে দেখাতে চান? তাহলে তারা সঠিক তথ্যটি দেখতে পাবে।”',
      answerEn:
        '"Absolutely, family alignment is crucial. Rather than explaining from memory, let us share our official PDF leaflet and short presentation video with them so they see verified details directly."',
    },
  ];

  // Commission Calculator State
  const [calcLeftBv, setCalcLeftBv] = useState<number>(2500);
  const [calcRightBv, setCalcRightBv] = useState<number>(3000);
  const [calcDirectReferrals, setCalcDirectReferrals] = useState<number>(2);

  const commissionResults = useMemo(() => {
    const matchedBv = Math.min(calcLeftBv, calcRightBv);
    const pairingBonusBdt = matchedBv * 12; // 12% pairing payout
    const directReferralBonusBdt = calcDirectReferrals * 3500; // ৳3,500 per sponsor
    const totalEarningsBdt = pairingBonusBdt + directReferralBonusBdt;
    const carryForwardBv = Math.abs(calcLeftBv - calcRightBv);

    return {
      matchedBv,
      pairingBonusBdt,
      directReferralBonusBdt,
      totalEarningsBdt,
      carryForwardBv,
      strongerLeg: calcLeftBv > calcRightBv ? 'Left' : 'Right',
    };
  }, [calcLeftBv, calcRightBv, calcDirectReferrals]);

  // Glossary filter
  const [glossarySearch, setGlossarySearch] = useState('');
  const filteredGlossary = useMemo(() => {
    const defaultGlossary = [
      { abbr: 'BV', term: 'Business Volume', desc: 'কমিশন গণনার একক পয়েন্ট (১ BV = কমিশনযোগ্য পয়েন্ট)' },
      { abbr: 'PV', term: 'Point Volume', desc: 'প্যাকেজ এবং মার্চেন্ডাইজ ক্রয়ের বিপরীতে অর্জিত পয়েন্ট' },
      { abbr: 'TPIN', term: 'Transaction PIN', desc: 'ব্যালেন্স উইথড্রয়াল ও মেম্বার ট্রান্সফারের ৪ সংখ্যার পিন কোড' },
      { abbr: 'BDT', term: 'Bangladeshi Taka', desc: 'বাংলাদেশের জাতীয় মুদ্রা (স্ট্যান্ডার্ড রেট: ১ USD = ১২০ BDT)' },
      { abbr: 'KYC', term: 'Know Your Customer', desc: 'জাতীয় পরিচয়পত্র বা পাসপোর্ট ভেরিফিকেশন প্রক্রিয়া' },
      { abbr: 'Spillover', term: 'Leg Placement Spill', desc: 'আপলাইনের টিম সম্প্রসারণের মাধ্যমে নিচের লিঙ্কে পাওয়া সদস্য প্লেসমেন্ট' },
      { abbr: 'Daily Cap', term: 'Daily Earnings Limit', desc: 'প্যাকেজ অনুযায়ী প্রতিদিন সর্বোচ্চ বাইনারি কমিশন উত্তোলনের সীমা' },
    ];

    const source = abbreviations.length > 0 ? abbreviations : defaultGlossary;
    if (!glossarySearch.trim()) return source;
    return source.filter(
      (item) =>
        (item.abbreviation || item.abbr || '').toLowerCase().includes(glossarySearch.toLowerCase()) ||
        (item.term || '').toLowerCase().includes(glossarySearch.toLowerCase()) ||
        (item.definition || item.desc || '').toLowerCase().includes(glossarySearch.toLowerCase()),
    );
  }, [abbreviations, glossarySearch]);

  // IF NOT LOGGED IN
  if (!token) {
    return (
      <div className="min-h-screen bg-[#070b14] flex flex-col items-center justify-center p-4">
        <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-6 sm:p-8 relative overflow-hidden">
          <div className="sbl-ribbon absolute top-0 left-0 right-0" />

          <div className="text-center mb-6 pt-2">
            <img
              src="/images/sbl-logo.webp"
              alt="SBL Marketing"
              className="h-12 mx-auto mb-3 object-contain"
              onError={(e: any) => {
                e.target.src = '/images/sbl-logo.png';
              }}
            />
            <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">SBL Growth Manager</h1>
            <p className="text-slate-400 text-xs sm:text-sm mt-1">
              Cloudflare Edge Portal • D1 + Drizzle ORM
            </p>
          </div>

          {loginError && (
            <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs sm:text-sm flex items-center gap-2">
              <ShieldAlert className="w-4 h-4 shrink-0" />
              <span>{loginError}</span>
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4">
            <div>
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
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-300 mb-1">
                {lang === 'bn' ? 'পাসওয়ার্ড' : 'Password'}
              </label>
              <input
                type="password"
                value={loginPassword}
                onChange={(e) => setLoginPassword(e.target.value)}
                required
                className="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
              />
            </div>

            <button
              type="submit"
              disabled={isLoggingIn}
              className="w-full py-3 px-4 bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm rounded-xl transition-all shadow-lg shadow-orange-600/30 flex items-center justify-center gap-2 disabled:opacity-50"
            >
              {isLoggingIn
                ? lang === 'bn'
                  ? 'প্রবেশ করা হচ্ছে...'
                  : 'Signing In...'
                : lang === 'bn'
                  ? 'ড্যাশবোর্ডে প্রবেশ করুন'
                  : 'Sign In to Dashboard'}
            </button>
          </form>

          <div className="mt-6 p-3.5 rounded-2xl bg-slate-800/60 border border-slate-700/80 text-xs space-y-2">
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
          </div>
        </div>
      </div>
    );
  }

  // LOGGED IN APP SHELL
  return (
    <div className="min-h-screen bg-[#070b14] text-slate-100 flex flex-col antialiased">
      <div className="sbl-ribbon" />

      {/* Impersonation Return Banner */}
      {getBackupAdminToken() && (
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
              const adminTok = getBackupAdminToken();
              if (adminTok) {
                setAuthToken(adminTok);
                clearBackupAdminToken();
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
      )}

      {/* Top Navbar */}
      <header className="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
              aria-label="Toggle navigation"
            >
              {sidebarOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>

            <a href="#" onClick={() => setActiveTab('dashboard')} className="flex items-center gap-2.5">
              <img
                src="/images/sbl-logo.webp"
                alt="SBL Marketing"
                className="h-9 w-auto object-contain"
                onError={(e: any) => {
                  e.target.src = '/images/sbl-logo.png';
                }}
              />
              <span className="hidden sm:inline-block font-extrabold text-white text-base tracking-tight">
                SBL Tools
              </span>
            </a>
          </div>

          {/* Controls */}
          <div className="flex items-center gap-2.5 sm:gap-3">
            <select
              value={currency}
              onChange={(e) => setCurrency(e.target.value as Currency)}
              className="bg-slate-800 text-xs font-bold text-slate-200 border border-slate-700 rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-orange-500"
              title="Currency Converter (1 USD = 120 BDT)"
            >
              <option value="BDT">BDT (৳120/$)</option>
              <option value="USD">USD ($)</option>
            </select>

            <button
              onClick={() => setLang(lang === 'en' ? 'bn' : 'en')}
              className="flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold border border-slate-700 bg-slate-800 hover:bg-slate-750 text-slate-300 transition-colors"
              title="Toggle Language"
            >
              <span className={lang === 'en' ? 'text-orange-500 font-extrabold' : 'text-slate-400'}>EN</span>
              <span className="text-slate-600">/</span>
              <span className={lang === 'bn' ? 'text-orange-500 font-extrabold' : 'text-slate-400'}>বাং</span>
            </button>

            <button
              onClick={() => {
                setSharePackageData(null);
                setShowShareModal(true);
              }}
              className="p-1.5 sm:px-2.5 sm:py-1.5 rounded-xl text-xs font-bold border border-slate-700 bg-slate-800 hover:bg-slate-750 text-slate-300 transition-colors flex items-center gap-1.5"
              title={lang === 'bn' ? 'প্ল্যাটফর্ম শেয়ার করুন' : 'Share Platform'}
            >
              <Share2 className="w-4 h-4 text-orange-400" />
              <span className="hidden sm:inline">{lang === 'bn' ? 'শেয়ার' : 'Share'}</span>
            </button>

            <button
              onClick={handleOpenAddLead}
              className="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-orange-600/30"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>{lang === 'bn' ? 'নতুন লিড' : 'New Lead'}</span>
            </button>

            <div className="flex items-center gap-2 pl-1 border-l border-slate-800">
              <div
                className="w-8 h-8 rounded-xl bg-orange-600 text-white font-extrabold flex items-center justify-center text-xs shadow-xs"
                title={user?.email || 'admin@sbl.test'}
              >
                {(user?.name || 'A').charAt(0).toUpperCase()}
              </div>
              <button
                onClick={handleLogout}
                className="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
                title={lang === 'bn' ? 'লগআউট' : 'Logout'}
              >
                <LogOut className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Container with Responsive Mobile Padding */}
      <div className="flex-1 max-w-7xl w-full mx-auto px-4 pt-6 pb-24 lg:pb-8 flex flex-col lg:flex-row gap-6">
        {/* SIDEBAR NAVIGATION */}
        <aside
          className={`lg:w-64 shrink-0 space-y-5 ${
            sidebarOpen ? 'block' : 'hidden lg:block'
          }`}
        >
          {/* Main Group */}
          <div>
            <div className="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">
              {lang === 'bn' ? 'প্রধান মেনু' : 'Main Menu'}
            </div>
            <div className="space-y-1">
              <button
                onClick={() => {
                  setActiveTab('dashboard');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'dashboard'
                    ? 'bg-orange-600 text-white font-bold shadow-md shadow-orange-600/20'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <LayoutDashboard className="w-4 h-4" />
                <span>{lang === 'bn' ? 'ড্যাশবোর্ড' : 'Dashboard'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('leads');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'leads'
                    ? 'bg-orange-600 text-white font-bold shadow-md shadow-orange-600/20'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <Users className="w-4 h-4" />
                <span>{lang === 'bn' ? 'লিডস ও পাইপলাইন' : 'Leads & Pipeline'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('tree');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'tree'
                    ? 'bg-orange-600 text-white font-bold shadow-md shadow-orange-600/20'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <Network className="w-4 h-4" />
                <span>{lang === 'bn' ? 'টিম ট্রি (১০-স্লট)' : 'Team Tree (10-Slot)'}</span>
              </button>
            </div>
          </div>

          {/* SBL Marketing Tools Group */}
          <div>
            <div className="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-orange-400">
              {lang === 'bn' ? 'এসবিএল মার্কেটিং টুলস' : 'SBL Marketing Tools'}
            </div>
            <div className="space-y-1">
              <button
                onClick={() => {
                  setActiveTab('packages');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'packages'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <Package className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'প্যাকেজসমূহ' : 'Packages'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('ranks');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'ranks'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <Award className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'র‍্যাঙ্ক সিস্টেম' : 'Ranks System'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('counseling');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'counseling'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <BookOpen className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'কাউন্সেলিং গাইড' : 'Counseling Guide'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('commission');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'commission'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <Calculator className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'কমিশন ক্যালকুলেটর' : 'Commission'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('links');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'links'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <Link2 className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'লিংকস হাব' : 'Links Hub'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('resources');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'resources'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <FolderDown className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'রিসোর্সেস' : 'Resources'}</span>
                <span className="ml-auto text-[9px] bg-orange-500/30 text-orange-300 px-1.5 py-0.5 rounded font-bold">
                  New
                </span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('contacts');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'contacts'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <PhoneCall className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'এসবিএল কন্টাক্ট' : 'SBL Contact'}</span>
              </button>

              <button
                onClick={() => {
                  setActiveTab('glossary');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'glossary'
                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <FileText className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'বিজনেস গ্লসারি' : 'Glossary'}</span>
              </button>
            </div>
          </div>

          {/* Administration Group (Super Admin Only) */}
          {user?.role === 'super_admin' && (
            <div>
              <div className="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                {lang === 'bn' ? 'প্ল্যাটফর্ম অ্যাডমিন' : 'SaaS Administration'}
              </div>
              <div className="space-y-1">
                <button
                  onClick={() => {
                    setActiveTab('users');
                    setSidebarOpen(false);
                  }}
                  className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors ${
                    activeTab === 'users'
                      ? 'bg-slate-800 text-white font-bold'
                      : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                  }`}
                >
                  <UserCheck className="w-4 h-4" />
                  <span>{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users & Accounts'}</span>
                </button>
              </div>
            </div>
          )}
        </aside>

        {/* MAIN WORKSPACE */}
        <main className="flex-1 min-w-0">
          {/* TAB 1: DASHBOARD */}
          {activeTab === 'dashboard' && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
                <div>
                  <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {lang === 'bn' ? 'দৈনিক কার্যক্রম ড্যাশবোর্ড' : 'Operations Dashboard'}
                  </h1>
                  <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {lang === 'bn'
                      ? 'রিয়েলটাইম পাইপলাইন, লিড কনভার্সন এবং দলের অগ্রগতি'
                      : 'Real-time sales pipeline, conversion funnels & network growth'}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={handleOpenAddLead}
                    className="px-3.5 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                  >
                    <Plus className="w-4 h-4" />
                    <span>{lang === 'bn' ? 'লিড যোগ করুন' : 'Add Lead'}</span>
                  </button>
                </div>
              </div>

              {/* Metric Cards Grid */}
              <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-orange-500/40 transition-colors">
                  <div className="flex items-center justify-between text-slate-400 mb-2">
                    <span className="text-xs font-semibold uppercase tracking-wider">
                      {lang === 'bn' ? 'মোট লিডস' : 'Total Leads'}
                    </span>
                    <Users className="w-4 h-4 text-orange-400" />
                  </div>
                  <div className="text-2xl sm:text-3xl font-black text-white">
                    {leads.length}
                  </div>
                  <div className="text-[11px] text-emerald-400 mt-1 flex items-center gap-1">
                    <TrendingUp className="w-3 h-3" />
                    <span>+12% {lang === 'bn' ? 'এই সপ্তাহে' : 'this week'}</span>
                  </div>
                </div>

                <div className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-orange-500/40 transition-colors">
                  <div className="flex items-center justify-between text-slate-400 mb-2">
                    <span className="text-xs font-semibold uppercase tracking-wider">
                      {lang === 'bn' ? 'হট প্রসপেক্টস' : 'Hot Prospects'}
                    </span>
                    <Sparkles className="w-4 h-4 text-rose-400" />
                  </div>
                  <div className="text-2xl sm:text-3xl font-black text-rose-400">
                    {leads.filter((l) => l.temperature === 'hot').length}
                  </div>
                  <div className="text-[11px] text-slate-400 mt-1">
                    {lang === 'bn' ? 'তাৎক্ষণিক ফলোআপ প্রয়োজন' : 'Immediate follow-up required'}
                  </div>
                </div>

                <div className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-orange-500/40 transition-colors">
                  <div className="flex items-center justify-between text-slate-400 mb-2">
                    <span className="text-xs font-semibold uppercase tracking-wider">
                      {lang === 'bn' ? 'কনভার্সন রেট' : 'Conversion Rate'}
                    </span>
                    <CheckCircle2 className="w-4 h-4 text-emerald-400" />
                  </div>
                  <div className="text-2xl sm:text-3xl font-black text-emerald-400">
                    {leads.length > 0
                      ? `${Math.round((leads.filter((l) => l.stage === 'won').length / leads.length) * 100)}%`
                      : '0%'}
                  </div>
                  <div className="text-[11px] text-slate-400 mt-1">
                    {lang === 'bn' ? 'ক্লোজিং সাকসেস রেশিও' : 'Closing success ratio'}
                  </div>
                </div>

                <div className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-orange-500/40 transition-colors">
                  <div className="flex items-center justify-between text-slate-400 mb-2">
                    <span className="text-xs font-semibold uppercase tracking-wider">
                      {lang === 'bn' ? 'টিম সাইজ' : 'Team Size'}
                    </span>
                    <Network className="w-4 h-4 text-blue-400" />
                  </div>
                  <div className="text-2xl sm:text-3xl font-black text-blue-400">
                    {treeNodes.length}
                  </div>
                  <div className="text-[11px] text-slate-400 mt-1">
                    {lang === 'bn' ? '১০-স্লট সক্রিয় সহযোগী' : '10-Slot active members'}
                  </div>
                </div>
              </div>

              {/* Funnel Stages Section */}
              <div className="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800">
                <h3 className="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                  <TrendingUp className="w-4 h-4 text-orange-400" />
                  <span>{lang === 'bn' ? 'লিড ফানেল পর্যায়' : 'Sales Pipeline Funnel'}</span>
                </h3>

                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-center text-xs">
                  {[
                    { key: 'new', label: 'New Lead', labelBn: 'নতুন লিড', color: 'bg-blue-500/20 text-blue-300' },
                    { key: 'contacted', label: 'Contacted', labelBn: 'যোগাযোগ', color: 'bg-amber-500/20 text-amber-300' },
                    { key: 'follow_up', label: 'Follow Up', labelBn: 'ফলোআপ', color: 'bg-purple-500/20 text-purple-300' },
                    { key: 'presentation', label: 'Presentation', labelBn: 'প্রেজেন্টেশন', color: 'bg-indigo-500/20 text-indigo-300' },
                    { key: 'negotiation', label: 'Negotiation', labelBn: 'আলোচনা', color: 'bg-rose-500/20 text-rose-300' },
                    { key: 'won', label: 'Converted', labelBn: 'সফল ক্লোজিং', color: 'bg-emerald-500/20 text-emerald-300' },
                  ].map((stage) => {
                    const count = leads.filter((l) => l.stage === stage.key).length;
                    return (
                      <div
                        key={stage.key}
                        onClick={() => {
                          setLeadFilter({ ...leadFilter, stage: stage.key });
                          setActiveTab('leads');
                        }}
                        className={`p-3 rounded-xl border border-slate-800 ${stage.color} cursor-pointer hover:scale-102 transition-transform`}
                      >
                        <div className="text-lg font-black">{count}</div>
                        <div className="text-[11px] font-semibold mt-0.5">
                          {lang === 'bn' ? stage.labelBn : stage.label}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Priority Prospects & Action Board */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {/* Hot Prospects */}
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                  <div className="flex items-center justify-between mb-3">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2">
                      <span className="text-rose-400">🔥</span>
                      <span>{lang === 'bn' ? 'জরুরি প্রসপেক্টস (হট)' : 'Priority Hot Prospects'}</span>
                    </h3>
                    <button
                      onClick={() => {
                        setLeadFilter({ stage: '', temperature: 'hot', search: '' });
                        setActiveTab('leads');
                      }}
                      className="text-xs text-orange-400 hover:underline"
                    >
                      {lang === 'bn' ? 'সবগুলো দেখুন →' : 'View all →'}
                    </button>
                  </div>

                  <div className="space-y-2.5">
                    {leads.filter((l) => l.temperature === 'hot').slice(0, 4).length > 0 ? (
                      leads
                        .filter((l) => l.temperature === 'hot')
                        .slice(0, 4)
                        .map((lead: any) => (
                          <div
                            key={lead.id}
                            className="p-3 rounded-xl bg-slate-800/50 border border-slate-750 flex items-center justify-between gap-2"
                          >
                            <div
                              onClick={() => openLeadDetails(lead.id)}
                              className="cursor-pointer"
                            >
                              <div className="text-xs font-bold text-white hover:text-orange-400 transition-colors">
                                {lead.name}
                              </div>
                              <div className="text-[11px] text-slate-400">
                                {lead.mobile} • {formatMoney(Number(lead.budgetRange || 50000))}
                              </div>
                            </div>
                            <div className="flex items-center gap-1.5">
                              <a
                                href={`https://wa.me/${(lead.whatsapp || lead.mobile || '').replace(/\D/g, '')}`}
                                target="_blank"
                                rel="noreferrer"
                                className="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 transition-colors"
                                title="WhatsApp Chat"
                              >
                                <MessageSquare className="w-3.5 h-3.5" />
                              </a>
                              <a
                                href={`tel:${lead.mobile}`}
                                className="p-1.5 rounded-lg bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 transition-colors"
                                title="Direct Call"
                              >
                                <Phone className="w-3.5 h-3.5" />
                              </a>
                              <button
                                onClick={() => openLeadDetails(lead.id)}
                                className="p-1.5 rounded-lg bg-slate-700 text-slate-200 hover:bg-slate-600"
                                title="View Details"
                              >
                                <Eye className="w-3.5 h-3.5" />
                              </button>
                            </div>
                          </div>
                        ))
                    ) : (
                      <div className="text-center py-6 text-slate-500 text-xs">
                        {lang === 'bn' ? 'কোনো হট প্রসপেক্ট নেই।' : 'No hot prospects currently.'}
                      </div>
                    )}
                  </div>
                </div>

                {/* Team Placement Shortcut */}
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                  <div className="flex items-center justify-between mb-3">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2">
                      <Network className="w-4 h-4 text-orange-400" />
                      <span>{lang === 'bn' ? '১০-স্লট টিম স্ট্যাটাস' : '10-Slot Team Snapshot'}</span>
                    </h3>
                    <button
                      onClick={() => setActiveTab('tree')}
                      className="text-xs text-orange-400 hover:underline"
                    >
                      {lang === 'bn' ? 'টিম ট্রি খুলুন →' : 'Open Tree →'}
                    </button>
                  </div>

                  <div className="p-4 rounded-xl bg-slate-800/30 border border-slate-800 text-center space-y-3">
                    <p className="text-xs text-slate-300 leading-relaxed">
                      {lang === 'bn'
                        ? 'আপনার লেফট ও রাইট লেগে নতুন মেম্বার প্লেস করে দ্রুত বাইনারি ম্যাচিং বোনাস আনলক করুন।'
                        : 'Place partners into your 10-slot binary visualizer to maximize your pair matching commissions.'}
                    </p>
                    <div className="flex justify-center gap-3">
                      <button
                        onClick={() => {
                          setActiveTab('tree');
                          setShowAddNodeModal(true);
                        }}
                        className="px-3.5 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-orange-600/30"
                      >
                        {lang === 'bn' ? '+ মেম্বার প্লেসমেন্ট' : '+ Place Member'}
                      </button>
                      <button
                        onClick={() => setActiveTab('commission')}
                        className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold transition-all"
                      >
                        {lang === 'bn' ? 'কমিশন হিসাব করুন' : 'Calc Commission'}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: LEADS & PIPELINE (FULL CRUD SYSTEM) */}
          {activeTab === 'leads' && (
            <div className="space-y-5">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
                <div>
                  <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {lang === 'bn' ? 'লিডস ও সেলস পাইপলাইন' : 'Leads & Sales Pipeline'}
                  </h1>
                  <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {lang === 'bn'
                      ? 'প্রসপেক্ট ট্র্যাকিং, স্টেজ অগ্রগতি, বিস্তারিত নোট ও সম্পূর্ণ CRUD ম্যানেজমেন্ট'
                      : 'Track prospects, advance stages, log follow-ups and complete lead CRUD lifecycle'}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <div className="bg-slate-800 p-0.5 rounded-xl border border-slate-700 flex items-center">
                    <button
                      onClick={() => setLeadsView('table')}
                      className={`px-3 py-1 rounded-lg text-xs font-bold transition-colors ${
                        leadsView === 'table' ? 'bg-orange-600 text-white' : 'text-slate-400'
                      }`}
                    >
                      {lang === 'bn' ? 'তালিকা' : 'Table'}
                    </button>
                    <button
                      onClick={() => setLeadsView('kanban')}
                      className={`px-3 py-1 rounded-lg text-xs font-bold transition-colors ${
                        leadsView === 'kanban' ? 'bg-orange-600 text-white' : 'text-slate-400'
                      }`}
                    >
                      {lang === 'bn' ? 'কানবান' : 'Kanban'}
                    </button>
                  </div>

                  <button
                    onClick={handleOpenAddLead}
                    className="px-3.5 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-orange-600/30"
                  >
                    <Plus className="w-3.5 h-3.5" />
                    <span>{lang === 'bn' ? 'নতুন লিড' : 'New Lead'}</span>
                  </button>
                </div>
              </div>

              {/* Filters Header */}
              <div className="bg-slate-900 border border-slate-800 rounded-2xl p-3 sm:p-4 flex flex-wrap gap-2.5 items-center justify-between">
                <div className="relative flex-1 min-w-[220px]">
                  <Search className="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
                  <input
                    type="text"
                    placeholder={lang === 'bn' ? 'নাম, মোবাইল বা লোকেশন খুঁজুন...' : 'Search name, phone or location...'}
                    value={leadFilter.search}
                    onChange={(e) => setLeadFilter({ ...leadFilter, search: e.target.value })}
                    className="w-full pl-9 pr-3 py-1.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-orange-500"
                  />
                </div>
                <div className="flex items-center gap-2">
                  <select
                    value={leadFilter.stage}
                    onChange={(e) => setLeadFilter({ ...leadFilter, stage: e.target.value })}
                    className="bg-slate-800 text-xs text-slate-300 border border-slate-700 rounded-xl px-2.5 py-1.5 focus:outline-none"
                  >
                    <option value="">{lang === 'bn' ? 'সকল পর্যায় (Stages)' : 'All Stages'}</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="presentation">Presentation</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="won">Won / Converted</option>
                    <option value="lost">Lost</option>
                  </select>

                  <select
                    value={leadFilter.temperature}
                    onChange={(e) => setLeadFilter({ ...leadFilter, temperature: e.target.value })}
                    className="bg-slate-800 text-xs text-slate-300 border border-slate-700 rounded-xl px-2.5 py-1.5 focus:outline-none"
                  >
                    <option value="">{lang === 'bn' ? 'সকল তাপমাত্রা' : 'All Temperatures'}</option>
                    <option value="hot">🔥 Hot</option>
                    <option value="warm">☀️ Warm</option>
                    <option value="cold">❄️ Cold</option>
                  </select>
                </div>
              </div>

              {/* TABLE VIEW */}
              {leadsView === 'table' ? (
                <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead className="bg-slate-800/80 text-slate-400 uppercase font-semibold border-b border-slate-800">
                        <tr>
                          <th className="py-3 px-4">{lang === 'bn' ? 'নাম ও যোগাযোগ' : 'Contact'}</th>
                          <th className="py-3 px-4">{lang === 'bn' ? 'পর্যায়' : 'Stage'}</th>
                          <th className="py-3 px-4">{lang === 'bn' ? 'তাপমাত্রা' : 'Temp'}</th>
                          <th className="py-3 px-4">{lang === 'bn' ? 'বাজেট' : 'Budget'}</th>
                          <th className="py-3 px-4">{lang === 'bn' ? 'উৎস' : 'Source'}</th>
                          <th className="py-3 px-4 text-right">{lang === 'bn' ? 'অ্যাকশন' : 'Actions'}</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-800 text-slate-300">
                        {leads.length > 0 ? (
                          leads.map((lead: any) => (
                            <tr key={lead.id} className="hover:bg-slate-800/40 transition-colors">
                              <td className="py-3 px-4">
                                <div
                                  onClick={() => openLeadDetails(lead.id)}
                                  className="font-bold text-white hover:text-orange-400 cursor-pointer flex items-center gap-1.5"
                                >
                                  <span>{lead.name}</span>
                                  {lead.score && (
                                    <span className="text-[10px] font-mono px-1.5 py-0.2 rounded bg-orange-600/30 text-orange-300">
                                      {lead.score} pts
                                    </span>
                                  )}
                                </div>
                                <div className="text-slate-400 text-[11px] flex items-center gap-2 mt-0.5">
                                  <span>{lead.mobile}</span>
                                  {lead.location && <span>• {lead.location}</span>}
                                </div>
                              </td>

                              <td className="py-3 px-4">
                                <select
                                  value={lead.stage || 'new'}
                                  onChange={(e) => handleQuickStageChange(lead.id, e.target.value)}
                                  className="bg-slate-800 border border-slate-700 text-slate-200 text-[11px] font-bold rounded-lg px-2 py-1 uppercase"
                                >
                                  <option value="new">NEW</option>
                                  <option value="contacted">CONTACTED</option>
                                  <option value="follow_up">FOLLOW UP</option>
                                  <option value="presentation">PRESENTATION</option>
                                  <option value="negotiation">NEGOTIATION</option>
                                  <option value="won">WON</option>
                                  <option value="lost">LOST</option>
                                </select>
                              </td>

                              <td className="py-3 px-4">
                                <span
                                  className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${
                                    lead.temperature === 'hot'
                                      ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30'
                                      : lead.temperature === 'warm'
                                        ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                                        : 'bg-blue-500/20 text-blue-300 border border-blue-500/30'
                                  }`}
                                >
                                  {lead.temperature === 'hot' ? '🔥 Hot' : lead.temperature === 'warm' ? '☀️ Warm' : '❄️ Cold'}
                                </span>
                              </td>

                              <td className="py-3 px-4 font-mono font-bold text-slate-200">
                                {formatMoney(Number(lead.budgetRange || 50000))}
                              </td>

                              <td className="py-3 px-4 text-[11px] text-slate-400">
                                {lead.sourceName || 'Direct'}
                              </td>

                              <td className="py-3 px-4 text-right">
                                <div className="inline-flex items-center gap-1.5">
                                  <a
                                    href={`https://wa.me/${(lead.whatsapp || lead.mobile || '').replace(/\D/g, '')}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 transition-colors"
                                    title="WhatsApp Chat"
                                  >
                                    <MessageSquare className="w-3.5 h-3.5" />
                                  </a>
                                  <a
                                    href={`tel:${lead.mobile}`}
                                    className="p-1.5 rounded-lg bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 transition-colors"
                                    title="Call"
                                  >
                                    <Phone className="w-3.5 h-3.5" />
                                  </a>
                                  <button
                                    onClick={() => openLeadDetails(lead.id)}
                                    className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                                    title="View Details"
                                  >
                                    <Eye className="w-3.5 h-3.5" />
                                  </button>
                                  <button
                                    onClick={() => handleOpenEditLead(lead)}
                                    className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-orange-400 transition-colors"
                                    title="Edit Lead"
                                  >
                                    <Edit3 className="w-3.5 h-3.5" />
                                  </button>
                                  <button
                                    onClick={() => setDeleteConfirmLead(lead)}
                                    className="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition-colors"
                                    title="Delete Lead"
                                  >
                                    <Trash2 className="w-3.5 h-3.5" />
                                  </button>
                                </div>
                              </td>
                            </tr>
                          ))
                        ) : (
                          <tr>
                            <td colSpan={6} className="text-center py-8 text-slate-500">
                              {lang === 'bn' ? 'কোনো লিড পাওয়া যায়নি।' : 'No leads found matching your criteria.'}
                            </td>
                          </tr>
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              ) : (
                /* KANBAN VIEW */
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
                  {[
                    { key: 'new', label: 'New Lead', labelBn: 'নতুন লিড' },
                    { key: 'follow_up', label: 'Follow Up', labelBn: 'ফলোআপ' },
                    { key: 'presentation', label: 'Presentation', labelBn: 'প্রেজেন্টেশন' },
                    { key: 'won', label: 'Won / Converted', labelBn: 'সফল ক্লোজিং' },
                  ].map((column) => {
                    const columnLeads = leads.filter((l) => l.stage === column.key);
                    return (
                      <div
                        key={column.key}
                        className="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 space-y-3 min-h-[350px]"
                      >
                        <div className="flex items-center justify-between pb-2 border-b border-slate-800">
                          <span className="text-xs font-bold text-white uppercase tracking-wider">
                            {lang === 'bn' ? column.labelBn : column.label}
                          </span>
                          <span className="text-[10px] font-bold bg-slate-800 text-orange-400 px-2 py-0.5 rounded-full border border-slate-700 font-mono">
                            {columnLeads.length}
                          </span>
                        </div>

                        <div className="space-y-2.5">
                          {columnLeads.map((lead: any) => (
                            <div
                              key={lead.id}
                              className="p-3 bg-slate-800/80 rounded-xl border border-slate-700 space-y-2 hover:border-orange-500/50 transition-colors shadow-sm"
                            >
                              <div className="flex items-center justify-between">
                                <span
                                  onClick={() => openLeadDetails(lead.id)}
                                  className="font-bold text-white text-xs hover:text-orange-400 cursor-pointer"
                                >
                                  {lead.name}
                                </span>
                                <span
                                  className={`text-[9px] font-bold px-1.5 py-0.5 rounded uppercase ${
                                    lead.temperature === 'hot'
                                      ? 'bg-rose-500/20 text-rose-300'
                                      : lead.temperature === 'warm'
                                        ? 'bg-amber-500/20 text-amber-300'
                                        : 'bg-blue-500/20 text-blue-300'
                                  }`}
                                >
                                  {lead.temperature}
                                </span>
                              </div>

                              <div className="text-[11px] text-slate-400">{lead.mobile}</div>

                              <div className="flex items-center justify-between pt-1 border-t border-slate-700/60 text-[10px]">
                                <span className="font-bold text-orange-400 font-mono">
                                  {formatMoney(Number(lead.budgetRange || 50000))}
                                </span>
                                <div className="flex items-center gap-1.5">
                                  <a
                                    href={`https://wa.me/${(lead.mobile || '').replace(/\D/g, '')}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-emerald-400 hover:text-emerald-300"
                                    title="WhatsApp"
                                  >
                                    <MessageSquare className="w-3.5 h-3.5" />
                                  </a>
                                  <button
                                    onClick={() => handleOpenEditLead(lead)}
                                    className="text-slate-400 hover:text-orange-400"
                                    title="Edit"
                                  >
                                    <Edit3 className="w-3.5 h-3.5" />
                                  </button>
                                  <button
                                    onClick={() => openLeadDetails(lead.id)}
                                    className="text-slate-400 hover:text-white"
                                    title="View"
                                  >
                                    <Eye className="w-3.5 h-3.5" />
                                  </button>
                                </div>
                              </div>

                              {/* Kanban Column Quick-Shift */}
                              <div className="pt-1 flex justify-between gap-1">
                                {column.key !== 'new' && (
                                  <button
                                    onClick={() => handleQuickStageChange(lead.id, 'new')}
                                    className="text-[9px] px-1.5 py-0.5 rounded bg-slate-700/60 text-slate-300 hover:bg-slate-700"
                                  >
                                    ← New
                                  </button>
                                )}
                                {column.key !== 'follow_up' && (
                                  <button
                                    onClick={() => handleQuickStageChange(lead.id, 'follow_up')}
                                    className="text-[9px] px-1.5 py-0.5 rounded bg-slate-700/60 text-slate-300 hover:bg-slate-700"
                                  >
                                    Follow Up
                                  </button>
                                )}
                                {column.key !== 'won' && (
                                  <button
                                    onClick={() => handleQuickStageChange(lead.id, 'won')}
                                    className="text-[9px] px-1.5 py-0.5 rounded bg-emerald-700/40 text-emerald-300 hover:bg-emerald-700/60"
                                  >
                                    Won ✓
                                  </button>
                                )}
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          )}

          {/* TAB 3: TEAM TREE (10-SLOT & MINDMAP) */}
          {activeTab === 'tree' && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
                <div>
                  <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {lang === 'bn' ? '১০-স্লট টিম এক্সপ্লোরার' : '10-Slot Team Explorer'}
                  </h1>
                  <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {lang === 'bn'
                      ? 'বাইনারি টিম কাঠামো, স্পনসর ও মেম্বার প্লেসমেন্ট ভিজ্যুয়ালাইজার'
                      : 'Interactive binary organization tree, node slots and member placement'}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <div className="bg-slate-800 p-0.5 rounded-xl border border-slate-700 flex items-center">
                    <button
                      onClick={() => setTreeView('slots')}
                      className={`px-3 py-1 rounded-lg text-xs font-bold transition-colors ${
                        treeView === 'slots' ? 'bg-orange-600 text-white' : 'text-slate-400'
                      }`}
                    >
                      {lang === 'bn' ? '১০-স্লট গ্রিড' : '10-Slot Grid'}
                    </button>
                    <button
                      onClick={() => setTreeView('mindmap')}
                      className={`px-3 py-1 rounded-lg text-xs font-bold transition-colors ${
                        treeView === 'mindmap' ? 'bg-orange-600 text-white' : 'text-slate-400'
                      }`}
                    >
                      {lang === 'bn' ? 'মাইন্ডম্যাপ' : 'Mindmap'}
                    </button>
                  </div>

                  <button
                    onClick={() => setShowAddNodeModal(true)}
                    className="px-3.5 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-orange-600/30"
                  >
                    <Plus className="w-3.5 h-3.5" />
                    <span>{lang === 'bn' ? 'মেম্বার প্লেস করুন' : 'Place Member'}</span>
                  </button>
                </div>
              </div>

              {/* Breadcrumb Hierarchy Bar */}
              <div className="flex items-center flex-wrap gap-2 p-3 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
                <span className="text-slate-400 font-bold flex items-center gap-1.5">
                  <Network className="w-3.5 h-3.5 text-orange-400" />
                  <span>{lang === 'bn' ? 'টিম হাইয়ারার্কি:' : 'Hierarchy:'}</span>
                </span>
                {treePath.map((step, idx) => (
                  <div key={idx} className="flex items-center gap-1.5">
                    {idx > 0 && <span className="text-slate-600 font-bold">›</span>}
                    <button
                      onClick={() => handleNavigateTreeBreadcrumb(idx)}
                      className={`px-2.5 py-1 rounded-lg font-bold transition-all ${
                        idx === treePath.length - 1
                          ? 'bg-orange-600 text-white shadow-sm shadow-orange-600/30'
                          : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white'
                      }`}
                    >
                      {step.name}
                    </button>
                  </div>
                ))}
                {treePath.length > 1 && (
                  <button
                    onClick={() => handleNavigateTreeBreadcrumb(0)}
                    className="ml-auto px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-lg text-[11px] font-bold transition-colors"
                  >
                    {lang === 'bn' ? 'মূল হোমে ফিরুন' : 'Back to Root'}
                  </button>
                )}
              </div>

              {/* 10-Slot Grid Visualizer */}
              {treeView === 'slots' ? (
                <div className="space-y-6">
                  {/* Current Root Leader Card */}
                  <div className="max-w-md mx-auto p-4 rounded-2xl bg-gradient-to-r from-orange-600/25 to-amber-600/25 border border-orange-500/40 text-center shadow-lg">
                    <span className="text-[10px] font-extrabold uppercase tracking-wider text-orange-400">
                      {treePath.length === 1 ? 'ROOT LEADER (YOU)' : 'CURRENT FOCUS LEADER'}
                    </span>
                    <h3 className="text-base font-black text-white mt-0.5">
                      {treePath[treePath.length - 1].name}
                    </h3>
                    <div className="text-xs text-slate-300 font-mono mt-0.5">
                      {lang === 'bn' ? '১০-স্লট বাইনারি টিম ম্যানেজমেন্ট' : '10-Slot Binary Team Management'}
                    </div>
                  </div>

                  {/* Left & Right Legs Split */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* LEFT LEG */}
                    <div className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                      <div className="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span className="text-xs font-extrabold text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                          <span>🔵</span>
                          <span>{lang === 'bn' ? 'লেফট টিম (১-৫)' : 'LEFT TEAM (SLOTS 1-5)'}</span>
                        </span>
                        <span className="text-[11px] font-bold text-slate-400">
                          {treeNodes.filter((n) => n.placementPosition <= 5).length}/5 Filled
                        </span>
                      </div>

                      <div className="space-y-2.5">
                        {[1, 2, 3, 4, 5].map((slotNumber) => {
                          const node = treeNodes.find((n) => n.placementPosition === slotNumber);
                          return (
                            <div
                              key={slotNumber}
                              className={`p-3 rounded-xl border flex items-center justify-between ${
                                node
                                  ? 'bg-slate-800/80 border-slate-700'
                                  : 'bg-slate-900/40 border-dashed border-slate-800'
                              }`}
                            >
                              <div className="flex items-center gap-3">
                                <span className="w-6 h-6 rounded-lg bg-blue-500/20 text-blue-400 font-black text-xs flex items-center justify-center">
                                  L{slotNumber}
                                </span>
                                {node ? (
                                  <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                      <span className="font-bold text-white text-xs">{node.memberName}</span>
                                      <span
                                        className={`px-2 py-0.5 rounded-md text-[9px] font-black uppercase ${
                                          (node.activeProject || node.packageName) === 'International'
                                            ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30'
                                            : (node.activeProject || node.packageName) === 'National'
                                              ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                                              : 'bg-sky-500/20 text-sky-300 border border-sky-500/30'
                                        }`}
                                      >
                                        {node.activeProject || node.packageName || 'Starter'}
                                      </span>
                                    </div>
                                    <div className="text-[10px] text-slate-400 font-mono flex items-center gap-2">
                                      <span>{node.phone}</span>
                                      <span>•</span>
                                      <span className="text-orange-400 font-bold">৳{(node.totalProjectInvest || 10000).toLocaleString()}</span>
                                    </div>
                                  </div>
                                ) : (
                                  <span className="text-xs text-slate-500 italic">
                                    {lang === 'bn' ? 'খালি স্লট (ভ্যাকেন্ট)' : 'Vacant Slot'}
                                  </span>
                                )}
                              </div>

                              {node ? (
                                <div className="flex items-center gap-1.5">
                                  <button
                                    onClick={() => handleOpenAddProject(node)}
                                    className="px-2 py-1 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 rounded-lg text-[10px] font-bold transition-colors"
                                    title={lang === 'bn' ? 'প্রজেক্ট যুক্ত করুন' : 'Add Project'}
                                  >
                                    + প্রজেক্ট
                                  </button>
                                  <button
                                    onClick={() => handleDrillDownTree(node)}
                                    className="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-orange-400 border border-slate-700 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-colors"
                                    title={lang === 'bn' ? 'সাব-টিম দেখুন' : 'Explore Sub-team'}
                                  >
                                    <span>টিম ({node.childCount || 0})</span>
                                    <ChevronRight className="w-3 h-3" />
                                  </button>
                                </div>
                              ) : (
                                <button
                                  onClick={() => {
                                    setNewNode({ ...newNode, placementPosition: slotNumber });
                                    setShowAddNodeModal(true);
                                  }}
                                  className="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-orange-400 border border-slate-700 rounded-lg text-[11px] font-bold"
                                >
                                  + Place
                                </button>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    {/* RIGHT LEG */}
                    <div className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                      <div className="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span className="text-xs font-extrabold text-orange-400 uppercase tracking-wider flex items-center gap-1.5">
                          <span>🟠</span>
                          <span>{lang === 'bn' ? 'রাইট টিম (৬-১০)' : 'RIGHT TEAM (SLOTS 6-10)'}</span>
                        </span>
                        <span className="text-[11px] font-bold text-slate-400">
                          {treeNodes.filter((n) => n.placementPosition > 5).length}/5 Filled
                        </span>
                      </div>

                      <div className="space-y-2.5">
                        {[6, 7, 8, 9, 10].map((slotNumber) => {
                          const node = treeNodes.find((n) => n.placementPosition === slotNumber);
                          return (
                            <div
                              key={slotNumber}
                              className={`p-3 rounded-xl border flex items-center justify-between ${
                                node
                                  ? 'bg-slate-800/80 border-slate-700'
                                  : 'bg-slate-900/40 border-dashed border-slate-800'
                              }`}
                            >
                              <div className="flex items-center gap-3">
                                <span className="w-6 h-6 rounded-lg bg-orange-500/20 text-orange-400 font-black text-xs flex items-center justify-center">
                                  R{slotNumber - 5}
                                </span>
                                {node ? (
                                  <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                      <span className="font-bold text-white text-xs">{node.memberName}</span>
                                      <span
                                        className={`px-2 py-0.5 rounded-md text-[9px] font-black uppercase ${
                                          (node.activeProject || node.packageName) === 'International'
                                            ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30'
                                            : (node.activeProject || node.packageName) === 'National'
                                              ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                                              : 'bg-sky-500/20 text-sky-300 border border-sky-500/30'
                                        }`}
                                      >
                                        {node.activeProject || node.packageName || 'Starter'}
                                      </span>
                                    </div>
                                    <div className="text-[10px] text-slate-400 font-mono flex items-center gap-2">
                                      <span>{node.phone}</span>
                                      <span>•</span>
                                      <span className="text-orange-400 font-bold">৳{(node.totalProjectInvest || 10000).toLocaleString()}</span>
                                    </div>
                                  </div>
                                ) : (
                                  <span className="text-xs text-slate-500 italic">
                                    {lang === 'bn' ? 'খালি স্লট (ভ্যাকেন্ট)' : 'Vacant Slot'}
                                  </span>
                                )}
                              </div>

                              {node ? (
                                <div className="flex items-center gap-1.5">
                                  <button
                                    onClick={() => handleOpenAddProject(node)}
                                    className="px-2 py-1 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 rounded-lg text-[10px] font-bold transition-colors"
                                    title={lang === 'bn' ? 'প্রজেক্ট যুক্ত করুন' : 'Add Project'}
                                  >
                                    + প্রজেক্ট
                                  </button>
                                  <button
                                    onClick={() => handleDrillDownTree(node)}
                                    className="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-orange-400 border border-slate-700 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-colors"
                                    title={lang === 'bn' ? 'সাব-টিম দেখুন' : 'Explore Sub-team'}
                                  >
                                    <span>টিম ({node.childCount || 0})</span>
                                    <ChevronRight className="w-3 h-3" />
                                  </button>
                                </div>
                              ) : (
                                <button
                                  onClick={() => {
                                    setNewNode({ ...newNode, placementPosition: slotNumber });
                                    setShowAddNodeModal(true);
                                  }}
                                  className="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-orange-400 border border-slate-700 rounded-lg text-[11px] font-bold"
                                >
                                  + Place
                                </button>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              ) : (
                /* Mindmap Tree View */
                <div className="p-6 rounded-2xl bg-slate-900 border border-slate-800 text-center space-y-6">
                  <div className="inline-block p-3 rounded-2xl bg-slate-800 border border-slate-700 text-white font-bold text-xs">
                    Root Leader: {user?.name || 'Admin'}
                  </div>
                  <div className="h-6 w-0.5 bg-slate-700 mx-auto" />
                  <div className="grid grid-cols-2 gap-8 max-w-lg mx-auto">
                    <div className="p-4 rounded-xl bg-blue-950/40 border border-blue-500/30 text-xs">
                      <div className="font-bold text-blue-300">Left Binary Branch</div>
                      <div className="text-[11px] text-slate-400 mt-1">
                        Active Members: {treeNodes.filter((n) => n.placementPosition <= 5).length}
                      </div>
                    </div>
                    <div className="p-4 rounded-xl bg-orange-950/40 border border-orange-500/30 text-xs">
                      <div className="font-bold text-orange-300">Right Binary Branch</div>
                      <div className="text-[11px] text-slate-400 mt-1">
                        Active Members: {treeNodes.filter((n) => n.placementPosition > 5).length}
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* TAB 4: PACKAGES */}
          {activeTab === 'packages' && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
                <div>
                  <div className="flex items-center gap-2">
                    <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                      {lang === 'bn' ? 'এসবিএল অফিসিয়াল প্যাকেজসমূহ' : 'SBL Packages'}
                    </h1>
                    <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">
                      Official 2026
                    </span>
                  </div>
                  <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {lang === 'bn'
                      ? 'অ্যাসোসিয়েট ও ক্লায়েন্টদের জন্য নির্ধারিত ক্যাপিটাল ও পয়েন্ট ভলিউম কাঠামো'
                      : 'Verified membership tiers, capital allocation and BV points distribution'}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => {
                      setPresentationIndex(0);
                      setPresentationOpen(true);
                    }}
                    className="px-3.5 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-orange-600/30 flex items-center gap-1.5"
                  >
                    <span>📺</span>
                    <span>{lang === 'bn' ? 'প্রেজেন্টেশন ব্রোশিউর' : 'Presentation Mode'}</span>
                  </button>
                  <button
                    onClick={() => setShowQrModal(true)}
                    className="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                  >
                    <span>📱</span>
                    <span>{lang === 'bn' ? 'কিউআর ও শিট' : 'QR & Sheet'}</span>
                  </button>
                </div>
              </div>

              {/* Package Cards Grid */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {officialPackages.map((pkg) => (
                  <div
                    key={pkg.id}
                    className="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-orange-500/40 transition-colors shadow-lg"
                  >
                    <div className="space-y-3">
                      <div className="flex items-center justify-between">
                        <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border ${pkg.badgeColor}`}>
                          {pkg.badge}
                        </span>
                        <span className="text-[11px] font-mono text-orange-400 font-bold">
                          {pkg.bv} BV
                        </span>
                      </div>

                      <div>
                        <h3 className="text-base font-black text-white">{pkg.name}</h3>
                        <div className="text-2xl font-black text-orange-500 mt-1 font-mono">
                          {formatMoney(pkg.priceBdt)}
                        </div>
                        <p className="text-xs text-slate-400 mt-1 leading-relaxed">
                          {pkg.description}
                        </p>
                      </div>

                      <div className="p-3 bg-slate-800/60 rounded-xl space-y-1.5 text-[11px] border border-slate-800">
                        <div className="flex justify-between text-slate-300">
                          <span className="text-slate-400">{lang === 'bn' ? 'মূলধন:' : 'Capital:'}</span>
                          <span className="font-bold">{formatMoney(pkg.capitalBdt)}</span>
                        </div>
                        <div className="flex justify-between text-slate-300">
                          <span className="text-slate-400">{lang === 'bn' ? 'সেটআপ ফি:' : 'Setup Fee:'}</span>
                          <span className="font-bold">{formatMoney(pkg.setupFeeBdt)}</span>
                        </div>
                        <div className="flex justify-between text-slate-300">
                          <span className="text-slate-400">{lang === 'bn' ? 'মেয়াদ:' : 'Duration:'}</span>
                          <span className="font-bold">{pkg.duration}</span>
                        </div>
                      </div>

                      <div className="space-y-1.5 pt-1">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                          {lang === 'bn' ? 'সুবিধাসমূহ:' : 'Included Benefits:'}
                        </span>
                        {pkg.benefits.map((b, idx) => (
                          <div key={idx} className="flex items-start gap-1.5 text-[11px] text-slate-300">
                            <span className="text-emerald-400 font-bold">✓</span>
                            <span>{b}</span>
                          </div>
                        ))}
                      </div>
                    </div>

                    <div className="pt-4 mt-4 border-t border-slate-800 flex gap-2">
                      <button
                        onClick={() => {
                          navigator.clipboard.writeText(
                            `https://sbltools.creationtech.info/packages?pkg=${pkg.id}`,
                          );
                          showToast(lang === 'bn' ? 'প্যাকেজ লিংক কপি করা হয়েছে!' : 'Package link copied!');
                          setSharePackageData(pkg);
                          setShowShareModal(true);
                        }}
                        className="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5"
                      >
                        <Share2 className="w-3.5 h-3.5 text-orange-400" />
                        <span>{lang === 'bn' ? 'শেয়ার' : 'Share'}</span>
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 5: RANKS */}
          {activeTab === 'ranks' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'র‍্যাঙ্ক ও লিডারশিপ অগ্রগতি' : 'Ranks & Leadership Progression'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'পয়েন্ট ভলিউম মাইলস্টোন, লিডারশিপ পুল বোনাস ও বিশেষ উপহার'
                    : 'Milestone requirements, leadership matching pools and official awards'}
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {ranksList.map((rank, idx) => (
                  <div
                    key={idx}
                    className="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden space-y-3"
                  >
                    <div className="flex items-center justify-between">
                      <span className="w-7 h-7 rounded-lg bg-orange-600/20 text-orange-400 font-black text-xs flex items-center justify-center">
                        #{idx + 1}
                      </span>
                      <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        Match Bonus {rank.matchBonus}
                      </span>
                    </div>

                    <div>
                      <h3 className="text-lg font-black text-white">
                        {lang === 'bn' ? rank.titleBn : rank.title}
                      </h3>
                      <div className="text-xs text-orange-400 font-semibold mt-0.5">
                        {rank.bvReq}
                      </div>
                    </div>

                    <div className="p-3 bg-slate-800/50 rounded-xl text-xs space-y-1 border border-slate-800">
                      <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
                        {lang === 'bn' ? 'অর্জন ও পুরষ্কার:' : 'Achievement Award:'}
                      </span>
                      <div className="text-white font-bold">{rank.reward}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 6: COUNSELING GUIDE */}
          {activeTab === 'counseling' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'কাউন্সেলিং ও অবজেকশন হ্যান্ডলিং গাইড' : 'Counseling & Closing Guide'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'প্রসপেক্টদের সাধারণ প্রশ্ন ও আপত্তির সঠিক প্রফেশনাল উত্তর'
                    : 'Proven script playbooks and 5-step framework to handle client objections'}
                </p>
              </div>

              {/* 5-Step Closing Framework */}
              <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
                <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                  {lang === 'bn' ? '৫-ধাপের কার্যকর ক্লোজিং ফ্রেমওয়ার্ক' : '5-Step Closing Framework'}
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
                  {[
                    { step: '1', title: 'Rapport', desc: 'বিশ্বাস তৈরি ও আন্তরিক সম্পর্ক স্থাপন' },
                    { step: '2', title: 'Discovery', desc: 'প্রসপেক্টের আসল আর্থিক প্রয়োজন ও স্বপ্ন জানা' },
                    { step: '3', title: 'Solution', desc: 'উপযুক্ত এসবিএল প্যাকেজ উপস্থাপন' },
                    { step: '4', title: 'Resolve', desc: 'প্রশ্ন ও দ্বিধার ইতিবাচক সমাধান' },
                    { step: '5', title: 'Action', desc: 'তাৎক্ষণিক মেম্বার রেজিস্ট্রেশন ও জয়েনিং' },
                  ].map((f) => (
                    <div key={f.step} className="p-3 rounded-xl bg-slate-800/60 border border-slate-750 space-y-1">
                      <span className="text-orange-400 font-black text-sm">Step {f.step}</span>
                      <div className="font-bold text-white text-xs">{f.title}</div>
                      <div className="text-[11px] text-slate-400">{f.desc}</div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Objection Handling Scripts */}
              <div className="space-y-3">
                <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                  {lang === 'bn' ? 'সাধারণ আপত্তি ও সমাধানের স্ক্রিপ্ট' : 'Objection Handling Playbook'}
                </h3>

                {objections.map((obj, i) => (
                  <div key={i} className="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-2">
                    <div className="flex items-center gap-2 text-rose-400 font-bold text-xs sm:text-sm">
                      <HelpCircle className="w-4 h-4 shrink-0" />
                      <span>{lang === 'bn' ? obj.qBn : obj.qEn}</span>
                    </div>
                    <div className="p-3 bg-slate-800/70 rounded-xl text-xs text-slate-200 leading-relaxed border border-slate-700/60">
                      {lang === 'bn' ? obj.answerBn : obj.answerEn}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 7: COMMISSION CALCULATOR */}
          {activeTab === 'commission' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'কমিশন ও আয় ক্যালকুলেটর' : 'Commission Calculator'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'ডিরেক্ট স্পন্সর ও বাইনারি পেয়ারিং ম্যাচিং আয়ের সঠিক হিসেব'
                    : 'Calculate real-time direct sponsor and binary pairing matching bonuses'}
                </p>
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-1 p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
                  <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                    {lang === 'bn' ? 'পয়েন্ট ইনপুট' : 'Volume Inputs'}
                  </h3>

                  <div>
                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                      {lang === 'bn' ? 'লেফট লেগ ভলিউম (BV)' : 'Left Leg Volume (BV)'}
                    </label>
                    <input
                      type="number"
                      value={calcLeftBv}
                      onChange={(e) => setCalcLeftBv(Number(e.target.value) || 0)}
                      className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                      {lang === 'bn' ? 'রাইট লেগ ভলিউম (BV)' : 'Right Leg Volume (BV)'}
                    </label>
                    <input
                      type="number"
                      value={calcRightBv}
                      onChange={(e) => setCalcRightBv(Number(e.target.value) || 0)}
                      className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-semibold text-slate-300 mb-1">
                      {lang === 'bn' ? 'ডিরেক্ট স্পন্সর সংখ্যা' : 'Direct Sponsoring Count'}
                    </label>
                    <input
                      type="number"
                      value={calcDirectReferrals}
                      onChange={(e) => setCalcDirectReferrals(Number(e.target.value) || 0)}
                      className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono"
                    />
                  </div>
                </div>

                <div className="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-5">
                  <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                    {lang === 'bn' ? 'আনুমানিক কমিশন ফলাফল' : 'Projected Payout Results'}
                  </h3>

                  <div className="grid grid-cols-2 gap-4">
                    <div className="p-4 rounded-xl bg-slate-800/80 border border-slate-750">
                      <span className="text-xs text-slate-400 block">
                        {lang === 'bn' ? 'বাইনারি ম্যাচিং ভলিউম' : 'Matched Pair Volume'}
                      </span>
                      <strong className="text-xl font-mono text-white block mt-1">
                        {commissionResults.matchedBv} BV
                      </strong>
                      <span className="text-[11px] text-emerald-400">
                        {formatMoney(commissionResults.pairingBonusBdt)} payout
                      </span>
                    </div>

                    <div className="p-4 rounded-xl bg-slate-800/80 border border-slate-750">
                      <span className="text-xs text-slate-400 block">
                        {lang === 'bn' ? 'ডিরেক্ট স্পন্সর বোনাস' : 'Direct Sponsor Bonus'}
                      </span>
                      <strong className="text-xl font-mono text-white block mt-1">
                        {formatMoney(commissionResults.directReferralBonusBdt)}
                      </strong>
                      <span className="text-[11px] text-slate-400">
                        {calcDirectReferrals} referrals
                      </span>
                    </div>
                  </div>

                  <div className="p-5 rounded-2xl bg-gradient-to-r from-orange-600/20 to-amber-600/20 border border-orange-500/40 text-center space-y-1">
                    <span className="text-xs font-bold uppercase tracking-wider text-orange-300">
                      {lang === 'bn' ? 'মোট আনুমানিক কমিশন' : 'Total Projected Commission'}
                    </span>
                    <div className="text-3xl sm:text-4xl font-black text-white font-mono">
                      {formatMoney(commissionResults.totalEarningsBdt)}
                    </div>
                    <div className="text-xs text-slate-300">
                      Carry forward to next cycle: {commissionResults.carryForwardBv} BV ({commissionResults.strongerLeg} Leg)
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 8: LINKS HUB */}
          {activeTab === 'links' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'এসবিএল লিংকস হাব' : 'Official Links Hub'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'অফিসিয়াল পোর্টাল, মোবাইল অ্যাপ ও অ্যাসোসিয়েট সিস্টেমের লিংক'
                    : 'Verified official corporate portals, associate dashboards and media tools'}
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {(links.length > 0
                  ? links
                  : [
                      { id: 1, title: 'SBL Official Corporate Portal', url: 'https://sbl.com.bd', category: 'Official' },
                      { id: 2, title: 'Associate Growth Manager', url: 'https://sbltools.creationtech.info', category: 'Platform' },
                      { id: 3, title: 'Central Support Desk', url: 'https://wa.me/8801700000000', category: 'Support' },
                    ]
                ).map((link: any) => (
                  <div
                    key={link.id}
                    className="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-orange-500/40 transition-colors"
                  >
                    <div>
                      <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-orange-400 border border-slate-700 uppercase">
                        {link.category || 'Official'}
                      </span>
                      <h3 className="text-sm font-bold text-white mt-2">{link.title}</h3>
                      <p className="text-xs text-slate-400 mt-1 truncate">{link.url}</p>
                    </div>

                    <div className="pt-4 mt-4 border-t border-slate-800 flex gap-2">
                      <a
                        href={link.url}
                        target="_blank"
                        rel="noreferrer"
                        className="flex-1 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold text-center transition-colors flex items-center justify-center gap-1"
                      >
                        <span>Open</span>
                        <ExternalLink className="w-3 h-3" />
                      </a>
                      <button
                        onClick={() => {
                          navigator.clipboard.writeText(link.url);
                          showToast('Link copied!');
                        }}
                        className="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors"
                        title="Copy URL"
                      >
                        <Copy className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 9: RESOURCES */}
          {activeTab === 'resources' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'মার্কেটিং রিসোর্সেস ও লিফলেট' : 'Marketing Resources & Leaflets'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'অফিসিয়াল লিফলেট, প্রেজেন্টেশন স্লাইড ও প্রিন্ট রেডি ডক্যুমেন্টস'
                    : 'Verified corporate leaflets, presentation decks and brochures'}
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                  <img
                    src="/images/sbl/sbl-office-leaflet.jpg"
                    alt="SBL Office Leaflet"
                    className="w-full h-48 object-cover rounded-xl"
                  />
                  <h3 className="text-sm font-bold text-white">
                    {lang === 'bn' ? 'অফিসিয়াল হেডকোয়ার্টার লিফলেট ২০২৬' : 'Official Corporate Leaflet 2026'}
                  </h3>
                  <p className="text-xs text-slate-400">
                    {lang === 'bn' ? 'নতুন ক্লায়েন্ট ও প্রসপেক্টদের দেওয়ার জন্য পূর্ণাঙ্গ বিবরণী।' : 'Comprehensive informational leaflet for prospect meetings.'}
                  </p>
                  <a
                    href="/images/sbl/sbl-office-leaflet.jpg"
                    target="_blank"
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all"
                  >
                    <Download className="w-3.5 h-3.5" />
                    <span>{lang === 'bn' ? 'ডাউনলোড করুন' : 'Download Leaflet'}</span>
                  </a>
                </div>

                <div className="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                  <img
                    src="/images/sbl-packages-sheet.png"
                    alt="Package Sheet"
                    className="w-full h-48 object-cover rounded-xl"
                  />
                  <h3 className="text-sm font-bold text-white">
                    {lang === 'bn' ? 'প্যাকেজ কম্প্যারিজন সামারি শিট' : 'Package Comparison Summary Sheet'}
                  </h3>
                  <p className="text-xs text-slate-400">
                    {lang === 'bn' ? 'এক নজরে সকল প্যাকেজের তুলনা ও আর্নিং চার্ট।' : 'All package tiers and daily capping limits at a glance.'}
                  </p>
                  <a
                    href="/images/sbl-packages-sheet.png"
                    target="_blank"
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold transition-all"
                  >
                    <Download className="w-3.5 h-3.5" />
                    <span>{lang === 'bn' ? 'পূর্ণাঙ্গ শিট দেখুন' : 'View Full Sheet'}</span>
                  </a>
                </div>
              </div>
            </div>
          )}

          {/* TAB 10: SBL CONTACT */}
          {activeTab === 'contacts' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'এসবিএল অফিশিয়াল কন্টাক্ট ডিরেক্টরি' : 'SBL Contact Directory'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'কাস্টমার সাপোর্ট, অ্যাকাউন্টস ও লিডারশিপ যোগাযোগের নম্বর'
                    : 'Direct hotlines and WhatsApp channels for support and finance'}
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {(contacts.length > 0
                  ? contacts
                  : [
                      { id: 1, name: 'Central Helpdesk', designation: 'Operations Lead', phone: '+8801700000000', email: 'support@sbl.test' },
                      { id: 2, name: 'Accounts & Finance', designation: 'Billing Dept', phone: '+8801700000001', email: 'finance@sbl.test' },
                      { id: 3, name: 'Leadership Coordinator', designation: 'Field Network', phone: '+8801700000002', email: 'network@sbl.test' },
                    ]
                ).map((c: any) => (
                  <div key={c.id} className="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                    <div className="w-10 h-10 rounded-xl bg-orange-600/20 text-orange-400 flex items-center justify-center font-black">
                      <PhoneCall className="w-5 h-5" />
                    </div>
                    <div>
                      <h3 className="text-sm font-bold text-white">{c.name}</h3>
                      <div className="text-xs text-slate-400">{c.designation || c.department}</div>
                    </div>
                    <div className="text-xs text-slate-300 font-mono">{c.phone}</div>
                    <div className="flex gap-2 pt-2 border-t border-slate-800">
                      <a
                        href={`https://wa.me/${(c.phone || '').replace(/\D/g, '')}`}
                        target="_blank"
                        rel="noreferrer"
                        className="flex-1 py-1.5 bg-emerald-600/20 text-emerald-300 hover:bg-emerald-600/30 rounded-xl text-xs font-bold text-center transition-colors flex items-center justify-center gap-1"
                      >
                        <MessageSquare className="w-3.5 h-3.5" />
                        <span>WhatsApp</span>
                      </a>
                      <a
                        href={`tel:${c.phone}`}
                        className="flex-1 py-1.5 bg-blue-600/20 text-blue-300 hover:bg-blue-600/30 rounded-xl text-xs font-bold text-center transition-colors flex items-center justify-center gap-1"
                      >
                        <Phone className="w-3.5 h-3.5" />
                        <span>Call</span>
                      </a>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 11: GLOSSARY */}
          {activeTab === 'glossary' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {lang === 'bn' ? 'বিজনেস অ্যাব্রিভিয়েশন ও গ্লসারি' : 'Business Glossary & Abbreviations'}
                  </h1>
                  <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {lang === 'bn'
                      ? 'এসবিএল ব্যবসার জরুরি পরিভাষা ও সংক্ষেপণের বিস্তারিত অর্থ'
                      : 'Comprehensive dictionary of terms, acronyms and operational formulas'}
                  </p>
                </div>
                <div className="relative w-full sm:w-64">
                  <Search className="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Search terms..."
                    value={glossarySearch}
                    onChange={(e) => setGlossarySearch(e.target.value)}
                    className="w-full pl-9 pr-3 py-1.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-orange-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {filteredGlossary.map((item: any, idx: number) => (
                  <div key={idx} className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
                    <div className="flex items-center justify-between">
                      <span className="font-mono text-sm font-black text-orange-400">
                        {item.abbreviation || item.abbr}
                      </span>
                      <span className="text-xs text-slate-300 font-bold">{item.term}</span>
                    </div>
                    <p className="text-xs text-slate-400 leading-relaxed pt-1">
                      {item.definition || item.desc}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* TAB 12: USERS & ACCOUNTS */}
          {activeTab === 'users' && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
                <div>
                  <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস অ্যাডমিনিস্ট্রেশন' : 'Users & Accounts'}
                  </h1>
                  <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {lang === 'bn'
                      ? 'মোবাইল নম্বর দিয়ে ইউজার ও পাসওয়ার্ড তৈরি, রোল নির্ধারণ ও ডিরেক্ট অ্যাকাউন্ট লগিন'
                      : 'Create users with phone number & password, set roles (Super Admin, Member, Demo)'}
                  </p>
                </div>
                {user?.role === 'super_admin' && (
                  <button
                    onClick={() => setShowAddUserModal(true)}
                    className="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-orange-600/30 flex items-center gap-2 self-start sm:self-auto"
                  >
                    <UserPlus className="w-4 h-4" />
                    <span>{lang === 'bn' ? '+ নতুন ইউজার তৈরি করুন' : '+ Create New User'}</span>
                  </button>
                )}
              </div>

              {/* Role Permission Legend */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div className="p-3 rounded-xl bg-orange-500/10 border border-orange-500/20 text-xs">
                  <div className="font-black text-orange-400 flex items-center gap-1.5">
                    <span>👑 Super Admin</span>
                  </div>
                  <div className="text-[11px] text-slate-300 mt-1">
                    {lang === 'bn' ? 'সকল ইউজারের ডাটা দেখতে পারবে, এডিট করতে পারবে এবং যেকোনো অ্যাকাউন্টে সুইচ করতে পারবে।' : 'Full administrative access and account impersonation.'}
                  </div>
                </div>
                <div className="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs">
                  <div className="font-black text-blue-400 flex items-center gap-1.5">
                    <span>👤 Member</span>
                  </div>
                  <div className="text-[11px] text-slate-300 mt-1">
                    {lang === 'bn' ? 'শুধুমাত্র নিজের লিড, নিজস্ব টিম ও আন্ডারে থাকা প্রজেক্টসমূহ স্বাধীনভাবে পরিচালনা করবে।' : 'Isolated member workspace for assigned leads & personal downline.'}
                  </div>
                </div>
                <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs">
                  <div className="font-black text-emerald-400 flex items-center gap-1.5">
                    <span>👀 Demo</span>
                  </div>
                  <div className="text-[11px] text-slate-300 mt-1">
                    {lang === 'bn' ? 'শুধুমাত্র সিস্টেমের ফিচারগুলো দেখতে পারবে (Read-Only), কোনো ডাটা পরিবর্তন করতে পারবে না।' : 'Read-only access to explore packages, calculator and team structure.'}
                  </div>
                </div>
              </div>

              {/* Users Table */}
              <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="p-4 border-b border-slate-800 flex items-center justify-between">
                  <span className="text-xs font-extrabold uppercase tracking-wider text-slate-300">
                    {lang === 'bn' ? `মোট ইউজার তালিকা (${usersList.length} জন)` : `System Users (${usersList.length})`}
                  </span>
                  <button
                    onClick={loadUsers}
                    className="text-xs text-orange-400 hover:text-orange-300 flex items-center gap-1 font-bold"
                  >
                    <span>{lang === 'bn' ? 'রিফ্রেশ' : 'Refresh'}</span>
                  </button>
                </div>

                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs text-slate-300">
                    <thead className="bg-slate-800/80 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-700">
                      <tr>
                        <th className="p-3.5">{lang === 'bn' ? 'ইউজার ও পদবি' : 'User & Designation'}</th>
                        <th className="p-3.5">{lang === 'bn' ? 'মোবাইল নম্বর (ইউজারনেম)' : 'Mobile (Username)'}</th>
                        <th className="p-3.5">{lang === 'bn' ? 'রোল' : 'Role'}</th>
                        <th className="p-3.5">{lang === 'bn' ? 'সংযুক্ত ডাটা' : 'Associated Data'}</th>
                        <th className="p-3.5 text-right">{lang === 'bn' ? 'অ্যাকশন' : 'Actions'}</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800/60">
                      {usersList.length > 0 ? (
                        usersList.map((u) => (
                          <tr key={u.id} className="hover:bg-slate-800/40 transition-colors">
                            <td className="p-3.5">
                              <div className="font-bold text-white text-xs">{u.name}</div>
                              <div className="text-[11px] text-slate-400">{u.designation || 'Associate'}</div>
                            </td>
                            <td className="p-3.5 font-mono text-slate-200">
                              {u.phone || u.username || u.email}
                            </td>
                            <td className="p-3.5">
                              <span
                                className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase ${
                                  u.role === 'super_admin'
                                    ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30'
                                    : u.role === 'demo'
                                      ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                                      : 'bg-blue-500/20 text-blue-300 border border-blue-500/30'
                                }`}
                              >
                                {u.role === 'super_admin' ? 'Super Admin' : u.role === 'demo' ? 'Demo' : 'Member'}
                              </span>
                            </td>
                            <td className="p-3.5 text-[11px] text-slate-400">
                              <div>লিড: {u.leadsCount || 0} টি</div>
                              <div>টিম মেম্বার: {u.nodesCount || 0} জন</div>
                            </td>
                            <td className="p-3.5 text-right">
                              <div className="flex items-center justify-end gap-2">
                                {user?.role === 'super_admin' && u.id !== user?.id && (
                                  <button
                                    onClick={() => handleImpersonate(u)}
                                    className="px-2.5 py-1 bg-orange-600/20 hover:bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-lg text-xs font-bold transition-colors flex items-center gap-1"
                                    title="Switch to this account"
                                  >
                                    <span>{lang === 'bn' ? 'লগিন করুন' : 'Login As'}</span>
                                  </button>
                                )}
                                {user?.role === 'super_admin' && u.id !== user?.id && (
                                  <button
                                    onClick={async () => {
                                      if (confirm(lang === 'bn' ? `আপনি কি ইউজার "${u.name}" ডিলিট করতে চান?` : `Delete user "${u.name}"?`)) {
                                        await api.deleteUser(u.id);
                                        showToast(lang === 'bn' ? 'ইউজার ডিলিট করা হয়েছে' : 'User deleted');
                                        loadUsers();
                                      }
                                    }}
                                    className="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
                                    title="Delete user"
                                  >
                                    <Trash2 className="w-3.5 h-3.5" />
                                  </button>
                                )}
                              </div>
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={5} className="text-center py-6 text-slate-500">
                            {isLoadingUsers ? 'ইউজার লোড হচ্ছে...' : 'কোনো ইউজার পাওয়া যায়নি।'}
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}
        </main>
      </div>

      {/* ======================================================== */}
      {/* MODAL 1: ADD / CREATE LEAD */}
      {/* ======================================================== */}
      {showAddLeadModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
          <div className="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button
              onClick={() => setShowAddLeadModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <h2 className="text-lg font-black text-white mb-4 flex items-center gap-2">
              <span className="text-orange-400">➕</span>
              <span>{lang === 'bn' ? 'নতুন লিড যুক্ত করুন' : 'Add New Prospect'}</span>
            </h2>

            <form onSubmit={handleCreateLead} className="space-y-3.5">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    {lang === 'bn' ? 'নাম *' : 'Name *'}
                  </label>
                  <input
                    type="text"
                    required
                    value={leadFormData.name}
                    onChange={(e) => setLeadFormData({ ...leadFormData, name: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    {lang === 'bn' ? 'মোবাইল নম্বর *' : 'Mobile *'}
                  </label>
                  <input
                    type="text"
                    required
                    value={leadFormData.mobile}
                    onChange={(e) => setLeadFormData({ ...leadFormData, mobile: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">WhatsApp</label>
                  <input
                    type="text"
                    value={leadFormData.whatsapp}
                    onChange={(e) => setLeadFormData({ ...leadFormData, whatsapp: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                  <input
                    type="email"
                    value={leadFormData.email}
                    onChange={(e) => setLeadFormData({ ...leadFormData, email: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Location / District</label>
                  <input
                    type="text"
                    value={leadFormData.location}
                    onChange={(e) => setLeadFormData({ ...leadFormData, location: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Profession / Business</label>
                  <input
                    type="text"
                    value={leadFormData.professionOrBusiness}
                    onChange={(e) => setLeadFormData({ ...leadFormData, professionOrBusiness: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Stage</label>
                  <select
                    value={leadFormData.stage}
                    onChange={(e) => setLeadFormData({ ...leadFormData, stage: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  >
                    <option value="new">New Lead</option>
                    <option value="contacted">Contacted</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="presentation">Presentation</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="won">Won / Converted</option>
                    <option value="lost">Lost</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Temperature</label>
                  <select
                    value={leadFormData.temperature}
                    onChange={(e) => setLeadFormData({ ...leadFormData, temperature: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  >
                    <option value="hot">🔥 Hot</option>
                    <option value="warm">☀️ Warm</option>
                    <option value="cold">❄️ Cold</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Target Budget (BDT)</label>
                  <input
                    type="number"
                    value={leadFormData.budgetRange}
                    onChange={(e) => setLeadFormData({ ...leadFormData, budgetRange: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Decision Timeline</label>
                  <select
                    value={leadFormData.decisionTimeline}
                    onChange={(e) => setLeadFormData({ ...leadFormData, decisionTimeline: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  >
                    <option value="Immediate">Immediate (১-৩ দিন)</option>
                    <option value="Within 15 Days">Within 15 Days (১৫ দিন)</option>
                    <option value="Next Month">Next Month (পরবর্তী মাস)</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Notes & Objections</label>
                <textarea
                  rows={2}
                  value={leadFormData.notes}
                  onChange={(e) => setLeadFormData({ ...leadFormData, notes: e.target.value })}
                  placeholder="Key background, interest in package, follow-up preferences..."
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowAddLeadModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-750"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-orange-600/30"
                >
                  Save Lead
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 2: EDIT LEAD */}
      {/* ======================================================== */}
      {showEditLeadModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
          <div className="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button
              onClick={() => setShowEditLeadModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <h2 className="text-lg font-black text-white mb-4 flex items-center gap-2">
              <Edit3 className="w-5 h-5 text-orange-400" />
              <span>{lang === 'bn' ? 'লিড তথ্য সম্পাদনা করুন' : 'Edit Lead Information'}</span>
            </h2>

            <form onSubmit={handleUpdateLead} className="space-y-3.5">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    {lang === 'bn' ? 'নাম *' : 'Name *'}
                  </label>
                  <input
                    type="text"
                    required
                    value={leadFormData.name}
                    onChange={(e) => setLeadFormData({ ...leadFormData, name: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    {lang === 'bn' ? 'মোবাইল নম্বর *' : 'Mobile *'}
                  </label>
                  <input
                    type="text"
                    required
                    value={leadFormData.mobile}
                    onChange={(e) => setLeadFormData({ ...leadFormData, mobile: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">WhatsApp</label>
                  <input
                    type="text"
                    value={leadFormData.whatsapp}
                    onChange={(e) => setLeadFormData({ ...leadFormData, whatsapp: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                  <input
                    type="email"
                    value={leadFormData.email}
                    onChange={(e) => setLeadFormData({ ...leadFormData, email: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:ring-1 focus:ring-orange-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Stage</label>
                  <select
                    value={leadFormData.stage}
                    onChange={(e) => setLeadFormData({ ...leadFormData, stage: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  >
                    <option value="new">New Lead</option>
                    <option value="contacted">Contacted</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="presentation">Presentation</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="won">Won / Converted</option>
                    <option value="lost">Lost</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Temperature</label>
                  <select
                    value={leadFormData.temperature}
                    onChange={(e) => setLeadFormData({ ...leadFormData, temperature: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  >
                    <option value="hot">🔥 Hot</option>
                    <option value="warm">☀️ Warm</option>
                    <option value="cold">❄️ Cold</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Target Budget (BDT)</label>
                  <input
                    type="number"
                    value={leadFormData.budgetRange}
                    onChange={(e) => setLeadFormData({ ...leadFormData, budgetRange: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Location</label>
                  <input
                    type="text"
                    value={leadFormData.location}
                    onChange={(e) => setLeadFormData({ ...leadFormData, location: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Notes</label>
                <textarea
                  rows={2}
                  value={leadFormData.notes}
                  onChange={(e) => setLeadFormData({ ...leadFormData, notes: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowEditLeadModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-750"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-orange-600/30"
                >
                  Update Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 3: LEAD DETAILS & ACTIVITY LOG DRAWER */}
      {/* ======================================================== */}
      {showDetailLeadModal && activeLeadDetail && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
          <div className="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative max-h-[92vh] overflow-y-auto space-y-5">
            <button
              onClick={() => setShowDetailLeadModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            {/* Header info */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
              <div>
                <div className="flex items-center gap-2">
                  <h2 className="text-xl font-black text-white">{activeLeadDetail.name}</h2>
                  <span
                    className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${
                      activeLeadDetail.temperature === 'hot'
                        ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30'
                        : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                    }`}
                  >
                    {activeLeadDetail.temperature}
                  </span>
                  <span className="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-orange-600/20 text-orange-300 border border-orange-500/30">
                    {activeLeadDetail.score || 30} pts
                  </span>
                </div>
                <div className="text-xs text-slate-400 mt-0.5">
                  ID: #{activeLeadDetail.id} • Created: {new Date(activeLeadDetail.createdAt).toLocaleDateString()}
                </div>
              </div>

              <div className="flex items-center gap-2">
                <a
                  href={`https://wa.me/${(activeLeadDetail.whatsapp || activeLeadDetail.mobile || '').replace(/\D/g, '')}`}
                  target="_blank"
                  rel="noreferrer"
                  className="px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 font-bold text-xs flex items-center gap-1.5"
                >
                  <MessageSquare className="w-3.5 h-3.5" />
                  <span>WhatsApp</span>
                </a>
                <a
                  href={`tel:${activeLeadDetail.mobile}`}
                  className="px-3 py-1.5 rounded-xl bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 font-bold text-xs flex items-center gap-1.5"
                >
                  <Phone className="w-3.5 h-3.5" />
                  <span>Call</span>
                </a>
                <button
                  onClick={() => handleOpenEditLead(activeLeadDetail)}
                  className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-orange-400 font-bold text-xs flex items-center gap-1.5"
                >
                  <Edit3 className="w-3.5 h-3.5" />
                  <span>Edit</span>
                </button>
              </div>
            </div>

            {/* Profile Matrix */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
              <div className="p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                <span className="text-slate-400 block text-[10px]">Mobile</span>
                <strong className="text-white block mt-0.5 font-mono">{activeLeadDetail.mobile}</strong>
              </div>
              <div className="p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                <span className="text-slate-400 block text-[10px]">Stage</span>
                <strong className="text-orange-400 block mt-0.5 uppercase">{activeLeadDetail.stage}</strong>
              </div>
              <div className="p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                <span className="text-slate-400 block text-[10px]">Target Budget</span>
                <strong className="text-white block mt-0.5 font-mono">
                  {formatMoney(Number(activeLeadDetail.budgetRange || 50000))}
                </strong>
              </div>
              <div className="p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                <span className="text-slate-400 block text-[10px]">Location</span>
                <strong className="text-white block mt-0.5">{activeLeadDetail.location || 'N/A'}</strong>
              </div>
            </div>

            {/* Notes Section */}
            {activeLeadDetail.notes && (
              <div className="p-3.5 bg-slate-800/40 rounded-xl border border-slate-800 text-xs">
                <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                  Lead Notes
                </span>
                <p className="text-slate-300 leading-relaxed">{activeLeadDetail.notes}</p>
              </div>
            )}

            {/* Activity History & Logger */}
            <div className="space-y-3 pt-2 border-t border-slate-800">
              <h3 className="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-1.5">
                <ActivityIcon className="w-4 h-4 text-orange-400" />
                <span>Follow-up History & Activity Logs</span>
              </h3>

              {/* Log new activity form */}
              <form onSubmit={handleAddActivity} className="p-3 bg-slate-800/50 rounded-xl border border-slate-750 flex gap-2">
                <select
                  value={activityType}
                  onChange={(e) => setActivityType(e.target.value)}
                  className="bg-slate-800 text-xs text-slate-300 border border-slate-700 rounded-lg px-2 py-1.5"
                >
                  <option value="call">Call</option>
                  <option value="whatsapp">WhatsApp</option>
                  <option value="meeting">Meeting</option>
                  <option value="note">Note</option>
                </select>
                <input
                  type="text"
                  required
                  placeholder="Log follow-up discussion or next steps..."
                  value={activityNote}
                  onChange={(e) => setActivityNote(e.target.value)}
                  className="flex-1 px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-white"
                />
                <button
                  type="submit"
                  disabled={isLoggingActivity}
                  className="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs rounded-lg transition-colors"
                >
                  {isLoggingActivity ? 'Saving...' : 'Add Log'}
                </button>
              </form>

              {/* History list */}
              <div className="space-y-2 max-h-48 overflow-y-auto">
                {activeLeadDetail.activities && activeLeadDetail.activities.length > 0 ? (
                  activeLeadDetail.activities.map((act: any) => (
                    <div
                      key={act.id}
                      className="p-2.5 rounded-lg bg-slate-800/30 border border-slate-800 text-xs flex items-start justify-between gap-2"
                    >
                      <div>
                        <span className="font-bold text-orange-400 uppercase text-[10px] mr-2">
                          [{act.type}]
                        </span>
                        <span className="text-slate-300">{act.details}</span>
                      </div>
                      <span className="text-[10px] text-slate-500 whitespace-nowrap">
                        {new Date(act.createdAt).toLocaleDateString()}
                      </span>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-4 text-slate-500 text-xs">
                    No activity logs recorded yet.
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 4: DELETE CONFIRMATION */}
      {/* ======================================================== */}
      {deleteConfirmLead && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm p-4">
          <div className="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl text-center space-y-4">
            <div className="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto">
              <Trash2 className="w-6 h-6" />
            </div>

            <h3 className="text-base font-bold text-white">
              {lang === 'bn' ? 'লিড মুছে ফেলতে চান?' : 'Delete Prospect?'}
            </h3>

            <p className="text-xs text-slate-400">
              {lang === 'bn'
                ? `আপনি কি নিশ্চিত যে "${deleteConfirmLead.name}" লিডটি মুছে ফেলতে চান?`
                : `Are you sure you want to delete "${deleteConfirmLead.name}"?`}
            </p>

            <div className="flex justify-center gap-2 pt-2">
              <button
                onClick={() => setDeleteConfirmLead(null)}
                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold"
              >
                Cancel
              </button>
              <button
                onClick={handleDeleteLead}
                className="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-600/30"
              >
                Confirm Delete
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 5: ADD TREE NODE MEMBER */}
      {/* ======================================================== */}
      {showAddNodeModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative">
            <button
              onClick={() => setShowAddNodeModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <h2 className="text-lg font-black text-white mb-4">
              {lang === 'bn' ? '১০-স্লট মেম্বার প্লেসমেন্ট' : 'Place Member in Binary Slot'}
            </h2>

            <form onSubmit={handleCreateNode} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Placement Slot (1-10)
                </label>
                <select
                  value={newNode.placementPosition}
                  onChange={(e) =>
                    setNewNode({ ...newNode, placementPosition: Number(e.target.value) })
                  }
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                >
                  {[1, 2, 3, 4, 5].map((s) => (
                    <option key={s} value={s}>
                      Left Leg Slot L{s}
                    </option>
                  ))}
                  {[6, 7, 8, 9, 10].map((s) => (
                    <option key={s} value={s}>
                      Right Leg Slot R{s - 5}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Member Name *</label>
                <input
                  type="text"
                  required
                  value={newNode.memberName}
                  onChange={(e) => setNewNode({ ...newNode, memberName: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Mobile Phone *</label>
                <input
                  type="text"
                  required
                  value={newNode.phone}
                  onChange={(e) => setNewNode({ ...newNode, phone: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'প্রজেক্ট নির্বাচন করুন' : 'Select Project'}
                </label>
                <select
                  value={newNode.packageName}
                  onChange={(e) => setNewNode({ ...newNode, packageName: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                >
                  <option value="Starter">1. Starter (৳১০,০০০ — ১০০ সপ্তাহে ৳১৫,০০০ রিটার্ন)</option>
                  <option value="National">2. National (৳১,০০,০০০ - ৳৪,৯০,০০০ — ১.৭৫%/সপ্তাহ)</option>
                  <option value="International">3. International (৳৫,০০,০০০+ — ২.০%/সপ্তাহ)</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAddNodeModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold"
                >
                  Confirm Placement
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 6: DIGITAL PRESENTATION BROCHURE SLIDER */}
      {/* ======================================================== */}
      {presentationOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
          <div className="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[92vh]">
            <div className="px-5 py-4 bg-slate-850 border-b border-slate-800 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="w-7 h-7 rounded-lg bg-orange-600 text-white font-black flex items-center justify-center text-xs">
                  SBL
                </span>
                <div>
                  <div className="text-xs font-bold text-orange-400">Digital Presentation Brochure</div>
                  <div className="text-[11px] text-slate-400">
                    Slide {presentationIndex + 1} of {officialPackages.length}
                  </div>
                </div>
              </div>
              <button
                onClick={() => setPresentationOpen(false)}
                className="text-slate-400 hover:text-white"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 sm:p-8 space-y-5 overflow-y-auto flex-1">
              {(() => {
                const currentPkg = officialPackages[presentationIndex];
                return (
                  <>
                    <div className="text-center space-y-2">
                      <span className={`inline-block px-3 py-1 rounded-full text-xs font-bold uppercase border ${currentPkg.badgeColor}`}>
                        {currentPkg.badge}
                      </span>
                      <h2 className="text-2xl font-black text-white">{currentPkg.name}</h2>
                      <div className="text-3xl font-black text-orange-500 font-mono">
                        {formatMoney(currentPkg.priceBdt)}
                      </div>
                      <p className="text-xs text-slate-300 max-w-md mx-auto leading-relaxed">
                        {currentPkg.description}
                      </p>
                    </div>

                    <div className="p-4 bg-slate-800/60 rounded-2xl border border-slate-750 grid grid-cols-2 gap-2 text-xs">
                      <div className="p-2.5 bg-slate-900 rounded-xl border border-slate-800">
                        <span className="text-slate-400 block text-[10px]">Capital</span>
                        <strong className="text-white block mt-0.5 font-mono">
                          {formatMoney(currentPkg.capitalBdt)}
                        </strong>
                      </div>
                      <div className="p-2.5 bg-slate-900 rounded-xl border border-slate-800">
                        <span className="text-slate-400 block text-[10px]">Setup Fee</span>
                        <strong className="text-white block mt-0.5 font-mono">
                          {formatMoney(currentPkg.setupFeeBdt)}
                        </strong>
                      </div>
                      <div className="p-2.5 bg-slate-900 rounded-xl border border-slate-800">
                        <span className="text-slate-400 block text-[10px]">BV Points</span>
                        <strong className="text-orange-400 block mt-0.5 font-mono">{currentPkg.bv} BV</strong>
                      </div>
                      <div className="p-2.5 bg-slate-900 rounded-xl border border-slate-800">
                        <span className="text-slate-400 block text-[10px]">Plan Duration</span>
                        <strong className="text-white block mt-0.5">{currentPkg.duration}</strong>
                      </div>
                    </div>

                    <div className="space-y-2">
                      <span className="text-xs font-bold uppercase tracking-wider text-slate-400 block">
                        Included Features
                      </span>
                      {currentPkg.benefits.map((b, i) => (
                        <div key={i} className="flex items-center gap-2 text-xs text-slate-200">
                          <span className="text-emerald-400 font-bold">✓</span>
                          <span>{b}</span>
                        </div>
                      ))}
                    </div>
                  </>
                );
              })()}
            </div>

            <div className="p-4 bg-slate-850 border-t border-slate-800 flex items-center justify-between">
              <button
                disabled={presentationIndex === 0}
                onClick={() => setPresentationIndex(presentationIndex - 1)}
                className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold disabled:opacity-40 transition-colors"
              >
                ← Previous
              </button>

              <div className="flex gap-1.5">
                {officialPackages.map((_, idx) => (
                  <span
                    key={idx}
                    className={`w-2.5 h-2.5 rounded-full transition-colors ${
                      presentationIndex === idx ? 'bg-orange-500' : 'bg-slate-700'
                    }`}
                  />
                ))}
              </div>

              <button
                disabled={presentationIndex === officialPackages.length - 1}
                onClick={() => setPresentationIndex(presentationIndex + 1)}
                className="px-3.5 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold disabled:opacity-40 transition-colors"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 7: QR CODE SHEET */}
      {/* ======================================================== */}
      {showQrModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative text-center space-y-4">
            <button
              onClick={() => setShowQrModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-base font-black text-white">SBL Package Official QR Code</h3>
            <div className="bg-white p-4 rounded-2xl inline-block shadow-lg mx-auto">
              <img
                src="/images/sbl-packages-qr.png"
                alt="Packages QR"
                className="w-48 h-48 object-contain"
              />
            </div>
            <p className="text-xs text-slate-400">
              Scan to instantly open official package brochure on any mobile device.
            </p>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL 8: SOCIAL SHARE & REFERRAL HUB */}
      {/* ======================================================== */}
      {showShareModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-md p-4">
          <div className="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative space-y-5">
            <button
              onClick={() => {
                setShowShareModal(false);
                setSharePackageData(null);
              }}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-2xl bg-orange-600/20 border border-orange-500/30 text-orange-400 flex items-center justify-center">
                <Share2 className="w-5 h-5" />
              </div>
              <div>
                <h3 className="text-base font-black text-white">
                  {sharePackageData
                    ? `${sharePackageData.name} - ${lang === 'bn' ? 'শেয়ার করুন' : 'Share Package'}`
                    : lang === 'bn'
                      ? 'এসবিএল প্লাটফর্ম ও রেফারাল শেয়ার'
                      : 'Share SBL Growth Platform'}
                </h3>
                <p className="text-xs text-slate-400">
                  {lang === 'bn'
                    ? 'হোয়াটসঅ্যাপ, ফেসবুক বা টেলিগ্রামে ১-ক্লিকেই ইনভাইটেশন লিংক পাঠান'
                    : '1-click shareable invites for WhatsApp, Facebook, and Telegram'}
                </p>
              </div>
            </div>

            {/* Generated Message Preview */}
            {(() => {
              const shareUrl = sharePackageData
                ? `https://sbltools.creationtech.info/packages?pkg=${sharePackageData.id}`
                : 'https://sbltools.creationtech.info/';
              const shareText = sharePackageData
                ? lang === 'bn'
                  ? `🔥 এসবিএল (SBL) অফিসিয়াল পার্টনারশিপ প্যাকেজ: *${sharePackageData.name}*\n💰 বাজেট: ${formatMoney(sharePackageData.priceBdt)} | পয়েন্ট: ${sharePackageData.bv} BV\n✨ সুবিধা: ${sharePackageData.benefits.slice(0, 2).join(', ')}\n\nবিস্তারিত দেখুন ও যুক্ত হোন:\n${shareUrl}`
                  : `🔥 Check out SBL Official Partnership Package: *${sharePackageData.name}*\n💰 Investment: ${formatMoney(sharePackageData.priceBdt)} | Points: ${sharePackageData.bv} BV\n✨ Perks: ${sharePackageData.benefits.slice(0, 2).join(', ')}\n\nExplore & Join:\n${shareUrl}`
                : lang === 'bn'
                  ? `🚀 স্মার্ট বিজনেস লজিস্টিকস ও ডিজিটাল কমার্স লিডারশিপে এসবিএল প্ল্যাটফর্মে আপনাকে স্বাগতম!\n\nসেলস পাইপলাইন অটোমেশন, ১০-স্লট বাইনারি নেটওয়ার্ক এবং ম্যাচিং কমিশনের বিস্তারিত জানতে ভিজিট করুন:\n${shareUrl}`
                  : `🚀 Welcome to SBL Growth Platform - Smart Business Logistics & Marketing Ecosystem!\n\nAutomate your sales pipeline, binary team tracking, and matching commissions. Explore here:\n${shareUrl}`;

              return (
                <div className="space-y-4">
                  <div className="p-3.5 bg-slate-800/80 rounded-2xl border border-slate-700/80 text-xs text-slate-200 whitespace-pre-line leading-relaxed font-sans max-h-40 overflow-y-auto">
                    {shareText}
                  </div>

                  {/* Share Action Buttons */}
                  <div className="grid grid-cols-3 gap-2.5">
                    <a
                      href={`https://wa.me/?text=${encodeURIComponent(shareText)}`}
                      target="_blank"
                      rel="noreferrer"
                      className="p-2.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/40 text-emerald-300 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition-colors"
                    >
                      <MessageSquare className="w-5 h-5 text-emerald-400" />
                      <span>WhatsApp</span>
                    </a>

                    <a
                      href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`}
                      target="_blank"
                      rel="noreferrer"
                      className="p-2.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/40 text-blue-300 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition-colors"
                    >
                      <ExternalLink className="w-5 h-5 text-blue-400" />
                      <span>Facebook</span>
                    </a>

                    <a
                      href={`https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`}
                      target="_blank"
                      rel="noreferrer"
                      className="p-2.5 rounded-xl bg-sky-600/20 hover:bg-sky-600/30 border border-sky-500/40 text-sky-300 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition-colors"
                    >
                      <Send className="w-5 h-5 text-sky-400" />
                      <span>Telegram</span>
                    </a>
                  </div>

                  {/* Copy Link Row */}
                  <div className="flex items-center gap-2 pt-2 border-t border-slate-800">
                    <input
                      type="text"
                      readOnly
                      value={shareUrl}
                      className="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-slate-300 text-xs font-mono select-all focus:outline-none"
                    />
                    <button
                      onClick={() => {
                        navigator.clipboard.writeText(shareText);
                        showToast(lang === 'bn' ? 'মেসেজ এবং লিংক কপি করা হয়েছে!' : 'Message & link copied to clipboard!');
                      }}
                      className="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-orange-600/30"
                    >
                      <Copy className="w-3.5 h-3.5" />
                      <span>{lang === 'bn' ? 'কপি করুন' : 'Copy'}</span>
                    </button>
                  </div>
                </div>
              );
            })()}
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MOBILE BOTTOM NAVIGATION BAR (SMARTPHONE OPTIMIZED) */}
      {/* ======================================================== */}
      <nav className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 px-2 py-1.5 flex items-center justify-around pb-safe shadow-2xl">
        <button
          onClick={() => {
            setActiveTab('dashboard');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }}
          className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
            activeTab === 'dashboard' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
          }`}
        >
          <LayoutDashboard className="w-5 h-5" />
          <span className="text-[10px]">{lang === 'bn' ? 'ড্যাশবোর্ড' : 'Home'}</span>
        </button>

        <button
          onClick={() => {
            setActiveTab('leads');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }}
          className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
            activeTab === 'leads' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
          }`}
        >
          <Users className="w-5 h-5" />
          <span className="text-[10px]">{lang === 'bn' ? 'লিডস' : 'Leads'}</span>
        </button>

        <button
          onClick={() => {
            setActiveTab('tree');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }}
          className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
            activeTab === 'tree' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
          }`}
        >
          <Network className="w-5 h-5" />
          <span className="text-[10px]">{lang === 'bn' ? '১০-স্লট টিম' : 'Team'}</span>
        </button>

        <button
          onClick={() => {
            setActiveTab('packages');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }}
          className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
            activeTab === 'packages' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
          }`}
        >
          <Package className="w-5 h-5" />
          <span className="text-[10px]">{lang === 'bn' ? 'প্যাকেজ' : 'Packages'}</span>
        </button>

        <button
          onClick={() => {
            setSharePackageData(null);
            setShowShareModal(true);
          }}
          className="flex flex-col items-center gap-0.5 p-1.5 rounded-xl text-orange-400 hover:text-orange-300 transition-colors"
        >
          <Share2 className="w-5 h-5" />
          <span className="text-[10px] font-bold">{lang === 'bn' ? 'শেয়ার' : 'Share'}</span>
        </button>

        <button
          onClick={() => setSidebarOpen(!sidebarOpen)}
          className="flex flex-col items-center gap-0.5 p-1.5 rounded-xl text-slate-400 hover:text-white transition-colors"
        >
          <Menu className="w-5 h-5" />
          <span className="text-[10px]">{lang === 'bn' ? 'মেনু' : 'Menu'}</span>
        </button>
      </nav>

      {/* ======================================================== */}
      {/* MODAL: ADD PROJECT TO MEMBER */}
      {/* ======================================================== */}
      {showAddProjectModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative">
            <button
              onClick={() => setShowAddProjectModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="flex items-center gap-2 mb-4">
              <Package className="w-5 h-5 text-orange-400" />
              <h2 className="text-base font-black text-white">
                {lang === 'bn' ? 'মেম্বারের আন্ডারে প্রজেক্ট যুক্ত করুন' : 'Allocate Project to Member'}
              </h2>
            </div>

            <div className="p-3 bg-slate-800/60 rounded-xl mb-4 text-xs border border-slate-800">
              <span className="text-slate-400">{lang === 'bn' ? 'নির্বাচিত মেম্বার:' : 'Selected Member:'} </span>
              <span className="font-bold text-white">{selectedMemberForProject?.memberName}</span>
              <span className="text-slate-400 font-mono ml-2">({selectedMemberForProject?.phone})</span>
            </div>

            <form onSubmit={handleSaveMemberProject} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'প্রজেক্ট নির্বাচন করুন *' : 'Select SBL Project *'}
                </label>
                <select
                  value={projectFormData.projectName}
                  onChange={(e) => {
                    const name = e.target.value;
                    let defAmount = 10000;
                    if (name === 'National') defAmount = 120000;
                    if (name === 'International') defAmount = 550000;
                    setProjectFormData({ ...projectFormData, projectName: name, amountBdt: defAmount });
                  }}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-bold"
                >
                  <option value="Starter">1. Starter (৳১০,০০০ — ১০০ সপ্তাহে ৳১৫,০০০ রিটার্ন)</option>
                  <option value="National">2. National (৳১,০০,০০০ - ৳৪,৯০,০০০ — ১.৭৫%/সপ্তাহ)</option>
                  <option value="International">3. International (৳৫,০০,০০০+ — ২.০%/সপ্তাহ)</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'বিনিয়োগের পরিমাণ (BDT) *' : 'Investment Amount (BDT) *'}
                </label>
                <input
                  type="number"
                  required
                  value={projectFormData.amountBdt}
                  onChange={(e) => setProjectFormData({ ...projectFormData, amountBdt: Number(e.target.value) })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono font-bold"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'রেফারেন্স / নোট (ঐচ্ছিক)' : 'Reference / Note (Optional)'}
                </label>
                <textarea
                  rows={2}
                  value={projectFormData.referenceNote}
                  onChange={(e) => setProjectFormData({ ...projectFormData, referenceNote: e.target.value })}
                  placeholder={lang === 'bn' ? 'যেমন: ন্যাশনাল ক্রাউডফান্ডিং পার্টনার' : 'e.g. National Shopify Store'}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAddProjectModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold"
                >
                  {lang === 'bn' ? 'বাতিল' : 'Cancel'}
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30"
                >
                  {lang === 'bn' ? 'প্রজেক্ট নিশ্চিত করুন' : 'Confirm Project'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ======================================================== */}
      {/* MODAL: ADD / CREATE USER (SUPER ADMIN ONLY) */}
      {/* ======================================================== */}
      {showAddUserModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative">
            <button
              onClick={() => setShowAddUserModal(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-white"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="flex items-center gap-2 mb-4">
              <UserPlus className="w-5 h-5 text-orange-400" />
              <h2 className="text-base font-black text-white">
                {lang === 'bn' ? 'নতুন ইউজার তৈরি করুন' : 'Create New User Account'}
              </h2>
            </div>

            <form onSubmit={handleCreateUser} className="space-y-3.5">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'ইউজারের পুরো নাম *' : 'Full Name *'}
                </label>
                <input
                  type="text"
                  required
                  value={newUserFormData.name}
                  onChange={(e) => setNewUserFormData({ ...newUserFormData, name: e.target.value })}
                  placeholder="e.g. Rafiqul Islam"
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'মোবাইল নম্বর (ইউজারনেম) *' : 'Mobile Number (Username) *'}
                </label>
                <input
                  type="text"
                  required
                  value={newUserFormData.phone}
                  onChange={(e) => setNewUserFormData({ ...newUserFormData, phone: e.target.value })}
                  placeholder="017XXXXXXXX"
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-mono"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'পাসওয়ার্ড সেট করুন *' : 'Set Password *'}
                </label>
                <input
                  type="password"
                  required
                  value={newUserFormData.password}
                  onChange={(e) => setNewUserFormData({ ...newUserFormData, password: e.target.value })}
                  placeholder="Min 6 characters"
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'ইউজার রোল *' : 'User Role *'}
                </label>
                <select
                  value={newUserFormData.role}
                  onChange={(e) => setNewUserFormData({ ...newUserFormData, role: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs font-bold"
                >
                  <option value="member">👤 Member (নিজস্ব ডাটা ও টিম পরিচালনা)</option>
                  <option value="demo">👀 Demo (শুধুমাত্র ভিউ ও ডেমো দেখা)</option>
                  <option value="super_admin">👑 Super Admin (সর্বোচ্চ ক্ষমতা ও নিয়ন্ত্রণ)</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  {lang === 'bn' ? 'পদবি / ডেজিগনেশন' : 'Designation'}
                </label>
                <input
                  type="text"
                  value={newUserFormData.designation}
                  onChange={(e) => setNewUserFormData({ ...newUserFormData, designation: e.target.value })}
                  placeholder="e.g. Marketing Associate"
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAddUserModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold"
                >
                  {lang === 'bn' ? 'বাতিল' : 'Cancel'}
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30"
                >
                  {lang === 'bn' ? 'ইউজার তৈরি করুন' : 'Create User'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* GLOBAL TOAST NOTIFICATION */}
      {toastMessage && (
        <div className="fixed bottom-16 lg:bottom-6 right-6 z-50 bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-xl shadow-2xl text-xs font-bold flex items-center gap-2">
          <span className="text-emerald-400">✓</span>
          <span>{toastMessage}</span>
        </div>
      )}
    </div>
  );
}
