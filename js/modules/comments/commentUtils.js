import { currentPostId } from './index.js';

export function createCommentElement(comment, level = 0) {
  const div = document.createElement('div');
  div.className = 'comment';
  div.dataset.commentId = comment.id;
  div.dataset.level = level;
  const username = `${comment.firstname} ${comment.lastname}`;

  div.innerHTML = `
    <div class="comment-header">
      <img src="assets/profileUploads/${comment.profile_picture}" 
          onerror="this.src='1.jpg'" 
          alt="Profile" 
          class="comment-profile-pic">
      <span class="comment-user">${username}</span>
    </div>
    <div class="comment-content">${comment.content}</div>
    <div class="comment-actions">
      <button class="reply-button" data-parent-id="${comment.id}" data-username="${username}" data-level="${level}" data-post-id="${currentPostId}">Reply</button>
    </div>
    <div class="replies-container"></div>
  `;

  return div;
}

export function updateCommentCount(postId, count) {
  const postCommentButton = document.querySelector(
    `button.comment-button[data-post-id="${postId}"]`
  );
  if (postCommentButton) {
    const countElement =
      postCommentButton.parentElement.querySelector('.comment-count');
    if (countElement) {
      countElement.textContent = count;
    }
  }
}

export function removeNoCommentsMessage() {
  const noCommentsMessage = document.querySelector(
    '.comments-list .no-comments'
  );
  if (noCommentsMessage) {
    noCommentsMessage.remove();
  }
}

export function handleNewComment(data, content, postId) {
  const newComment = {
    id: data.comments[0].id,
    content: content,
    firstname: data.comments[0].firstname,
    lastname: data.comments[0].lastname,
    profile_picture: data.comments[0].profile_picture,
    parent_id: null,
    replies: [],
  };

  const commentsList = document.querySelector('.comments-list');
  const commentElement = createCommentElement(newComment);

  if (commentsList.firstChild) {
    commentsList.insertBefore(commentElement, commentsList.firstChild);
  } else {
    commentsList.appendChild(commentElement);
  }

  updateCommentCount(postId, data.commentCount);
  removeNoCommentsMessage();
}

export async function handleReplySubmission(
  content,
  parentId,
  parentComment,
  postId
) {
  try {
    const response = await fetch('action/comment_handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        post_id: postId,
        content: content,
        parent_id: parentId,
      }),
    });

    if (!response.ok) throw new Error('Network response was not ok');

    const data = await response.json();
    if (data.success) {
      addNewReply(data, content, parentId, parentComment);
      updateCommentCount(postId, data.commentCount);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

function addNewReply(data, content, parentId, parentComment) {
  const newReply = {
    id: data.comments[0].id,
    content: content,
    firstname: data.comments[0].firstname,
    lastname: data.comments[0].lastname,
    profile_picture: data.comments[0].profile_picture,
    parent_id: parentId,
    replies: [],
  };

  const repliesContainer = parentComment.querySelector('.replies-container');
  const replyElement = createCommentElement(
    newReply,
    parseInt(parentComment.dataset.level) + 1
  );

  if (repliesContainer.firstChild) {
    repliesContainer.insertBefore(replyElement, repliesContainer.firstChild);
  } else {
    repliesContainer.appendChild(replyElement);
  }
}
