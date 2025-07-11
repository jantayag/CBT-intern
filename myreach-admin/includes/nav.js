const express = require('express');
const router = express.Router();

router.use((req, res, next) => {
    if (!req.session.user) {
        return res.redirect('/');
    }
    next();
});

router.get('/', (req, res) => {
    const firstName = req.session.user ? req.session.user.firstName : '';
    const lastName = req.session.user ? req.session.user.lastName : '';
    res.send(`
        <nav id="nav">
            <i class='bx bx-menu'></i>
            <div class="profile">
                <div class="admin-name">
                    <h1>${firstName} ${lastName}</h1>
                    <a href="/logout">Logout</a>
                </div>
                <img src="/public/img/goat.jpg" alt="admin-avatar">
            </div>
        </nav>
    `);
});

module.exports = router;
