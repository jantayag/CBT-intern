document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    const fileInput = document.getElementById('csv-upload');
    const dragDropArea = document.getElementById('drag-drop-area');

    if(userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = formData.has('user_id');
            
            if (fileInput && fileInput.files.length > 0) {
                handleCSVUpload(fileInput.files[0]);
                return;
            }

            handleSingleUserSubmit(formData, isEdit);
        });
    }

    function handleCSVUpload(file) {
        const formData = new FormData();
        formData.append('csv-upload', file);

        fetch('/api/users/csv', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
                cancelForm();
            } else {
                alert(data.message || 'Error uploading CSV');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the CSV file.');
        });
    }

    function handleSingleUserSubmit(formData, isEdit) {
        const url = isEdit ? '/api/users/edit' : '/api/users/add';

        const userData = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            password: formData.get('password'),
            user_type: formData.get('user_type')
        };

        if (isEdit) {
            userData.user_id = formData.get('user_id');
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
                cancelForm();
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    }
});

function showUserCreator() {
    document.getElementById('usersModal').style.display = 'block';
    document.querySelector('.save-btn').style.display = 'none';
    document.querySelector('.view-btn').style.display = '';
    document.querySelector('.question-form-heading').textContent = 'Create User';
}

function cancelForm() {
    document.getElementById('usersModal').style.display = 'none';
    document.getElementById('userForm').reset();
    document.getElementById('file-name').textContent = '';
}

window.onclick = function(event) {
    const modal = document.getElementById('usersModal');
    if (event.target === modal) {
        cancelForm();
    }
};

function deleteUser(userId) {
    if(!confirm('Are you sure you want to delete this user?')) return;

    fetch('/api/users/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            document.getElementById(`users-${userId}`).remove();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error: ', error);
        alert('An error occurred while trying to delete the user.');
    });
}

function editUser(userId) {
    fetch(`/api/users/details?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('userForm');
  
                document.querySelector('.question-form-heading').textContent = 'Edit User';
                form.querySelector('.view-btn').style.display = 'none';
                form.querySelector('.save-btn').style.display = '';
     
                form.querySelector('#first_name').value = data.user.first_name;
                form.querySelector('#last_name').value = data.user.last_name;
                form.querySelector('#email').value = data.user.email;
                form.querySelector('#password').value = data.user.password;
                form.querySelector('#user_type').value = data.user.user_type;
          
                let userIdInput = form.querySelector('input[name="user_id"]');
                if (!userIdInput) {
                    userIdInput = document.createElement('input');
                    userIdInput.type = 'hidden';
                    userIdInput.name = 'user_id';
                    form.appendChild(userIdInput);
                }
                userIdInput.value = userId;
                
                document.getElementById('usersModal').style.display = 'block';
            } else {
                alert('Failed to get user data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while getting user data.');
        });
}
