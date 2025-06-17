document.addEventListener('DOMContentLoaded', () => {
    const dragDropArea = document.getElementById('drag-drop-area');
    const fileInput = document.getElementById('csv-upload');
    const fileNameDisplay = document.getElementById('file-name');

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        dragDropArea.classList.add('highlight');
    }
    
    function unhighlight() {
        dragDropArea.classList.remove('highlight');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
    
        fileInput.files = files;
        fileNameDisplay.textContent = `Selected File: ${files[0].name}`;
    }
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dragDropArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dragDropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dragDropArea.addEventListener(eventName, unhighlight, false);
    });

    dragDropArea.addEventListener('drop', handleDrop, false);
    dragDropArea.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', (e) => {
        if (fileInput.files.length > 0) {
            fileNameDisplay.textContent = `Selected File: ${fileInput.files[0].name}`;
        } else {
            fileNameDisplay.textContent = '';
        }
    });
});