import { registerAbbreviations } from './abbreviations';
import './bootstrap';
import './toolkit-analytics';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { registerShell } from './shell';
import { enhanceAccessibility } from './accessibility';

window.Alpine = Alpine;
window.Sortable = Sortable;

registerAbbreviations(Alpine);
registerShell(Alpine);
Alpine.start();
enhanceAccessibility(Alpine);
