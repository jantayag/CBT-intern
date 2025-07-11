function updateButtonStates(row) {
    const evaluationStart = row.querySelector('.evaluation-start').textContent.trim();
    const evaluationEnd = row.querySelector('.evaluation-end').textContent.trim();
    const isEvaluationPeriodSet = evaluationStart !== 'Not Set' && evaluationEnd !== 'Not Set';
    const publishBtn = row.querySelector('.publish-btn');
    
    if (publishBtn) {
        if (!isEvaluationPeriodSet) {
            publishBtn.style.backgroundColor = '#cccccc';
            publishBtn.style.cursor = 'not-allowed';
            publishBtn.style.color = '#666666';
        } else {
            publishBtn.style.backgroundColor = '';
            publishBtn.style.cursor = '';
            publishBtn.style.border = '';
            publishBtn.style.color = '';
        }
    }
}

function publishAssessment(assessmentId, topicId) {
    const row = document.querySelector(`#assessment-${assessmentId}`);
    const evaluationStart = row.querySelector('.evaluation-start').textContent.trim();
    const evaluationEnd = row.querySelector('.evaluation-end').textContent.trim();

 

    fetch('php/class-queries/publish_assessment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ assessment_id: assessmentId, topic_id: topicId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);

            const publishedCell = row.querySelector('.is-published');   
            const unpublishBtn = row.querySelector('.unpublish-btn');
            const editBtn = row.querySelector('.edit-btn');

            publishedCell.textContent = 'Yes'; 
            unpublishBtn.style.display = '';    
            editBtn.style.display = 'none';     
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while publishing the assessment.');
    });
}

async function saveAndPublish() {
    const form = document.getElementById('assessmentForm');
    const evaluationStartInput = document.getElementById('evaluation_start');
    const evaluationEndInput = document.getElementById('evaluation_end');
    const modal = document.getElementById('assessmentModal');

    const formData = new FormData(form);

    if (!evaluationStartInput.value) {
        formData.set('evaluation_start', '');
    }
    if (!evaluationEndInput.value) {
        formData.set('evaluation_end', '');
    }

    try {
        const response = await fetch('php/class-queries/edit_topic_assessment.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Extract IDs from hidden fields
            const assessmentId = formData.get('assessment_id');
            const topicId = formData.get('topic_id');

            // Hide the modal
            modal.style.display = 'none';

            // Wait briefly to ensure DB update
            setTimeout(() => {
                publishAssessment(assessmentId, topicId);
            }, 100); // optional delay before publish
        } else {
            alert('Error updating assessment: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving and publishing the assessment.');
    }
}

function unpublishAssessment(assessmentId, topicId) {
    fetch('php/class-queries/has_assessment_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ 
            assessment_id: assessmentId,
            topic_id: topicId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.has_active_users) {
                alert('Cannot unpublish assessment: There are students currently taking this assessment in this topic.');
                return;
            }
            
            return fetch('php/class-queries/unpublish_assessment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ assessment_id: assessmentId, topic_id: topicId })
            });
        } else {
            throw new Error('Failed to check active users');
        }
    })
    .then(response => {
        if (response) { 
            return response.json();
        }
    })
    .then(data => {
        if (data && data.success) {
            alert(data.message);

            const row = document.querySelector(`#assessment-${assessmentId}`);
            const publishedCell = row.querySelector('.is-published');
            const unpublishBtn = row.querySelector('.unpublish-btn');
            const editBtn = row.querySelector('.edit-btn');

            publishedCell.textContent = 'No';    
            unpublishBtn.style.display = 'none';
            editBtn.style.display = '';        
        } else if (data) {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while unpublishing the assessment.');
    });
}


function removeAssessment(assessmentId, topicId) {
    if (confirm("Are you sure you want to remove this assessment? You will not be able to recover this.")) {
        fetch('php/class-queries/remove_assessment_from_topic.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ topic_id: topicId, assessment_id: assessmentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred when trying to remove the assessment.');
        });
    } else {
        alert("The assessment removal has been canceled.");
    }
}


