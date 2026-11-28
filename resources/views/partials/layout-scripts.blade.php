<script>
    document.addEventListener('alpine:init', () => {
        const computeProportionalSidebarWidth = () => {
            return Math.round(Math.min(Math.max(window.innerWidth * 0.16, 185), 256));
        };

        Alpine.data('adminLayout', () => ({
            sidebarOpen: true,
            sidebarCollapsed: localStorage.getItem('bdes_sidebar_collapsed') === 'true',
            sidebarWidth: parseInt(localStorage.getItem('bdes_sidebar_width_custom')) || computeProportionalSidebarWidth(),
            windowWidth: window.innerWidth,
            isResizing: false,
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                this.sidebarOpen = true;
                localStorage.setItem('bdes_sidebar_collapsed', this.sidebarCollapsed ? 'true' : 'false');
            },
            expandSidebar() {
                if (this.sidebarCollapsed) {
                    this.sidebarCollapsed = false;
                    localStorage.setItem('bdes_sidebar_collapsed', 'false');
                }
            },
            startResize(e) {
                e.preventDefault();
                this.isResizing = true;
                const startX = e.clientX;
                const startWidth = this.sidebarWidth;
                const doResize = (moveEvent) => {
                    if (!this.isResizing) return;
                    const newWidth = Math.min(Math.max(startWidth + (moveEvent.clientX - startX), 185), 480);
                    this.sidebarWidth = newWidth;
                    localStorage.setItem('bdes_sidebar_width_custom', newWidth);
                };
                const stopResize = () => {
                    this.isResizing = false;
                    window.removeEventListener('mousemove', doResize);
                    window.removeEventListener('mouseup', stopResize);
                };
                window.addEventListener('mousemove', doResize);
                window.addEventListener('mouseup', stopResize);
            },
            resetSidebarWidth() {
                localStorage.removeItem('bdes_sidebar_width_custom');
                this.sidebarWidth = computeProportionalSidebarWidth();
            },
            init() {
                window.addEventListener('resize', () => {
                    this.windowWidth = window.innerWidth;
                    if (!localStorage.getItem('bdes_sidebar_width_custom')) {
                        this.sidebarWidth = computeProportionalSidebarWidth();
                    }
                });
            }
        }));
    });
</script>
