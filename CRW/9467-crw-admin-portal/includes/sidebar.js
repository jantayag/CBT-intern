const express = require('express');
const router = express.Router();

router.use((req, res, next) => {
    if (!req.session.user) {
        return res.redirect('/');
    }
    next();
});
router.get('/', (req, res) => {
    const userType = req.session.user ? req.session.user.userType : '';
    let userImage = '';
    if (userType === 'Admin') {
        userImage = '<img src="/public/img/admin.png">';
    } else if (userType === 'Faculty') {
        userImage = '<img src="/public/img/faculty.png">';
    } else if (userType === 'Student') {
        userImage = '<img src="/public/img/student.png">';
    }

    res.send(`
        <section id="sidebar">
            <a href="/classes" class="brand">
                ${userImage}
            </a>
            <ul class="side-menu top">
                <li>
                    <a href="/classes">
                        <i class='bx bxs-smile'></i>
                        <span class="text">Home</span>
                    </a>
                </li>
                ${['Admin', 'Faculty'].includes(userType) ? `
                <li>
                    <a href="/questions">
                        <i class='bx bxs-smile'></i>
                        <span class="text">Questions</span>
                    </a>
                </li>
                <li>
                    <a href="/assessments">
                        <i class='bx bxs-smile'></i>
                        <span class="text">Assessments</span>
                    </a>
                </li>` : ''}
                ${userType === 'Admin' ? `
                <li>
                    <a href="/users.html">
                        <i class='bx bxs-smile'></i>
                        <span class="text">Users</span>
                    </a>
                </li>` : ''}
            </ul>
        </section>
    `);
});

module.exports = router;
