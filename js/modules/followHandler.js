export function initializeFollowHandlers() {
  document.querySelectorAll('.follow-button').forEach((button) => {
    button.addEventListener('click', async function () {
      const userId = this.dataset.userId;
      try {
        const response = await fetch('action/follow_handler.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ user_id: userId }),
        });

        const data = await response.json();
        if (data.success) {
          this.textContent = data.following ? 'Unfollow' : 'Follow';
        }
      } catch (error) {
        console.error('Error:', error);
      }
    });
  });
}
