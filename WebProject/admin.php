<?php
session_start();
include('server/connection.php');

if (isset($_SESSION['logged_in']) || $_SESSION['user_id'] == 1) {

    $success = "";
    $error = "";
    // Initialize variables first to avoid undefined variable warnings
    $latestOrders = null;
    $latestUsers = null;

    //dashboard query
    $userCountResult = $conn->query("SELECT COUNT(*) AS total_users FROM users");
    $ordersCountResult = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
    $pendingCountResult = $conn->query("SELECT COUNT(*) AS pending_orders FROM bookings WHERE status = 'Pending'");
    $total_reviewsResult = $conn->query("SELECT COUNT(*) AS total_reviews FROM reviews");
    $latestreview = $conn->query("SELECT * FROM reviews ORDER BY review_id DESC LIMIT 1");
    $latestOrder = $conn->query("SELECT * FROM orders ORDER BY order_id DESC LIMIT 1");
    $latestUser = $conn->query("SELECT * FROM users ORDER BY user_id DESC LIMIT 1");

    //user count
    if ($userCountResult) {
        $userCountRow = $userCountResult->fetch_assoc();
        $totalUsers = $userCountRow['total_users'];
    } else {
        $totalUsers = 0;
    }

    //order count
    if ($ordersCountResult) {
        $ordersCountRow = $ordersCountResult->fetch_assoc();
        $totalOrders = $ordersCountRow['total_orders'];
    } else {
        $totalOrders = 0;
    }

    //pending booking count
    if ($pendingCountResult) {
        $pendingCountRow = $pendingCountResult->fetch_assoc();
        $pendingOrders = $pendingCountRow['pending_orders'];
    } else {
        $pendingOrders = 0;
    }

    //revies count
    if ($total_reviewsResult) {
        $reviewsCountRow = $total_reviewsResult->fetch_assoc();
        $totalReviews = $reviewsCountRow['total_reviews'];
    } else {
        $totalUsers = 0;
    }


    //latest order 
    if ($latestOrder && $latestOrder->num_rows > 0) {
        $latestOrders = $latestOrder->fetch_assoc();

        $orderformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($latestOrders['order_date']));
    }

    //latest review
    if ($latestreview && $latestreview->num_rows > 0) {
        $latestreview = $latestreview->fetch_assoc();
        //change the format of time and date
        $reviewformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($latestreview['created_at']));
    }


    //latest user
    if ($latestUser && $latestUser->num_rows > 0) {
        $latestUsers = $latestUser->fetch_assoc();
        $userformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($latestUsers['created_at']));
    }

    //end of dashboard query


    // Check if the user is logged in as an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
        header("Location: login.php");
        exit();
    }


    // Users query
    $stmt = $conn->prepare("SELECT user_id, user_name, user_email, created_at FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    if (isset($_POST['delete_user'])) {
        if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
            $user_id = (int) $_POST['user_id']; // Cast to int for safety

            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);


            if ($stmt->execute()) {
                $success = "User deleted successfully.";
                header("Location: admin.php#product");
                exit();
            } else {
                $error = "Error deleting user: " . htmlspecialchars($stmt->error) . "";
            }

            $stmt->close();
        }
    }


    // Remove 'On hold' orders older than 2 days
    $delete_old_on_hold = $conn->prepare("
    DELETE FROM orders 
    WHERE order_status = 'On hold' 
    AND TIMESTAMPDIFF(HOUR, order_date, NOW()) >= 48
");

    // delete from order_item first to avoid foreign key constraint error
    $delete_related_items = $conn->prepare("
    DELETE FROM order_item 
    WHERE order_id IN (
        SELECT order_id FROM orders 
        WHERE order_status = 'On hold' 
        AND TIMESTAMPDIFF(HOUR, order_date, NOW()) >= 48
    )
");

    $delete_related_items->execute();
    $delete_related_items->close();

    $delete_old_on_hold->execute();
    $delete_old_on_hold->close();


    // Orders query 
    $stmt1 = $conn->prepare("SELECT * FROM orders");
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    if (isset($_POST['save_order'])) {
        $order_id = $_POST['order_id'];
        $order_action = $_POST['order_action'];

        // Make sure an action was selected
        if (!empty($order_action)) {
            $update_stmt1 = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $update_stmt1->bind_param("si", $order_action, $order_id);

            if ($update_stmt1->execute()) {
                echo "<script>alert('Order status updated successfully!'); window.location.href='admin.php';</script>";
            } else {
                echo "<script>alert('Failed to update order status.'); window.location.href='admin.php';</script>";
            }
        }
    }
    if (isset($_POST['delete_order'])) {
        if (isset($_POST['order_id']) && is_numeric($_POST['order_id'])) {
            $order_id = (int) $_POST['order_id'];
        }
        // First, delete all related order_item entries (this step is optional if your DB constraint is already set to CASCADE)
        $stmt0 = $conn->prepare("DELETE FROM order_item WHERE order_id = ?");
        $stmt0->bind_param("i", $order_id);
        $stmt0->execute();

        $statement = $conn->prepare("DELETE FROM orders WHERE order_id=?");
        $statement->bind_param('i', $order_id);

        if ($statement->execute()) {
            echo "<script>alert('Order deleted successfully!'); </script>";
            header("Location: admin.php#product");
            exit();
        } else {
            $error = "Error deleting order: " . htmlspecialchars($stmt->error) . "";
        }

        $stmt->close();
    }




    //bookings query
    $stmt2 = $conn->prepare("SELECT booking_id, user_id, full_name, service_type, preferred_time,  preferred_date, status FROM bookings");
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if (isset($_POST['save_booking'])) {
        $booking_id = $_POST['booking_id'];
        $booking_action = $_POST['booking_action'];

        // Make sure an action was selected
        if (!empty($booking_action)) {
            $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
            $update_stmt->bind_param("si", $booking_action, $booking_id);

            if ($update_stmt->execute()) {
                echo "<script>alert('Booking updated successfully!'); window.location.href='admin.php';</script>";
            } else {
                echo "<script>alert('Failed to update booking.'); window.location.href='admin.php';</script>";
            }
        }
    }
    if (isset($_POST['delete_booking'])) {
        if (isset($_POST['booking_id']) && is_numeric($_POST['booking_id'])) {
            $booking_id = (int) $_POST['booking_id'];
        }

        $statement1 = $conn->prepare("DELETE FROM bookings WHERE booking_id=?");
        $statement1->bind_param('i', $booking_id);

        if ($statement1->execute()) {
            echo "<script>alert('Booking deleted successfully.');</script>";
            header("Location: admin.php#product");
            exit();
        } else {
            $error = "Error deleting booking: " . htmlspecialchars($stmt->error) . "";
        }

        $stmt->close();
    }


    //product query
    $stmt3 = $conn->prepare("SELECT product_id, product_image, product_name, product_description, product_price, product_stock FROM product");
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    if (isset($_POST['delete_product'])) {
        if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
            $product_id = (int) $_POST['product_id']; // Cast to int for safety

            $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);

            if ($stmt->execute()) {
                echo "<script>alert('Product deleted successfully.'); window.locaction.href='admin.php#products';</script>";
                header("Location: admin.php#product");
                exit();
            } else {
                $error = "Error deleting product: " . htmlspecialchars($stmt->error) . "";
            }

            $stmt->close();
        }
    }

    //update stocks
    if (isset($_POST['update_stockBtn'])) {
        $product_id = (int) $_POST['product_id']; // Cast to int for safety
        $product_stock = $_POST['product_stock'];

        $stock_stmt = $conn->prepare("UPDATE product SET product_stock = ? WHERE product_id = ?");
        $stock_stmt->bind_param("ii", $product_stock, $product_id);
        $stock_stmt->execute();

        echo "<script>alert('Product Stocks updated successfully'); window.locaction.href='admin.php#products';</script>";
    }
    // Add Product
    if (isset($_POST['add_productBtn'])) {
        $product_name = $_POST['product_name'];
        $product_price = $_POST['product_price'];
        $product_description = $_POST['product_description'];

        // Handling the file upload securely
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $product_image = $_FILES['product_image']['name'];
            $product_image_tmp = $_FILES['product_image']['tmp_name'];

            // Check if the uploaded file is an image
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($product_image_tmp);

            if (in_array($file_type, $allowed_types)) {
                // Validate file size (max 5MB)
                if ($_FILES['product_image']['size'] <= 5 * 1024 * 1024) {
                    $target_dir = "assets/images/";
                    $target_file = $target_dir . basename($product_image);

                    // Check if the product already exists
                    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM product WHERE product_name = ? OR product_image = ?");
                    $check_stmt->bind_param("ss", $product_name, $product_image);
                    $check_stmt->execute();
                    $check_stmt->bind_result($product_count);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    if ($product_count > 0) {
                        $error = "Product with the same name or image already exists.";
                    } else {
                        // Move the uploaded file
                        if (move_uploaded_file($product_image_tmp, $target_file)) {
                            // Insert into the database
                            $stmt = $conn->prepare("INSERT INTO product (product_name, product_price, product_description, product_image) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("ssss", $product_name, $product_price, $product_description, $product_image);

                            if ($stmt->execute()) {
                                $success = "Product added successfully.";
                            } else {
                                $error = "Error adding product: " . htmlspecialchars($stmt->error);
                            }
                            $stmt->close();
                        } else {
                            $error = "Error uploading file.";
                        }
                    }
                } else {
                    $error = "File size exceeds the limit (5MB).";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            }
        } else {
            $error = "No image selected or an error occurred during upload.";
        }
    }


    //for reviews
    // Load all product reviews
    // Check if the product already exists
    $totallReviews_stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE product_name = ? ");
    $totallReviews_stmt->bind_param("s", $product_name);
    $totallReviews_stmt->execute();
    $totallReviews_stmt->bind_result($reviews_count);
    $totallReviews_stmt->fetch();
    $totallReviews_stmt->close();

    $stmt_reviews = $conn->prepare("SELECT user_name, product_name, user_rating, user_review, created_at FROM reviews ORDER BY created_at DESC");
    $stmt_reviews->execute();
    $review_result = $stmt_reviews->get_result();

    // Load all products
    $stmt_products = $conn->prepare("SELECT * FROM product");
    $stmt_products->execute();
    $product_result = $stmt_products->get_result();
} else {
    header("Location:login.php");
}


