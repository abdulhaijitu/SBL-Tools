import re

with open("src/client/App.tsx", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Add UserPlus to imports if not present
if "UserPlus," not in content:
    content = content.replace("Users,", "Users,\n  UserPlus,")

# 2. Add tree and user management state after tree states (line 139)
old_tree_state = """  const [newNode, setNewNode] = useState({
    placementPosition: 1,
    memberName: '',
    phone: '',
    sponsorName: '',
    password: 'password123',
    tpin: '1234',
    packageName: 'National Growth',
  });"""

new_tree_state = """  const [newNode, setNewNode] = useState({
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
  });"""

if old_tree_state in content:
    content = content.replace(old_tree_state, new_tree_state)
    print("Replaced tree state successfully")
else:
    print("Warning: old_tree_state not found verbatim")

with open("src/client/App.tsx", "w", encoding="utf-8") as f:
    f.write(content)

print("Saved stage 1")

