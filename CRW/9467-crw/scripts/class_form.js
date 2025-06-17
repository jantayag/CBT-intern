const modal = document.querySelector('.modal');
const classForm = document.getElementById('classForm');

function createClass() {
    resetForm();
    document.querySelector('.question-form-heading').textContent = 'Create New Class';
    document.querySelector('.view-btn').style.display = '';
    document.querySelector('.save-btn').style.display = 'none';
    
    const classIdInput = document.querySelector('input[name="class_id"]');
    if (classIdInput) {
        classIdInput.remove();
    }
    
    modal.style.display = 'block';
}

function editClass(classId) {
    fetch(`php/class-queries/edit_class.php?id=${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resetForm();
                
                document.querySelector('.question-form-heading').textContent = 'Edit Class';
                document.querySelector('.view-btn').style.display = 'none';
                document.querySelector('.save-btn').style.display = '';
             
                let classIdInput = document.querySelector('input[name="class_id"]');
                if (!classIdInput) {
                    classIdInput = document.createElement('input');
                    classIdInput.type = 'hidden';
                    classIdInput.name = 'class_id';
                    classForm.appendChild(classIdInput);
                }
                classIdInput.value = classId;
                
                document.getElementById('class_code').value = data.data.class_code;
                document.getElementById('program').value = data.data.program_id;
                document.getElementById('type').value = data.data.sem;
       
                document.getElementById('start_year').value = data.data.start_year;
                updateEndYear();
                document.getElementById('end_year').value = data.data.end_year;
                
                modal.style.display = 'block';
            } else {
                alert('Error loading class: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading class');
        });
}

function cancelForm() {
    if (modal) {
        modal.style.display = 'none';
        resetForm();
    }
}

function resetForm() {
    if (classForm) {
        classForm.reset();
        const endYearSelect = document.getElementById('end_year');
        if (endYearSelect) {
            endYearSelect.innerHTML = '<option value="">Select End Year</option>';
        }
    }
}

function updateEndYear() {
    const startYear = parseInt(document.getElementById('start_year').value);
    const endYearSelect = document.getElementById('end_year');

    if (!endYearSelect) {
        console.error('End year select element not found');
        return;
    }

    endYearSelect.innerHTML = '<option value="">Select End Year</option>';

    if (!isNaN(startYear)) {
        for (let year = startYear; year <= startYear + 1; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.text = year;
            endYearSelect.appendChild(option);
        }
    }
}

window.onclick = function(event) {
    if (event.target === modal) {
        cancelForm();
    }
}

classForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const isEdit = formData.has('class_id');
    const url = isEdit ? 'php/class-queries/edit_class.php' : 'php/class-queries/create_class.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`An error occurred while ${isEdit ? 'editing' : 'creating'} the class`);
    });
});

function loadPrograms() {
    fetch('php/class-queries/get_programs.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const programSelect = document.getElementById('program');
                if (programSelect) {
                    data.programs.forEach(program => {
                        const option = document.createElement('option');
                        option.value = program.id;
                        option.textContent = program.name;
                        programSelect.appendChild(option);
                    });
                }
            } else {
                console.error('Error loading programs:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function initializeEditButtons() {
    const editButtons = document.querySelectorAll('.edit-button');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            editClass(classId);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadPrograms();
    initializeEditButtons();
});

function searchClass() {
    const searchInput = document.querySelector('.search-bar');
    const filter = searchInput.value.toLowerCase();
    const classCards = document.querySelectorAll('.card');

    classCards.forEach(card => {
        const classCode = card.querySelector('#class-code').textContent.toLowerCase();
        const programName = card.querySelector('.sem-and-ay').textContent.toLowerCase();

        if (classCode.includes(filter) || programName.includes(filter)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

document.querySelector('.search-bar').addEventListener('keydown', function (event) {
    if (event.key === 'Enter' || event.keyCode === 13) {
        event.preventDefault(); 
        searchClass(); 
    }
});

