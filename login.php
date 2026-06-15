<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP Biñan Classroom Reservation and Scheduling System</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="login-wrapper">
        <div class="login-left">
            <div class="brand-overlay"></div>
            <div class="brand-content">
                <div class="logo-container">
                    <div class="pup-logo">
                        <i class="fa-solid fa-star star-main"></i>
                    </div>
                </div>
                <h1>Classroom Reservation and Scheduling System</h1>
                <p class="subtitle">Polytechnic University of the Philippines<br>Biñan Campus</p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-calendar-days"></i></div>
                        <div class="feature-text">
                            <strong>Reserve classrooms</strong>
                            <p>Easily reserve rooms for your classes or meetings.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-clock"></i></div>
                        <div class="feature-text">
                            <strong>Manage schedules</strong>
                            <p>View and manage your upcoming reservations.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="feature-text">
                            <strong>Track status</strong>
                            <p>Stay updated on the approval status of your reservations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <div class="form-container">
                <h2>Login</h2>
                <p class="form-instruction">Please select your user type to continue.</p>
                
                <label class="select-label">Select Your Role</label>
                <div class="role-selector">
                    <div class="role-card active" data-role="1">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>Student</span>
                    </div>
                    <div class="role-card" data-role="2">
                        <i class="fa-solid fa-book-open-reader"></i>
                        <span>Professor</span>
                    </div>
                    <div class="role-card" data-role="3">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Administrator</span>
                    </div>
                </div>
                
                <div class="dynamic-heading">
                    <i class="fa-solid fa-graduation-cap" id="dynamic-icon"></i>
                    <h3 id="dynamic-title">Student Login</h3>
                </div>
                
                <form id="login-form" action="login2.php" method="POST">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-field-wrapper">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="username" id="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-field-wrapper">
                            <i class="fa-solid fa-key input-icon"></i>
                            <input type="password" name="password" id="password" placeholder="Enter your password" required>
                            <i class="fa-solid fa-eye toggle-password" id="view-password"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login" name="Submit">Login</button>
                </form>
                
                <div class="form-links">
                    <a href="#" class="forgot-pass">Forgot Password?</a>
                    <a href="register.php" class="register-link">Don't have an account? Register here</a>
                </div>
                
                <footer class="login-footer">
                    © 2026 Classroom Reservation and Scheduling Management System.<br>All rights reserved.
                </footer>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>