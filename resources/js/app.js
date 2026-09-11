import { registerAbbreviations } from "./abbreviations";
import { registerContacts } from "./contacts";
import { registerUsers } from "./users";
import { registerRoles } from "./roles";
import "./bootstrap";
import "./toolkit-analytics";

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import Sortable from "sortablejs";
import { registerShell } from "./shell";
import { registerSearch } from "./search";
import { enhanceAccessibility } from "./accessibility";

Alpine.plugin(collapse);
window.Alpine = Alpine;
window.Sortable = Sortable;

registerAbbreviations(Alpine);
registerContacts(Alpine);
registerUsers(Alpine);
registerRoles(Alpine);
registerShell(Alpine);
registerSearch(Alpine);
Alpine.start();
enhanceAccessibility(Alpine);
