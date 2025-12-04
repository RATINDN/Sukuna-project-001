<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: index.php");
    exit();
}

// Get all users from the database (restore original logic with activity tracking)
try {
    $stmt = $pdo->prepare("SELECT id, user_name, email, phone, role, status FROM car ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "خطا در دریافت اطلاعات کاربران: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | لوکس کار</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/loginstyle.css">
    

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="success-popup" id="successPopup" style="display:none;"></div>
    
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <h1>پنل مدیریت</h1>
            </div>
            <div class="admin-sidebar-content">
                <a href="index.php" class="back-to-site">بازگشت به سایت</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-left">
                    <button id="menuToggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 style="width : fit-content !important">مدیریت کاربران</h2>
                    <div class="search-bar" style=" position: relative; right: 5px; width :fit-content;">
                <input type="search" name="" placeholder="شناسه کاربر....." style="width: 50%;" id="admin-search">
                </div>
                </div>

               
                <div class="mon" id="model">
        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="moon2" id="moon2" viewBox="0 0 16 16">
          <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
        </svg>

        <svg onclick="darkmode()" xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="sun2" fill="currentColor" class="sun2" style="display: none;" viewBox="0 0 16 16">
          <path d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
        </svg>
      </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>شناسه</th>
                            <th>نام کاربری</th>
                            <th>ایمیل</th>
                            <th>تلفن</th>
                            <th>نقش</th>
                            <th>وضعیت</th>
                            <th>وضعیت فعالیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?php echo $user['id']; ?>" data-user-role="<?php echo $user['role']; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo $user['role'] == 1 ? 'مدیر' : 'کاربر عادی'; ?></td>
                            <td><?php echo $user['status'] == 1 ? 'تایید شده' : 'تایید نشده'; ?></td>
                            <td class="activity-status-cell" data-user-id="<?php echo $user['id']; ?>">
                                <span class="activity-status">در حال بارگذاری...</span>
                            </td>
                            <td class="actions">
                                <?php if ($user['role'] == 0): ?>
                                    <button class="promote-btn" data-user-id="<?php echo $user['id']; ?>">ارتقا به مدیر</button>
                                    <button class="delete-btn" data-user-id="<?php echo $user['id']; ?>">حذف</button>
                                <?php else: ?>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="demote-btn" data-user-id="<?php echo $user['id']; ?>">تنزل به کاربر عادی</button>
                                    <?php else: ?>
                                        <span class="current-user-label">کاربر فعلی</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-dialog">
            <h3 id="confirmationTitle">تایید عملیات</h3>
            <p id="confirmationMessage"></p>
            <div class="confirmation-buttons">
                <button id="cancelActionBtn">انصراف</button>
                <button id="confirmActionBtn">تایید</button>
            </div>
        </div>
    </div>
    <script src="js/login signup.js"></script>
    <script src="js/activity_tracker.js"></script>
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           const confirmationModal = document.getElementById('confirmationModal');
           const confirmationTitle = document.getElementById('confirmationTitle');
           const confirmationMessage = document.getElementById('confirmationMessage');
           const confirmActionBtn = document.getElementById('confirmActionBtn');
           const cancelActionBtn = document.getElementById('cancelActionBtn');
           const successPopup = document.getElementById('successPopup');
           const menuToggle = document.getElementById('menuToggle');
           const adminSidebar = document.getElementById('adminSidebar');
           
           let currentAction = null;
           let currentUserId = null;
           
           // Menu toggle functionality for mobile
           menuToggle.addEventListener('click', function() {
               adminSidebar.classList.toggle('active');
           });
           
           // Close sidebar when clicking outside of it on mobile
           document.addEventListener('click', function(event) {
               const isClickInsideSidebar = adminSidebar.contains(event.target);
               const isClickOnMenuToggle = menuToggle.contains(event.target);
               
               if (!isClickInsideSidebar && !isClickOnMenuToggle && adminSidebar.classList.contains('active')) {
                   adminSidebar.classList.remove('active');
               }
           });
           
           // Dark mode toggle functionality
           
           // Function to show success message
            function showSuccessMessage(message) {
                successPopup.textContent = message;
                successPopup.style.display = 'block';
                setTimeout(() => {
                    successPopup.style.display = 'none';
                }, 3000);
            }
            
            // Function to show confirmation modal
            function showConfirmation(title, message, action, userId) {
                console.log('Showing confirmation for action:', action, 'userId:', userId);
                confirmationTitle.textContent = title;
                confirmationMessage.textContent = message;
                currentAction = action;
                currentUserId = userId;
                confirmationModal.style.display = 'flex';
            }
            
            // Close modal when cancel button is clicked
            cancelActionBtn.addEventListener('click', function() {
                confirmationModal.style.display = 'none';
            });
            
            // Handle confirmation button click
            confirmActionBtn.addEventListener('click', function() {
                confirmationModal.style.display = 'none';
                
                if (currentAction === 'delete') {
                    deleteUser(currentUserId);
                } else if (currentAction === 'promote') {
                    promoteUser(currentUserId);
                } else if (currentAction === 'demote') {
                    demoteUser(currentUserId);
                }
            });
            
            // Add event listeners to delete buttons
            console.log('Delete buttons found:', document.querySelectorAll('.delete-btn').length);
            document.querySelectorAll('.delete-btn').forEach(button => {
                console.log('Adding click listener to delete button for user ID:', button.getAttribute('data-user-id'));
                button.addEventListener('click', function(event) {
                    console.log('Delete button clicked for user ID:', this.getAttribute('data-user-id'));
                    event.preventDefault();
                    const userId = this.getAttribute('data-user-id');
                    showConfirmation(
                        'تایید حذف کاربر',
                        'آیا از حذف این کاربر اطمینان دارید؟ این عمل غیرقابل بازگشت است.',
                        'delete',
                        userId
                    );
                });
            });
            
            // Add event listeners to promote buttons
            console.log('Promote buttons found:', document.querySelectorAll('.promote-btn').length);
            document.querySelectorAll('.promote-btn').forEach(button => {
                console.log('Adding click listener to promote button for user ID:', button.getAttribute('data-user-id'));
                button.addEventListener('click', function(event) {
                    console.log('Promote button clicked for user ID:', this.getAttribute('data-user-id'));
                    event.preventDefault();
                    const userId = this.getAttribute('data-user-id');
                    showConfirmation(
                        'تایید ارتقا به مدیر',
                        'آیا از ارتقای این کاربر به مدیر اطمینان دارید؟',
                        'promote',
                        userId
                    );
                });
            });
            
            // Add event listeners to demote buttons
            console.log('Demote buttons found:', document.querySelectorAll('.demote-btn').length);
            document.querySelectorAll('.demote-btn').forEach(button => {
                console.log('Adding click listener to demote button for user ID:', button.getAttribute('data-user-id'));
                button.addEventListener('click', function(event) {
                    console.log('Demote button clicked for user ID:', this.getAttribute('data-user-id'));
                    event.preventDefault();
                    const userId = this.getAttribute('data-user-id');
                    showConfirmation(
                        'تایید تنزل به کاربر عادی',
                        'آیا از تنزل این مدیر به کاربر عادی اطمینان دارید؟',
                        'demote',
                        userId
                    );
                });
            });
            
            // Function to delete user
            function deleteUser(userId) {
                console.log('Deleting user with ID:', userId);
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&user_id=${userId}`
                })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Delete response data:', data);
                    if (data.success) {
                        // Remove the user row from the table
                        const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (userRow) {
                            userRow.remove();
                        }
                        showSuccessMessage('کاربر با موفقیت حذف شد');
                    } else {
                        showSuccessMessage(`خطا: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error('Error during delete:', error);
                    showSuccessMessage('خطا در ارتباط با سرور');
                });
            }
            
            // Function to promote user to admin
            function promoteUser(userId) {
                console.log('Promoting user with ID:', userId);
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=promote&user_id=${userId}`
                })
                .then(response => {
                    console.log('Promote response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Promote response data:', data);
                    if (data.success) {
                        // Update the user row in the table
                        const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (userRow) {
                            userRow.querySelector('td:nth-child(5)').textContent = 'مدیر';
                            userRow.setAttribute('data-user-role', '1');
                            
                            // Update action buttons
                            const actionsCell = userRow.querySelector('td.actions');
                            actionsCell.innerHTML = `
                                <button class="demote-btn" data-user-id="${userId}">تنزل به کاربر عادی</button>
                            `;
                            
                            // Add event listener to the new demote button
                            actionsCell.querySelector('.demote-btn').addEventListener('click', function(event) {
                                console.log('New demote button clicked for user ID:', userId);
                                event.preventDefault();
                                showConfirmation(
                                    'تایید تنزل به کاربر عادی',
                                    'آیا از تنزل این مدیر به کاربر عادی اطمینان دارید؟',
                                    'demote',
                                    userId
                                );
                            });
                        }
                        showSuccessMessage('کاربر با موفقیت به مدیر ارتقا یافت');
                    } else {
                        showSuccessMessage(`خطا: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error('Error during promote:', error);
                    showSuccessMessage('خطا در ارتباط با سرور');
                });
            }
            
            // Function to demote admin to regular user
            function demoteUser(userId) {
                console.log('Demoting user with ID:', userId);
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=demote&user_id=${userId}`
                })
                .then(response => {
                    console.log('Demote response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Demote response data:', data);
                    if (data.success) {
                        // Update the user row in the table
                        const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (userRow) {
                            userRow.querySelector('td:nth-child(5)').textContent = 'کاربر عادی';
                            userRow.setAttribute('data-user-role', '0');
                            
                            // Update action buttons
                            const actionsCell = userRow.querySelector('td.actions');
                            actionsCell.innerHTML = `
                                <button class="promote-btn" data-user-id="${userId}">ارتقا به مدیر</button>
                                <button class="delete-btn" data-user-id="${userId}">حذف</button>
                            `;
                            
                            // Add event listeners to the new buttons
                            actionsCell.querySelector('.promote-btn').addEventListener('click', function(event) {
                                console.log('New promote button clicked for user ID:', userId);
                                event.preventDefault();
                                showConfirmation(
                                    'تایید ارتقا به مدیر',
                                    'آیا از ارتقای این کاربر به مدیر اطمینان دارید؟',
                                    'promote',
                                    userId
                                );
                            });
                            
                            actionsCell.querySelector('.delete-btn').addEventListener('click', function(event) {
                                console.log('New delete button clicked for user ID:', userId);
                                event.preventDefault();
                                showConfirmation(
                                    'تایید حذف کاربر',
                                    'آیا از حذف این کاربر اطمینان دارید؟ این عمل غیرقابل بازگشت است.',
                                    'delete',
                                    userId
                                );
                            });
                        }
                        showSuccessMessage('مدیر با موفقیت به کاربر عادی تنزل یافت');
                    } else {
                        showSuccessMessage(`خطا: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error('Error during demote:', error);
                    showSuccessMessage('خطا در ارتباط با سرور');
                });
            }
        });

        // Search functionality
        const searchInput = document.getElementById('admin-search');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');

            rows.forEach(row => {
                const userId = row.cells[0].textContent.toLowerCase();
                if (userId.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Activity tracking is active but status column shows verification status
        console.log('Activity tracking loaded - online status tracked in background');

        // Online/offline status updates every 30 seconds
        let statusUpdateInterval;

        function updateActivityStatuses() {
            fetch('get_online_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.users) {
                        // Update each user's activity status
                        data.users.forEach(user => {
                            const cell = document.querySelector(`.activity-status-cell[data-user-id="${user.id}"] .activity-status`);
                            if (cell) {
                                // Add timestamp info for better UX
                                const timeAgo = calculateTimeAgo(user.last_activity);

                                // First, calculate time difference ourselves to verify API result
                                let isActuallyOnline = false;
                                if (user.last_activity) {
                                    const now = new Date();
                                    const lastActivityTime = new Date(user.last_activity);
                                    const diffMs = now - lastActivityTime;
                                    const diffMinutes = diffMs / 60000;

                                    // Overridden logic: online if activity within 3 minutes (not 180 seconds exactly)
                                    isActuallyOnline = diffMinutes <= 3;
                                }

                                const displayText = isActuallyOnline ?
                                    `🟢 آنلاین${timeAgo ? ' (' + timeAgo + ')' : ''}` :
                                    `🔴 آفلاین${timeAgo ? ' (' + timeAgo + ')' : ''}`;

                                cell.innerHTML = displayText;

                                // Apply color class based on actual calculated status
                                cell.className = 'activity-status';
                                if (isActuallyOnline) {
                                    cell.classList.add('status-online');
                                } else {
                                    cell.classList.add('status-offline');
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error updating activity statuses:', error);
                });
        }

        function calculateTimeAgo(lastActivity) {
            if (!lastActivity) return '';

            const now = new Date();
            const lastActivityTime = new Date(lastActivity);
            const diffMs = now - lastActivityTime;
            const diffMinutes = Math.floor(diffMs / 60000); // minutes

            if (diffMinutes < 1) return 'هم اکنون';
            if (diffMinutes === 1) return '۱ دقیقه پیش';
            if (diffMinutes < 60) return `${diffMinutes} دقیقه پیش`;

            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours === 1) return '۱ ساعت پیش';
            if (diffHours < 24) return `${diffHours} ساعت پیش`;

            const diffDays = Math.floor(diffHours / 24);
            if (diffDays === 1) return '۱ روز پیش';
            return `${diffDays} روز پیش`;
        }

        // Start status updates when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initial update
            updateActivityStatuses();

            // Set up periodic updates every 30 seconds
            statusUpdateInterval = setInterval(updateActivityStatuses, 30000);
        });

        // Clear interval when page unloads
        window.addEventListener('beforeunload', function() {
            if (statusUpdateInterval) {
                clearInterval(statusUpdateInterval);
            }
        });

    </script>
</body>
</html>
