document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const roleCards = document.querySelectorAll('.role-card');
    const dynamicIcon = document.getElementById('dynamic-icon');
    const dynamicTitle = document.getElementById('dynamic-title');
   
    let activeRoleId = 1; // Default: Student


    // Role switcher animation layout rules
    roleCards.forEach(card => {
        card.addEventListener('click', () => {
            roleCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
           
            activeRoleId = parseInt(card.getAttribute('data-role'));
           
            if (activeRoleId === 1) {
                dynamicIcon.className = "fa-solid fa-graduation-cap";
                dynamicTitle.textContent = "Student Login";
            } else if (activeRoleId === 2) {
                dynamicIcon.className = "fa-solid fa-book-open-reader";
                dynamicTitle.textContent = "Professor Login";
            } else if (activeRoleId === 3) {
                dynamicIcon.className = "fa-solid fa-user-shield";
                dynamicTitle.textContent = "Administrator Login";
            }
        });
    });

});

