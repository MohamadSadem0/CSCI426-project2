import { createCommentElement } from './commentUtils.js';

export async function loadComments(postId) {
  try {
    const response = await fetch(
      `action/comment_handler.php?post_id=${encodeURIComponent(postId)}`
    );
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    const data = await response.json();
    const commentsList = document.querySelector('.comments-list');
    
    if (!commentsList) {
      console.error('Comments list element not found');
      return;
    }
    
    commentsList.innerHTML = '';

    if (data.success && data.comments && Array.isArray(data.comments)) {
      if (data.comments.length > 0) {
        const commentMap = new Map();
        data.comments.forEach((comment) => {
          commentMap.set(comment.id, {
            ...comment,
            replies: [],
          });
        });

        const rootComments = [];
        commentMap.forEach((comment) => {
          if (comment.parent_id) {
            const parent = commentMap.get(comment.parent_id);
            if (parent) {
              parent.replies.push(comment);
            }
          } else {
            rootComments.push(comment);
          }
        });

        renderComments(rootComments, commentsList);
      } else {
        commentsList.innerHTML =
          '<p class="no-comments">No comments yet. Be the first to comment!</p>';
      }
    }
  } catch (error) {
    console.error('Error loading comments:', error);
    const commentsList = document.querySelector('.comments-list');
    if (commentsList) {
      commentsList.innerHTML = '<p class="error">Error loading comments</p>';
    }
  }
}

export function renderComments(comments, container, level = 0) {
  comments.forEach((comment) => {
    const commentElement = createCommentElement(comment, level);
    container.appendChild(commentElement);

    if (comment.replies && comment.replies.length > 0) {
      const repliesContainer = commentElement.querySelector('.replies-container');
      renderComments(comment.replies, repliesContainer, level + 1);
    }
  });
}
