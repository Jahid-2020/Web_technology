<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php'; // Must contain $conn = new mysqli(...);

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $location = $_POST['location'];

    if ($email != $user['email']) {
        // Check if new email already exists
        $check = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "This email is already in use.";
        } else {
            $update = $conn->prepare("UPDATE user SET name = ?, email = ?, phone = ?, location = ? WHERE id = ?");
            $update->bind_param("ssssi", $name, $email, $phone, $location, $user_id);
            $update->execute();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $success = "Profile updated successfully!";
        }
    } else {
        $update = $conn->prepare("UPDATE user SET name = ?, phone = ?, location = ? WHERE id = ?");
        $update->bind_param("sssi", $name, $phone, $location, $user_id);
        $update->execute();
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
    }
}

// Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $image     = $_FILES['profile_image'];
    $imageName = time() . '_' . basename($image['name']);
    $imageTmp  = $image['tmp_name'];
    $imagePath = 'uploads/' . $imageName;

    if (move_uploaded_file($imageTmp, $imagePath)) {
        $stmt = $conn->prepare("UPDATE user SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $imagePath, $user_id);
        $stmt->execute();
        $success_image = "Profile image updated!";
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $error_image = "Failed to upload image.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 40px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-image img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .form-group input {
            border-radius: 25px;
            padding: 10px 20px;
        }
        .btn-update {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1.1rem;
        }
        .btn-logout {
            background-color: #dc3545;
            color: white;
            font-size: 1rem;
            padding: 8px 20px;
            border-radius: 20px;
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            margin-top: 20px;
        }
        .alert-success {
            background-color: #28a745;
            color: white;
        }
        .alert-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-header">
        <h2>User Profile</h2>
        <div class="profile-image mt-3 mb-3">
            <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                <img src="<?= $user['profile_image'] ?>" alt="Profile Image">
            <?php else: ?>
                <img src="https://via.placeholder.com/150" alt="Default Profile Image">
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_image" class="form-control mb-2">
            <button type="submit" class="btn btn-primary">Upload Image</button>
        </form>
        <?php if (isset($success_image)): ?>
            <div class="alert alert-success"><?= $success_image ?></div>
        <?php endif; ?>
        <?php if (isset($error_image)): ?>
            <div class="alert alert-danger"><?= $error_image ?></div>
        <?php endif; ?>
    </div>

    <form method="POST">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control"
                   value="<?= htmlspecialchars($user['phone']) ?>">
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" class="form-control"
                   value="<?= htmlspecialchars($user['location']) ?>">
        </div>
        <button type="submit" name="update_profile" class="btn btn-update">Update Profile</button>
    </form>

    <a href="profile.php?logout=true" class="btn btn-logout">Logout</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
