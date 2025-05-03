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
            $record_date = $_POST['record_date'];
            $weight = $_POST['weight'];
            $height = $_POST['height'];
            $blood_pressure = $_POST['blood_pressure'];
            $heart_rate = $_POST['heart_rate'];
            $symptoms = $_POST['symptoms'];
            $diagnosis = $_POST['diagnosis'];
            $medications = $_POST['medications'];
            $notes = $_POST['notes'];

            $stmt = $pdo->prepare("INSERT INTO health_records (family_member_id, record_date, weight, height, blood_pressure, heart_rate, symptoms, diagnosis, medications, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$family_member_id, $record_date, $weight, $height, $blood_pressure, $heart_rate, $symptoms, $diagnosis, $medications, $notes]);
            
            // Redirect to prevent form resubmission
            header('Location: health_records.php?success=1');
            exit();
        } elseif ($_POST['action'] === 'delete' && isset($_POST['record_id'])) {
            $stmt = $pdo->prepare("DELETE FROM health_records WHERE id = ? AND family_member_id IN (SELECT id FROM family_members WHERE user_id = ?)");
            $stmt->execute([$_POST['record_id'], $_SESSION['user_id']]);
            
            // Redirect to prevent form resubmission
            header('Location: health_records.php?success=2');
            exit();
        }
    }
}

// Fetch family members for dropdown
$stmt = $pdo->prepare("SELECT * FROM family_members WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$family_members = $stmt->fetchAll();

// Fetch health records
$stmt = $pdo->prepare("SELECT h.*, f.name as member_name 
    FROM health_records h 
    JOIN family_members f ON h.family_member_id = f.id 
    WHERE f.user_id = ? 
    ORDER BY h.record_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$health_records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Records - Family Health Tracker</title>
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
                        url('https://img.freepik.com/free-photo/medical-banner-with-stethoscope_23-2149611199.jpg');
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
                        <a class="nav-link active" href="health_records.php">Health Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
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
                    <h1 class="display-4 hero-text">Health Records</h1>
                    <p class="lead">Track and manage your family's health history.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php if ($_GET['success'] == 1): ?>
                    Health record added successfully!
                <?php elseif ($_GET['success'] == 2): ?>
                    Health record deleted successfully!
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Health Record</h5>
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
                                <label for="record_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="record_date" name="record_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="weight" name="weight">
                            </div>
                            <div class="mb-3">
                                <label for="height" class="form-label">Height (cm)</label>
                                <input type="number" step="0.01" class="form-control" id="height" name="height">
                            </div>
                            <div class="mb-3">
                                <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" placeholder="e.g., 120/80">
                            </div>
                            <div class="mb-3">
                                <label for="heart_rate" class="form-label">Heart Rate (bpm)</label>
                                <input type="number" class="form-control" id="heart_rate" name="heart_rate">
                            </div>
                            <div class="mb-3">
                                <label for="symptoms" class="form-label">Symptoms</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="diagnosis" class="form-label">Diagnosis</label>
                                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="medications" class="form-label">Medications</label>
                                <textarea class="form-control" id="medications" name="medications" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Record</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Health Records List</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Family Member</th>
                                        <th>Date</th>
                                        <th>Weight</th>
                                        <th>Blood Pressure</th>
                                        <th>Heart Rate</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($health_records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['member_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['record_date']); ?></td>
                                        <td><?php echo htmlspecialchars($record['weight']); ?> kg</td>
                                        <td><?php echo htmlspecialchars($record['blood_pressure']); ?></td>
                                        <td><?php echo htmlspecialchars($record['heart_rate']); ?> bpm</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info view-record" data-record-id="<?php echo $record['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">
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
    <div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRecordModalLabel">Health Record Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Family Member:</strong> <span id="modal-member-name"></span></p>
                            <p><strong>Date:</strong> <span id="modal-record-date"></span></p>
                            <p><strong>Weight:</strong> <span id="modal-weight"></span> kg</p>
                            <p><strong>Height:</strong> <span id="modal-height"></span> cm</p>
                            <p><strong>Blood Pressure:</strong> <span id="modal-blood-pressure"></span></p>
                            <p><strong>Heart Rate:</strong> <span id="modal-heart-rate"></span> bpm</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Symptoms:</strong></p>
                            <p id="modal-symptoms"></p>
                            <p><strong>Diagnosis:</strong></p>
                            <p id="modal-diagnosis"></p>
                            <p><strong>Medications:</strong></p>
                            <p id="modal-medications"></p>
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
            const viewModal = new bootstrap.Modal(document.getElementById('viewRecordModal'), {
                backdrop: 'static',
                keyboard: false,
                focus: true
            });

            // Handle view button clicks
            const viewButtons = document.querySelectorAll('.view-record');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const recordId = this.getAttribute('data-record-id');
                    
                    // Find the corresponding record data
                    const record = <?php echo json_encode($health_records); ?>.find(r => r.id == recordId);
                    
                    if (record) {
                        // Populate the modal with record data
                        document.getElementById('modal-member-name').textContent = record.member_name;
                        document.getElementById('modal-record-date').textContent = record.record_date;
                        document.getElementById('modal-weight').textContent = record.weight;
                        document.getElementById('modal-height').textContent = record.height;
                        document.getElementById('modal-blood-pressure').textContent = record.blood_pressure;
                        document.getElementById('modal-heart-rate').textContent = record.heart_rate;
                        document.getElementById('modal-symptoms').textContent = record.symptoms;
                        document.getElementById('modal-diagnosis').textContent = record.diagnosis;
                        document.getElementById('modal-medications').textContent = record.medications;
                        document.getElementById('modal-notes').textContent = record.notes;
                        
                        // Show the modal
                        viewModal.show();
                    }
                });
            });

            // Handle modal close
            document.getElementById('viewRecordModal').addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
</body>
</html> 