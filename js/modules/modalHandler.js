export function initializeModalHandlers() {
  // Close modal when clicking the close button or outside
  const commentModal = document.getElementById('commentModal');
  const editModal = document.getElementById('editModal');
  const userStatsModal = document.querySelector('.user-stats-modal');

  // Comment modal close handlers
  document.querySelector('#commentModal .close-modal')?.addEventListener('click', () => {
    commentModal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  // Edit modal close handlers
  document.querySelector('#editModal .close-modal')?.addEventListener('click', () => {
    editModal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  // User stats modal close handlers
  document.querySelector('.close-stats-modal')?.addEventListener('click', () => {
    userStatsModal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  // Global click handler for modals
  window.onclick = function (event) {
    if (event.target === commentModal || event.target === editModal || event.target === userStatsModal) {
      event.target.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  };
}
