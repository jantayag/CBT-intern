const modal = document.createElement('div');
modal.className = 'modal';
document.body.appendChild(modal);


const form = document.getElementById('questionForm');
const modalContent = document.createElement('div');
modalContent.className = 'modal-content';
modal.appendChild(modalContent);
modalContent.appendChild(form);

function addQuestion() {
    resetForm();
    modal.style.display = 'block';
}

function cancelForm() {
    modal.style.display = 'none';
    resetForm();
}

function resetForm() {
    document.getElementById('questionForm').reset();
    hideAllAnswerContainers();
    
    document.querySelector('.question-form-heading').textContent = 'Add New Question';
    document.querySelector('.view-btn[type="submit"]').style.display = '';
    document.querySelector('.save-btn').style.display = 'none';
    
    const questionIdInput = document.querySelector('input[name="question_id"]');
    if (questionIdInput) {
        questionIdInput.remove();
    }
}

window.onclick = function(event) {
    if (event.target === modal) {
        cancelForm();
    }
    const modal2 = document.querySelector('.modal-answers').parentElement;
    const modalContent = document.querySelector('.answer-modal-content');
    if (event.target === modal2 && !modalContent.contains(event.target)) {
        closeAnswersModal();
    }
}

document.getElementById('type').addEventListener('change', function() {
    hideAllAnswerContainers();
    const selectedType = this.value;
    
    switch(selectedType) {
        case 'alternate-response':
            document.getElementById('alternate-response').style.display = 'block';
            break;
        case 'mc':
            document.getElementById('mc').style.display = 'block';
            break;
        case 'identification':
            document.getElementById('identification').style.display = 'block';
            break;
    }
});

document.getElementById('questionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const isEdit = formData.has('question_id');
    const url = isEdit ? 'php/question-queries/edit_question.php' : 'php/question-queries/add_question.php';
    const fileInput = document.getElementById('csv-upload');
            
    if (fileInput && fileInput.files.length > 0) {
        formData.append('csv-upload', fileInput.files[0]);
    }
    
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
            alert(data.message);
        }
    })
    .catch(error => console.error(error));
});

function hideAllAnswerContainers() {
    const containers = document.getElementsByClassName('answer-container');
    for (let container of containers) {
        container.style.display = 'none';
    }
}


function addChoice() {
    const container = document.getElementById('choices-container');
    const choiceCount = container.getElementsByClassName('choice').length;
    
    const choiceDiv = document.createElement('div');
    choiceDiv.className = 'choice';
    choiceDiv.innerHTML = `
        <input type="text" name="choice[]" placeholder="Choice ${choiceCount + 1}" />
        <input type="radio" name="correctChoice" value="${choiceCount}" /> Correct
        <button class="del-btn" type="button" onclick="removeChoice(this)">Remove</button>
    `;
    
    container.appendChild(choiceDiv);
}

function removeChoice(button) {
    const choiceDiv = button.parentElement;
    choiceDiv.remove();
    
    const choices = document.getElementsByClassName('choice');
    for (let i = 0; i < choices.length; i++) {
        const choiceInput = choices[i].getElementsByTagName('input')[0];
        const radioInput = choices[i].getElementsByTagName('input')[1];
        choiceInput.placeholder = `Choice ${i + 1}`;
        radioInput.value = i;
    }
}

// view answers script
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
                    const color = answer.is_answer === 'Y' ? '#ddf0dd' : '#f0dddd'; // if is_answer set text color to green, else red
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

// delete question script
function deleteQuestion(questionId) {
    if (!confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
        return;
    }
    fetch('php/question-queries/del_question.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `question_id=${questionId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr:has(button[onclick="deleteQuestion(${questionId})"])`);
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
    });
}

// edit question script
function editQuestion(questionId) {
    fetch(`php/question-queries/get_question_details.php?question_id=${questionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('questionForm');
                
                document.querySelector('.question-form-heading').textContent = 'Edit Question';

                form.querySelector('.view-btn[type="submit"]').style.display = 'none';
                form.querySelector('.save-btn').style.display = '';
 
                let questionIdInput = form.querySelector('input[name="question_id"]');
                if (!questionIdInput) {
                    questionIdInput = document.createElement('input');
                    questionIdInput.type = 'hidden';
                    questionIdInput.name = 'question_id';
                    form.appendChild(questionIdInput);
                }
                questionIdInput.value = questionId;
                
                document.getElementById('question_text').value = data.question.question_text;
                document.getElementById('difficulty').value = data.question.difficulty;
                document.getElementById('points').value = data.question.points;
                document.getElementById('type').value = data.question.type;
                
                const event = new Event('change');
                document.getElementById('type').dispatchEvent(event);
                
                switch(data.question.type) {
                    case 'alternate-response':
                        const correctAnswer = data.answers.find(a => a.is_answer === 'Y');
                        if (correctAnswer) {
                            document.querySelector(`input[name="answer"][value="${correctAnswer.text}"]`).checked = true;
                        }
                        break;
                        
                    case 'mc':
                        const choicesContainer = document.getElementById('choices-container');
                        choicesContainer.innerHTML = ''; 
                        
                        data.answers.forEach((answer, index) => {
                            const choiceDiv = document.createElement('div');
                            choiceDiv.className = 'choice';
                            choiceDiv.innerHTML = `
                                <input type="text" name="choice[]" placeholder="Choice ${index + 1}" value="${answer.text}" />
                                <input type="radio" name="correctChoice" value="${index}" ${answer.is_answer === 'Y' ? 'checked' : ''} /> Correct
                                <button class="del-btn" type="button" onclick="removeChoice(this)">Remove</button>
                            `;
                            choicesContainer.appendChild(choiceDiv);
                        });
                        break;
                        
                    case 'identification':
                        const correctIdentAnswer = data.answers.find(a => a.is_answer === 'Y');
                        if (correctIdentAnswer) {
                            document.getElementById('identificationAnswer').value = correctIdentAnswer.text;
                        }
                        break;
                }
            
                modal.style.display = 'block';
            } else {
                alert('Error loading question: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading question');
        });
}
