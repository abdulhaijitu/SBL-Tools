with open('src/client/App.tsx', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Header Users button to be strictly Super Admin only
old_header = """            {/* Quick Header Access to Users & Impersonation */}
            <button
              onClick={() => setActiveTab('users')}
              className={`px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border ${
                activeTab === 'users'
                  ? 'bg-orange-600 text-white border-orange-500 shadow-md shadow-orange-600/30'
                  : 'bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-750 hover:text-white'
              }`}
              title="ইউজার ম্যানেজমেন্ট ও অ্যাকাউন্ট সুইচিং"
            >
              <UserCheck className="w-4 h-4 text-orange-400" />
              <span className="hidden sm:inline">{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users'}</span>
              <span className="px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-orange-500/20 text-orange-400 border border-orange-500/30">
                {isSuperAdmin ? 'ADMIN' : 'USER'}
              </span>
            </button>"""

new_header = """            {/* Quick Header Access to Users & Impersonation (Super Admin Only) */}
            {isSuperAdmin && (
              <button
                onClick={() => setActiveTab('users')}
                className={`px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border ${
                  activeTab === 'users'
                    ? 'bg-orange-600 text-white border-orange-500 shadow-md shadow-orange-600/30'
                    : 'bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-750 hover:text-white'
                }`}
                title="ইউজার ম্যানেজমেন্ট ও অ্যাকাউন্ট সুইচিং"
              >
                <UserCheck className="w-4 h-4 text-orange-400" />
                <span className="hidden sm:inline">{lang === 'bn' ? 'ইউজার ও অ্যাকাউন্টস' : 'Users & Accounts'}</span>
                <span className="px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-orange-500/20 text-orange-400 border border-orange-500/30">
                  ADMIN
                </span>
              </button>
            )}"""
content = content.replace(old_header, new_header)

# 2. Update Sidebar Administration section to be strictly Super Admin only
old_sidebar = """          {/* Administration Group - Always Accessible */}
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

new_sidebar = """          {/* Administration Group (Super Admin Only) */}
          {isSuperAdmin && (
            <div>
              <div className=\"px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between\">
                <span>{lang === 'bn' ? 'ইউজার ও প্ল্যাটফর্ম অ্যাডমিন' : 'Administration'}</span>
                <span className=\"text-[9px] px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-400 font-bold border border-orange-500/30\">
                  👑 SUPER ADMIN
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
            </div>
          )}"""
content = content.replace(old_sidebar, new_sidebar)

# 3. Update Mobile bottom navigation Users button to be strictly Super Admin only
old_mobile = """        <button
          onClick={() => {
            setActiveTab('users');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }}
          className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
            activeTab === 'users' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
          }`}
        >
          <UserCheck className="w-5 h-5" />
          <span className="text-[10px]">{lang === 'bn' ? 'ইউজার' : 'Users'}</span>
        </button>"""

new_mobile = """        {isSuperAdmin && (
          <button
            onClick={() => {
              setActiveTab('users');
              window.scrollTo({ top: 0, behavior: 'smooth' });
            }}
            className={`flex flex-col items-center gap-0.5 p-1.5 rounded-xl transition-colors ${
              activeTab === 'users' ? 'text-orange-500 font-bold' : 'text-slate-400 hover:text-white'
            }`}
          >
            <UserCheck className="w-5 h-5" />
            <span className="text-[10px]">{lang === 'bn' ? 'ইউজার' : 'Users'}</span>
          </button>
        )}"""
content = content.replace(old_mobile, new_mobile)

# 4. Tab loader effect guard
old_effect = """    } else if (activeTab === 'users') {
      loadUsers();
    }"""

new_effect = """    } else if (activeTab === 'users') {
      if (isSuperAdmin) {
        loadUsers();
      } else {
        setActiveTab('dashboard');
      }
    }"""
content = content.replace(old_effect, new_effect)

# 5. Tab 12 gate
old_tab_12 = """          {/* TAB 12: USERS & ACCOUNTS */}
          {activeTab === 'users' && ("""

new_tab_12 = """          {/* TAB 12: USERS & ACCOUNTS (SUPER ADMIN ONLY) */}
          {activeTab === 'users' && isSuperAdmin && ("""
content = content.replace(old_tab_12, new_tab_12)

with open('src/client/App.tsx', 'w', encoding='utf-8') as f:
    f.write(content)

print('Updated App.tsx successfully!')

