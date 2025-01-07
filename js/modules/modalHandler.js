export function initializeModalHandlers() {
  const commentModal = document.getElementById('commentModal');
  const editModal = document.getElementById('editModal');
  const userStatsModal = document.querySelector('.user-stats-modal');

  document.querySelector('#commentModal .close-modal')?.addEventListener('click', () => {
    commentModal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  document.querySelector('#editModal .close-modal')?.addEventListener('click', () => {
    editModal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  document.querySelector('.close-stats-modal')?.addEventListener('click', () => {
    userStatsModal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  window.onclick = function (event) {
    if (event.target === commentModal || event.target === editModal || event.target === userStatsModal) {
      event.target.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  };
}
