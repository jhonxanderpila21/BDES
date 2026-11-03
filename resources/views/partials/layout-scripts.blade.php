<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminLayout', () => ({
            sidebarOpen: window.innerWidth >= 1024,
            sidebarWidth: Math.min(Math.max(parseInt(localStorage.getItem('bdes_sidebar_width')) || 256, 200), 480),
            windowWidth: window.innerWidth,
            isResizing: false,
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
                    if (this.windowWidth < 1024 && this.sidebarOpen && !this.isResizing) {
                        this.sidebarOpen = false;
                    }
                });
            }
        }));
    });
</script>

