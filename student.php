<?php 
    include "get.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | PUP Biñan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --maroon: #6a040f; --gold: #ffba08; --light: #f4f4f4; --border: #ddd; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; height: 100vh; background: var(--light); }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--maroon); color: white; padding: 20px; display: flex; flex-direction: column; }
        .brand { font-size: 1.2rem; font-weight: bold; margin-bottom: 30px; color: var(--gold); }
        .nav-item { padding: 12px 15px; cursor: pointer; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: #8e0b17; border-left: 4px solid var(--gold); }
        .sidebar-label { padding: 20px 15px 5px; font-size: 0.7rem; color: rgba(255,255,255,0.6); text-transform: uppercase; font-weight: bold; cursor: default; }

        /* Content */
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; border-top: 5px solid var(--maroon); }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; border-radius: 8px; text-align: center; border-top: 5px solid var(--maroon); }
        
        /* Form & Table */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; grid-column: span 2; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        input, select, textarea { padding: 10px; border: 1px solid var(--border); border-radius: 5px; background: #fafafa; }
        .btn-submit { background: var(--maroon); color: white; padding: 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #fdf2f2; padding: 12px; text-align: left; color: var(--maroon); border-bottom: 2px solid var(--border); }
        td { padding: 12px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand"><i class="fa fa-calendar-check"></i> PUP System</div>
        <div class="nav-item active" onclick="loadPage('Dashboard', this)"><i class="fa fa-tachometer-alt"></i> Dashboard</div>
        <div class="nav-item" onclick="loadPage('My Reservations', this)"><i class="fa fa-list"></i> My Reservations</div>
        <div class="nav-item" onclick="loadPage('New Reservation', this)"><i class="fa fa-plus-circle"></i> New Reservation</div>
        
        <div class="sidebar-label">ACCOUNT</div>
        <div class="nav-item" onclick="loadPage('Profile', this)"><i class="fa fa-user"></i> Profile</div>
        <div class="nav-item" onclick="logout()"><i class="fa fa-sign-out-alt"></i> Logout</div>
    </div>

    <div class="main-content" id="content"></div>

    <script>
        function loadPage(page, element) {
            if(element) {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                element.classList.add('active');
            }
            const content = document.getElementById('content');
            
            if(page === 'Dashboard') {
                content.innerHTML = `<h1>Student Dashboard</h1>
                <div class="stats-grid">
                    <div class="stat-box"><i class="fa-solid fa-check-circle" style="color:green; font-size:1.5rem"></i><h3>Approved</h3><p><?php echo $countSApproved; ?></p></div>
                    <div class="stat-box"><i class="fa-solid fa-clock" style="color:orange; font-size:1.5rem"></i><h3>Pending</h3><p><?php echo $countSPending; ?></p></div>
                    <div class="stat-box"><i class="fa-solid fa-circle-xmark" style="color:red; font-size:1.5rem"></i><h3>Rejected</h3><p><?php echo $countSRejected; ?></p></div>
                </div>`;
            } else if(page === 'My Reservations') {
                content.innerHTML = `<h1>My Reservations</h1><div class="card"><table>
                    <thead><tr><th>Reservation ID</th><th>Classroom</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Purpose</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                        if (mysqli_num_rows($sqlStudentReservations) > 0) {
                            while ($r = mysqli_fetch_assoc($sqlStudentReservations)) {
                                $color = $r['status'] === 'Approved' ? 'green' : ($r['status'] === 'Pending' ? 'orange' : 'red');
                                echo "<tr>
                                    <td>{$r['reservationId']}</td>
                                    <td>{$r['roomName']}</td>
                                    <td>{$r['reservationDate']}</td>
                                    <td>{$r['startTime']}</td>
                                    <td>{$r['endTime']}</td>
                                    <td>{$r['purpose']}</td>
                                    <td><span style='color:{$color}; font-weight:bold;'>{$r['status']}</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; color:#999;'>No reservations found.</td></tr>";
                        }
                    ?>
                    </tbody>
                </table></div>`;
            } else if(page === 'New Reservation') {
                content.innerHTML = `<h1>New Reservation Request</h1>
                <div style="display: flex; gap: 20px;">
                    <div class="card" style="flex: 2;">
                        <form method="POST" action="add.php" required>
                            <div class="form-grid">
                                <div class="form-group"><label>Classroom</label>
                                    <select name="classroom" id="classroom" required>
                                      <option value="default">Select..</option>
                                          <?php while($c = mysqli_fetch_array($sqlRooms)) { ?>
                                            <option value="<?php echo $c['roomId']; ?>"><?php echo htmlspecialchars($c['roomName']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column: span 2;"><label>Reserve Date</label><input type="date" name="reserveDate" required></div>
                                <div class="time-grid">
                                    <div class="form-group"><label>Start Time</label><input type="time" name="startTime" required></div>
                                    <div class="form-group"><label>End Time</label><input type="time" name="endTime" required></div>
                                </div>
                                <div class="form-group" style="grid-column: span 2;"><label>Reserve Purpose</label><textarea name="reservePurpose" required></textarea></div>
                                <button type="submit" class="btn-submit" name="submitStudentClassroom" style="background:var(--gold); color:black;">SUBMIT PRIORITY REQUEST</button>
                            </div>
                        </form>
                    </div>
                    <div style="flex: 1;">
                        <div class="card" style="border-top: 5px solid var(--gold);">
                            <h3><i class="fa fa-calendar-alt"></i> Classroom Availability</h3>
                            <ul style="margin-top: 15px; list-style: none;">
                                <?php
                                $sqlAR = mysqli_query($connection, "SELECT roomName, roomFloor FROM classroom WHERE roomStatus = 'Available' ORDER BY roomFloor, roomName");
                                if (mysqli_num_rows($sqlAR) > 0) {
                                    while ($ar = mysqli_fetch_assoc($sqlAR)) {
                                        echo "<li style='padding:8px 0; border-bottom:1px solid #eee;'><i class='fa fa-check-circle' style='color:green;'></i> " . htmlspecialchars($ar['roomName']) . " (" . htmlspecialchars($ar['roomFloor']) . "): Available</li>";
                                    }
                                } else {
                                    echo "<li style='padding:8px 0; color:#999;'><i class='fa fa-times-circle' style='color:red;'></i> No classrooms available</li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>`;
            } else if(page === 'My Schedule') {
            } else if(page === 'Profile') {
                content.innerHTML = `<h1>My Profile</h1>
                <div class="card">
                    <div class="form-grid">
                        <div class="form-group"><label>Student Number</label><input type="text" value="<?php echo htmlspecialchars($studentProfile['studentNumber'] ?? ''); ?>" readonly></div>
                        <div class="form-group"><label>First Name</label><input type="text" value="<?php echo htmlspecialchars($studentProfile['firstName'] ?? ''); ?>" readonly></div>
                        <div class="form-group"><label>Middle Name</label><input type="text" value="<?php echo htmlspecialchars($studentProfile['middleName'] ?? ''); ?>" readonly></div>
                        <div class="form-group"><label>Last Name</label><input type="text" value="<?php echo htmlspecialchars($studentProfile['lastName'] ?? ''); ?>" readonly></div>
                    </div>
                </div>`;
            }
        }
        function logout() { if(confirm("Logout na?")) window.location.href = 'login.php'; }
        loadPage('Dashboard');
    </script>
</body>
</html>