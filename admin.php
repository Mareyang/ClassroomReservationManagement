<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";
include "get.php";

// Build reservations array for JS
$reservationsForJS = [];
while ($row = mysqli_fetch_assoc($sqlAllReservations)) {
    $reservationsForJS[] = [
        'id'            => $row['reservationId'],
        'requestor'     => htmlspecialchars($row['requesterName']),
        'type'          => htmlspecialchars($row['requesterType']),
        'room'          => htmlspecialchars($row['roomName']),
        'date'          => htmlspecialchars($row['reservationDate']),
        'time'          => htmlspecialchars($row['startTime']) . ' - ' . htmlspecialchars($row['endTime']),
        'purpose'       => htmlspecialchars($row['purpose']),
        'status'        => strtoupper($row['status']),
        'approvedBy'    => $row['approvedBy'],
    ];
}

$queryStudents = "SELECT studentId, studentNumber, firstName, middleName, lastName, sectionId FROM student ORDER BY lastName, firstName";
$sqlStudents = mysqli_query($connection, $queryStudents);
if (!$sqlStudents) { $sqlStudents = null; } // prevent fatal error if query fails

$queryProfessors = "SELECT professorId, employeeNumber, firstName, middleName, lastName, professorType FROM professor ORDER BY lastName, firstName";
$sqlProfessors = mysqli_query($connection, $queryProfessors);
if (!$sqlProfessors) { $sqlProfessors = null; }

$queryCourses = "SELECT * FROM course";
$sqlCourses = mysqli_query($connection, $queryCourses);

$queryPrograms = "SELECT programId, programName FROM program ORDER BY programName";
$sqlPrograms = mysqli_query($connection, $queryPrograms);

