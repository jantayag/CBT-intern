document.addEventListener('DOMContentLoaded', () => {
    const questionRows = Array.from(document.querySelectorAll('#questionsTable tbody tr'));
    const questionsPagination = new Pagination({
        containerId: 'questions-container', 
        items: questionRows,
        itemsPerPage: 10,
        renderItem: (row) => row.cloneNode(true),
        tableId: 'questionsTable',
        tableClass: 'table-responsive',
        itemsText: 'questions'
    });
});