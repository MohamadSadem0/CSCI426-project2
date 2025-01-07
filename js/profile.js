document.addEventListener('DOMContentLoaded', function() {
    const profileUpload = document.getElementById('profile-upload');
    const profilePreview = document.getElementById('profile-preview');

    profileUpload.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            // Check if file is an image
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            };
            reader.onerror = function() {
                alert('Error reading file');
            };
            reader.readAsDataURL(file);
        }
    });
});
