function deleteClass(classId) {
    if (!confirm('Are you sure you want to delete this class?')) {
        return;
    }

    fetch('php/class-queries/del_class.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ class_id: classId }),
        credentials: 'same-origin',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cardElement = document.querySelector(`.delete-button[data-class-id="${classId}"]`).closest('.card');
            if (cardElement) {
                cardElement.remove();  
                alert(data.message);  
                if (document.querySelectorAll('.card').length === 0) {
                    const section = document.querySelector('.card-section');
                    if (section) {
                        section.innerHTML = '<h1>No classes found.</h1>';
                    }
                }
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the class. Please try again later.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const cardSection = document.querySelector('.card-section');
    if (cardSection) {
        cardSection.addEventListener('click', function(e) {
            const deleteButton = e.target.closest('.delete-button');
            if (deleteButton) {
                e.preventDefault();
                e.stopPropagation();
                const classId = parseInt(deleteButton.dataset.classId);
                deleteClass(classId); 
            }
        });
    }
});
