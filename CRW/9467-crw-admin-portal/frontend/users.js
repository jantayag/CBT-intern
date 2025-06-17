// scripts/users.js
async function fetchUsers(searchParams = new URLSearchParams()) {
    try {
        const response = await fetch(`/api/users?${searchParams.toString()}`);
        if (!response.ok) throw new Error('Failed to fetch users');
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return [];
    }
}

function renderUsers(users) {
    const userList = document.getElementById('user-list');
    
    const html = `
        <div class="heading">
            <h1>User Management</h1>
            <div class="filter-section">
                <form id="filterForm">
                    <input type="text" name="search" class="search-bar" 
                           placeholder="Search user..." value="${new URLSearchParams(window.location.search).get('search') || ''}">

                    <select name="filter" id="sortnfilter">
                        <option value="default">Filter by: Default</option>
                        <option value="admin">Filter by: Type (Admin)</option>
                        <option value="student">Filter by: Type (Student)</option>
                        <option value="faculty">Filter by: Type (Faculty)</option>
                    </select>

                    <select name="sort" id="sortnfilter">
                        <option value="default">Sort by: Default</option>
                        <option value="lastName (A-Z)">Sort by: Last Name (A-Z)</option>
                        <option value="lastName (Z-A)">Sort by: Last Name (Z-A)</option>
                        <option value="firstName (A-Z)">Sort by: First Name (A-Z)</option>
                        <option value="firstName (Z-A)">Sort by: First Name (Z-A)</option>
                    </select>

                    <button type="submit" class="action-btn">Apply Filters</button>
                </form>
                <div class="question-actions">
                    <button class="action-btn" onclick="showUserCreator()">Create User</button>
                </div>
            </div>
        </div>
        ${users.length > 0 ? `
            <div id="users-container">
                <div class="table-responsive">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-tbody">
                            ${users.map((user, index) => `
                                <tr id="users-${user.id}">
                                    <td>${index + 1}</td>
                                    <td>${user.first_name} ${user.last_name}</td>
                                    <td>${user.user_type}</td>
                                    <td>${user.email}</td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" onclick="editUser(${user.id})">Edit</button>
                                        <button class="del-btn" onclick="deleteUser(${user.id})">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        ` : '<div class="no-questions">No users found.</div>'}
    `;
    
    userList.innerHTML = html;

    // Initialize pagination after rendering users
    if (users.length > 0) {
        const userRows = Array.from(document.querySelectorAll('#usersTable tbody tr'));
        new Pagination({
            containerId: 'users-container',
            items: userRows,
            itemsPerPage: 10,
            renderItem: (row) => row.cloneNode(true),
            tableId: 'usersTable',
            tableClass: 'table-responsive',
            itemsText: 'users'
        });
    }

    // Set up form handler
    document.getElementById('filterForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const searchParams = new URLSearchParams(formData);
        const users = await fetchUsers(searchParams);
        renderUsers(users);
        
        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({ path: newUrl }, '', newUrl);
    });
}

// Initial load
async function initializeUsers() {
    const searchParams = new URLSearchParams(window.location.search);
    const users = await fetchUsers(searchParams);
    renderUsers(users);
}

// Call initialization when document is ready
document.addEventListener('DOMContentLoaded', initializeUsers);