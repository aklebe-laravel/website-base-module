/**
 * Unified Popover + Dropdown Handler
 * Supports: mouse-follow popover & anchored dropdown
 */
export default function popoverHandler(mode = 'popover') {  // 'popover' | 'dropdown'
    return {
        mode: mode,
        ticking: false,
        listenersAttached: false,

        init() {
            const el = this.$el;
            const trigger = el.parentElement?.querySelector('[data-trigger]') || el.parentElement;

            if (!trigger) return;

            this.trigger = trigger;
            this.isDropdown = this.mode === 'dropdown';

            this._show = (e) => {
                if (window.matchMedia('(max-width: 1023px)').matches) return;

                el.showPopover();
                this.updatePosition(e);
            };

            this._hide = () => {
                if (el.isConnected) el.hidePopover();
            };

            this._move = (e) => {
                if (this.isDropdown || window.matchMedia('(max-width: 1023px)').matches) return;
                if (!this.ticking) {
                    requestAnimationFrame(() => {
                        this.updatePosition(e);
                        this.ticking = false;
                    });
                    this.ticking = true;
                }
            };

            this.attachListeners();
        },

        attachListeners() {
            if (this.listenersAttached) return;

            if (this.isDropdown) {
                // Dropdown: Hover mit Bridge + Click Outside
                this.trigger.addEventListener('mouseenter', this._show);
                this.trigger.addEventListener('mouseleave', this._hide);
                this.$el.addEventListener('mouseenter', this._show);
                this.$el.addEventListener('mouseleave', this._hide);
            } else {
                // Classic Popover: Mouse Follow
                this.trigger.addEventListener('mouseenter', this._show);
                this.trigger.addEventListener('mousemove', this._move);
                this.trigger.addEventListener('mouseleave', this._hide);
            }

            this.listenersAttached = true;
        },

        updatePosition(e) {
            const el = this.$el;
            const rect = this.trigger.getBoundingClientRect();

            if (this.isDropdown) {
                // Statisches Dropdown unter dem Trigger
                el.style.left = `${rect.left}px`;
                el.style.top = `${rect.bottom + 8}px`;
            } else {
                // Mouse-Follow Popover
                const offset = 20;
                let left = e.clientX + offset;
                let top = e.clientY + offset;

                const screenPadding = 8;
                if (left + el.offsetWidth > window.innerWidth - screenPadding) {
                    left = window.innerWidth - el.offsetWidth - screenPadding;
                }
                if (top + el.offsetHeight > window.innerHeight - screenPadding) {
                    top = window.innerHeight - el.offsetHeight - screenPadding;
                }

                el.style.left = `${left}px`;
                el.style.top = `${top}px`;
            }
        },

        destroy() {
            // Cleanup
        }
    };
}