function showEditTopicAssessmentModal(assessmentId, topicId) {
    const modal = document.getElementById('assessmentModal');
    const form = document.getElementById('assessmentForm');
    const row = document.getElementById(`assessment-${assessmentId}`);

    const startDateCell = row.querySelector('.evaluation-start');
    const endDateCell = row.querySelector('.evaluation-end');
    const startDate = startDateCell.textContent.trim();
    const endDate = endDateCell.textContent.trim();
    const canView = row.getAttribute('data-can-view') === 'true';

    const evaluationStartInput = document.getElementById('evaluation_start');
    const evaluationEndInput = document.getElementById('evaluation_end');
    const canViewYesInput = document.getElementById('can_view_yes');
    const canViewNoInput = document.getElementById('can_view_no');

    function convertToDatetimeLocal(dateStr) {
        if (dateStr === 'Not Set') return '';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return '';

        return new Date(date.getTime() - (date.getTimezoneOffset() * 60000))
            .toISOString()
            .slice(0, 16);
    }

    evaluationStartInput.value = convertToDatetimeLocal(startDate);
    evaluationEndInput.value = convertToDatetimeLocal(endDate);
    
    if (canView) {
        canViewYesInput.checked = true;
        canViewNoInput.checked = false;
    } else {
        canViewYesInput.checked = false;
        canViewNoInput.checked = true;
    }

    let assessmentIdInput = form.querySelector('input[name="assessment_id"]');
    let topicIdInput = form.querySelector('input[name="topic_id"]');

    if (!assessmentIdInput) {
        assessmentIdInput = document.createElement('input');
        assessmentIdInput.type = 'hidden';
        assessmentIdInput.name = 'assessment_id';
        form.appendChild(assessmentIdInput);
    }

    if (!topicIdInput) {
        topicIdInput = document.createElement('input');
        topicIdInput.type = 'hidden';
        topicIdInput.name = 'topic_id';
        form.appendChild(topicIdInput);
    }

    assessmentIdInput.value = assessmentId;
    topicIdInput.value = topicId;

    modal.style.display = 'block';

    form.onsubmit = async function (e) {
        e.preventDefault();

        try {
            const formData = new FormData(form);
            if (!evaluationStartInput.value) {
                formData.set('evaluation_start', '');
            }
            if (!evaluationEndInput.value) {
                formData.set('evaluation_end', '');
            }

            const response = await fetch('php/class-queries/edit_topic_assessment.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                const startDateTime = formData.get('evaluation_start')
                    ? new Date(formData.get('evaluation_start')).toLocaleString('en-US', {
                          year: 'numeric',
                          month: 'short',
                          day: 'numeric',
                          hour: 'numeric',
                          minute: 'numeric',
                          hour12: true
                      })
                    : 'Not Set';

                const endDateTime = formData.get('evaluation_end')
                    ? new Date(formData.get('evaluation_end')).toLocaleString('en-US', {
                          year: 'numeric',
                          month: 'short',
                          day: 'numeric',
                          hour: 'numeric',
                          minute: 'numeric',
                          hour12: true
                      })
                    : 'Not Set';

                startDateCell.textContent = startDateTime;
                endDateCell.textContent = endDateTime;

                const canViewValue = formData.get('can_view') === 'True';
                row.setAttribute('data-can-view', canViewValue);
                row.cells[3].textContent = canViewValue ? 'Yes' : 'No';
                
                updateButtonStates(row);
                
                modal.style.display = 'none';
            } else {
                alert('Error updating assessment: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the assessment');
        }
        
    };
    
}

document.addEventListener('DOMContentLoaded', function () {
    const evaluationStart = document.getElementById('evaluation_start');
    const evaluationEnd = document.getElementById('evaluation_end');

    if (evaluationStart && evaluationEnd) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

        evaluationStart.min = minDateTime;
        evaluationEnd.min = minDateTime;

        evaluationStart.addEventListener('change', function () {
            evaluationEnd.min = this.value || minDateTime;
        });

        evaluationEnd.addEventListener('change', function () {
            if (this.value && this.value <= evaluationStart.value) {
                alert('End date must be after start date');
                this.value = '';
            }
        });
    }
});

