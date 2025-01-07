let currentEditPostId = null;

export function initializePostHandlers() {
  initializePostOptions();
  initializeDeletePost();
  initializeEditPost();
  initializeImagePreview();
  initializeUpdatePost();
}

function initializePostOptions() {
  document.querySelectorAll('.post-options').forEach((button) => {
    button.addEventListener('click', (e) => {
      e.stopPropagation();
      const dropdown = button.parentElement.querySelector('.options-dropdown');
      dropdown.classList.toggle('show');
    });
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.options-dropdown.show').forEach((dropdown) => {
      dropdown.classList.remove('show');
    });
  });
}

function initializeDeletePost() {
  document.querySelectorAll('.delete-post').forEach((button) => {
    button.addEventListener('click', async function () {
      if (confirm('Are you sure you want to delete this post?')) {
        const postId = this.dataset.postId;
        try {
          const response = await fetch('action/delete_post.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId }),
          });

          const data = await response.json();
          if (data.success) {
            this.closest('.post-card').remove();
          }
        } catch (error) {
          console.error('Error:', error);
        }
      }
    });
  });
}

function initializeEditPost() {
  document.querySelectorAll('.edit-post').forEach((button) => {
    button.addEventListener('click', function () {
      const postId = this.dataset.postId;
      const postCard = this.closest('.post-card');
      const description = postCard.querySelector('.description').textContent;
      const currentImage = postCard.querySelector('.post-image').src;
      currentEditPostId = postId;

      document.querySelector('.edit-description').value = description;
      document.querySelector('.current-post-image').src = currentImage;

      document.getElementById('editModal').style.display = 'block';
      document.body.style.overflow = 'hidden';

      this.closest('.options-dropdown').classList.remove('show');
    });
  });
}

function initializeImagePreview() {
  document
    .querySelector('.edit-image-input')
    .addEventListener('change', function (event) {
      const file = event.target.files[0];
      if (file) {
        if (!file.type.startsWith('image/')) {
          alert('Please select an image file');
          this.value = '';
          return;
        }

        const reader = new FileReader();
        const previewImage = document.querySelector('.current-post-image');

        reader.onload = function (e) {
          previewImage.src = e.target.result;
        };

        reader.onerror = function () {
          alert('Error reading file');
        };

        reader.readAsDataURL(file);
      }
    });
}

function initializeUpdatePost() {
  document
    .querySelector('.update-post-btn')
    .addEventListener('click', async function () {
      const description = document
        .querySelector('.edit-description')
        .value.trim();
      const imageInput = document.querySelector('.edit-image-input');
      const formData = new FormData();

      formData.append('post_id', currentEditPostId);
      formData.append('description', description);

      if (imageInput.files.length > 0) {
        formData.append('post_image', imageInput.files[0]);
      }

      try {
        const response = await fetch('action/update_post.php', {
          method: 'POST',
          body: formData,
        });

        const data = await response.json();
        if (data.success) {
          updatePostInDOM(currentEditPostId, description, data.image);
          closeEditModal();
          resetFileInput(imageInput);
        }
      } catch (error) {
        console.error('Error:', error);
      }
    });
}

function updatePostInDOM(postId, description, image) {
  const postCard = document
    .querySelector(`.post-card .edit-post[data-post-id="${postId}"]`)
    .closest('.post-card');
  postCard.querySelector('.description').textContent = description;

  if (image) {
    postCard.querySelector('.post-image').src = `assets/postsUploads/${image}`;
  }
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
  document.body.style.overflow = 'auto';
}

function resetFileInput(input) {
  input.value = '';
}
