<?php
session_start();

$identifier = trim($_POST['login_id'] ?? ($_POST['email'] ?? ''));
$password = trim($_POST['password'] ?? '');

if ($identifier === '' || $password === '') {
    die("Missing credentials. <a href='log-in.html'>Try again</a>");
}

require_once __DIR__ . '/db-config.php';

$stmt = $conn->prepare("SELECT id, name, role, username, active FROM staff WHERE username = ? AND password = ? LIMIT 1");
$stmt->bind_param("ss", $identifier, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $staff = $result->fetch_assoc();

    if ((int)$staff['active'] !== 1) {
        $stmt->close();
        $conn->close();
        die("Staff account is inactive. <a href='log-in.html'>Try again</a>");
    }

    $_SESSION['staff_username'] = $staff['username'];
    $_SESSION['staff_name'] = $staff['name'];
    $_SESSION['staff_role'] = $staff['role'];

    // Admin roles get admin dashboard access.
    if (in_array(strtolower((string)$staff['role']), ['manager', 'admin', 'administrator'], true)) {
        $_SESSION['is_admin'] = true;
        header('Location: cafe-admin.php');
    } else {
        $_SESSION['is_admin'] = false;
        header('Location: cafe.html');
    }

    $stmt->close();
    $conn->close();
    exit();
}

$stmt->close();
$conn->close();

echo "Invalid admin/staff credentials. <a href='log-in.html'>Try again</a>";

?>