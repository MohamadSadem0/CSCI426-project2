export function initializeUserStatsHandlers() {
  document.addEventListener('click', async function (e) {
    if (
      e.target.classList.contains('username') &&
      e.target.classList.contains('clickable')
    ) {
      e.preventDefault();
      e.stopPropagation();

      const postCard = e.target.closest('.post-card');
      if (!postCard) return;

      const followButton = postCard.querySelector('.follow-button');
      const userId = followButton ? followButton.dataset.userId : null;

      if (!userId) return;

      try {
        const response = await fetch(
          `action/get_user_stats.php?user_id=${userId}`
        );
        const data = await response.json();

        if (data.success) {
          showUserStatsModal(postCard, e.target.textContent, data);
        }
      } catch (error) {
        console.error('Error fetching user stats:', error);
      }
    }
  });
}

function showUserStatsModal(postCard, username, data) {
  const modal = document.querySelector('.user-stats-modal');
  const userImg = postCard.querySelector('.post-header img').src;

  document.getElementById('modal-user-img').src = userImg;
  document.getElementById('modal-username').textContent = username;
  document.getElementById('modal-followers').textContent = data.followers;
  document.getElementById('modal-following').textContent = data.following;

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
