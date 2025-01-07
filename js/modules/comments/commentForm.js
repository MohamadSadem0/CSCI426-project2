import { handleNewComment } from './commentUtils.js';

export function setupCommentForm(modal, postId) {
  const commentForm = modal.querySelector('.comment-form');
  const input = commentForm.querySelector('input');
  const submitButton = commentForm.querySelector('.post-comment');

  input.value = '';

  submitButton.onclick = async () => {
    const content = input.value.trim();
    const contentWithoutMentions = content.replace(/@\w+\s*/g, '').trim();
    if (!contentWithoutMentions) return;

    try {
      const response = await fetch('action/comment_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          post_id: postId,
          content: content,
        }),
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();
      if (data.success) {
        handleNewComment(data, content, postId);
        input.value = '';
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };
}
