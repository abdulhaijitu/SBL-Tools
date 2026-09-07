<section class="rounded-2xl border border-slate-200 bg-white overflow-hidden" aria-label="Team mind map"
    x-data="{
        zoom: .85, observer: null, dragging: false, origin: null,
        init() {
            this.observer = new ResizeObserver(() => this.draw());
            this.observer.observe(this.$refs.board);
            this.$nextTick(() => { this.draw(); this.focusRoot(); });
        },
        destroy() { this.observer?.disconnect(); },
        focusRoot() {
            const root = this.$refs.board.querySelector('.mindmap-member.is-root');
            if (!root) return;
            const viewport = this.$refs.viewport, box = viewport.getBoundingClientRect(), member = root.getBoundingClientRect();
            viewport.scrollLeft += member.left - box.left - 24;
            viewport.scrollTop += member.top - box.top - (viewport.clientWidth < 640 ? 48 : (viewport.clientHeight - member.height) / 2);
        },
        fit() {
            this.zoom = Math.max(.3, Math.min(1, (this.$refs.viewport.clientWidth - 40) / this.$refs.board.offsetWidth, 620 / this.$refs.board.offsetHeight));
            this.$nextTick(() => this.draw());
        },
        setZoom(value) { this.zoom = Math.max(.3, Math.min(1.5, value)); this.$nextTick(() => this.draw()); },
        draw() {
            const board = this.$refs.board, canvas = this.$refs.connections;
            if (!board || !canvas) return;
            canvas.width = board.offsetWidth; canvas.height = board.offsetHeight;
            const ctx = canvas.getContext('2d'), bounds = board.getBoundingClientRect();
            ctx.strokeStyle = '#939393'; ctx.lineWidth = 1.6;
            const anchors = [...board.querySelectorAll('[data-map-anchor]')];
            const byId = new Map(anchors.map(el => [el.dataset.mapAnchor, el]));
            for (const child of anchors) {
                const parent = byId.get(child.dataset.mapParent);
                if (!parent || !child.getClientRects().length || !parent.getClientRects().length) continue;
                const a = parent.getBoundingClientRect(), b = child.getBoundingClientRect();
                const x1 = (a.right - bounds.left) / this.zoom, y1 = (a.top + a.height / 2 - bounds.top) / this.zoom;
                const x2 = (b.left - bounds.left) / this.zoom, y2 = (b.top + b.height / 2 - bounds.top) / this.zoom;
                const bend = Math.max(24, (x2 - x1) * .55);
                ctx.beginPath(); ctx.moveTo(x1, y1); ctx.bezierCurveTo(x1 + bend, y1, x2 - bend, y2, x2, y2); ctx.stroke();
            }
        }
    }">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-3">
        <div><h2 class="font-semibold text-slate-900">Member network</h2><p class="text-xs text-slate-500">Right Side above · Left Side below · Select a member for details</p></div>
        <div class="flex items-center gap-2 text-xs font-semibold">
            <button type="button" aria-label="Zoom out" @click="setZoom(zoom - .1)" class="rounded-lg border border-slate-200 px-3 py-2">−</button>
            <span class="w-12 text-center" x-text="Math.round(zoom * 100) + '%'"></span>
            <button type="button" aria-label="Zoom in" @click="setZoom(zoom + .1)" class="rounded-lg border border-slate-200 px-3 py-2">+</button>
            <button type="button" @click="fit()" class="rounded-lg border border-slate-200 px-3 py-2">Fit to view</button>
        </div>
    </div>
    <div x-ref="viewport" class="mindmap-viewport" tabindex="0" aria-label="Scrollable team tree; drag background to pan"
        @pointerdown="if (!$event.target.closest('button, a')) { dragging = true; origin = {x: $event.clientX, y: $event.clientY, left: $el.scrollLeft, top: $el.scrollTop}; $el.setPointerCapture($event.pointerId); }"
        @pointermove="if (dragging) { $el.scrollLeft = origin.left - ($event.clientX - origin.x); $el.scrollTop = origin.top - ($event.clientY - origin.y); }"
        @pointerup="dragging = false" @pointercancel="dragging = false">
        <div class="mindmap-board" x-ref="board" :style="{zoom: zoom}">
            <canvas x-ref="connections" class="mindmap-connections" aria-hidden="true"></canvas>
            @if(!empty($treeData['tree']))
                <div class="mindmap-row">
                    <div class="mindmap-sponsor" data-map-anchor="sponsor">{{ $treeData['stats']['sponsor_name'] ?? 'Sponsor' }}<span>Sponsor / Upline</span></div>
                    @include('binary.partials.mindmap-node', ['node' => $treeData['tree'], 'mapParent' => 'sponsor', 'isRoot' => true])
                </div>
            @else
                <p class="p-10 text-slate-500">No members in this team yet.</p>
            @endif
        </div>
    </div>
    <div class="border-t border-slate-200 px-5 py-3 text-xs text-slate-500">Drag the background or scroll to explore. Use View team to continue into deeper generations.</div>
</section>
<style>
.mindmap-viewport{height:680px;overflow:auto;touch-action:pan-x pan-y;background-color:#fafafa;background-image:radial-gradient(circle,#b8bcc2 1px,transparent 1px);background-size:18px 18px;cursor:grab;overscroll-behavior:contain}
.mindmap-viewport:active{cursor:grabbing}
.mindmap-board{position:relative;width:max-content;min-width:900px;min-height:600px;padding:48px 60px;margin-inline:auto;isolation:isolate}
.mindmap-connections{position:absolute;inset:0;pointer-events:none;z-index:-1}
.mindmap-row,.mindmap-branch{display:flex;align-items:center;gap:65px}
.mindmap-sponsor{width:130px;flex-shrink:0;font-size:12px;font-weight:600;color:#333}
.mindmap-sponsor span{display:block;font-size:10px;font-weight:400;color:#777;margin-top:4px}
.mindmap-member{width:178px;flex-shrink:0;padding:8px 0;color:#303030;font-size:11px;line-height:1.5;overflow-wrap:anywhere}
.mindmap-member.is-root{color:#ea580c}
.mindmap-name{display:block;font-weight:700;font-size:14px;text-align:left;line-height:1.4;cursor:pointer}
.mindmap-name:hover{text-decoration:underline}
.mindmap-actions{display:flex;gap:12px;margin-top:6px;font-size:10px;color:#78716c}
.mindmap-actions button,.mindmap-actions a{cursor:pointer;text-decoration:underline;text-underline-offset:3px}
.mindmap-branches{display:flex;flex-direction:column;gap:28px}
.mindmap-branch-label{flex-shrink:0;width:80px;font-size:12px;font-weight:600;color:#404040}
.mindmap-children{display:flex;flex-direction:column;gap:12px}
.mindmap-vacant{width:178px;text-align:left;color:#737373;font-size:11px;line-height:1.4;padding:5px 0;cursor:pointer}
.mindmap-vacant:hover{color:#ea580c}
.mindmap-member button:focus-visible,.mindmap-vacant:focus-visible,.mindmap-actions a:focus-visible{outline:2px solid #ea580c;outline-offset:4px}
@media(max-width:640px){.mindmap-viewport{height:500px}.mindmap-board{padding-bottom:360px}}
</style>
