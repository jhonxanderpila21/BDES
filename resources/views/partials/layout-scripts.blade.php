<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminLayout', () => ({
            sidebarOpen: true,
            sidebarCollapsed: localStorage.getItem('bdes_sidebar_collapsed') === 'true',
            sidebarWidth: Math.min(Math.max(parseInt(localStorage.getItem('bdes_sidebar_width')) || 256, 200), 480),
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
                    const newWidth = Math.min(Math.max(startWidth + (moveEvent.clientX - startX), 200), 480);
                    this.sidebarWidth = newWidth;
                    localStorage.setItem('bdes_sidebar_width', newWidth);
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
                this.sidebarWidth = 256;
                localStorage.setItem('bdes_sidebar_width', 256);
            },
            init() {
                window.addEventListener('resize', () => {
                    this.windowWidth = window.innerWidth;
                });
            }
        }));
    });
</script>
