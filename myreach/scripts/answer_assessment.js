const assessmentId = document.querySelector('input[name="assessment_id"]').value;
const topicId = document.querySelector('input[name="topic_id"]').value;
const timeRemainingElement = document.getElementById('time-remaining');
const form = document.querySelector('form');

let answers = {};
let hasUnsavedChanges = false;

function saveAnswersDraft() {
    const formData = new FormData();
    formData.append('assessment_id', assessmentId);
    formData.append('answers', JSON.stringify(answers));

    fetch('php/student-queries/save_draft_answers.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to save draft:', data.error);
            }
        })
        .catch(error => {
            console.error('Error saving draft:', error);
        });

    hasUnsavedChanges = false;
}

function loadSavedAnswers() {
    fetch(`php/student-queries/get_saved_answers.php?assessment_id=${assessmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.answers) {
                answers = data.answers;

                Object.entries(answers).forEach(([questionId, answer]) => {
                    // Handle text inputs (e.g., identification questions)
                    const textInput = document.querySelector(`input[name="answers[${questionId}]"][type="text"]`);
                    if (textInput) {
                        textInput.value = answer;
                    }

                    // Handle radio inputs (e.g., multiple-choice questions)
                    const radioInput = document.querySelector(`input[name="answers[${questionId}]"][type="radio"][value="${answer}"]`);
                    if (radioInput) {
                        radioInput.checked = true;
                    }
                });
            } else {
                console.warn('No answers found or failed to load answers.');
            }
        })
        .catch(error => {
            console.error('Error loading saved answers:', error);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    loadSavedAnswers();
    fetchTimeRemaining();

    form.addEventListener('submit', function (event) {
        if (!confirm('Are you sure you want to submit your answers?')) {
            event.preventDefault();
            return;
        }
        if (hasUnsavedChanges) {
            saveAnswersDraft();
        }
    });

    document.querySelectorAll('input[type="text"], input[type="radio"]').forEach(input => {
        input.addEventListener('change', function () {
            const questionId = this.name.match(/\d+/)[0];
            answers[questionId] = this.type === 'radio' ? this.value : this.value.trim();
            hasUnsavedChanges = true;
        });
    });
});

setInterval(() => {
    if (hasUnsavedChanges) {
        saveAnswersDraft();
    }
}, 30000);

window.addEventListener('beforeunload', function (e) {
    if (hasUnsavedChanges) {
        const formData = new FormData();
        formData.append('assessment_id', assessmentId);
        formData.append('answers', JSON.stringify(answers));

        navigator.sendBeacon('php/student-queries/save_draft_answers.php', formData);
        hasUnsavedChanges = false;
    }
});

document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden' && hasUnsavedChanges) {
        const formData = new FormData();
        formData.append('assessment_id', assessmentId);
        formData.append('answers', JSON.stringify(answers));

        navigator.sendBeacon('php/student-queries/save_draft_answers.php', formData);
        hasUnsavedChanges = false;
    }
});

function startCountdown(evaluationEndDate) {
    const countdownTimer = setInterval(() => {
        const now = new Date();
        const timeRemaining = Math.max(0, (evaluationEndDate - now) / 1000);

        if (timeRemaining <= 0) {
            clearInterval(countdownTimer);
            timeRemainingElement.textContent = 'Time is up!';
            form.submit();
            return;
        }

        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = Math.floor(timeRemaining % 60);

        timeRemainingElement.textContent =
            `${hours.toString().padStart(2, '0')}:` +
            `${minutes.toString().padStart(2, '0')}:` +
            `${seconds.toString().padStart(2, '0')}`;
    }, 1000);
}

function fetchTimeRemaining() {
    fetch(`php/student-queries/get_time_remaining.php?assessment_id=${assessmentId}&topic_id=${topicId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                timeRemainingElement.textContent = 'Timer Error';
                return;
            }
            startCountdown(new Date(data.evaluation_end));
        })
        .catch(error => {
            console.error('Error:', error);
            timeRemainingElement.textContent = 'Timer Error';
        });
}
