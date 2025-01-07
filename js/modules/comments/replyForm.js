import { handleReplySubmission } from './commentUtils.js';

export function createReplyForm(username) {
  const div = document.createElement('div');
  div.className = 'reply-form';
  div.innerHTML = `
    <div class="mention-wrapper">
      <span class="mention">@${username}</span>
      <textarea placeholder="Write a reply..."></textarea>
    </div>
    <button type="button">Reply</button>
  `;
  return div;
}

export function setupReplyForm(replyForm, parentId, parentComment, postId) {
  const textarea = replyForm.querySelector('textarea');
  const button = replyForm.querySelector('button');
  const mention = replyForm.querySelector('.mention');

  textarea.focus();
  textarea.setSelectionRange(textarea.value.length, textarea.value.length);

  button.onclick = async () => {
    const content = textarea.value.trim();
    if (!content) return;

    const fullContent = `${mention.textContent} ${content}`;
    await handleReplySubmission(fullContent, parentId, parentComment, postId);
    replyForm.remove();
  };
}

export function initializeReplyHandlers() {
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('reply-button')) {
      const existingForms = document.querySelectorAll('.reply-form');
      existingForms.forEach((form) => form.remove());

      const parentId = e.target.dataset.parentId;
      const username = e.target.dataset.username;
      const parentComment = e.target.closest('.comment');
      const repliesContainer = parentComment.querySelector('.replies-container');
      const postId = e.target.dataset.postId;

      const replyForm = createReplyForm(username);
      repliesContainer.insertBefore(replyForm, repliesContainer.firstChild);

      setupReplyForm(replyForm, parentId, parentComment, postId);
    }
  });
}
