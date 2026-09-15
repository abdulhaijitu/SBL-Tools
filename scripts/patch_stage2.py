with open("src/client/App.tsx", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update load active tab data
old_tab_effect = """    if (activeTab === 'dashboard') {
      api.getDashboardSummary().then(setDashboardData).catch(console.error);
    } else if (activeTab === 'leads') {
      loadLeads();
      api.getLeadSources().then(setLeadSources).catch(console.error);
    } else if (activeTab === 'tree') {
      api.getTreeNodes().then(setTreeNodes).catch(console.error);
    } else if (activeTab === 'links' || activeTab === 'contacts' || activeTab === 'glossary') {"""

new_tab_effect = """    if (activeTab === 'dashboard') {
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
    } else if (activeTab === 'links' || activeTab === 'contacts' || activeTab === 'glossary') {"""

if old_tab_effect in content:
    content = content.replace(old_tab_effect, new_tab_effect)
    print("Replaced tab effect successfully")
else:
    print("Warning: old_tab_effect not found")

# 2. Add helper handlers right after handleLogin
old_login_block = """  // Handle Login
  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoggingIn(true);
    setLoginError('');

    try {
      const res = await api.login({ email: loginEmail, password: loginPassword });
      setAuthToken(res.token);
      setToken(res.token);
      setUser(res.user);
    } catch (err: any) {
      setLoginError(err.message || 'Login failed. Please check credentials.');
    } finally {
      setIsLoggingIn(false);
    }
  };"""

new_login_block = """  // Load tree nodes for parent
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
      if (!api.getBackupAdminToken() && token) {
        api.setBackupAdminToken(token);
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
  };"""

if old_login_block in content:
    content = content.replace(old_login_block, new_login_block)
    print("Replaced login block successfully")
else:
    print("Warning: old_login_block not found")

with open("src/client/App.tsx", "w", encoding="utf-8") as f:
    f.write(content)

print("Saved stage 2")
