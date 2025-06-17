function showAssessmentCreator() {
    const form = document.getElementById('assessmentForm');
    form.reset();
    document.querySelector('.question-form-heading').textContent = 'Create New Assessment';
    document.getElementById('assessment_id').value = '';
    document.getElementById('assessmentModal').style.display = 'block';
}

function cancelForm() {
    document.getElementById('assessmentModal').style.display = 'none';
    document.getElementById('assessmentForm').reset();
}

window.onclick = function(event) {
    const modal = document.getElementById('assessmentModal');
    if (event.target === modal) {
        cancelForm();
    }
};

function searchAssessment() {
    const searchInput = document.querySelector('.search-bar');
    const filter = searchInput.value.toLowerCase();
    const assessments = document.querySelectorAll('#assessments-tbody tr');

    assessments.forEach(assessment => {
        const title = assessment.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        if (title.includes(filter)) {
            assessment.style.display = ''; 
        } else {
            assessment.style.display = 'none'; 
        }
    });
}

function addAssessmentRow(id, formData) {
    const assessmentsTbody = document.getElementById('assessments-tbody');

    if (assessmentsTbody) {
        const title = formData.get('title');
        const dateCreated = new Date().toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });

        const newRow = document.createElement('tr');
        newRow.id = `assessment-${id}`;

        newRow.innerHTML = `
            <td>${assessmentsTbody.childElementCount + 1}</td>
            <td>${title}</td>
            <td>${timeLimit}</td>
            <td>${dateCreated}</td>
            <td class="action-buttons">
                <button class="view-btn" onclick="viewAssessment(${id})">View</button>
                <button class="edit-btn" onclick="editAssessment(${id})">Edit</button>
                <button class="del-btn" onclick="deleteAssessment(${id})">Delete</button>
            </td>
        `;
        assessmentsTbody.appendChild(newRow);
    }
}

function deleteAssessment(assessmentId) {
    if (!confirm('Are you sure you want to delete this assessment?')) return;

    fetch('php/assessment-queries/del_assessment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ assessment_id: assessmentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById(`assessment-${assessmentId}`).remove();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the assessment.');
    });
}

function editAssessment(id) {
    fetch(`php/assessment-queries/get_assessment.php?assessment_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('assessmentForm');
                form.reset();
                document.querySelector('.question-form-heading').textContent = 'Edit Assessment';
                document.getElementById('title').value = data.assessment.title;
                document.getElementById('assessment_id').value = id;
                document.getElementById('assessmentModal').style.display = 'block';
            } else {
                alert('Failed to load assessment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching assessment details.');
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('assessmentForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        const formData = new FormData(this);
    
        const assessmentId = formData.get('assessment_id');
        const url = assessmentId 
            ? 'php/assessment-queries/edit_assessment.php'
            : 'php/assessment-queries/create_assessment.php';

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
            alert('An error occurred while processing the assessment.');
        });
    });
});
