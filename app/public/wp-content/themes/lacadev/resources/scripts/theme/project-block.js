/**
 * Laca Project Block Frontend Logic
 */
const initProjectBlock = () => {
    const blocks = document.querySelectorAll('.laca-project-block');

    blocks.forEach(block => {
        const tabs = block.querySelectorAll('.tab-item');
        const grid = block.querySelector('.laca-project-block__grid');
        const loader = block.querySelector('.laca-skeleton-loader');
        
        if (!tabs.length || !grid) return;

        const orderBy = block.dataset.orderBy || 'date';
        const countDesktop = block.dataset.countDesktop || 6;
        const countMobile = block.dataset.countMobile || 4;
        const categoryIds = block.dataset.categoryIds || ''; // Comma separated selected IDs

        tabs.forEach(tab => {
            tab.addEventListener('click', async () => {
                if (tab.classList.contains('is-active')) return;

                const categoryId = tab.dataset.category;

                // Update UI state
                tabs.forEach(t => t.classList.remove('is-active'));
                tab.classList.add('is-active');

                // Show loader
                if (loader) {
                    loader.classList.add('is-loading');
                }
                grid.style.opacity = '0.3';

                try {
                    // Use theme localized ajaxurl if available, else fallback
                    const ajaxUrl = typeof themeData !== 'undefined' ? themeData.ajaxurl : '/wp-admin/admin-ajax.php';
                    
                    const formData = new FormData();
                    formData.append('action', 'laca_filter_projects');
                    formData.append('category', categoryId);
                    formData.append('allowed_categories', categoryIds); // Pass all selected to handle "All" tab restriction
                    formData.append('order_by', orderBy);
                    formData.append('count_desktop', countDesktop);
                    formData.append('count_mobile', countMobile);

                    const response = await fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();

                    if (result.success && result.data.html) {
                        grid.innerHTML = result.data.html;
                        
                        // Re-initialize cursor events
                        if (typeof window.initCustomCursor === 'function') {
                            window.initCustomCursor();
                        }
                    } else {
                        grid.innerHTML = '<p>No projects found.</p>';
                    }
                } catch (error) {
                    console.error('Error filtering projects:', error);
                } finally {
                    if (loader) {
                        loader.classList.remove('is-loading');
                    }
                    grid.style.opacity = '1';
                }
            });
        });
    });
};

// Initialize on load
document.addEventListener('DOMContentLoaded', initProjectBlock);

// For Swup navigation
window.addEventListener('load', () => {
    if (window.swup) {
        window.swup.hooks.on('content:replace', initProjectBlock);
    }
    // Theme might use Swup hook differently or expose a global
    if (typeof Swup !== 'undefined') {
        // Handle potential existing swup instance logic from index.js
    }
});

// Fallback init
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initProjectBlock();
}
