export function initializeLikeHandlers() {
  document.querySelectorAll('.like-button').forEach((button) => {
    button.addEventListener('click', async function () {
      if (!this.classList.contains('processing')) {
        this.classList.add('processing');

        const postId = this.dataset.postId;
        const iconElement = this.querySelector('ion-icon');
        const countElement = this.parentElement.querySelector('.like-count');

        try {
          const response = await fetch('action/like_handler.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              post_id: postId,
            }),
          });

          const data = await response.json();

          if (data.success) {
            if (data.action === 'liked') {
              this.classList.add('liked');
              iconElement.setAttribute('name', 'heart');
            } else {
              this.classList.remove('liked');
              iconElement.setAttribute('name', 'heart-outline');
            }

            countElement.textContent = data.likeCount;
            this.classList.add('animate-like');
            setTimeout(() => this.classList.remove('animate-like'), 300);
          }
        } catch (error) {
          console.error('Error:', error);
        } finally {
          this.classList.remove('processing');
        }
      }
    });
  });
}
