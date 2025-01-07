import { setupCommentForm } from './commentForm.js';
import { loadComments } from './commentRender.js';
import { initializeReplyHandlers } from './replyForm.js';

export let currentPostId = null;

export function initializeCommentHandlers() {
  const buttons = document.querySelectorAll('.comment-button');

  buttons.forEach((button) => {
    button.addEventListener('click', function () {
      currentPostId = this.dataset.postId;
      const modal = document.getElementById('commentModal');

      if (!modal) {
        console.error('Comment modal not found!');
        return;
      }

      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';

      loadComments(currentPostId);
      setupCommentForm(modal, currentPostId);
    });
  });

  initializeReplyHandlers();
}
