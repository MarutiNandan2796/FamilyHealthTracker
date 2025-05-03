<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $family_member_id = $_POST['family_member_id'];
            $appointment_date = $_POST['appointment_date'];
            $doctor_name = $_POST['doctor_name'];
            $specialization = $_POST['specialization'];
            $reason = $_POST['reason'];
            $status = $_POST['status'];

            $stmt = $pdo->prepare("INSERT INTO appointments (family_member_id, appointment_date, doctor_name, specialization, reason, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$family_member_id, $appointment_date, $doctor_name, $specialization, $reason, $status]);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['appointment_id'])) {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND family_member_id IN (SELECT id FROM family_members WHERE user_id = ?)");
            $stmt->execute([$_POST['appointment_id'], $_SESSION['user_id']]);
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND family_member_id IN (SELECT id FROM family_members WHERE user_id = ?)");
            $stmt->execute([$_POST['status'], $_POST['appointment_id'], $_SESSION['user_id']]);
        }
    }
}

// Fetch family members for dropdown
$stmt = $pdo->prepare("SELECT * FROM family_members WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$family_members = $stmt->fetchAll();

// Fetch appointments
$stmt = $pdo->prepare("SELECT a.*, f.name as member_name 
    FROM appointments a 
    JOIN family_members f ON a.family_member_id = f.id 
    WHERE f.user_id = ? 
    ORDER BY a.appointment_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Family Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }
        .navbar-nav .nav-item {
            margin: 0 5px;
        }
        .navbar-nav .nav-link {
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        .navbar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            font-weight: 500;
        }
        .navbar-nav .nav-link i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 80%;
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .navbar-brand:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .navbar-brand i {
            color: #fff;
            margin-right: 10px;
        }
        @media (max-width: 991.98px) {
            .navbar-nav .nav-item {
                margin: 5px 0;
            }
            .navbar-nav .nav-link::after {
                display: none;
            }
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%),
                        url('https://img.freepik.com/free-photo/doctor-with-stethoscope-hands-hospital-background_1423-1.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .hero-text {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
            margin: 0;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }
        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .dropdown-item .btn-link {
            color: #212529;
            padding: 0;
        }
        .dropdown-item .btn-link:hover {
            color: #0d6efd;
        }
        .dropdown-item i {
            width: 20px;
            text-align: center;
        }
        .btn-group .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Family Health Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="family_members.php">Family Members</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="health_records.php">Health Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medications.php">Medications</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 hero-text">Appointments</h1>
                    <p class="lead">Schedule and manage medical appointments for your family.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Appointment</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="family_member_id" class="form-label">Family Member</label>
                                <select class="form-select" id="family_member_id" name="family_member_id" required>
                                    <?php foreach ($family_members as $member): ?>
                                        <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="appointment_date" class="form-label">Appointment Date & Time</label>
                                <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="doctor_name" class="form-label">Doctor Name</label>
                                <input type="text" class="form-control" id="doctor_name" name="doctor_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" required>
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Visit</label>
                                <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Appointments List</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Family Member</th>
                                        <th>Date & Time</th>
                                        <th>Doctor</th>
                                        <th>Specialization</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['member_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $appointment['status'] === 'completed' ? 'success' : 
                                                    ($appointment['status'] === 'cancelled' ? 'danger' : 'primary'); 
                                            ?>">
                                                <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info view-appointment" data-appointment-id="<?php echo $appointment['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Update Status
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <form method="POST" class="dropdown-item">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="status" value="scheduled">
                                                            <button type="submit" class="btn btn-link text-decoration-none w-100 text-start">
                                                                <i class="fas fa-calendar-check me-2"></i>Scheduled
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="dropdown-item">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-link text-decoration-none w-100 text-start">
                                                                <i class="fas fa-check-circle me-2"></i>Completed
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="dropdown-item">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" class="btn btn-link text-decoration-none w-100 text-start">
                                                                <i class="fas fa-times-circle me-2"></i>Cancelled
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAppointmentModalLabel">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Family Member:</strong> <span id="modal-member-name"></span></p>
                            <p><strong>Date & Time:</strong> <span id="modal-appointment-date"></span></p>
                            <p><strong>Doctor:</strong> <span id="modal-doctor-name"></span></p>
                            <p><strong>Specialization:</strong> <span id="modal-specialization"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span id="modal-status"></span></p>
                            <p><strong>Reason for Visit:</strong></p>
                            <p id="modal-reason"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the view modal
            const viewModal = new bootstrap.Modal(document.getElementById('viewAppointmentModal'), {
                backdrop: 'static',
                keyboard: false,
                focus: true
            });

            // Handle view button clicks
            const viewButtons = document.querySelectorAll('.view-appointment');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-appointment-id');
                    
                    // Find the corresponding appointment data
                    const appointment = <?php echo json_encode($appointments); ?>.find(a => a.id == appointmentId);
                    
                    if (appointment) {
                        // Populate the modal with appointment data
                        document.getElementById('modal-member-name').textContent = appointment.member_name;
                        document.getElementById('modal-appointment-date').textContent = appointment.appointment_date;
                        document.getElementById('modal-doctor-name').textContent = appointment.doctor_name;
                        document.getElementById('modal-specialization').textContent = appointment.specialization;
                        document.getElementById('modal-status').textContent = appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1);
                        document.getElementById('modal-reason').textContent = appointment.reason;
                        
                        // Show the modal
                        viewModal.show();
                    }
                });
            });

            // Handle modal close
            document.getElementById('viewAppointmentModal').addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
</body>
</html> 