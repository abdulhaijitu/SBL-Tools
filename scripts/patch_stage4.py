with open("src/client/App.tsx", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update handleCreateNode
old_create_node = """  const handleCreateNode = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.addTreeNode({
        ...newNode,
        userId: user?.id || 1,
      });
      setShowAddNodeModal(false);
      showToast(lang === 'bn' ? 'টিম মেম্বার যুক্ত হয়েছে!' : 'Team member placed successfully!');
      api.getTreeNodes().then(setTreeNodes);
    } catch (err: any) {
      alert(err.message || 'Failed to place member');
    }
  };"""

new_create_node = """  const handleCreateNode = async (e: React.FormEvent) => {
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
  };"""

if old_create_node in content:
    content = content.replace(old_create_node, new_create_node)
    print("Replaced handleCreateNode successfully")
else:
    print("Warning: old_create_node not found")

# 2. Update Place Member modal package options
old_modal_options = """              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Package Tier</label>
                <select
                  value={newNode.packageName}
                  onChange={(e) => setNewNode({ ...newNode, packageName: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs"
                >
                  <option value="Basic Starter">Basic Starter (৳15,000)</option>
                  <option value="National Growth">National Growth (৳35,000)</option>
                  <option value="International Executive">International Executive (৳75,000)</option>
                  <option value="Leadership Elite">Leadership Elite (৳150,000)</option>
                </select>
              </div>"""

new_modal_options = """              <div>
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
              </div>"""

if old_modal_options in content:
    content = content.replace(old_modal_options, new_modal_options)
    print("Replaced modal options successfully")
else:
    print("Warning: old_modal_options not found")

# 3. Update Tab 12 Users & Accounts
old_users_tab = """          {/* TAB 12: USERS & ACCOUNTS */}
          {activeTab === 'users' && (
            <div className="space-y-6">
              <div className="pb-2 border-b border-slate-800">
                <h1 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                  {lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস অ্যাডমিনিস্ট্রেশন' : 'Users & Accounts'}
                </h1>
                <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
                  {lang === 'bn'
                    ? 'প্ল্যাটফর্মের সহযোগী সদস্য ও সুপার অ্যাডমিন অ্যাকাউন্ট ব্যবস্থাপনা'
                    : 'System accounts, role hierarchy and security control'}
                </p>
              </div>

              <div className="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <div className="flex items-center justify-between p-3 rounded-xl bg-slate-800/60 border border-slate-750">
                  <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-orange-600 text-white font-black flex items-center justify-center text-sm">
                      A
                    </div>
                    <div>
                      <div className="text-xs font-bold text-white">Administrator (Admin)</div>
                      <div className="text-[11px] text-slate-400">admin@sbl.test • Chief Operating Officer</div>
                    </div>
                  </div>
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase">
                    super_admin
                  </span>
                </div>
              </div>
            </div>
          )}"""

new_users_tab = """          {/* TAB 12: USERS & ACCOUNTS */}
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
          )}"""

if old_users_tab in content:
    content = content.replace(old_users_tab, new_users_tab)
    print("Replaced users tab successfully")
else:
    print("Warning: old_users_tab not found")

with open("src/client/App.tsx", "w", encoding="utf-8") as f:
    f.write(content)

print("Saved stage 4")

