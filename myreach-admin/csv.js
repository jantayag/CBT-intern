function uploadStudentCSV() {
    document.getElementById("student-csv").click();

    document.getElementById("student-csv").addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            const csvText = event.target.result;
            const lines = csvText.split("\n").map(line => line.trim()).filter(line => line);

            const users = [];
            for (let i = 1; i < lines.length; i++) { // Skip header
                const [idNumber, name, courseYear, email, status] = lines[i].split(",");

                if (!idNumber || !name || !email) continue;

                const [firstName, ...rest] = name.trim().split(" ");
                const lastName = rest.join(" ") || "-";

                users.push({
                    email: email.trim(),
                    password: idNumber.trim(), // password same as ID number
                    first_name: firstName,
                    last_name: lastName,
                    user_type: "Student"
                });
            }

            // Send to backend
            fetch('/api/users/bulk_add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ users })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Upload completed');
            })
            .catch(err => console.error('Error uploading:', err));
        };
        reader.readAsText(file);
    });
}
