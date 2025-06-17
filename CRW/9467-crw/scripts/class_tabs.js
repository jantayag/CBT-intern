const tabs = document.querySelectorAll('.tabs');
const tabContents = document.querySelectorAll('.tab-content');

document.addEventListener('DOMContentLoaded', () => {
    tabs.forEach(tab => {
        tab.addEventListener('click', () => activateTab(tab));
    });

    if (tabs.length > 0) {
        activateTab(tabs[0]);
    }
});

function activateTab(tab) {
    const tabId = tab.getAttribute('data-tab');

    tabs.forEach(t => t.classList.remove('active'));
    tabContents.forEach(content => content.style.display = 'none');

    tab.classList.add('active');
    const activeContent = document.getElementById(tabId);
    if (activeContent) {
        activeContent.style.display = 'block';
    }
}


function updateButtonVisibility(activeTabId) {
    const tabsHeader = document.querySelector('.tabs-header');
    const actionButtons = tabsHeader.querySelectorAll('.action-btn');
    const addAssessmentButtons = document.querySelectorAll('.view-btn[onclick="addAssessments()"]');

    if (activeTabId === 'stats') {
        actionButtons.forEach(button => {
            button.style.display = 'none';
        });

        addAssessmentButtons.forEach(button => {
            button.style.display = 'none';
        });
        return;
    }
    
    actionButtons.forEach(button => {

        button.style.display = '';

        if (button.getAttribute('onclick') === 'showAddTopicModal()') {
            button.style.display = activeTabId === 'topics' ? '' : 'none';
        } else if (button.getAttribute('onclick') === 'showAddStudentModal()') {
            button.style.display = activeTabId === 'students' ? '' : 'none';
        }
    });

    addAssessmentButtons.forEach(button => {
        button.style.display = activeTabId === 'topics' ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'topics';
    updateButtonVisibility(activeTab);

    const tabLinks = document.querySelectorAll('.tabs');
    tabLinks.forEach(tab => {
        tab.addEventListener('click', (e) => {
            const tabId = tab.getAttribute('data-tab');
            updateButtonVisibility(tabId);
        });
    });
});

function showAddTopicModal() {
    const modals = document.querySelectorAll('.modal');
    const topicModal = modals[1];
    topicModal.style.display = 'block';
    const topicTextarea = topicModal.querySelector('textarea[name="topic"]');

    if (topicTextarea) {
        topicTextarea.value = '';
    }
}

function addTopic() {
    const classCode = new URLSearchParams(window.location.search).get('id');
    const topicTextarea = document.querySelector('textarea[name="topic"]');
    const topic = topicTextarea.value.trim();
    
    if (!topic) {
        alert('Please enter a topic');
        return;
    }
    
    fetch('php/class-queries/add_topic.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ 
            class_code: classCode, 
            topic: topic 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'topics');
            window.location.href = url.toString();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Error adding topic: ' + error);
    });

    const modals = document.querySelectorAll('.modal');
    modals[1].style.display = 'none';
}

function deleteTopic(topicId) {
    if (confirm('Are you sure you want to delete this topic? This will also delete all assessments in it.')) {
        const classCode = new URLSearchParams(window.location.search).get('id');
        
        fetch('php/class-queries/del_topic.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ 
                topic_id: topicId,
                class_code: classCode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                const topicRow = document.querySelector(`tr[data-topic-id="${topicId}"]`);
                if (topicRow) {
                    topicRow.remove();
                } else {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'topics');
                    window.location.href = url.toString();
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('Error deleting topic: ' + error);
        });
    }
}

function removeStudent(studentId) {
    if (confirm('Are you sure you want to remove this student?')) {
        fetch('php/class-queries/remove_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ student_id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);

                const studentRow = document.querySelector(`tr[data-student-id="${studentId}"]`);
                if (studentRow) {
                    studentRow.remove();
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('Error removing student: ' + error);
        });
    }
}

function showAddStudentModal() {
    const studentModal = document.getElementById('addStudentModal');
    studentModal.style.display = 'block';
}

function addStudents() {
    const emailInput = document.getElementById('student-email');
    const fileInput = document.getElementById('csv-upload');
    const classCode = new URLSearchParams(window.location.search).get('id');
    if (!emailInput.value && !fileInput.files.length) {
        alert('Please enter an email or upload a CSV file');
        return;
    }

    const formData = new FormData();
    formData.append('class_code', classCode);

    if (emailInput.value) {
        formData.append('email', emailInput.value);
    }

    if (fileInput.files.length) {
        formData.append('csv_file', fileInput.files[0]);
    }

    fetch('php/class-queries/add_students.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'students');
            window.location.href = url.toString();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding students');
    });
}

function cancelForm() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
    document.getElementById("addAssessmentModal").style.display = "none";
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function showAddAssessmentsModal(topicId) {
    const addAssessmentModal = document.getElementById('addAssessmentModal');
    addAssessmentModal.style.display = 'block';

    const addButton = document.querySelector('.view-btn');
    addButton.setAttribute('data-topic-id', topicId);

    fetch(`php/class-queries/get_assessments_not_in_topic.php?topic_id=${topicId}`)
        .then(response => response.json())
        .then(data => {
            const assessmentsTable = document.querySelector('#addAssessmentModal tbody');
            assessmentsTable.innerHTML = '';

            data.assessments.forEach(assessment => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${assessment.title}</td>
                    <td>${assessment.date_created}</td>
                    <td>
                        <input type="checkbox" class="question-checkbox" name="assessment_ids[]" value="${assessment.id}">
                    </td>
                `;
                assessmentsTable.appendChild(tr);
            });
        })
        .catch(error => {
            alert('Error fetching assessments: ' + error);
        });
}

function addAssessments() {
    const addButton = document.querySelector('.view-btn');
    const topicId = addButton.getAttribute('data-topic-id');

    const checkedAssessments = document.querySelectorAll('#addAssessmentModal input[name="assessment_ids[]"]:checked');
    const assessmentIds = Array.from(checkedAssessments).map(checkbox => checkbox.value);

    fetch('php/class-queries/add_assessments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ topic_id: topicId, assessment_ids: assessmentIds.join(',') })
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Error adding assessments');
        }
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'topics');
            window.location.href = url.toString();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Error adding assessments: ' + error.message);
    });

    const addAssessmentModal = document.getElementById('addAssessmentModal');
    addAssessmentModal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    fetch('php/get_user_type.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user_type === 'Student') {
                hideFromStudent();
                const tabLinks = document.querySelectorAll('.tabs');
                tabLinks.forEach(tab => {
                    tab.addEventListener('click', () => {
                        hideFromStudent();
                    });
                });
            }
        })
        .catch(error => console.error('Error fetching user type:', error));
});

function hideFromStudent() {
    const tabsHeaderButtons = document.querySelectorAll('.tabs-header button');
    tabsHeaderButtons.forEach(button => button.style.display = 'none');

    const actionCells = document.querySelectorAll('#studentsTable th:last-child, #studentsTable td:last-child');
    actionCells.forEach(cell => cell.style.display = 'none');

    const topicActions = document.querySelectorAll('.action-buttons');
    topicActions.forEach(actionCell => {
        const buttons = actionCell.querySelectorAll('button');
        buttons.forEach(button => {
            if (!button.textContent.trim().includes('View Assessments')) {
                button.style.display = 'none';
            }
        });
    });
}

