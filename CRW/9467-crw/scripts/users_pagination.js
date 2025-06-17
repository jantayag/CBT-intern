document.addEventListener('DOMContentLoaded', () => {
    const userRows = Array.from(document.querySelectorAll('#usersTable tbody tr'));
    const usersPagination = new Pagination({
        containerId: 'users-container',
        items: userRows,
        itemsPerPage: 10,
        renderItem: (row) => row.cloneNode(true),
        tableId: 'usersTable',
        tableClass: 'table-responsive',
        itemsText: 'users'
    });
});