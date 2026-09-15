import re

with open('src/client/App.tsx', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add LogIn to lucide-react import if missing
if 'LogIn,' not in content:
    content = content.replace("Send,\n} from 'lucide-react';", "Send,\n  LogIn,\n} from 'lucide-react';")

# 2. Add isSuperAdmin memo
if 'const isSuperAdmin =' not in content:
    needle = "const [token, setToken] = useState<string | null>(getAuthToken());\n  const [user, setUser] = useState<any>(null);"
    replacement = """const [token, setToken] = useState<string | null>(getAuthToken());
  const [user, setUser] = useState<any>(null);

  const isSuperAdmin = useMemo(() => {
    return (
      user?.role === 'super_admin' ||
      user?.primaryRole === 'super_admin' ||
      user?.email === 'admin@sbl.test' ||
      user?.phone === '+8801700000000' ||
      user?.phone === '01700000000' ||
      user?.id === 1
    );
  }, [user]);"""
    content = content.replace(needle, replacement)

# 3. Update activeTab loader effect for users
old_effect = """    } else if (activeTab === 'users') {
      if (user?.role === 'super_admin') {
        loadUsers();
      }
    }"""
new_effect = """    } else if (activeTab === 'users') {
      loadUsers();
    }"""
content = content.replace(old_effect, new_effect)
content = content.replace("}, [token, activeTab, leadFilter]);", "}, [token, activeTab, leadFilter, user]);")

# 4. Add Quick Users button to Header
old_header_btn = """            <button
              onClick={handleOpenAddLead}
              className=\"hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-orange-600/30\"
            >
              <Plus className=\"w-3.5 h-3.5\" />
              <span>{lang === 'bn' ? 'নতুন লিড' : 'New Lead'}</span>
            </button>"""

new_header_btn = """            <button
              onClick={handleOpenAddLead}
              className=\"hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-orange-600/30\"
            >
              <Plus className=\"w-3.5 h-3.5\" />
              <span>{lang === 'bn' ? 'নতুন লিড' : 'New Lead'}</span>
            </button>

            {/* Quick Header Access to Users & Impersonation */}
            <button
              onClick={() => setActiveTab('users')}
              className={`px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border ${
                activeTab === 'users'
                  ? 'bg-orange-600 text-white border-orange-500 shadow-md shadow-orange-600/30'
                  : 'bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-750 hover:text-white'
              }`}
              title=\"ইউজার ম্যানেজমেন্ট ও অ্যাকাউন্ট সুইচিং\"
            >
              <UserCheck className=\"w-4 h-4 text-orange-400\" />
              <span className=\"hidden sm:inline\">{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users'}</span>
              <span className=\"px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-orange-500/20 text-orange-400 border border-orange-500/30\">
                {isSuperAdmin ? 'ADMIN' : 'USER'}
              </span>
            </button>"""
content = content.replace(old_header_btn, new_header_btn)

# 5. Make sidebar Administration group always visible with active styling
old_sidebar_admin = """          {/* Administration Group (Super Admin Only) */}
          {user?.role === 'super_admin' && (
            <div>
              <div className=\"px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400\">
                {lang === 'bn' ? 'প্ল্যাটফর্ম অ্যাডমিন' : 'SaaS Administration'}
              </div>
              <div className=\"space-y-1\">
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
                  <UserCheck className=\"w-4 h-4\" />
                  <span>{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users & Accounts'}</span>
                </button>
              </div>
            </div>
          )}"""

new_sidebar_admin = """          {/* Administration Group - Always Accessible */}
          <div>
            <div className=\"px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between\">
              <span>{lang === 'bn' ? 'ইউজার অ্যাডমিন' : 'Administration'}</span>
              <span className=\"text-[9px] px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-400 font-bold border border-orange-500/30\">
                {isSuperAdmin ? '👑 ADMIN' : 'ACCESS'}
              </span>
            </div>
            <div className=\"space-y-1\">
              <button
                onClick={() => {
                  setActiveTab('users');
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors ${
                  activeTab === 'users'
                    ? 'bg-orange-600 text-white font-bold shadow-md shadow-orange-600/20'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                }`}
              >
                <div className=\"flex items-center gap-2.5\">
                  <UserCheck className=\"w-4 h-4 text-orange-400\" />
                  <span>{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্ট সুইচিং' : 'Users & Impersonation'}</span>
                </div>
                <span className=\"text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 font-bold border border-slate-700\">
                  {usersList.length > 0 ? `${usersList.length} জন` : '৩ জন'}
                </span>
              </button>
            </div>
          </div>"""
content = content.replace(old_sidebar_admin, new_sidebar_admin)

# 6. Add User tab to mobile bottom navigation
old_mobile_share = """        <button
          onClick={() => {
            setSharePackageData(null);
            setShowShareModal(true);
          }}
          className=\"flex flex-col items-center gap-0.5 p-1.5 rounded-xl text-orange-400 hover:text-orange-300 transition-colors\"
        >
          <Share2 className=\"w-5 h-5\" />
          <span className=\"text-[10px] font-bold\">{lang === 'bn' ? 'শেয়ার' : 'Share'}</span>
        </button>"""

new_mobile_share = """        <button
          onClick={() => {
            setActiveTab('users');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }}
          className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
            activeTab === 'users' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
          }`}
        >
          <UserCheck className=\"w-5 h-5\" />
          <span className=\"text-[10px]\">{lang === 'bn' ? 'ইউজার' : 'Users'}</span>
        </button>

        <button
          onClick={() => {
            setSharePackageData(null);
            setShowShareModal(true);
          }}
          className=\"flex flex-col items-center gap-0.5 p-1.5 rounded-xl text-orange-400 hover:text-orange-300 transition-colors\"
        >
          <Share2 className=\"w-5 h-5\" />
          <span className=\"text-[10px] font-bold\">{lang === 'bn' ? 'শেয়ার' : 'Share'}</span>
        </button>"""
content = content.replace(old_mobile_share, new_mobile_share)

# 7. Update users tab action buttons in table and header
content = content.replace("{user?.role === 'super_admin' && (\n                  <button\n                    onClick={() => setShowAddUserModal(true)}", "{isSuperAdmin && (\n                  <button\n                    onClick={() => setShowAddUserModal(true)}")

old_action_cell = """                              <div className=\"flex items-center justify-end gap-2\">
                                {user?.role === 'super_admin' && u.id !== user?.id && (
                                  <button
                                    onClick={() => handleImpersonate(u)}
                                    className=\"px-2.5 py-1 bg-orange-600/20 hover:bg-orange-600/30 text-orange-400 border border-orange-500/30 rounded-lg text-xs font-bold transition-colors flex items-center gap-1\"
                                    title=\"Switch to this account\"
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
                                    className=\"p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors\"
                                    title=\"Delete user\"
                                  >
                                    <Trash2 className=\"w-3.5 h-3.5\" />
                                  </button>
                                )}
                              </div>"""

new_action_cell = """                              <div className=\"flex items-center justify-end gap-2\">
                                {u.id === user?.id ? (
                                  <span className=\"px-2.5 py-1 bg-slate-800 text-slate-400 border border-slate-700 rounded-lg text-[11px] font-bold\">
                                    {lang === 'bn' ? '✓ বর্তমান সক্রিয়' : 'Active Account'}
                                  </span>
                                ) : (
                                  <button
                                    onClick={() => handleImpersonate(u)}
                                    className=\"px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer\"
                                    title=\"এই ইউজারের অ্যাকাউন্টে প্রবেশ করুন\"
                                  >
                                    <LogIn className=\"w-3.5 h-3.5\" />
                                    <span>{lang === 'bn' ? 'লগিন করুন' : 'Login As'}</span>
                                  </button>
                                )}
                                {isSuperAdmin && u.id !== user?.id && u.role !== 'super_admin' && (
                                  <button
                                    onClick={async () => {
                                      if (confirm(lang === 'bn' ? `আপনি কি ইউজার "${u.name}" ডিলিট করতে চান?` : `Delete user "${u.name}"?`)) {
                                        await api.deleteUser(u.id);
                                        showToast(lang === 'bn' ? 'ইউজার ডিলিট করা হয়েছে' : 'User deleted');
                                        loadUsers();
                                      }
                                    }}
                                    className=\"p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors\"
                                    title=\"Delete user\"
                                  >
                                    <Trash2 className=\"w-3.5 h-3.5\" />
                                  </button>
                                )}
                              </div>"""
content = content.replace(old_action_cell, new_action_cell)

with open('src/client/App.tsx', 'w', encoding='utf-8') as f:
    f.write(content)

print('Updated App.tsx successfully!')
