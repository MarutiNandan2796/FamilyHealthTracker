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
            $medicine_name = $_POST['medicine_name'];
            $dosage = $_POST['dosage'];
            $frequency = $_POST['frequency'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $notes = $_POST['notes'];

            $stmt = $pdo->prepare("INSERT INTO medications (family_member_id, medicine_name, dosage, frequency, start_date, end_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$family_member_id, $medicine_name, $dosage, $frequency, $start_date, $end_date, $notes]);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['medication_id'])) {
            $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ? AND family_member_id IN (SELECT id FROM family_members WHERE user_id = ?)");
            $stmt->execute([$_POST['medication_id'], $_SESSION['user_id']]);
        }
    }
}

// Fetch family members for dropdown
$stmt = $pdo->prepare("SELECT * FROM family_members WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$family_members = $stmt->fetchAll();

// Fetch medications
$stmt = $pdo->prepare("SELECT m.*, f.name as member_name 
    FROM medications m 
    JOIN family_members f ON m.family_member_id = f.id 
    WHERE f.user_id = ? 
    ORDER BY m.start_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$medications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - Family Health Tracker</title>
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
                        url('https://img.freepik.com/free-photo/medicine-capsules-global-health-with-geometric-pattern-digital-remix_53876-126742.jpg');
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
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="medications.php">Medications</a>
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
                    <h1 class="display-4 hero-text">Medications</h1>
                    <p class="lead">Track and manage medications for your family members.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Medication</h5>
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
                                <label for="medicine_name" class="form-label">Medicine Name</label>
                                <input type="text" class="form-control" id="medicine_name" name="medicine_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="dosage" class="form-label">Dosage</label>
                                <input type="text" class="form-control" id="dosage" name="dosage" required>
                            </div>
                            <div class="mb-3">
                                <label for="frequency" class="form-label">Frequency</label>
                                <input type="text" class="form-control" id="frequency" name="frequency" required>
                            </div>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Medication</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Medications List</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Family Member</th>
                                        <th>Medicine</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medications as $medication): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($medication['member_name']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['medicine_name']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['start_date']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['end_date']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info view-medication" data-medication-id="<?php echo $medication['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="medication_id" value="<?php echo $medication['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this medication?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
    <div class="modal fade" id="viewMedicationModal" tabindex="-1" aria-labelledby="viewMedicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMedicationModalLabel">Medication Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Family Member:</strong> <span id="modal-member-name"></span></p>
                            <p><strong>Medicine Name:</strong> <span id="modal-medicine-name"></span></p>
                            <p><strong>Dosage:</strong> <span id="modal-dosage"></span></p>
                            <p><strong>Frequency:</strong> <span id="modal-frequency"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> <span id="modal-start-date"></span></p>
                            <p><strong>End Date:</strong> <span id="modal-end-date"></span></p>
                            <p><strong>Notes:</strong></p>
                            <p id="modal-notes"></p>
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
            const viewModal = new bootstrap.Modal(document.getElementById('viewMedicationModal'), {
                backdrop: 'static',
                keyboard: false,
                focus: true
            });

            // Handle view button clicks
            const viewButtons = document.querySelectorAll('.view-medication');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const medicationId = this.getAttribute('data-medication-id');
                    
                    // Find the corresponding medication data
                    const medication = <?php echo json_encode($medications); ?>.find(m => m.id == medicationId);
                    
                    if (medication) {
                        // Populate the modal with medication data
                        document.getElementById('modal-member-name').textContent = medication.member_name;
                        document.getElementById('modal-medicine-name').textContent = medication.medicine_name;
                        document.getElementById('modal-dosage').textContent = medication.dosage;
                        document.getElementById('modal-frequency').textContent = medication.frequency;
                        document.getElementById('modal-start-date').textContent = medication.start_date;
                        document.getElementById('modal-end-date').textContent = medication.end_date || 'Not specified';
                        document.getElementById('modal-notes').textContent = medication.notes || 'No notes';
                        
                        // Show the modal
                        viewModal.show();
                    }
                });
            });

            // Handle modal close
            document.getElementById('viewMedicationModal').addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
</body>
</html>