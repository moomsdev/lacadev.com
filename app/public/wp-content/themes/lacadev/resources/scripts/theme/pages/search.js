/**
 * Search Results - Load More Functionality
 */

document.addEventListener('DOMContentLoaded', () => {
  const loadMoreButtons = document.querySelectorAll('.load-more-btn');

  if (!loadMoreButtons.length) {
    return;
  }

  loadMoreButtons.forEach((button) => {
    button.addEventListener('click', handleLoadMore);
  });

  async function handleLoadMore(e) {
    e.preventDefault();

    const button = e.currentTarget;
    const postType = button.dataset.postType;
    const searchQuery = button.dataset.search;
    let currentPage = parseInt(button.dataset.page, 10);

    // Disable button and show loading state
    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Đang tải...';
    button.classList.add('loading');

    try {
      const formData = new FormData();
      formData.append('action', 'load_more_search');
      formData.append('nonce', window.themeSearch.nonce);
      formData.append('post_type', postType);
      formData.append('search', searchQuery);
      formData.append('paged', currentPage + 1);

      const response = await fetch(window.themeSearch.ajaxurl, {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        // Find the results container for this post type
        const section = button.closest('.search-section');
        const resultsContainer = section.querySelector('.list-post');

        // Append new posts
        if (resultsContainer) {
          resultsContainer.insertAdjacentHTML('beforeend', data.data.html);
        }

        // Update button page number
        button.dataset.page = data.data.next_page;

        // Hide button if no more posts
        if (!data.data.has_more) {
          button.style.display = 'none';
        }

        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
        button.classList.remove('loading');
      } else {
        // Error or no more posts
        button.style.display = 'none';
        console.error('Load more error:', data.data.message);
      }
    } catch (error) {
      console.error('Load more failed:', error);
      button.disabled = false;
      button.textContent = originalText;
      button.classList.remove('loading');
    }
  }
});
