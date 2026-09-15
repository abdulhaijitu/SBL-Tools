with open("src/client/App.tsx", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update tree slot card rendering and add breadcrumbs
old_tree_body_start = """              {/* 10-Slot Grid Visualizer */}
              {treeView === 'slots' ? (
                <div className="space-y-6">
                  {/* Root Leader Card */}
                  <div className="max-w-md mx-auto p-4 rounded-2xl bg-gradient-to-r from-orange-600/30 to-amber-600/30 border border-orange-500/50 text-center shadow-lg">
                    <span className="text-[10px] font-bold uppercase tracking-wider text-orange-300">
                      ROOT LEADER (YOU)
                    </span>
                    <h3 className="text-base font-black text-white mt-0.5">
                      {user?.name || 'Admin Leader'}
                    </h3>
                    <div className="text-xs text-slate-300 font-mono mt-0.5">
                      ID: {user?.id || 1} • Package: Leadership Elite
                    </div>
                  </div>"""

new_tree_body_start = """              {/* Breadcrumb Hierarchy Bar */}
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
                  </div>"""

if old_tree_body_start in content:
    content = content.replace(old_tree_body_start, new_tree_body_start)
    print("Replaced tree header & breadcrumbs successfully")
else:
    print("Warning: old_tree_body_start not found")

# Replace slot node rendering with project badges and action buttons
old_left_slot = """                                {node ? (
                                  <div>
                                    <div className="font-bold text-white text-xs">{node.memberName}</div>
                                    <div className="text-[10px] text-slate-400 font-mono">
                                      {node.phone} • {node.packageName || 'Growth Builder'}
                                    </div>
                                  </div>
                                ) : (
                                  <span className="text-xs text-slate-500 italic">
                                    {lang === 'bn' ? 'খালি স্লট (ভ্যাকেন্ট)' : 'Vacant Slot'}
                                  </span>
                                )}
                              </div>

                              {!node && (
                                <button
                                  onClick={() => {
                                    setNewNode({ ...newNode, placementPosition: slotNumber });
                                    setShowAddNodeModal(true);
                                  }}
                                  className="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-orange-400 border border-slate-700 rounded-lg text-[11px] font-bold"
                                >
                                  + Place
                                </button>
                              )}"""

new_slot_component = """                                {node ? (
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
                              )}"""

# Replace both occurrences (Left leg and Right leg)
if old_left_slot in content:
    content = content.replace(old_left_slot, new_slot_component)
    print("Replaced first slot component successfully")
    if old_left_slot in content:
        content = content.replace(old_left_slot, new_slot_component)
        print("Replaced second slot component successfully")
else:
    print("Warning: old_left_slot not found")

# 2. Add New Modals: Add Project to Member & Add User
modals_insertion_point = "      {/* GLOBAL TOAST NOTIFICATION */}"

new_modals = """      {/* ======================================================== */}
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

"""

if modals_insertion_point in content:
    content = content.replace(modals_insertion_point, new_modals + modals_insertion_point)
    print("Inserted new modals successfully")
else:
    print("Warning: modals_insertion_point not found")

# 3. Restrict sidebar Administration group to Super Admin only
old_admin_sidebar = """          {/* Administration Group */}
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
                <Users className="w-4 h-4 text-orange-400" />
                <span>{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users & Accounts'}</span>
              </button>
            </div>
          </div>"""

new_admin_sidebar = """          {/* Administration Group (Super Admin Only) */}
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
                  <Users className="w-4 h-4 text-orange-400" />
                  <span>{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users & Accounts'}</span>
                </button>
              </div>
            </div>
          )}"""

if old_admin_sidebar in content:
    content = content.replace(old_admin_sidebar, new_admin_sidebar)
    print("Restricted administration sidebar successfully")
else:
    print("Warning: old_admin_sidebar not found")

with open("src/client/App.tsx", "w", encoding="utf-8") as f:
    f.write(content)

print("Saved stage 5")

