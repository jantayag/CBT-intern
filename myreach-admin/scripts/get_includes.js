fetch('/sidebar')
    .then(response => {
        if (!response.ok) {
            throw new Error(`Sidebar fetch failed: ${response.status}`);
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('sidebar-container').innerHTML = html;
    })
    .catch(error => console.error('Sidebar fetch error:', error));

fetch('/nav')
    .then(response => {
        if (!response.ok) {
            throw new Error(`Nav fetch failed: ${response.status}`);
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('nav-container').innerHTML = html;
    })
    .catch(error => console.error('Nav fetch error:', error));