function cancelForm() {
    const modal = document.getElementById('assessmentModal');
    modal.style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('assessmentModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    fetch('php/get_user_type.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user_type === 'Student') {
                hideFromStudent();
                showFromStudent();
            }
        })
        .catch(error => console.error('Error fetching user type:', error));
});

function hideFromStudent() {
    const actionButtons = document.querySelectorAll('.action-buttons');

    actionButtons.forEach(actionCell => {
        const buttons = actionCell.querySelectorAll('button');
        buttons.forEach(button => {
            if (!button.textContent.trim().includes('View Assessments')) {
                button.style.display = 'none';
            }
        });
    });

    const table = document.getElementById('assessmentsTable');
    if (!table) return;

    const headers = table.querySelectorAll('thead th');
    let publishedIndex = -1;

    headers.forEach((header, index) => {
        if (header.textContent.trim().toLowerCase() === 'published') {
            publishedIndex = index;
        }
    });

    if (publishedIndex === -1) return; 
    headers[publishedIndex].style.display = 'none';
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells[publishedIndex]) {
            cells[publishedIndex].style.display = 'none';
        }
    });
}

function showFromStudent() {
    const actionButtons = document.querySelectorAll('.action-buttons');
    const userId = document.getElementById('student_id').value;

    actionButtons.forEach(actionCell => {
        const row = actionCell.closest('tr');
        const assessmentId = row.id.split('-')[1];
        const urlParams = new URLSearchParams(window.location.search);
        const topicId = urlParams.get('topic_id');
        const canView = row.getAttribute('data-can-view') === 'true';

        fetch(`php/class-queries/if_answered.php?user_id=${userId}&assessment_id=${assessmentId}&topic_id=${topicId}`)
            .then(response => response.json())
            .then(result => {
                if (!result.is_published) {
                    const message = document.createElement('span');
                    message.textContent = 'No Access';
                    message.classList.add('no-access');
                    actionCell.appendChild(message);
                } else if (result.already_answered) {
                    if (canView) {
                        const viewBtn = document.createElement('button');
                        viewBtn.textContent = 'View Results';
                        viewBtn.classList.add('view2-btn');
                        viewBtn.addEventListener('click', () => {
                            let url = `view_assessment.php?assessment_id=${assessmentId}&topic_id=${topicId}`;
                            window.location.href = url;
                        });
                        actionCell.appendChild(viewBtn);
                    } else {
                        const message = document.createElement('span');
                        message.textContent = 'Cannot View Results';
                        message.classList.add('cannot-view-text');
                        actionCell.appendChild(message);
                    }
                } else {
                    const ansBtn = document.createElement('button');
                    ansBtn.textContent = 'Answer';
                    ansBtn.classList.add('ans-btn');
                    ansBtn.addEventListener('click', () => {
                        let url = `student_assessment.php?assessment_id=${assessmentId}&topic_id=${topicId}`;
                        window.open(url, '_blank');
                    });
                    actionCell.appendChild(ansBtn);
                }
            })
            .catch(error => {
                console.error('Error checking if assessment was answered or its publish status:', error);
                alert("Error checking assessment status. Please try again.");
            });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const assessmentRows = document.querySelectorAll('[id^="assessment-"]');
    
    assessmentRows.forEach(row => {
        const isPublished = row.querySelector('.is-published').textContent === 'Yes';
        const unpublishBtn = row.querySelector('.unpublish-btn');
        const editBtn = row.querySelector('.edit-btn');
        
        if (isPublished) {
            unpublishBtn.style.display = '';
            editBtn.style.display = 'none';
        } else {
            unpublishBtn.style.display = 'none';
            editBtn.style.display = '';

            updateButtonStates(row);
        }
    });
});
