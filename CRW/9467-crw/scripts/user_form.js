function showUserCreator() {
    document.getElementById('usersModal').style.display = 'block';
    document.querySelector('.save-btn').style.display = 'none';
    document.querySelector('.view-btn').style.display = '';
}

function cancelForm() {
    document.getElementById('usersModal').style.display = 'none';
    document.getElementById('userForm').reset();
}

window.onclick = function(event) {
    const modal = document.getElementById('usersModal');
    if (event.target === modal) {
        cancelForm();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    if(userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = formData.has('user_id');
            const url = isEdit ? 'php/user-queries/edit_user.php' : 'php/user-queries/add_user.php';
            const fileInput = document.getElementById('csv-upload');
            
            if (fileInput && fileInput.files.length > 0) {
                formData.append('csv-upload', fileInput.files[0]);
            }
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(userData => {
                if(userData.success) {
                    alert(userData.message);
                    location.reload();
                    cancelForm();
                } else {
                    alert(userData.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
        });
    }
});

function deleteUser(userId) {
    if(!confirm('Are you sure you want to delete this user?')) return;

    fetch('php/user-queries/del_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({user_id: userId})
    })
    .then(response=> response.json())
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
        alert('An error occured while trying to delete the user.');
    });
}

function editUser(userId) {
    fetch(`php/user-queries/get_user_details.php?id=${userId}`)
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
