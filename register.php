<?php 
    include "get.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP Biñan Classroom Reservation and Scheduling System - Register</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="register-wrapper" style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; padding: 40px 20px; overflow-y: auto;">
        <div class="register-right" style="flex: none; width: 100%; max-width: 650px; background: none; padding: 0;">
            <div class="form-container" style="background: #ffffff; padding: 45px; border-radius: 12px; box-shadow: 0 4px 25px rgba(0,0,0,0.06);">

                <h2 style="margin-bottom: 8px; font-weight: 700; color: #1e293b;">Create Account</h2>
                <p class="form-instruction" style="margin-bottom: 30px; color: #64748b;">Select your role below to continue.</p>

                <label class="select-label" style="font-weight: 600; color: #334155; margin-bottom: 12px; display: block;">Select Your Role</label>
                <div class="role-selector" style="margin-bottom: 35px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
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

                <!-- Single form for all roles -->
                <form id="register-form" action="add.php" method="POST" style="display: flex; flex-direction: column; gap: 28px;">

                    <!-- Hidden role tracker -->
                    <input type="hidden" id="selected-role" name="selectedRole" value="1">

                    <!-- Account Credentials (shared) -->
                    <div>
                        <p class="section-title" style="margin-bottom: 16px; font-weight: 600; color: #730000; border-bottom: 2px solid #f1f2f6; padding-bottom: 6px;">
                            <i class="fa-solid fa-user-gear"></i> Account Credentials
                        </p>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div class="input-group">
                                <label for="reg-username" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Username *</label>
                                <input type="text" id="reg-username" name="username" placeholder="Create unique username" required style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                            </div>
                            <div class="input-group">
                                <label for="reg-password" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Password *</label>
                                <input type="password" id="reg-password" name="password" placeholder="Create secure password" required style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Personal Profile (shared) -->
                    <div>
                        <p class="section-title" style="margin-bottom: 16px; font-weight: 600; color: #730000; border-bottom: 2px solid #f1f2f6; padding-bottom: 6px;">
                            <i class="fa-solid fa-address-card"></i> Personal Profile
                        </p>
                        <div class="form-grid profile-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                            <div class="input-group">
                                <label for="reg-firstName" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">First Name *</label>
                                <input type="text" id="reg-firstName" name="firstName" placeholder="e.g., Juan" required style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                            </div>
                            <div class="input-group">
                                <label for="reg-middleName" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Middle Name</label>
                                <input type="text" id="reg-middleName" name="middleName" placeholder="e.g., Ramos" style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                            </div>
                            <div class="input-group">
                                <label for="reg-lastName" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Last Name *</label>
                                <input type="text" id="reg-lastName" name="lastName" placeholder="e.g., Dela Cruz" required style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                            </div>
                        </div>
                    </div>

                    <!-- ID Number field — shown for Student and Admin, hidden for Professor -->
                    <div class="input-group" id="container-idField" style="display: flex; flex-direction: column;">
                        <label id="lbl-idNumber" for="reg-idNumber" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Student Number *</label>
                        <input type="text" id="reg-idNumber" name="studentNumber" placeholder="e.g., 2023-00123-BN-0" required style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <!-- Role-specific section -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div id="dynamic-divider" style="margin-bottom: 4px; font-weight: 600; color: #730000; border-bottom: 2px solid #f1f2f6; padding-bottom: 6px;">
                            <p class="section-title"><i class="fa-solid fa-graduation-cap"></i> Academic Details</p>
                        </div>

                        <!-- STUDENT fields -->
                        <div id="student-fields" class="role-fields" style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                            <div class="input-group">
                                <label for="reg-sectionId" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Section Assignment *</label>
                                <select id="reg-sectionId" name="sectionId" style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; background-color: white;">
                                    <option value="">Select your section</option>
                                <?php while($row = mysqli_fetch_assoc($sqlSections)){ ?>
                                  
                                <option value="<?php echo $row['sectionId']; ?>"><?php echo htmlspecialchars($row['sectionCode']); ?></option>
                              
                                <?php } ?>
                                  </select>
                            </div>
                        </div>

                        <!-- PROFESSOR fields -->
                        <div id="professor-fields" class="role-fields hidden" style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                            <div class="input-group">
                                <label for="reg-profTypeId" style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #334155;">Faculty Type Classification *</label>
                                <select id="reg-profTypeId" name="profTypeId" style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; background-color: white;">
                                    <option value="" disabled selected>Select Status Classification</option>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                </select>
                            </div>
                        </div>

                        <!-- ADMIN fields -->
                        <div id="admin-fields" class="role-fields hidden"></div>
                    </div>

                    <!-- Single submit button — name changes based on selected role -->
                    <button type="submit" id="btn-submit" name="submitStudent" class="btn-register"
                        style="width: 100%; background-color: #730000; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px;">
                        Complete Registration
                    </button>

                </form>

                <div class="form-links" style="margin-top: 25px; text-align: center;">
                    <a href="login.php" class="login-link" style="color: #730000; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Already have an institutional account? Log in here</a>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const roleCards       = document.querySelectorAll('.role-card');
        const selectedRoleInput = document.getElementById('selected-role');
        const btnSubmit       = document.getElementById('btn-submit');

        const idFieldContainer = document.getElementById('container-idField');
        const lblIdNumber      = document.getElementById('lbl-idNumber');
        const inputIdNumber    = document.getElementById('reg-idNumber');

        const dynamicDivider   = document.getElementById('dynamic-divider');
        const studentFields    = document.getElementById('student-fields');
        const professorFields  = document.getElementById('professor-fields');
        const adminFields      = document.getElementById('admin-fields');

        const sectionSelect    = document.getElementById('reg-sectionId');
        const profTypeSelect   = document.getElementById('reg-profTypeId');

        function applyRole(role) {
            // Reset all role-specific fields
            studentFields.classList.add('hidden');
            professorFields.classList.add('hidden');
            adminFields.classList.add('hidden');
            idFieldContainer.classList.remove('hidden');

            // Remove required from all role-specific inputs
            sectionSelect.required  = false;
            profTypeSelect.required = false;
            inputIdNumber.required  = false;

            if (role === 1) {
                // Student
                lblIdNumber.textContent = 'Student Number *';
                inputIdNumber.name      = 'studentNumber';
                inputIdNumber.placeholder = 'e.g., 2023-00123-BN-0';
                inputIdNumber.required  = true;

                dynamicDivider.innerHTML = '<p class="section-title"><i class="fa-solid fa-graduation-cap"></i> Academic Details</p>';
                studentFields.classList.remove('hidden');
                sectionSelect.required = true;

                btnSubmit.name = 'submitStudent';

            } else if (role === 2) {
                // Professor — no ID number field
                idFieldContainer.classList.add('hidden');

                dynamicDivider.innerHTML = '<p class="section-title"><i class="fa-solid fa-book-open-reader"></i> Faculty Settings</p>';
                professorFields.classList.remove('hidden');
                profTypeSelect.required = true;

                btnSubmit.name = 'submitProfessor';

            } else if (role === 3) {
                // Admin — no extra fields, just shared credentials + name
                idFieldContainer.classList.add('hidden');

              //  dynamicDivider.innerHTML = '<p class="section-title"><i class="fa-solid fa-user-shield"></i> Admin Account</p>';

                btnSubmit.name = 'submitAdmin';
            }

            selectedRoleInput.value = role;
        }

        // Card click handler
        roleCards.forEach(card => {
            card.addEventListener('click', () => {
                roleCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                applyRole(parseInt(card.getAttribute('data-role')));
            });
        });

        // Apply default role on page load
        applyRole(1);
    });
    </script>
</body>
</html>
