/**
 * Custom Post Order - Vanilla JavaScript
 * Drag & Drop ordering for posts, pages, and taxonomies
 *
 * @package LacaDev
 * @since 1.0.0
 */

class PostOrderDragDrop {
  constructor() {
    // Check if lacaPostOrder is defined (only defined when post type is enabled)
    if (typeof lacaPostOrder === 'undefined') {
      console.log('PostOrder: Not enabled for this post type');
      return;
    }

    this.table = document.querySelector('.wp-list-table tbody');
    this.isTermPage = document.body.classList.contains('edit-tags-php');

    if (!this.table) return;

    this.init();
  }

  init() {
    // Make table rows draggable
    this.makeSortable();
  }

  makeSortable() {
    const rows = this.table.querySelectorAll('tr');

    rows.forEach((row) => {
      row.setAttribute('draggable', 'true');
      row.style.cursor = 'move';

      row.addEventListener('dragstart', e => this.handleDragStart(e));
      row.addEventListener('dragover', e => this.handleDragOver(e));
      row.addEventListener('drop', e => this.handleDrop(e));
      row.addEventListener('dragend', e => this.handleDragEnd(e));
    });
  }

  handleDragStart(e) {
    this.draggedElement = e.target.closest('tr');
    this.draggedElement.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.draggedElement.innerHTML);
  }

  handleDragOver(e) {
    if (e.preventDefault) {
      e.preventDefault();
    }

    e.dataTransfer.dropEffect = 'move';

    const targetRow = e.target.closest('tr');
    if (targetRow && targetRow !== this.draggedElement) {
      const rect = targetRow.getBoundingClientRect();
      const midpoint = rect.top + rect.height / 2;

      if (e.clientY < midpoint) {
        targetRow.parentNode.insertBefore(this.draggedElement, targetRow);
      } else {
        targetRow.parentNode.insertBefore(this.draggedElement, targetRow.nextSibling);
      }
    }

    return false;
  }

  handleDrop(e) {
    if (e.stopPropagation) {
      e.stopPropagation();
    }

    this.updateOrder();
    return false;
  }

  handleDragEnd(e) {
    this.draggedElement.classList.remove('dragging');

    // Remove all drag-over classes
    const rows = this.table.querySelectorAll('tr');
    rows.forEach(row => row.classList.remove('drag-over'));
  }

  updateOrder() {
    const rows = this.table.querySelectorAll('tr');
    const order = [];

    rows.forEach((row, index) => {
      const id = row.id.replace('post-', '').replace('tag-', '');
      if (id) {
        order.push(`post[]=${id}`);
      }
    });

    const orderString = order.join('&');
    const action = this.isTermPage ? 'update_term_order' : 'update_post_order';

    this.sendAjaxRequest(action, orderString);
  }

  sendAjaxRequest(action, order) {
    // Debug: Check if lacaPostOrder is defined
    if (typeof lacaPostOrder === 'undefined') {
      console.error('lacaPostOrder is not defined! Script may not be enqueued correctly.');
      return;
    }

    console.log('Sending AJAX request:', {
      action,
      order,
      ajaxUrl: lacaPostOrder.ajaxUrl,
    });

    const formData = new FormData();
    formData.append('action', action);
    formData.append('order', order);
    formData.append('nonce', lacaPostOrder.nonce);

    fetch(lacaPostOrder.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    })
      .then((response) => {
        console.log('Response status:', response.status);
        return response.text();
      })
      .then((data) => {
        console.log('Response data:', data);
        if (data === 'success') {
          console.log('✅ Order updated successfully');
        } else {
          console.warn('⚠️ Unexpected response:', data);
        }
      })
      .catch((error) => {
        console.error('❌ Error updating order:', error);
      });
  }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    new PostOrderDragDrop();
  });
} else {
  new PostOrderDragDrop();
}
