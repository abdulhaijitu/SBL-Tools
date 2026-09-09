<section class="rounded-2xl border border-slate-200 bg-white overflow-hidden" aria-label="Team mind map"
    x-data="{
        zoom: .85, 
        observer: null, 
        dragging: false, 
        origin: null,
        wheelMode: 'scroll', // 'scroll' (standard up/down, shift: left/right, ctrl: zoom) or 'zoom' (direct wheel zoom)
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
        resetZoom() {
            this.zoom = 1;
            this.$nextTick(() => { this.draw(); this.focusRoot(); });
        },
        setZoom(value) { 
            this.zoom = Math.max(.3, Math.min(1.8, Math.round(value * 100) / 100)); 
            this.$nextTick(() => this.draw()); 
        },
        pan(dx, dy) {
            const vp = this.$refs.viewport;
            if (vp) {
                vp.scrollBy({ left: dx, top: dy, behavior: 'smooth' });
            }
        },
        handleWheel(e) {
            const viewport = this.$refs.viewport;
            if (!viewport) return;

            // Zoom handling (Ctrl/Meta held, or 'zoom' mode selected)
            if (e.ctrlKey || e.metaKey || this.wheelMode === 'zoom') {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.08 : -0.08;
                const oldZoom = this.zoom;
                const newZoom = Math.max(0.3, Math.min(1.8, Math.round((this.zoom + delta) * 100) / 100));

                if (newZoom !== oldZoom) {
                    const rect = viewport.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left;
                    const mouseY = e.clientY - rect.top;

                    const contentX = (viewport.scrollLeft + mouseX) / oldZoom;
                    const contentY = (viewport.scrollTop + mouseY) / oldZoom;

                    this.zoom = newZoom;
                    this.$nextTick(() => {
                        this.draw();
                        viewport.scrollLeft = (contentX * newZoom) - mouseX;
                        viewport.scrollTop = (contentY * newZoom) - mouseY;
                    });
                }
                return;
            }

            // Horizontal scrolling (Shift key held or trackpad deltaX)
            if (e.shiftKey || (Math.abs(e.deltaX) > Math.abs(e.deltaY) && Math.abs(e.deltaX) > 0)) {
                e.preventDefault();
                const delta = e.shiftKey ? (e.deltaY || e.deltaX) : e.deltaX;
                viewport.scrollLeft += delta;
                return;
            }

            // Standard Vertical scrolling (Mouse wheel up/down)
            e.preventDefault();
            viewport.scrollTop += e.deltaY;
        },
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
        <div>
            <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                <span>Member network</span>
                <span class="text-[11px] font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">Right: Above · Left: Below</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Scroll or drag to navigate. Select any member to open card details.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
            <!-- D-Pad Directional Pan Buttons (Up-Down, Left-Right) -->
            <div class="flex items-center bg-slate-100/80 p-1 rounded-xl border border-slate-200 gap-0.5" title="Pan in direction">
                <button type="button" @click="pan(-180, 0)" title="Pan Left" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white active:scale-90 text-slate-700 transition-all font-bold">←</button>
                <button type="button" @click="pan(0, -140)" title="Pan Up" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white active:scale-90 text-slate-700 transition-all font-bold">↑</button>
                <button type="button" @click="pan(0, 140)" title="Pan Down" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white active:scale-90 text-slate-700 transition-all font-bold">↓</button>
                <button type="button" @click="pan(180, 0)" title="Pan Right" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white active:scale-90 text-slate-700 transition-all font-bold">→</button>
                <button type="button" @click="focusRoot()" title="Center to Root Member" class="px-1.5 h-7 flex items-center justify-center rounded-lg hover:bg-white active:scale-90 text-[11px] text-orange-700 font-bold transition-all">🎯 Center</button>
            </div>

            <!-- Mouse Wheel Mode Toggle -->
            <div class="flex items-center bg-slate-100/80 p-1 rounded-xl border border-slate-200 text-[11px]" title="Mouse wheel scrolling behavior">
                <button type="button" 
                        @click="wheelMode = 'scroll'" 
                        :class="wheelMode === 'scroll' ? 'bg-white shadow-xs font-bold text-slate-900' : 'text-slate-500 hover:text-slate-800'"
                        class="px-2.5 py-1 rounded-lg transition-all">
                    🖱️ Scroll (Up/Down)
                </button>
                <button type="button" 
                        @click="wheelMode = 'zoom'" 
                        :class="wheelMode === 'zoom' ? 'bg-orange-600 shadow-xs font-bold text-white' : 'text-slate-500 hover:text-slate-800'"
                        class="px-2.5 py-1 rounded-lg transition-all">
                    🔍 Direct Zoom
                </button>
            </div>

            <!-- Zoom Controls -->
            <div class="flex items-center gap-1">
                <button type="button" aria-label="Zoom out" @click="setZoom(zoom - .1)" class="min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xl border border-slate-200 hover:bg-slate-50 active:scale-95 transition-all text-sm font-bold text-slate-700">−</button>
                <span class="w-12 text-center font-mono text-slate-700" x-text="Math.round(zoom * 100) + '%'"></span>
                <button type="button" aria-label="Zoom in" @click="setZoom(zoom + .1)" class="min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xl border border-slate-200 hover:bg-slate-50 active:scale-95 transition-all text-sm font-bold text-slate-700">+</button>
                <button type="button" @click="resetZoom()" title="Reset to 100%" class="min-h-[34px] px-2.5 flex items-center justify-center rounded-xl border border-slate-200 hover:bg-slate-50 active:scale-95 transition-all text-[11px] text-slate-600 font-semibold">100%</button>
                <button type="button" @click="fit()" class="min-h-[34px] px-3 flex items-center justify-center rounded-xl border border-slate-200 hover:bg-slate-50 active:scale-95 transition-all text-[11px] text-slate-700">Fit view</button>
            </div>
        </div>
    </div>
    <div x-ref="viewport" class="mindmap-viewport" tabindex="0" aria-label="Scrollable team tree; mouse wheel to scroll/zoom; drag background to pan"
        @wheel="handleWheel($event)"
        @pointerdown="if (!$event.target.closest('button, a, input, textarea')) { dragging = true; origin = {x: $event.clientX, y: $event.clientY, left: $el.scrollLeft, top: $el.scrollTop}; $el.setPointerCapture($event.pointerId); }"
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
    <div class="border-t border-slate-200 px-5 py-2.5 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
        <div class="flex items-center gap-3 text-[11px] text-slate-600">
            <span class="font-semibold text-slate-800">🖱️ Mouse & Scroll Shortcuts:</span>
            <span><strong>Wheel:</strong> Up-Down</span>
            <span>•</span>
            <span><strong>Shift + Wheel:</strong> Left-Right</span>
            <span>•</span>
            <span><strong>Ctrl + Wheel:</strong> Zoom In/Out</span>
            <span>•</span>
            <span><strong>Click & Drag:</strong> Free Pan</span>
        </div>
        <div class="text-[11px] text-slate-400">
            Click <strong>View team</strong> on any member card to view deeper generations.
        </div>
    </div>
</section>
<style>
.mindmap-viewport{height:680px;overflow:auto;touch-action:pan-x pan-y;background-color:#fafafa;background-image:radial-gradient(circle,#b8bcc2 1px,transparent 1px);background-size:18px 18px;cursor:grab;overscroll-behavior:contain}
.mindmap-viewport:active{cursor:grabbing}
.mindmap-board{position:relative;width:max-content;min-width:900px;min-height:600px;padding:48px 60px;margin-inline:auto;isolation:isolate}
.mindmap-connections{position:absolute;inset:0;pointer-events:none;z-index:-1}
.mindmap-row,.mindmap-branch{display:flex;align-items:center;gap:65px}
.mindmap-sponsor{width:130px;flex-shrink:0;font-size:12px;font-weight:600;color:#333}
.mindmap-sponsor span{display:block;font-size:10px;font-weight:400;color:#777;margin-top:4px}
.mindmap-member{width:195px;flex-shrink:0;padding:8px 0;color:#303030;font-size:11px;line-height:1.5;overflow-wrap:anywhere}
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
