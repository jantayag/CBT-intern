window.onclick = function(event) {
    closeAnswersModal();
}

function viewAnswers(questionId) {
    fetch(`php/question-queries/view_answer.php?question_id=${questionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.querySelector('.modal-answers');
                let html = `
                    <div class="answer-modal-content">
                        <h3 style="padding-bottom: 5px;">Question:</h3>
                          <p style="padding-bottom: 15px;">${data.question_text}</p> 
                        <h3>Answers:</h3>
                        <div class="answers-list">
                `;

                data.answers.forEach(answer => {
                    const color = answer.is_answer === 'Y' ? '#ddf0dd' : '#f0dddd';
                    html += `
                        <div class="answer-item" style="background-color: ${color}; padding: 10px; margin: 2px; border-radius: 4px;">
                            ${answer.text}
                        </div>
                    `;
                });

                html += `
                        </div>
                        <button class="del-btn" onclick="closeAnswersModal()">Close</button>
                    </div>
                `;
                modal.innerHTML = html;
                modal.parentElement.style.display = 'block';
            } else {
                alert('Error loading answers: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading answers');
        });
}

function closeAnswersModal() {
    document.querySelector('.modal-answers').parentElement.style.display = 'none';
}

function removeQuestionFromAssessment(assessmentId, questionId) {
    if (confirm("Are you sure you want to remove this question from the assessment?")) {
        fetch('php/assessment-queries/remove_q_from_assessment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ assessment_id: assessmentId, question_id: questionId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`tr[data-assessment-id="${assessmentId}"][data-question-id="${questionId}"]`);
                if (row) {
                    row.remove();
                }

                alert(data.message);
            
                const tbody = document.querySelector('#questionsTable tbody');
                if (tbody && !tbody.hasChildNodes()) {
                    const container = document.querySelector('.table-responsive');
                    container.innerHTML = '<div class="no-questions">No questions found.</div>';
                }
            } else {
                alert('Error deleting question: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the question.');
        });
    }
}

function showQuestionSelection(assessmentId) {
    document.getElementById('assessment_id').value = assessmentId;
    document.getElementById('questionSelectionModal').style.display = 'block';
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

function closeQuestionSelection() {
    document.getElementById('questionSelectionModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const questionSelectionForm = document.getElementById('questionSelectionForm');

    if (questionSelectionForm) {
        questionSelectionForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'Add Questions');  

            fetch('php/assessment-queries/add_assessment_questions.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closeQuestionSelection(); 
                        location.reload();  
                    } else {
                        alert('Error adding questions: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding questions.');
                });
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="qa-text"]');
    const sortFilterForm = document.getElementById('filterForm');
    const questionTableBody = document.getElementById('qa-tbody');
    
    let originalQuestions = Array.from(questionTableBody.querySelectorAll('tr'));

    const applyFilters = () => {
        const filterValue = sortFilterForm.querySelector('select[name="filter"]').value;
        const sortValue = sortFilterForm.querySelector('select[name="sort"]').value;

        let rows = [...originalQuestions];

        if (filterValue !== 'default') {
            rows = rows.filter(row => {
                const type = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                const difficulty = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                return (
                    type === filterValue.toLowerCase() || 
                    difficulty === filterValue.toLowerCase()
                );
            });
        }

        if (sortValue !== 'default') {
            rows.sort((a, b) => {
                const idA = parseInt(a.querySelector('td:nth-child(1)').textContent, 10);
                const idB = parseInt(b.querySelector('td:nth-child(1)').textContent, 10);

                return sortValue === 'id-asc' ? idA - idB : idB - idA;
            });
        }

        questionTableBody.innerHTML = '';
        rows.forEach(row => questionTableBody.appendChild(row));
    };

    const performSearch = () => {
        const searchText = searchInput.value.toLowerCase();

        let rows = Array.from(questionTableBody.querySelectorAll('tr'));

        rows = rows.filter(row => {
            const questionText = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            return questionText.includes(searchText); 
        });

        questionTableBody.innerHTML = '';  
        rows.forEach(row => questionTableBody.appendChild(row));  
    };

    searchInput.addEventListener('input', performSearch);

    sortFilterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        applyFilters();
    });
});

function handleReturn() {
    if (document.referrer.includes('topic_assessments.php')) {
        history.back();
    } else {
        window.location.href = 'assessments.php';
    }
}