//admin profile info
$profile_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$profile_stmt->bind_param("i", $user_id);
$user_id = 1;

$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile_info = $profile_result->fetch_assoc();

//admin profile gcash info, shipping fee and gcash info are on the same table in data base
$gcash_stmt = $conn->prepare("SELECT * FROM shipping_fee");

$gcash_stmt->execute();
$gcash_result = $gcash_stmt->get_result();
$gcash_info = $gcash_result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Admin dashboard for LianmotoTech Motorcycle Parts" />
    <meta name="keywords" content="admin, dashboard, motorcycle parts, management" />
    <meta name="author" content="LianmotoTech" />
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <title>Admin Dashboard | LianmotoTech</title>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <img src="assets/images/logo.png" alt="LianmotoTech logo">
                <h5 class="mb-0 fw-bold">LianmotoTech</h5>
            </div>
            <i class="btn bi bi-x close-btn d-block d-lg-none" id="closeBtn"></i>
        </div>

        <div class="sidebar-menu d-flex flex-column" style="height: calc(100% - 80px);">
            <div class="flex-grow-1">
                <a href="#dashboard" class="menu-item active" data-section="dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#users" class="menu-item" data-section="users">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <a href="#orders" class="menu-item" data-section="orders">
                    <i class="bi bi-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="#bookings" class="menu-item" data-section="bookings">
                    <i class="bi bi-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <a href="#products" class="menu-item" data-section="products">
                    <i class="bi bi-box-seam"></i>
                    <span>Products</span>
                </a>
                <a href="#profile" class="menu-item" data-section="profile">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </div>
            <a href="server/logOut.php" class="logout-btn btn text-danger d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </a>
        </div>
    </div>

    <!-- Topbar -->
    <div class="topbar d-flex align-items-center">
        <button class="btn menu-toggle d-lg-none" id="menuToggle">
            <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="#profile" class="menu-item bg-none" id="profileIcon" data-section="profile">
                <i class="btn bi bi-person-fill text-primary"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <div class="section active" id="dashboard-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="fw-bold">Dashboard Overview</h2>
                    <p class="text-muted">Welcome back! Here's what's happening with your store today.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card stat-card">
                        <div class="icon users">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="value"><?php echo number_format($totalUsers); ?></div>
                        <div class="label">Total Users</div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card stat-card">
                        <div class="icon orders">
                            <i class="bi bi-cart"></i>
                        </div>
                        <div class="value"><?php echo number_format($totalOrders); ?></div>
                        <div class="label">Total Orders</div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card stat-card">
                        <div class="icon bookings">
                            <i class="bi bi-calendar"></i>
                        </div>
                        <div class="value"><?php echo number_format($pendingOrders); ?></div>
                        <div class="label">Pending Bookings</div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card stat-card">
                        <div class="icon reviews">
                            <i class="bi bi-star"></i>
                        </div>
                        <div class="value"><?php echo number_format($totalReviews); ?></div>
                        <div class="label">Total Reviews</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-item">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-2 rounded">
                                        <i class="bi bi-cart-check text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <?php if ($latestOrders): ?>
                                            <h6 class="mb-1">New order #<?php echo htmlspecialchars($latestOrders['order_id']); ?></h6>
                                            <p class="mb-1 text-muted">
                                                <?php echo htmlspecialchars($latestOrders['user_name']); ?> placed a new order for ₱<?php echo htmlspecialchars($latestOrders['order_cost']); ?>
                                            </p>
                                            <small class="text-muted"><?php echo $orderformattedDateTime; ?></small>
                                        <?php else: ?>
                                            <p class="mb-0 text-muted">No recent orders.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-2 rounded">
                                        <i class="bi bi-person-plus text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <?php if ($latestUsers): ?>
                                            <h6 class="mb-1">New user registered</h6>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($latestUsers['user_name']); ?> created an account</p>
                                            <small class="text-muted"><?php echo $userformattedDateTime; ?></small>
                                        <?php else: ?>
                                            <p class="mb-0 text-muted">No recent user registrations.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 bg-warning bg-opacity-10 p-2 rounded">
                                        <i class="bi bi-chat-square-text text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">New review received</h6>
                                        <p class="mb-1 text-muted"><?php echo $latestreview['user_name']; ?> reviewed "<?php echo $latestreview['product_name']; ?>"</p>
                                        <small class="text-muted"><?php echo $reviewformattedDateTime; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reviews -->
                <div class="col-lg-4 mb-4 w-100">
                    <div class="card h-100 ">
                        <div class="card-header">
                            <h5 class="mb-0">Latest Reviews</h5>
                        </div>
                        <div class="card-body">
                            <?php while ($rows = $review_result->fetch_assoc()): ?>
                                <div class="mb-4 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><?php echo $rows['user_name']; ?></h6>
                                        <div class="text-warning">
                                            <?php
                                            $rating = (int)$rows['user_rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-2"><?php echo $rows['product_name']; ?></p>
                                    <p class="mb-2"><?php echo $rows['user_review']; ?></p>
                                    <small class="text-muted"><?php echo date("M j, Y", strtotime($rows['created_at'])); ?></small>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div class="section" id="users-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="fw-bold">User Management</h2>
                    <p class="text-muted">View and manage all registered users</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['user_id']; ?></td>
                                        <td><?php echo $row['user_name']; ?></td>
                                        <td><?php echo $row['user_email']; ?></td>
                                        <td><?php echo date("M j, Y", strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <form action="admin.php#users" method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="section" id="orders-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="fw-bold">Order Management</h2>
                    <p class="text-muted">View and manage customer orders</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row1 = $result1->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row1['order_id']; ?></td>
                                        <td><?php echo $row1['user_name']; ?></td>
                                        <td><?php echo date("M j, Y", strtotime($row1['order_date'])); ?></td>
                                        <td>₱<?php echo $row1['order_cost']; ?></td>
                                        <td>
                                            <?php
                                            $status = $row1['order_status'];
                                            $statusClass = '';
                                            if ($status == 'Pending') $statusClass = 'bg-warning';
                                            elseif ($status == 'Shipped') $statusClass = 'bg-info';
                                            elseif ($status == 'Completed') $statusClass = 'bg-success';
                                            elseif ($status == 'Preparing') $statusClass = 'bg-primary';
                                            elseif ($status == 'On hold') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td><?php echo $row1['reference']; ?></td>
                                        <td>
                                            <form action="admin.php" method="POST" class="d-flex gap-2">
                                                <select name="order_action" class="form-select form-select-sm">
                                                    <option value="">Update Status</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="Preparing">Preparing</option>
                                                    <option value="Shipped">Shipped</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                                <input type="hidden" name="order_id" value="<?php echo $row1['order_id']; ?>">
                                                <button type="submit" name="save_order" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button type="submit" name="delete_order" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Section -->
        <div class="section" id="bookings-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="fw-bold">Booking Management</h2>
                    <p class="text-muted">Manage all service bookings</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row2 = $result2->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row2['booking_id']; ?></td>
                                        <td><?php echo $row2['full_name']; ?></td>
                                        <td><?php echo $row2['service_type']; ?></td>
                                        <td><?php echo date("M j, Y g:i A", strtotime($row2['preferred_date'] . ' ' . $row2['preferred_time'])); ?></td>
                                        <td>
                                            <?php
                                            $status = $row2['status'];
                                            $statusClass = '';
                                            if ($status == 'Complete') $statusClass = 'bg-success';
                                            elseif ($status == 'Pending') $statusClass = 'bg-warning';
                                            elseif ($status == 'Confirm') $statusClass = 'bg-primary';
                                            elseif ($status == 'Decline') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td>
                                            <form action="admin.php" method="POST" class="d-flex gap-2">
                                                <select name="booking_action" class="form-select form-select-sm">
                                                    <option value="Pending">Pending</option>
                                                    <option value="Confirm">Confirm</option>
                                                    <option value="Decline">Decline</option>
                                                </select>
                                                <input type="hidden" name="booking_id" value="<?php echo $row2['booking_id']; ?>">
                                                <button type="submit" name="save_booking" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button type="submit" name="delete_booking" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="section" id="products-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="fw-bold">Product Management</h2>
                    <p class="text-muted">Add or remove products from your store</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary" id="addProductBtn">
                        <i class="bi bi-plus-circle me-2"></i>Add Product
                    </button>
                </div>
            </div>

            <div class="card" id="productList">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Products</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row3 = $result3->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row3['product_id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="assets/images/<?php echo $row3['product_image']; ?>" class="product-thumb">
                                                <span><?php echo $row3['product_name']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo substr($row3['product_description'], 0, 50) . '...'; ?></td>
                                        <td>₱<?php echo $row3['product_price']; ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-flex gap-2">
                                                <input type="hidden" value="<?php echo $row3['product_id']; ?>" name="product_id">
                                                <input type="number" name="product_stock" value="<?php echo $row3['product_stock']; ?>" min="0" class="form-control" style="width: 80px;">
                                                <button class="btn btn-sm btn-primary" type="submit" name="update_stockBtn">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="admin.php" method="POST">
                                                <input type="hidden" value="<?php echo $row3['product_id']; ?>" name="product_id">
                                                <button class="btn btn-sm btn-outline-danger" name="delete_product" type="submit" onclick="return confirm('Are you sure you want to delete this product?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Product Form -->
            <div class="card mt-4" id="Add_product" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">Add New Product</h5>
                </div>
                <div class="card-body">
                    <form id="productFormData" method="POST" action="admin.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="productName" class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="product_name" id="productName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="productPrice" class="form-label">Price</label>
                                <input type="number" class="form-control" id="productPrice" name="product_price" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="productDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="productDescription" name="product_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="product_image" id="productImage" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="cancelProductForm">Cancel</button>
                            <button type="submit" class="btn btn-primary" name="add_productBtn">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- admin profile sectione-->

        <div class="container section mb-5" id="profile-section">
            <div class="card d-flex justify-content-around">
                <div class="profile-header">
                    <h1>Admin Profile</h1>
                    <div class="role">Super Administrator</div>
                </div>

                <div class="profile-content">
                    <div class="Profilesection">
                        <h2 class="section-title">Personal Information</h2>
                        <div class="info-row">
                            <div class="info-label">Admin user Id::</div>
                            <div class="info-value"><?php echo $profile_info['user_id']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Full Name:</div>
                            <div class="info-value"><?php echo $profile_info['user_name']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo $profile_info['user_email']; ?></div>
                        </div>
                    </div>


                    <div class="Profilesection">
                        <h2 class="section-title">GCash Information</h2>
                        <div class="info-row">
                            <div class="info-label">GCash Name:</div>
                            <div class="info-value"><?php echo $gcash_info['gcash_name']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">GCash Number:</div>
                            <div class="info-value"><?php echo $gcash_info['gcash_number']; ?></div>
                        </div>
                    </div>
                    <div class="button-group">
                        <button class="edit-btn save-btn" id="js-edit_Info">Edit Informations</button>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <hr class="w-50">
                </div>


                <div id="js-change_adminInfo" class="d-none">
                    <div class="profile-content">
                        <h2 class="section-title">Change Information</h2>


                        <form action="server/profileChanges.php" method="POST">
                            <div class="Profilesection">
                                <h3 class="section-title">Personal Information</h3>
                                <div class="info-row">
                                    <div class="info-label">Full Name:</div>
                                    <input type="text" class="info-value" name="admin_name" placeholder="Enter new name" value="<?php echo $profile_info['user_name']; ?>">
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Email:</div>
                                    <input type="email" class="info-value" name="admin_email" placeholder="Enter new email" value="<?php echo $profile_info['user_email']; ?>">
                                </div>
                                <div class="info-row">
                                    <div class="info-label">New Password:</div>
                                    <input type="password" name="admin_newPass" class="info-value" placeholder="Enter new password" required>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Confirm Password:</div>
                                    <input type="password" name="admin_confirmPass" class="info-value" placeholder="Confirm new password" required>
                                </div>
                            </div>

                            <div class="Profilesection">
                                <h3 class="section-title">GCash Information</h3>
                                <div class="info-row">
                                    <div class="info-label">GCash Name:</div>
                                    <input type="text" name="gcash_name" class="info-value" placeholder="Enter new gcash name" value="<?php echo $gcash_info['gcash_name']; ?>">
                                </div>
                                <div class="info-row">
                                    <div class="info-label">GCash Number:</div>
                                    <input type="tel" name="gcash_number" class="info-value" placeholder="0917 123 4567" value="<?php echo $gcash_info['gcash_number']; ?>">
                                </div>
                            </div>

                            <div class="button-group">
                                <input type="button" name="cancel" class="edit-btn" id="js-cancelEdit" value=" Cancel">
                                <button class="edit-btn save-btn" type="submit" name="save_changes">Save Changes</button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/MotoPart.js">
    </script>
</body>

</html>