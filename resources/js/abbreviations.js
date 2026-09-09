export function registerAbbreviations(Alpine) {
    Alpine.data('abbreviationManager', () => ({
        terms: [], canManage: false, category: 'all', search: '', editing: false, busy: false, error: '', form: {},
        init() {
            this.terms = JSON.parse(this.$el.dataset.terms);
            this.canManage = this.$el.dataset.canManage === '1';
        },
        count(category) { return this.terms.filter(t => category === 'all' || t.category_slug === category).length; },
        get filtered() {
            const query = this.search.trim().toLowerCase();
            return this.terms.filter(t => (this.category === 'all' || t.category_slug === this.category) &&
                ['code', 'name', 'category', 'meaning_bn', 'description_bn', 'tag'].some(k => String(t[k] || '').toLowerCase().includes(query)));
        },
        edit(term) {
            this.form = term ? {...term} : {code: '', name: '', category_slug: 'ecommerce', meaning_bn: '', description_bn: '', icon: '', tag: ''};
            this.error = ''; this.editing = true;
            this.$nextTick(() => this.$refs.editor.querySelector('input').focus());
        },
        async request(path, method, body) {
            const response = await fetch(path, {method, headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''}, body: body ? JSON.stringify(body) : undefined});
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : data.message || 'Unable to save changes. Please try again.');
            }
            return response.status === 204 ? null : response.json();
        },
        async save() {
            if (this.busy) return;
            this.busy = true; this.error = '';
            try {
                const term = await this.request('/abbreviations' + (this.form.id ? '/' + this.form.id : ''), this.form.id ? 'PUT' : 'POST', this.form);
                const index = this.terms.findIndex(t => t.id === term.id);
                if (index < 0) this.terms.push(term); else this.terms.splice(index, 1, term);
                this.editing = false; this.category = 'all'; this.search = '';
                this.$dispatch('notify', {message: 'Abbreviation saved.'});
            } catch (e) { this.error = e.message; } finally { this.busy = false; }
        },
        async remove(term) {
            if (this.busy || !confirm('Delete ' + term.code + '? This cannot be undone.')) return;
            this.busy = true; this.error = '';
            try {
                await this.request('/abbreviations/' + term.id, 'DELETE');
                this.terms = this.terms.filter(t => t.id !== term.id);
                if (this.form.id === term.id) this.editing = false;
                this.$dispatch('notify', {message: 'Abbreviation deleted.'});
            } catch (e) { this.error = e.message; } finally { this.busy = false; }
        },
        async copy(text) {
            try { await navigator.clipboard.writeText(text); this.$dispatch('notify', {message: 'Copied to clipboard.'}); }
            catch { this.error = 'Could not copy. Please copy the text manually.'; }
        }
    }));
}
