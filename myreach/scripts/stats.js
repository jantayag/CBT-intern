document.addEventListener('DOMContentLoaded', async () => {
    const classCode = document.getElementById('class_code').value;
    const response = await fetch(`php/stat-queries/get_stats.php?class_code=${classCode}`);
    const data = await response.json();
    const getRandomColor = () => {
        const r = Math.floor(Math.random() * 256);
        const g = Math.floor(Math.random() * 256);
        const b = Math.floor(Math.random() * 256);
        return `rgba(${r}, ${g}, ${b}, 0.6)`; 
    };

    if (data.assessmentPerformance.length > 0) {
        const assessmentLabels = data.assessmentPerformance.map(a => a.title);
        const assessmentScores = data.assessmentPerformance.map(a => a.average_score);

        new Chart(document.getElementById('assessmentChart'), {
            type: 'bar',
            data: {
                labels: assessmentLabels,
                datasets: [{
                    label: 'Average Score',
                    data: assessmentScores,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Assessment Performance Overview',
                        font: {
                            size: 20,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    }
                }
            }
        });
    }

    if (data.questionPerformance.length > 0) {
        const questionLabels = data.questionPerformance.map(q => q.question_text);
        const questionAccuracy = data.questionPerformance.map(q => 
            ((q.correct_count / q.total_attempts) * 100).toFixed(2)
        );
        const participationRate = data.questionPerformance.map(q => 
            ((q.unique_students / q.total_class_students) * 100).toFixed(2)
        );
    
        new Chart(document.getElementById('questionChart'), {
            type: 'bar',
            data: {
                labels: questionLabels,
                datasets: [
                    {
                        label: 'Accuracy (%)',
                        data: questionAccuracy,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        order: 2
                    },
                    {
                        label: 'Student Participation (%)',
                        data: participationRate,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        type: 'line',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false,
                        tension: 0.4,
                        order: 1
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                plugins: {
                    title: {
                        display: true,
                        text: 'Question Performance Analysis',
                        font: {
                            size: 20,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Accuracy (%)') {
                                    const questionData = data.questionPerformance[context.dataIndex];
                                    return [
                                        `Accuracy: ${context.raw}%`
                                    ];
                                } else if (context.dataset.label === 'Student Participation (%)') {
                                    const questionData = data.questionPerformance[context.dataIndex];
                                    return [
                                        `Participation: ${context.raw}%`,
                                        `Students who attempted: ${questionData.unique_students}/${questionData.total_class_students}`
                                    ];
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                }
            }
        });
    }
    

    if (data.topicEngagement.length > 0) {
        const topicLabels = data.topicEngagement.map(t => t.topic);
        const topicCounts = data.topicEngagement.map(t => t.assessment_count);
        const backgroundColors = topicLabels.map(() => getRandomColor());
    
        new Chart(document.getElementById('topicChart'), {
            type: 'doughnut',
            data: {
                labels: topicLabels,
                datasets: [{
                    label: 'Assessments per Topic',
                    data: topicCounts,
                    backgroundColor: backgroundColors
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Assessment Distribution',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    }
                }
            }
        });
    }
    
    if (data.assessmentParticipation.length > 0) {
        const participationLabels = data.assessmentParticipation.map(p => p.participation_level);
        const participationCounts = data.assessmentParticipation.map(p => p.assessment_count);

        new Chart(document.getElementById('participationChart'), {
            type: 'doughnut',
            data: {
                labels: participationLabels,
                datasets: [{
                    data: participationCounts,
                    backgroundColor: [
                         'rgba(231, 76, 60, 0.6)',
                         'rgba(230, 126, 34, 0.6)', 
                         'rgba(241, 196, 15, 0.6)', 
                         'rgba(52, 152, 219, 0.6)', 
                         'rgba(46, 204, 113, 0.6)',  
                    ]
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Assessment Participation Rates',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    }
                }
            }
        });
    }

    if (data.performanceDistribution.length > 0) {
        const performanceLabels = data.performanceDistribution.map(p => p.performance_level);
        const studentCounts = data.performanceDistribution.map(p => p.student_count);

        const performanceColors = {
            'Needs Improvement (<60%)': 'rgba(231, 76, 60, 0.6)',
            'Fair (60-69%)': 'rgba(230, 126, 34, 0.6)',           
            'Good (70-79%)': 'rgba(241, 196, 15, 0.6)',           
            'Very Good (80-89%)': 'rgba(52, 152, 219, 0.6)',       
            'Excellent (90-100%)': 'rgba(46, 204, 113, 0.6)'       
        };

        const backgroundColors = performanceLabels.map(label => performanceColors[label]);
    
        new Chart(document.getElementById('performanceChart'), {
            type: 'doughnut',
            data: {
                labels: performanceLabels,
                datasets: [{
                    data: studentCounts,
                    backgroundColor: backgroundColors
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Student Performance Distribution',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw} students (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    

    document.getElementById('studentCount').textContent = `Total Students: ${data.studentParticipation}`;
});