$queryClassrooms = "SELECT roomId, roomName, roomFloor, roomCapacity, roomStatus FROM classroom ORDER BY roomFloor, roomName";
$sqlClassrooms = mysqli_query($connection, $queryClassrooms);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Reservation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --maroon-main: #500408; /* Exact deep maroon background from images */
            --maroon-hover: #3d0306;
            --gold-accent: #E5A93C; /* Warm gold typography accent */
            --body-bg: #F3F4F6;
            --card-white: #FFFFFF;
            --text-dark: #1F2937;
            --text-muted: #6B7280;
            --border-gray: #E5E7EB;
            
            /* UI Chips Color System */
            --status-available-bg: #DEF7EC;
            --status-available-text: #03543F;
            --status-occupied-bg: #4c050a;
            --status-occupied-text: #FFFFFF;
            --status-reserved-bg: #FFFBEB;
            --status-reserved-text: #D97706;
            
            --approved-bg: #E6FFFA;
            --approved-text: #234E52;
            --approved-border: #B2F5EA;
            --pending-bg: #FEFCBF;
            --pending-text: #744210;
            --pending-border: #FEEBC8;
            --rejected-bg: #FED7D7;
            --rejected-text: #742A2A;
            --rejected-border: #FEB2B2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            display: flex;
            background-color: var(--body-bg);
            color: var(--text-dark);
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* --- SIDEBAR CONSOLE NAVIGATION --- */
        .sidebar {
            width: 260px;
            background-color: var(--maroon-main);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            color: var(--gold-accent);
        }

        .user-profile {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .profile-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .profile-info .name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--gold-accent);
        }

        .profile-info .role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .menu-section-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.4);
            padding: 15px 20px 5px;
            font-weight: 700;
        }

        .sidebar-menu {
            list-style: none;
            overflow-y: auto;
            flex-grow: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 11px 20px;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 0.9rem;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .sidebar-menu li a i {
            width: 18px;
            text-align: center;
            font-size: 1.05rem;
        }

        .sidebar-menu li:hover a, .sidebar-menu li.active a {
            color: var(--gold-accent);
            background-color: rgba(0, 0, 0, 0.15);
        }

        .sidebar-menu li.active a {
            border-left: 4px solid var(--gold-accent);
            padding-left: 16px;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 10px 0;
        }

        /* --- MAIN DASHBOARD VIEWPORT VIEW --- */
        .main-viewport {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-header {
            height: 60px;
            background-color: var(--card-white);
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            flex-shrink: 0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
        }

        .view-content-wrapper {
            padding: 24px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
        }

        /* --- DASHBOARD SUMMARY TOP METERS --- */
        .meters-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .meter-card {
            background-color: #EBF0F5;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
        }

        .meter-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: var(--card-white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #6B7280;
        }

        .meter-card.gold-style .meter-icon-box {
            background-color: #FEF3C7;
            color: var(--gold-accent);
        }

        .meter-info p {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .meter-info h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 2px 0;
        }

        .meter-trend {
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        /* --- SPLIT BLOCKS --- */
        .dashboard-split-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .content-block-card {
            background-color: var(--card-white);
            border-radius: 12px;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .block-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .block-header h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .block-header .view-all-link {
            font-size: 0.8rem;
            color: #B91C1C;
            text-decoration: none;
            font-weight: 600;
        }

        /* --- AUTHENTIC TABLE STYLING --- */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
            text-align: left;
        }

        th {
            background-color: #F9FAFB;
            padding: 12px 16px;
            font-weight: 600;
            color: #4B5563;
            border-bottom: 1px solid var(--border-gray);
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.02em;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-gray);
            color: #374151;
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }

        /* --- TEXT ACTION BADGES --- */
        .text-action-btn {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            margin-right: 5px;
            transition: opacity 0.2s;
        }
        .text-action-btn:hover { opacity: 0.85; }
        .btn-approve-action { background-color: #DEF7EC; color: #03543F; }
        .btn-reject-action { background-color: #FDE8E8; color: #9B1C1C; }

        /* --- CHIPS & STATUS CONTROLS --- */
        .status-chip {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }

        .chip-available { background-color: #DEF7EC; color: #03543F; }
        .chip-occupied { background-color: #FDE8E8; color: #9B1C1C; }
        .chip-reserved { background-color: #FEF3C7; color: #92400E; }

        .badge-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .pill-approved { background-color: var(--approved-bg); color: var(--approved-text); border-color: var(--approved-border); }
        .pill-pending { background-color: var(--pending-bg); color: var(--pending-text); border-color: var(--pending-border); }
        .pill-rejected { background-color: var(--rejected-bg); color: var(--rejected-text); border-color: var(--rejected-border); }

        .action-btn-circle {
            width: 26px;
            height: 26px;
            border-radius: 4px;
            border: 1px solid var(--border-gray);
            background: #FFF;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 4px;
        }
        .action-btn-circle.x-btn { color: #EF4444; }
        .action-btn-circle.x-btn:hover { background-color: #FDE8E8; }

        /* --- QUICK ACTIONS HUB --- */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 15px;
        }

        .qa-btn {
            background: #FFF;
            border: 1px solid #D1D5DB;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
        }
        .qa-btn:hover { border-color: var(--maroon-main); background-color: #FAFAFA; }

        /* --- WORKSPACE SPECIFICS --- */
        .workspace-tabs-strip {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 16px;
        }

        .tab-counter-pill {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            background-color: #E5E7EB;
            color: #4B5563;
        }
        .tab-counter-pill.active-all { background-color: #FDE8E8; color: #9B1C1C; border: 1px solid #F87171; }
        .tab-counter-pill.approved-tab { background-color: var(--approved-bg); color: var(--approved-text); }
        .tab-counter-pill.pending-tab { background-color: var(--pending-bg); color: var(--pending-text); }
        .tab-counter-pill.rejected-tab { background-color: var(--rejected-bg); color: var(--rejected-text); }

        .search-filters-bar {
            display: flex;
            gap: 12px;
            background-color: var(--card-white);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
            margin-bottom: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-input-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
            min-width: 150px;
        }

        .filter-input-group label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #4B5563;
        }

        .search-field-wrapper { position: relative; }
        .search-field-wrapper i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 0.85rem;
        }
        .search-field-wrapper input { padding-left: 30px !important; }

        .filter-input-group input, .filter-input-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.82rem;
            outline: none;
            background: #FFF;
        }

        .split-workspace-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            align-items: start;
        }

        .inspector-panel {
            background-color: var(--card-white);
            border-radius: 12px;
            border: 1px solid var(--border-gray);
            padding: 20px;
        }

        .inspector-panel h4 {
            font-size: 0.9rem;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-gray);
            margin-bottom: 14px;
        }

        .inspector-field { margin-bottom: 12px; }
        .inspector-field label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            display: block;
            margin-bottom: 2px;
        }
        .inspector-field p {
            font-size: 0.84rem;
            color: var(--text-dark);
            line-height: 1.4;
        }

        

        /* --- ASSETS FORMS GRID DESIGN --- */
        .asset-creation-form {
            padding: 16px;
            background-color: #FAFAFA;
            border-bottom: 1px solid var(--border-gray);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto;
            gap: 16px;
            align-items: flex-end;
        }

        .btn-form-submit {
            background-color: var(--maroon-main);
            color: #FFF;
            padding: 9px 18px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
        }
        .btn-form-submit:hover { background-color: var(--maroon-hover); }

        /* --- SYSTEM SETTINGS MODAL INTERFACE --- */
        .settings-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .settings-modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .settings-card {
            background: #FFF;
            width: 650px;
            max-width: 90%;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            overflow: hidden;
            display: grid;
            grid-template-columns: 200px 1fr;
            height: 480px;
        }
        .settings-sidebar {
            background-color: #F9FAFB;
            border-right: 1px solid var(--border-gray);
            padding: 20px 0;
        }
        .settings-tab-btn {
            width: 100%;
            padding: 12px 20px;
            border: none;
            background: none;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4B5563;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .settings-tab-btn.active {
            background-color: #F3F4F6;
            color: var(--maroon-main);
            border-left: 4px solid var(--maroon-main);
        }
        .settings-content-pane {
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
        }
        .settings-header-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border-gray);
            padding-bottom: 8px;
        }
        .settings-row {
            margin-bottom: 16px;
        }
        .settings-row label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }
        .settings-row input[type="text"], .settings-row input[type="password"], .settings-row select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        .settings-toggle-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FAFAFA;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border-gray);
            margin-bottom: 10px;
        }
        .settings-toggle-group span { font-size: 0.82rem; font-weight: 500; }
        .settings-pane-view { display: none; }
        .settings-pane-view.active { display: block; }
        
        .settings-footer-actions {
            border-top: 1px solid var(--border-gray);
            padding-top: 12px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-secondary { background: #E5E7EB; color: #374151; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;}
        .btn-primary { background: var(--maroon-main); color: #FFF; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;}
        .btn-primary:hover { background-color: var(--maroon-hover); }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div>
            <div class="sidebar-brand">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Classroom Reservation</span>
            </div>
            
            <div class="user-profile">
                <div class="profile-avatar"><i class="fa-solid fa-user-gear"></i></div>
                <div class="profile-info">
                    <div class="name">Admin Workspace</div>
                    <div class="role">System Administrator</div>
                </div>
            </div>

            <div class="menu-section-label">Main Menu</div>
            <ul class="sidebar-menu">
                <li class="active" data-view="dashboard"><a href="#"><i class="fa-solid fa-chart-pie"></i>Dashboard</a></li>
                <li data-view="reservation-requests"><a href="#"><i class="fa-solid fa-envelope-open-text"></i>Reservation Requests</a></li>
                <li data-view="manage-classrooms"><a href="#"><i class="fa-solid fa-school"></i>Manage Classroom</a></li>
                <li data-view="manage-courses"><a href="#"><i class="fa-solid fa-book"></i>Manage Course</a></li>
                <!-- <li data-view="manage-sections"><a href="#"><i class="fa-solid fa-layer-group"></i>Sections</a></li> -->
                <!-- <li data-view="schedules-calendar"><a href="#"><i class="fa-solid fa-calendar-days"></i>Schedules Calendar</a></li> -->
                <li data-view="students-directory"><a href="#"><i class="fa-solid fa-user-graduate"></i>Students List</a></li>
                <li data-view="teachers-directory"><a href="#"><i class="fa-solid fa-chalkboard-user"></i>Teachers List</a></li>
                <!-- <li data-view="reports-view"><a href="#"><i class="fa-solid fa-chart-line"></i>Reports Dashboard</a></li> -->
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <div class="menu-section-label">Settings</div>
            <ul class="sidebar-menu">
                <li><a href="#" onclick="toggleSettingsModal(true)"><i class="fa-solid fa-sliders"></i>System Settings</a></li>
                <li><a href="#" onclick="triggerSystemLogout()" style="color: #F87171;"><i class="fa-solid fa-power-off"></i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-viewport">
        <header class="top-header">
            <div class="header-left">
                <i class="fa-solid fa-bars" style="cursor: pointer; color: var(--text-muted);"></i>
                <h2 id="view-title-injector" style="font-size: 1.3rem; font-weight: 600;">Dashboard</h2>
            </div>
            <div class="header-right">
                <i class="fa-regular fa-bell" style="font-size: 1.1rem; cursor: pointer;"></i>
                <span>May 25, 2026 | 10:30 AM</span>
            </div>
        </header>

        <div class="view-content-wrapper">

            <section id="dashboard" class="view-section active">
                <div class="meters-grid">
                    <div class="meter-card" onclick="triggerViewShift('reservation-requests')" style="cursor:pointer;">
                        <div class="meter-icon-box"><i class="fa-solid fa-list-check"></i></div>
                        <div class="meter-info"><p>Total Reservations</p><h3 id="count-total"><?php echo $countAdminTotal; ?></h3></div>
                    </div>
                    <div class="meter-card" onclick="triggerViewShift('reservation-requests'); document.getElementById('filter-status-dropdown').value='APPROVED'; applyFilters();" style="cursor:pointer;">
                        <div class="meter-icon-box" style="color:#03543F;"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="meter-info"><p>Approved</p><h3 id="count-approved" style="color:#03543F;"><?php echo $countAdminApproved; ?></h3></div>
                    </div>
                    <div class="meter-card" onclick="triggerViewShift('reservation-requests'); document.getElementById('filter-status-dropdown').value='PENDING'; applyFilters();" style="cursor:pointer;">
                        <div class="meter-icon-box" style="color:#D97706;"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div class="meter-info"><p>Pending</p><h3 id="count-pending" style="color:#D97706;"><?php echo $countAdminPending; ?></h3></div>
                    </div>
                    <div class="meter-card" onclick="triggerViewShift('reservation-requests'); document.getElementById('filter-status-dropdown').value='REJECTED'; applyFilters();" style="cursor:pointer;">
                        <div class="meter-icon-box" style="color:#9B1C1C;"><i class="fa-solid fa-circle-xmark"></i></div>
                        <div class="meter-info"><p>Rejected</p><h3 id="count-rejected" style="color:#9B1C1C;"><?php echo $countAdminRejected; ?></h3></div>
                    </div>
                    <?php
                        $sqlTotalRooms     = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM classroom");
                        $sqlAvailableRooms = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM classroom WHERE roomStatus = 'Available'");
                        $totalRooms        = mysqli_fetch_assoc($sqlTotalRooms)['cnt'];
                        $availableRooms    = mysqli_fetch_assoc($sqlAvailableRooms)['cnt'];
                    ?>
                    <div class="meter-card gold-style" onclick="triggerViewShift('manage-classrooms')" style="cursor:pointer;" title="Manage classrooms">
                        <div class="meter-icon-box"><i class="fa-solid fa-door-open"></i></div>
                        <div class="meter-info">
                            <p>Total Rooms</p>
                            <h3 id="count-total-rooms"><?php echo $totalRooms; ?></h3>
                            <span id="count-available-rooms" style="font-size:0.72rem; color:#16A34A; font-weight:600;"><?php echo $availableRooms; ?> Available</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-split-row">
                    <div class="content-block-card">
                        <div class="block-header">
                            <h4>Pending Approval Queue</h4>
                            <a href="#" class="view-all-link" id="trigger-switch-requests">View all requests &rarr;</a>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr><th>Request ID</th><th>Requested By</th><th>Type</th><th>Room</th><th>Date & Time</th><th>Purpose</th><th>Actions</th></tr>
                                </thead>
                                <tbody id="dashboard-pending-tbody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                
                    <div class="content-block-card">
                        <div class="block-header"><h4>Reservation Overview</h4></div>
                        <div style="padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 220px;">
                            <canvas id="canvas-dashboard-pie" style="max-width: 190px; max-height: 190px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="dashboard-split-row" style="grid-template-columns: 2fr 1fr;">
                    <div class="content-block-card">
                        <div class="block-header"><h4>Room Availability Overview</h4></div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr><th>Room</th><th>Capacity</th><th>Status</th><th>Floor</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sqlRoomsDash = mysqli_query($connection, "SELECT roomName, roomCapacity, roomStatus, roomFloor FROM classroom ORDER BY roomFloor, roomName");
                                    if ($sqlRoomsDash) while ($rm = mysqli_fetch_assoc($sqlRoomsDash)) {
                                        $sc = $rm['roomStatus'] === 'Available' ? 'chip-available' : ($rm['roomStatus'] === 'Occupied' ? 'chip-occupied' : 'chip-reserved');
                                        echo "<tr>
                                            <td><strong>" . htmlspecialchars($rm['roomName']) . "</strong></td>
                                            <td>" . htmlspecialchars($rm['roomCapacity']) . " seats</td>
                                            <td><span class='status-chip $sc'>" . htmlspecialchars($rm['roomStatus']) . "</span></td>
                                            <td>" . htmlspecialchars($rm['roomFloor']) . "</td>
                                        </tr>";
                                    } else {
                                        echo "<tr><td colspan='4' style='text-align:center; color:#6B7280;'>No classrooms found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="content-block-card">
                            <div class="block-header"><h4>Quick Actions</h4></div>
                            <div class="quick-actions-grid">
                                <button class="qa-btn" onclick="triggerViewShift('reservation-requests')"><i class="fa-solid fa-envelope-open-text"></i> View Requests</button>
                                <button class="qa-btn" id="qa-jump-sched"><i class="fa-solid fa-calendar-days"></i> Manage Schedule</button>
                                <button class="qa-btn" id="qa-jump-room"><i class="fa-solid fa-door-open"></i> Add New Room</button>
                                <button class="qa-btn" id="qa-jump-reports"><i class="fa-solid fa-file-invoice"></i> Manage Courses</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="reservation-requests" class="view-section">
                <div class="workspace-tabs-strip">
                    <div class="tab-counter-pill active-all">Total Requests: <?php echo $countAdminTotal; ?></div>
                    <div class="tab-counter-pill approved-tab"><?php echo $countAdminApproved; ?> Approved</div>
                    <div class="tab-counter-pill pending-tab"><?php echo $countAdminPending; ?> Pending</div>
                    <div class="tab-counter-pill rejected-tab"><?php echo $countAdminRejected; ?> Rejected</div>
                </div>

                <div class="search-filters-bar">
                    <div class="filter-input-group search-field-wrapper">
                        <label>Search</label>
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="filter-search-input" placeholder="Search by name, room, or purpose..." oninput="applyFilters()">
                    </div>
                    <div class="filter-input-group">
                        <label>Date From</label>
                        <input type="date" id="filter-date-from" onchange="applyFilters()" style="padding:8px 12px; border:1px solid #D1D5DB; border-radius:6px; font-size:0.82rem;">
                    </div>
                    <div class="filter-input-group">
                        <label>Date To</label>
                        <input type="date" id="filter-date-to" onchange="applyFilters()" style="padding:8px 12px; border:1px solid #D1D5DB; border-radius:6px; font-size:0.82rem;">
                    </div>
                    <div class="filter-input-group">
                        <label>Type</label>
                        <select id="filter-type-dropdown" onchange="applyFilters()">
                            <option value="ALL">All Types</option>
                            <option value="Professor">Professor</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div class="filter-input-group">
                        <label>Status</label>
                        <select id="filter-status-dropdown" onchange="applyFilters()">
                            <option value="ALL">All Statuses</option>
                            <option value="APPROVED">Approved</option>
                            <option value="PENDING">Pending</option>
                            <option value="REJECTED">Rejected</option>
                        </select>
                    </div>
                    <div class="filter-input-group" style="flex-grow:0;">
                        <label>&nbsp;</label>
                        <button onclick="clearFilters()" style="padding:8px 14px; border:1px solid #D1D5DB; border-radius:6px; background:#fff; font-size:0.82rem; cursor:pointer; font-weight:600; color:#4B5563;">
                            <i class="fa-solid fa-rotate-left"></i> Clear
                        </button>
                    </div>
                </div>

                <div class="split-workspace-layout">
                    <div class="content-block-card">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr><th>Reservation ID</th><th>Date</th><th>Time</th><th>Room</th><th>Purpose</th><th>Requestor</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody id="workspace-reservations-tbody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="inspector-panel" id="workspace-inspector">
                        <h4>Reservation Details</h4>
                        <div id="inspector-placeholder-fallback">
                            <p style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;">Click on any row item from the left tracking matrix to inspect metrics details.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="manage-classrooms" class="view-section">
                <div class="content-block-card" style="margin-bottom: 24px;">
                    <div class="block-header"><h4>Add New Classroom</h4></div>
                    <form id="form-add-classroom" class="asset-creation-form">
                        <div class="filter-input-group"><label>Room Name</label><input type="text" id="input-cls-name" required placeholder="e.g., Room 302"></div>
                        <div class="filter-input-group"><label>Room Floor</label><input type="text" id="input-cls-floor" required placeholder="e.g., 3rd Floor"></div>
                        <div class="filter-input-group"><label>Seating Capacity</label><input type="number" id="input-cls-capacity" required placeholder="e.g., 40" min="1"></div>
                        <div class="filter-input-group">
                            <label>Room Status</label>
                            <select id="input-cls-status">
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-form-submit">Save Classroom</button>
                    </form>
                </div>
                <div class="content-block-card">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr><th>Room Name</th><th>Floor</th><th>Capacity</th><th>Room Status</th><th>Edit</th><th>Delete</th></tr>
                            </thead>
                            <tbody id="table-classrooms-tbody">
                                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- COURSE MANAGEMENT UI -->
            <section id="manage-courses" class="view-section">
                <div class="content-block-card" style="margin-bottom: 24px;">
                    <div class="block-header"><h4>Add Course / Subject</h4></div>
                    <form id="form-add-course" class="asset-creation-form">
                        <div class="filter-input-group"><label>Subject Code</label><input type="text" id="input-crs-code" required placeholder="e.g., CS102"></div>
                        <div class="filter-input-group"><label>Course Descriptive Title</label><input type="text" id="input-crs-name" required placeholder="e.g., Data Structures"></div>
                        <div class="filter-input-group"><label>Course Units</label><input type="number" id="input-crs-units" required placeholder="e.g., 3" min="1"></div>
                        <div class="filter-input-group">
                            <label>Program</label>
                            <select id="input-crs-program" required>
                                <option value="">Select Program</option>
                                <?php if ($sqlPrograms) while ($p = mysqli_fetch_assoc($sqlPrograms)) { ?>
                                <option value="<?php echo $p['programId']; ?>"><?php echo htmlspecialchars($p['programName']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-form-submit" name="submitCourseAdd">Save Course</button>
                    </form>
                </div>
                <div class="content-block-card">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr><th>Subject Code</th><th>Course Descriptive Title</th><th>Units</th><th>Edit</th><th>Delete</th></tr>
                            </thead>
                            <tbody id="table-courses-tbody">
                                <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="manage-sections" class="view-section">
                <div class="content-block-card" style="margin-bottom: 24px;">
                    <div class="block-header"><h4>Register Academic Student Block Section</h4></div>
                    <form id="form-add-section" class="asset-creation-form">
                        <div class="filter-input-group"><label>Section Label Identifier</label><input type="text" id="input-sec-name" required placeholder="e.g., BSCS 3A"></div>
                        <div class="filter-input-group"><label>Year Level Grouping</label><input type="text" id="input-sec-year" required placeholder="e.g., 3rd Year"></div>
                        <button type="submit" class="btn-form-submit">Save Section</button>
                    </form>
                </div>
                <div class="content-block-card">
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Section Identifier</th><th>Year Level Tier</th><th>Clear Asset</th></tr></thead>
                            <tbody id="table-sections-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

        
            
            <section id="students-directory" class="view-section">
                <div class="content-block-card">
                    <div class="block-header"><h4>Registered Student Directory</h4></div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr><th>Student Number</th><th>First Name</th><th>Last Name</th><th>Section</th><th>Edit</th><th>Delete</th></tr>
                            </thead>
                            <tbody id="table-students-tbody">
                                <?php if ($sqlStudents && mysqli_num_rows($sqlStudents) > 0) {
                                    while ($row = mysqli_fetch_assoc($sqlStudents)) { ?>
                                    <tr id="student-row-<?php echo $row['studentId']; ?>">
                                        <td><?php echo htmlspecialchars($row['studentNumber']); ?></td>
                                        <td class="s-fname"><?php echo htmlspecialchars($row['firstName']); ?></td>
                                        <td class="s-lname"><?php echo htmlspecialchars($row['lastName']); ?></td>
                                        <td class="s-id"><?php echo htmlspecialchars($row['sectionId']); ?></td>
                                        <td>
                                            <button class="text-action-btn btn-approve-action" onclick="editStudent(<?php echo $row['studentId']; ?>, '<?php echo htmlspecialchars($row['firstName'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['middleName'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['lastName'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['studentNumber'], ENT_QUOTES); ?>')">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                        </td>
                                        <td>
                                            <button class="text-action-btn btn-reject-action" onclick="deleteUser('student', <?php echo $row['studentId']; ?>, 'student-row-<?php echo $row['studentId']; ?>')">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } } else { ?>
                                    <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:20px;">No registered students found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="teachers-directory" class="view-section">
                <div class="content-block-card">
                    <div class="block-header"><h4>Active Faculty Directory</h4></div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr><th>Employee Number</th><th>First Name</th><th>Last Name</th><th>Professor Type</th><th>Edit</th><th>Delete</th></tr>
                            </thead>
                            <tbody id="table-teachers-tbody">
                                <?php if ($sqlProfessors && mysqli_num_rows($sqlProfessors) > 0) {
                                    while ($row = mysqli_fetch_assoc($sqlProfessors)) { ?>
                                    <tr id="professor-row-<?php echo $row['professorId']; ?>">
                                        <td><?php echo htmlspecialchars($row['employeeNumber']); ?></td>
                                        <td><?php echo htmlspecialchars($row['firstName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lastName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['professorType']); ?></td>
                                        <td>
                                            <button class="text-action-btn btn-approve-action" onclick="editProfessor(<?php echo $row['professorId']; ?>, '<?php echo htmlspecialchars($row['firstName'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['middleName'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['lastName'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['employeeNumber'], ENT_QUOTES); ?>', '<?php echo $row['professorType']; ?>')">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                        </td>
                                        <td>
                                            <button class="text-action-btn btn-reject-action" onclick="deleteUser('professor', <?php echo $row['professorId']; ?>, 'professor-row-<?php echo $row['professorId']; ?>')">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } } else { ?>
                                    <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:20px;">No professors found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="reports-view" class="view-section">
                <div class="dashboard-split-row" style="grid-template-columns: 1.5fr 1fr; margin-bottom: 20px;">
                    <div class="content-block-card" style="padding: 20px;">
                        <h4 style="margin-bottom: 12px;">Room Utilization Trend (This Semester)</h4>
                        <canvas id="canvas-reports-line" height="150"></canvas>
                    </div>
                    <div class="content-block-card" style="padding: 20px;">
                        <h4 style="margin-bottom: 12px;">Usage By Department</h4>
                        <canvas id="canvas-reports-bar" height="220"></canvas>
                    </div>
                </div>
                <div class="meters-grid">
                    <div class="meter-card" style="background:#FFF; border:1px solid var(--border-gray); justify-content:center; text-align:center;">
                        <div class="meter-info"><p>Total Bookings (Month)</p><h3 style="font-size:2rem;">1,245</h3></div>
                    </div>
                    <div class="meter-card" style="background:#FFF; border:1px solid var(--border-gray); justify-content:center; text-align:center;">
                        <div class="meter-info"><p>Cancelled Slots</p><h3 style="font-size:2rem; color:var(--maroon-main);">3.5%</h3></div>
                    </div>
                    <div class="meter-card" style="background:#FFF; border:1px solid var(--border-gray); justify-content:center; text-align:center;">
                        <div class="meter-info"><p>Average Fill Rate</p><h3 style="font-size:2rem; color:#10B981;">72%</h3></div>
                    </div>
                    <div class="meter-card" style="background:#FFF; border:1px solid var(--border-gray); justify-content:center; text-align:center;">
                        <div class="meter-info"><p>User Growth Meter</p><h3 style="font-size:2rem; color:#2563EB;">+8%</h3></div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- EDIT CLASSROOM MODAL -->
    <div id="edit-modal-classroom" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center;">
        <div style="background:#fff; border-radius:10px; padding:30px; width:100%; max-width:480px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <h4 style="margin-bottom:20px; color:var(--maroon-main);"><i class="fa-solid fa-pen-to-square"></i> Edit Classroom</h4>
            <form id="form-edit-classroom">
                <input type="hidden" id="edit-cls-id">
                <div style="display:grid; gap:12px;">
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Room Name</label><input type="text" id="edit-cls-name" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Room Floor</label><input type="text" id="edit-cls-floor" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Seating Capacity</label><input type="number" id="edit-cls-capacity" required min="1" style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Room Status</label>
                        <select id="edit-cls-status" style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;">
                            <option value="Available">Available</option>
                            <option value="Occupied">Occupied</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                    <button type="button" onclick="closeModal('edit-modal-classroom')" style="padding:9px 18px; border:1px solid #D1D5DB; border-radius:6px; background:#fff; cursor:pointer; font-weight:600;">Cancel</button>
                    <button type="submit" style="padding:9px 18px; background:var(--maroon-main); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT COURSE MODAL -->
    <div id="edit-modal-course" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center;">
        <div style="background:#fff; border-radius:10px; padding:30px; width:100%; max-width:480px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <h4 style="margin-bottom:20px; color:var(--maroon-main);"><i class="fa-solid fa-pen-to-square"></i> Edit Course</h4>
            <form id="form-edit-course">
                <input type="hidden" id="edit-crs-id">
                <div style="display:grid; gap:12px;">
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Subject Code</label><input type="text" id="edit-crs-code" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Course Descriptive Title</label><input type="text" id="edit-crs-title" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Course Units</label><input type="number" id="edit-crs-units" required min="1" style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                    <button type="button" onclick="closeModal('edit-modal-course')" style="padding:9px 18px; border:1px solid #D1D5DB; border-radius:6px; background:#fff; cursor:pointer; font-weight:600;">Cancel</button>
                    <button type="submit" style="padding:9px 18px; background:var(--maroon-main); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT STUDENT MODAL -->
    <div id="edit-modal-student" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center;">
        <div style="background:#fff; border-radius:10px; padding:30px; width:100%; max-width:480px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <h4 style="margin-bottom:20px; color:var(--maroon-main);"><i class="fa-solid fa-pen-to-square"></i> Edit Student</h4>
            <form id="form-edit-student">
                <input type="hidden" id="edit-s-id">
                <div style="display:grid; gap:12px;">
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Student Number</label><input type="text" id="edit-s-number" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">First Name</label><input type="text" id="edit-s-firstname" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Middle Name</label><input type="text" id="edit-s-middlename" style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Last Name</label><input type="text" id="edit-s-lastname" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                    <button type="button" onclick="closeModal('edit-modal-student')" style="padding:9px 18px; border:1px solid #D1D5DB; border-radius:6px; background:#fff; cursor:pointer; font-weight:600;">Cancel</button>
                    <button type="submit" style="padding:9px 18px; background:var(--maroon-main); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT PROFESSOR MODAL -->
    <div id="edit-modal-professor" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center;">
        <div style="background:#fff; border-radius:10px; padding:30px; width:100%; max-width:480px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <h4 style="margin-bottom:20px; color:var(--maroon-main);"><i class="fa-solid fa-pen-to-square"></i> Edit Professor</h4>
            <form id="form-edit-professor">
                <input type="hidden" id="edit-p-id">
                <div style="display:grid; gap:12px;">
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Employee Number</label><input type="text" id="edit-p-empnum" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">First Name</label><input type="text" id="edit-p-firstname" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Middle Name</label><input type="text" id="edit-p-middlename" style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Last Name</label><input type="text" id="edit-p-lastname" required style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;"></div>
                    <div><label style="font-size:0.8rem; font-weight:600; color:#374151;">Professor Type</label>
                        <select id="edit-p-type" style="width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; margin-top:4px;">
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                    <button type="button" onclick="closeModal('edit-modal-professor')" style="padding:9px 18px; border:1px solid #D1D5DB; border-radius:6px; background:#fff; cursor:pointer; font-weight:600;">Cancel</button>
                    <button type="submit" style="padding:9px 18px; background:var(--maroon-main); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="settings-overlay" class="settings-modal-overlay" onclick="toggleSettingsModal(false)">
        <div class="settings-card" onclick="event.stopPropagation()">
            <div class="settings-sidebar">
                <button class="settings-tab-btn active" onclick="switchSettingsPane(event, 'set-general')"><i class="fa-solid fa-sliders"></i> General Configuration</button>
                <button class="settings-tab-btn" onclick="switchSettingsPane(event, 'set-security')"><i class="fa-solid fa-lock"></i> Change Password</button>
                <button class="settings-tab-btn" onclick="switchSettingsPane(event, 'set-notifications')"><i class="fa-solid fa-bell"></i> Notifications</button>
            </div>
            
            <div class="settings-content-pane">
                <div>
                    <div id="set-general" class="settings-pane-view active">
                        <div class="settings-header-title">General Configuration</div>
                        <div class="settings-row">
                            <label>Institution Identity Name</label>
                            <input type="text" value="State University Admin Hub">
                        </div>
                        <div class="settings-row">
                            <label>Default Reservation Expiry Threshold</label>
                            <select>
                                <option>24 Hours before slot</option>
                                <option selected>48 Hours before slot</option>
                                <option>72 Hours before slot</option>
                            </select>
                        </div>
                    </div>

                    <div id="set-security" class="settings-pane-view">
                        <div class="settings-header-title">Change Password</div>
                        <div class="settings-row">
                            <label>Current Security Password</label>
                            <input type="password" placeholder="••••••••••••">
                        </div>
                        <div class="settings-row">
                            <label>New Security Password</label>
                            <input type="password" placeholder="Enter new strong password">
                        </div>
                        <div class="settings-row">
                            <label>Re-type New Password</label>
                            <input type="password" placeholder="Confirm new password">
                        </div>
                    </div>

                    <div id="set-notifications" class="settings-pane-view">
                        <div class="settings-header-title">Notification Channels</div>
                        <div class="settings-toggle-group">
                            <span>Email alerts upon auto-rejections</span>
                            <input type="checkbox" checked style="width:16px; height:16px; accent-color:var(--maroon-main);">
                        </div>
                        <div class="settings-toggle-group">
                            <span>Real-time conflicts validation prompt</span>
                            <input type="checkbox" checked style="width:16px; height:16px; accent-color:var(--maroon-main);">
                        </div>
                    </div>
                </div>

                <div class="settings-footer-actions">
                    <button class="btn-secondary" onclick="toggleSettingsModal(false)">Cancel</button>
                    <button class="btn-primary" onclick="alert('System settings configurations committed successfully.'); toggleSettingsModal(false)">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Memory Repository States
        // let classroomsData = [
        //     { name: "Room 101", capacity: 40, status: "Available", nextBooking: "10:00 AM - 12:00 PM (Data Structures)" },
        //     { name: "Room 102", capacity: 35, status: "Occupied", nextBooking: "8:00 AM - 10:00 AM (Calculus Class)" },
        //     { name: "Room 205", capacity: 35, status: "Available", nextBooking: "1:00 PM - 3:00 PM (Group Study)" },
        //     { name: "Room 301", capacity: 30, status: "Reserved", nextBooking: "1:00 PM - 3:00 PM (Department Meeting)" },
        //     { name: "Audio Visual Room", capacity: 60, status: "Available", nextBooking: "No upcoming booking" }
        // ];

        // let coursesData = [];

        // let sectionsData = [
        //     { name: "BSCS 1A", year: "1st Year" },
        //     { name: "BSCS 3A", year: "3rd Year" },
        //     { name: "BSIT 2B", year: "2nd Year" }
        // ];

        // let studentsData = [
        //     { id: "STUD-2026-0199", name: "Juan Dela Cruz", section: "BSCS 3A" },
        //     { id: "STUD-2026-1024", name: "Maria Clara Santos", section: "BSCS 1A" },
        //     { id: "STUD-2026-8841", name: "Pater Reyes", section: "BSIT 2B" },
        //     { id: "STUD-2026-5022", name: "Anna Garcia", section: "BSCS 3A" }
        // ];

        // let teachersData = [
        //     { id: "FAC-9022-A", name: "Prof. Maria Santos", dept: "College of Computer Studies" },
        //     { id: "FAC-4411-K", name: "Dr. Alexis Ramos", dept: "Department of Mathematics" },
        //     { id: "FAC-1102-M", name: "Engr. Roberto Blanco", dept: "College of Engineering" }
        // ];

        let mainReservations = <?php echo json_encode($reservationsForJS); ?>;

        // SIDEBAR CORE SPA NAVIGATION INTERCEPTOR
        document.querySelectorAll('.sidebar-menu li').forEach(item => {
            item.addEventListener('click', function() {
                const targetViewId = this.getAttribute('data-view');
                if(!targetViewId) return;

                document.querySelectorAll('.sidebar-menu li').forEach(li => li.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.view-section').forEach(section => section.classList.remove('active'));
                document.getElementById(targetViewId).classList.add('active');

                document.getElementById('view-title-injector').innerText = this.innerText;

                if (targetViewId === 'reports-view') loadAnalyticsReportsCharts();
            });
        });

        // SYSTEM SETTINGS MODAL INTERFACES CONTROLLERS
        function toggleSettingsModal(open) {
            const overlay = document.getElementById('settings-overlay');
            if (open) {
                overlay.classList.add('active');
            } else {
                overlay.classList.remove('active');
            }
        }

        function switchSettingsPane(event, paneId) {
            document.querySelectorAll('.settings-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.settings-pane-view').forEach(pane => pane.classList.remove('active'));
            
            event.currentTarget.classList.add('active');
            document.getElementById(paneId).classList.add('active');
        }

        // REDIRECT TO YOUR OWN CREATED LOGIN PAGE
        function triggerSystemLogout() {
            if(confirm("Are you sure you want to log out of the scheduling console session?")) {
                // Clear any data states
                localStorage.clear(); 
                sessionStorage.clear();

                // Dito mo ilalagay ang tamang file name ng login page mo (e.g., login.html, index.html)
                window.location.href = "login.php"; 
            }
        }

        // JUMP UTILITIES FOR QUICK ACTION SHORTCUT BUTTONS
        document.getElementById('trigger-switch-requests').addEventListener('click', () => triggerViewShift('reservation-requests'));
        document.getElementById('qa-jump-sched').addEventListener('click', () => triggerViewShift('schedules-calendar'));
        document.getElementById('qa-jump-room').addEventListener('click', () => triggerViewShift('manage-classrooms'));
        document.getElementById('qa-jump-reports').addEventListener('click', () => triggerViewShift('reports-view'));

        function triggerViewShift(viewId) {
            const matchedSelector = document.querySelector(`[data-view="${viewId}"]`);
            if (matchedSelector) matchedSelector.click();
        }

        // RENDERING PIPELINE CONTROLLERS
        function loadDashboardPendingQueue() {
            const tbody = document.getElementById('dashboard-pending-tbody');
            tbody.innerHTML = "";
            let pendingItems = mainReservations.filter(r => r.status === "PENDING");
            
            if(pendingItems.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--text-muted);">No current pending reservation clearances in queue.</td></tr>`;
                return;
            }

            pendingItems.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>#${item.id}</strong></td>
                        <td>${item.requestor} <small style="color:var(--text-muted);">(${item.type})</small></td>
                        <td>${item.type}</td>
                        <td>${item.room}</td>
                        <td>${item.date}<br><small style="color:var(--text-muted); font-weight:600;">${item.time}</small></td>
                        <td>${item.purpose}</td>
                        <td>
                            <button class="text-action-btn btn-approve-action" onclick="modifyStatus(${item.id}, 'APPROVED')">Approve</button>
                            <button class="text-action-btn btn-reject-action" onclick="modifyStatus(${item.id}, 'REJECTED')">Reject</button>
                        </td>
                    </tr>
                `;
            });
        }

        // ROOMS TRACKING LOADER
        function loadDashboardRoomsAvailability() { /* classrooms are PHP-rendered */ }

        // RESERVATION GRID LOADER — accepts optional filtered array
        function loadWorkspaceReservationsGrid(dataset) {
            const tbody = document.getElementById('workspace-reservations-tbody');
            tbody.innerHTML = "";
            const rows = dataset !== undefined ? dataset : mainReservations;

            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:var(--text-muted); padding:20px;">No reservations match the current filters.</td></tr>`;
                return;
            }

            rows.forEach(item => {
                let statusPillClass = item.status === "APPROVED" ? "pill-approved" : (item.status === "PENDING" ? "pill-pending" : "pill-rejected");

                let actionMarkup = item.status === "PENDING" ? `
                    <button class="text-action-btn btn-approve-action" onclick="event.stopPropagation(); modifyStatus(${item.id},'APPROVED')">Approve</button>
                    <button class="text-action-btn btn-reject-action" onclick="event.stopPropagation(); modifyStatus(${item.id},'REJECTED')">Reject</button>
                ` : `<span style="font-size:0.75rem; color:var(--text-muted);">—</span>`;

                tbody.innerHTML += `
                    <tr style="cursor:pointer;" onclick="populateInspector(${item.id})">
                        <td><strong>#${item.id}</strong></td>
                        <td>${item.date}</td>
                        <td>${item.time}</td>
                        <td>${item.room}</td>
                        <td>${item.purpose}</td>
                        <td>${item.requestor} <small style="color:var(--text-muted);">(${item.type})</small></td>
                        <td><span class="badge-pill ${statusPillClass}">${item.status}</span></td>
                        <td><div style="display:flex; align-items:center; gap:4px;">${actionMarkup}</div></td>
                    </tr>
                `;
            });
        }

        // FILTER ENGINE
        function applyFilters() {
            const search    = document.getElementById('filter-search-input').value.trim().toLowerCase();
            const dateFrom  = document.getElementById('filter-date-from').value;   // YYYY-MM-DD
            const dateTo    = document.getElementById('filter-date-to').value;
            const typeVal   = document.getElementById('filter-type-dropdown').value;
            const statusVal = document.getElementById('filter-status-dropdown').value;

            const filtered = mainReservations.filter(item => {
                // Search: matches requestor name, room, or purpose
                if (search) {
                    const haystack = `${item.requestor} ${item.room} ${item.purpose}`.toLowerCase();
                    if (!haystack.includes(search)) return false;
                }

                // Date range — item.date is YYYY-MM-DD from PHP
                if (dateFrom && item.date < dateFrom) return false;
                if (dateTo   && item.date > dateTo)   return false;

                // Requester type
                if (typeVal !== 'ALL' && item.type !== typeVal) return false;

                // Status
                if (statusVal !== 'ALL' && item.status !== statusVal) return false;

                return true;
            });

            loadWorkspaceReservationsGrid(filtered);

            // Update counter pills to reflect filtered counts
            const total    = filtered.length;
            const approved = filtered.filter(r => r.status === 'APPROVED').length;
            const pending  = filtered.filter(r => r.status === 'PENDING').length;
            const rejected = filtered.filter(r => r.status === 'REJECTED').length;

            document.querySelector('.tab-counter-pill.active-all').textContent  = `Total Requests: ${total}`;
            document.querySelector('.tab-counter-pill.approved-tab').textContent = `${approved} Approved`;
            document.querySelector('.tab-counter-pill.pending-tab').textContent  = `${pending} Pending`;
            document.querySelector('.tab-counter-pill.rejected-tab').textContent = `${rejected} Rejected`;
        }

        function clearFilters() {
            document.getElementById('filter-search-input').value = '';
            document.getElementById('filter-date-from').value    = '';
            document.getElementById('filter-date-to').value      = '';
            document.getElementById('filter-type-dropdown').value   = 'ALL';
            document.getElementById('filter-status-dropdown').value = 'ALL';
            applyFilters();
        }

        function populateInspector(id) {
            const matched = mainReservations.find(r => r.id == id);
            const panel = document.getElementById('workspace-inspector');
            if(!matched) return;

            panel.innerHTML = `
                <h4>Reservation Details</h4>
                <div class="inspector-field"><label>Reservation ID</label><p><strong>#${matched.id}</strong></p></div>
                <div class="inspector-field"><label>Requested By</label><p>${matched.requestor} <span style="color:var(--text-muted);">(${matched.type})</span></p></div>
                <div class="inspector-field"><label>Room</label><p><strong>${matched.room}</strong></p></div>
                <div class="inspector-field"><label>Date & Time</label><p>${matched.date} | ${matched.time}</p></div>
                <div class="inspector-field"><label>Purpose</label><p>${matched.purpose}</p></div>
                <div class="inspector-field"><label>Status</label><p><span class="badge-pill ${matched.status === 'APPROVED' ? 'pill-approved' : (matched.status === 'PENDING' ? 'pill-pending' : 'pill-rejected')}">${matched.status}</span></p></div>
                ${matched.status === 'PENDING' ? `
                <div style="margin-top:16px; display:flex; gap:8px;">
                    <button class="text-action-btn btn-approve-action" style="flex:1; padding:10px;" onclick="modifyStatus(${matched.id},'APPROVED')">Approve</button>
                    <button class="text-action-btn btn-reject-action" style="flex:1; padding:10px;" onclick="modifyStatus(${matched.id},'REJECTED')">Reject</button>
                </div>` : ''}
            `;
        }

        function modifyStatus(id, newStatus) {
            const label = newStatus === 'APPROVED' ? 'Approved' : 'Rejected';
            if (!confirm(`Set Reservation #${id} to ${label}?`)) return;

            const formData = new FormData();
            formData.append('reservationId', id);
            formData.append('newStatus', label);

            fetch('update_status.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let item = mainReservations.find(r => r.id == id);
                        if (item) item.status = newStatus;
                        loadDashboardPendingQueue();
                        applyFilters();
                        renderOverviewPieChart();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => alert('Network error. Please try again.'));
        }

        // ASSET ENTRIES PIPELINE RENDERING
        function renderClassroomsView() { /* handled by loadClassrooms() */ }
        function renderCoursesView()    { /* handled by loadCourses() */ }
        function renderSectionsView()   { /* section nav is hidden */ }
        function renderDirectories()    { /* tables are PHP-rendered */ }

        // TRANSACTIONAL FORM DISPATCH HANDLERS

        // COURSE MANAGEMENT — no page refresh
        function loadCourses() {
            const fd = new FormData();
            fd.append('action', 'getCourses');
            fetch('manage_course.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('table-courses-tbody');
                    if (!data.success || data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">No courses found.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.data.map(c => `
                        <tr id="course-row-${c.courseId}">
                            <td><code>${c.courseCode}</code></td>
                            <td>${c.courseName}</td>
                            <td>${c.units}</td>
                            <td><button class="text-action-btn btn-approve-action" onclick="editCourse(${c.courseId}, '${c.courseCode.replace(/'/g,"\\'")}', '${c.courseName.replace(/'/g,"\\'")}', ${c.units})"><i class="fa-solid fa-pen-to-square"></i> Edit</button></td>
                            <td><button class="text-action-btn btn-reject-action" onclick="deleteCourse(${c.courseId})"><i class="fa-solid fa-trash"></i> Delete</button></td>
                        </tr>
                    `).join('');
                });
        }

        function editCourse(id, code, name, units) {
            document.getElementById('edit-crs-id').value    = id;
            document.getElementById('edit-crs-code').value  = code;
            document.getElementById('edit-crs-title').value = name;
            document.getElementById('edit-crs-units').value = units;
            document.getElementById('edit-modal-course').style.display = 'flex';
        }

        function deleteCourse(id) {
            if (!confirm('Are you sure you want to delete this course?')) return;
            const fd = new FormData();
            fd.append('action', 'deleteCourse');
            fd.append('courseId', id);
            fetch('manage_course.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { loadCourses(); }
                    else alert('Error: ' + data.message);
                });
        }

        // Form listeners are all wired safely inside window.onload below

        // CHART CANVAS DECORATOR OBJECT REFERENCES
        let activeCharts = {};

        function renderOverviewPieChart() {
            if(activeCharts.pie) activeCharts.pie.destroy();
            const ctx = document.getElementById('canvas-dashboard-pie').getContext('2d');
            
            let approvedCount = mainReservations.filter(r => r.status === "APPROVED").length;
            let pendingCount  = mainReservations.filter(r => r.status === "PENDING").length;
            let rejectedCount = mainReservations.filter(r => r.status === "REJECTED").length;

            activeCharts.pie = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Rejected'],
                    datasets: [{ data: [approvedCount, pendingCount, rejectedCount], backgroundColor: ['#03543F', '#D97706', '#9B1C1C'] }]
                },
                options: { plugins: { legend: { display: true, position: 'bottom' } }, cutout: '70%' }
            });
        }

        function loadAnalyticsReportsCharts() {
            if(activeCharts.line) activeCharts.line.destroy();
            if(activeCharts.bar) activeCharts.bar.destroy();

            const lineCtx = document.getElementById('canvas-reports-line').getContext('2d');
            activeCharts.line = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
                    datasets: [{ label: 'Utilization Trend %', data: [9, 41, 30, 60, 43, 50, 70, 49, 53, 90], borderColor: '#500408', fill: false, tension: 0.2 }]
                }
            });

            const barCtx = document.getElementById('canvas-reports-bar').getContext('2d');
            activeCharts.bar = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Physics', 'Biology', 'IT Services', 'Chemistry', 'Geomatics'],
                    datasets: [{ label: 'Hours Used', data: [189.1, 135.7, 73.4, 65.6, 33.7], backgroundColor: '#500408' }]
                }
            });
        }

        // USER DELETE & EDIT
        function deleteUser(type, id, rowId) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            const fd = new FormData();
            fd.append('type', type);
            fd.append('id', id);
            fetch('delete_user.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(rowId).remove();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function editStudent(id, firstName, middleName, lastName, studentNumber) {
            document.getElementById('edit-s-id').value         = id;
            document.getElementById('edit-s-number').value     = studentNumber;
            document.getElementById('edit-s-firstname').value  = firstName;
            document.getElementById('edit-s-middlename').value = middleName;
            document.getElementById('edit-s-lastname').value   = lastName;
            document.getElementById('edit-modal-student').style.display = 'flex';
        }

        function editProfessor(id, firstName, middleName, lastName, employeeNumber, professorType) {
            document.getElementById('edit-p-id').value           = id;
            document.getElementById('edit-p-empnum').value       = employeeNumber;
            document.getElementById('edit-p-firstname').value    = firstName;
            document.getElementById('edit-p-middlename').value   = middleName;
            document.getElementById('edit-p-lastname').value     = lastName;
            document.getElementById('edit-p-type').value         = professorType;
            document.getElementById('edit-modal-professor').style.display = 'flex';
        }

        function editClassroom(id, name, floor, capacity, status) {
            document.getElementById('edit-cls-id').value       = id;
            document.getElementById('edit-cls-name').value     = name;
            document.getElementById('edit-cls-floor').value    = floor;
            document.getElementById('edit-cls-capacity').value = capacity;
            document.getElementById('edit-cls-status').value   = status;
            document.getElementById('edit-modal-classroom').style.display = 'flex';
        }

        function loadClassrooms() {
            const fd = new FormData();
            fd.append('action', 'getClassrooms');
            fetch('manage_classroom.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('table-classrooms-tbody');
                    if (!data.success || data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px;">No classrooms found.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.data.map(cls => `
                        <tr id="classroom-row-${cls.roomId}">
                            <td>${cls.roomName}</td>
                            <td>${cls.roomFloor}</td>
                            <td>${cls.roomCapacity}</td>
                            <td>${cls.roomStatus}</td>
                            <td><button class="text-action-btn btn-approve-action" onclick="editClassroom(${cls.roomId}, '${cls.roomName.replace(/'/g,"\\'")}', '${cls.roomFloor.replace(/'/g,"\\'")}', ${cls.roomCapacity}, '${cls.roomStatus}')"><i class="fa-solid fa-pen-to-square"></i> Edit</button></td>
                            <td><button class="text-action-btn btn-reject-action" onclick="deleteClassroom(${cls.roomId})"><i class="fa-solid fa-trash"></i> Delete</button></td>
                        </tr>
                    `).join('');
                });
        }

        function deleteClassroom(id) {
            if (!confirm('Delete this classroom? Any reservations linked to this room will also be removed.')) return;
            const fd = new FormData();
            fd.append('action', 'deleteClassroom');
            fd.append('roomId', id);
            fetch('manage_classroom.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { loadClassrooms(); refreshCounts(); }
                    else alert('Error: ' + data.message);
                });
        }

        document.getElementById('form-add-classroom').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action',       'submitClassroomAdd');
            fd.append('roomName',     document.getElementById('input-cls-name').value.trim());
            fd.append('roomFloor',    document.getElementById('input-cls-floor').value.trim());
            fd.append('roomCapacity', document.getElementById('input-cls-capacity').value);
            fd.append('roomStatus',   document.getElementById('input-cls-status').value);
            fetch('add.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { this.reset(); loadClassrooms(); refreshCounts(); }
                    else alert('Error: ' + data.message);
                });
        });

        document.getElementById('form-edit-classroom').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action',       'editClassroom');
            fd.append('roomId',       document.getElementById('edit-cls-id').value);
            fd.append('roomName',     document.getElementById('edit-cls-name').value.trim());
            fd.append('roomFloor',    document.getElementById('edit-cls-floor').value.trim());
            fd.append('roomCapacity', document.getElementById('edit-cls-capacity').value);
            fd.append('roomStatus',   document.getElementById('edit-cls-status').value);
            fetch('manage_classroom.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { closeModal('edit-modal-classroom'); loadClassrooms(); }
                    else alert('Error: ' + data.message);
                });
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.getElementById('form-edit-student').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action',        'editStudent');
            fd.append('studentId',     document.getElementById('edit-s-id').value);
            fd.append('studentNumber', document.getElementById('edit-s-number').value.trim());
            fd.append('firstName',     document.getElementById('edit-s-firstname').value.trim());
            fd.append('middleName',    document.getElementById('edit-s-middlename').value.trim());
            fd.append('lastName',      document.getElementById('edit-s-lastname').value.trim());
            fetch('manage_users.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { closeModal('edit-modal-student'); location.reload(); }
                    else alert('Error: ' + data.message);
                });
        });

        document.getElementById('form-edit-professor').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action',         'editProfessor');
            fd.append('professorId',    document.getElementById('edit-p-id').value);
            fd.append('employeeNumber', document.getElementById('edit-p-empnum').value.trim());
            fd.append('firstName',      document.getElementById('edit-p-firstname').value.trim());
            fd.append('middleName',     document.getElementById('edit-p-middlename').value.trim());
            fd.append('lastName',       document.getElementById('edit-p-lastname').value.trim());
            fd.append('professorType',  document.getElementById('edit-p-type').value);
            fetch('manage_users.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { closeModal('edit-modal-professor'); location.reload(); }
                    else alert('Error: ' + data.message);
                });
        });

        function refreshCounts() {
            fetch('get_counts.php')
                .then(r => r.json())
                .then(data => {
                    if (!data) return;
                    if (document.getElementById('count-total'))           document.getElementById('count-total').textContent           = data.total;
                    if (document.getElementById('count-approved'))        document.getElementById('count-approved').textContent        = data.approved;
                    if (document.getElementById('count-pending'))         document.getElementById('count-pending').textContent         = data.pending;
                    if (document.getElementById('count-rejected'))        document.getElementById('count-rejected').textContent        = data.rejected;
                    if (document.getElementById('count-total-rooms'))     document.getElementById('count-total-rooms').textContent     = data.totalRooms;
                    if (document.getElementById('count-available-rooms')) document.getElementById('count-available-rooms').textContent = data.availableRooms + ' Available';
                })
                .catch(() => {});
        }

        // APP WORKSPACE RUNTIME STARTER LIFE-CYCLE
        window.onload = function() {
            loadDashboardPendingQueue();
            applyFilters();
            renderOverviewPieChart();
            loadClassrooms();
            loadCourses();

            // Wire all form listeners safely inside onload so crashes don't block JS
            const bind = (id, fn) => { const el = document.getElementById(id); if (el) el.addEventListener('submit', fn); };

            bind('form-add-classroom', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',       'addClassroom');
                fd.append('roomName',     document.getElementById('input-cls-name').value.trim());
                fd.append('roomFloor',    document.getElementById('input-cls-floor').value.trim());
                fd.append('roomCapacity', document.getElementById('input-cls-capacity').value);
                fd.append('roomStatus',   document.getElementById('input-cls-status').value);
                fetch('manage_classroom.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { this.reset(); loadClassrooms(); refreshCounts(); }
                        else alert('Error: ' + data.message);
                    });
            });

            bind('form-edit-classroom', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',       'editClassroom');
                fd.append('roomId',       document.getElementById('edit-cls-id').value);
                fd.append('roomName',     document.getElementById('edit-cls-name').value.trim());
                fd.append('roomFloor',    document.getElementById('edit-cls-floor').value.trim());
                fd.append('roomCapacity', document.getElementById('edit-cls-capacity').value);
                fd.append('roomStatus',   document.getElementById('edit-cls-status').value);
                fetch('manage_classroom.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { closeModal('edit-modal-classroom'); loadClassrooms(); }
                        else alert('Error: ' + data.message);
                    });
            });

            bind('form-edit-student', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',        'editStudent');
                fd.append('studentId',     document.getElementById('edit-s-id').value);
                fd.append('studentNumber', document.getElementById('edit-s-number').value.trim());
                fd.append('firstName',     document.getElementById('edit-s-firstname').value.trim());
                fd.append('middleName',    document.getElementById('edit-s-middlename').value.trim());
                fd.append('lastName',      document.getElementById('edit-s-lastname').value.trim());
                fetch('manage_users.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { closeModal('edit-modal-student'); location.reload(); }
                        else alert('Error: ' + data.message);
                    });
            });

            bind('form-edit-professor', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',         'editProfessor');
                fd.append('professorId',    document.getElementById('edit-p-id').value);
                fd.append('employeeNumber', document.getElementById('edit-p-empnum').value.trim());
                fd.append('firstName',      document.getElementById('edit-p-firstname').value.trim());
                fd.append('middleName',     document.getElementById('edit-p-middlename').value.trim());
                fd.append('lastName',       document.getElementById('edit-p-lastname').value.trim());
                fd.append('professorType',  document.getElementById('edit-p-type').value);
                fetch('manage_users.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { closeModal('edit-modal-professor'); location.reload(); }
                        else alert('Error: ' + data.message);
                    });
            });

            bind('form-add-course', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',     'addCourse');
                fd.append('courseCode', document.getElementById('input-crs-code').value.trim());
                fd.append('courseName', document.getElementById('input-crs-name').value.trim());
                fd.append('units',      document.getElementById('input-crs-units').value);
                const prog = document.getElementById('input-crs-program');
                fd.append('programId',  prog ? prog.value : '1');
                fetch('manage_course.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { this.reset(); loadCourses(); }
                        else alert('Error: ' + data.message);
                    });
            });

            bind('form-edit-course', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',     'editCourse');
                fd.append('courseId',   document.getElementById('edit-crs-id').value);
                fd.append('courseCode', document.getElementById('edit-crs-code').value.trim());
                fd.append('courseName', document.getElementById('edit-crs-title').value.trim());
                fd.append('units',      document.getElementById('edit-crs-units').value);
                fetch('manage_course.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { closeModal('edit-modal-course'); loadCourses(); }
                        else alert('Error: ' + data.message);
                    });
            });

            bind('form-add-section', function(e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('action',      'addSection');
                fd.append('sectionName', document.getElementById('input-sec-name').value.trim());
                fd.append('yearLevel',   document.getElementById('input-sec-year').value.trim());
                fetch('manage_section.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { this.reset(); location.reload(); }
                        else alert('Error: ' + data.message);
                    });
            });
        };
    </script>
</body>
</html>