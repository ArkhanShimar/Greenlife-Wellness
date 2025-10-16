<?php
session_start();
include '../../includes/db.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$userId = $_POST['userId'] ?? null;
$userType = $_POST['userType'] ?? '';

$email = $_POST['email'];
$password = $_POST['password'];

// --- Profile Picture Upload Logic ---
$profilePicName = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $targetDir = "../../assets/images/profiles/";
    $fileExtension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
    $profilePicName = "profile_" . uniqid() . "." . $fileExtension;
    $targetFile = $targetDir . $profilePicName;

    // Basic validation
    $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['success' => false, 'message' => 'File is not an image.']);
        exit();
    }
    if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
        echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file.']);
        exit();
    }
}

// Determine if it's an ADD or EDIT operation
if (empty($userId)) { // ADD NEW THERAPIST
    $name = $_POST['name'];
    $username = $_POST['username_therapist'];
    $qualification = $_POST['qualification'];
    $speciality = $_POST['speciality'];
    $experience = $_POST['experience'];

    // Check if therapist email already exists
    $stmt = $conn->prepare("SELECT therapist_id FROM therapists WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A therapist with this email already exists.']);
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO therapists (name, username, email, password, qualification, speciality, experience, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $name, $username, $email, $passwordHash, $qualification, $speciality, $experience, $profilePicName);

} else { // EDIT EXISTING USER
    if ($userType === 'client') {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $username = $_POST['username'];
        $phone = $_POST['phone'];
        $dob = $_POST['date_of_birth'];
        $address = $_POST['address'];
        $status = $_POST['status'];

        $sql = "UPDATE users SET email=?, first_name=?, last_name=?, username=?, phone=?, date_of_birth=?, address=?, status=?";
        $params = [$email, $firstName, $lastName, $username, $phone, $dob, $address, $status];
        $types = "ssssssss";

        if (!empty($password)) {
            $sql .= ", password=?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }
        if ($profilePicName) {
            $sql .= ", profile_pic=?";
            $params[] = $profilePicName;
            $types .= "s";
        }
        $sql .= " WHERE user_id=?";
        $params[] = $userId;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

    } else { // therapist
        $name = $_POST['name'];
        $username = $_POST['username_therapist'];
        $qualification = $_POST['qualification'];
        $speciality = $_POST['speciality'];
        $experience = $_POST['experience'];
        
        $sql = "UPDATE therapists SET name=?, username=?, email=?, qualification=?, speciality=?, experience=?";
        $params = [$name, $username, $email, $qualification, $speciality, $experience];
        $types = "sssssi";

        if (!empty($password)) {
            $sql .= ", password=?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }
        if ($profilePicName) {
            $sql .= ", profile_pic=?";
            $params[] = $profilePicName;
            $types .= "s";
        }
        $sql .= " WHERE therapist_id=?";
        $params[] = $userId;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
    }
}

if ($stmt && $stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User saved successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database operation failed: ' . $conn->error]);
}
$stmt->close();

if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['userId']) && isset($_POST['userType'])) {
    $deleteId = intval($_POST['userId']);
    $deleteType = $_POST['userType'];
    if ($deleteType === 'client') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $deleteId);
    } else if ($deleteType === 'therapist') {
        $stmt = $conn->prepare("DELETE FROM therapists WHERE therapist_id = ?");
        $stmt->bind_param("i", $deleteId);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
        exit();
    }
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
    }
    $stmt->close();
    exit();
}
?> 