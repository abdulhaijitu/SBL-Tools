import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { registerShell } from './shell';
import { enhanceAccessibility } from './accessibility';

window.Alpine = Alpine;
window.Sortable = Sortable;

registerShell(Alpine);
Alpine.start();
enhanceAccessibility(Alpine);
