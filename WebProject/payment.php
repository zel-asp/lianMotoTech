<?php
session_start();
include 'server/connection.php';

$success = '';
$error = '';
$orderProducts = [];
$totalPrice = 0;

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $stmt = $conn->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $orderProducts[] = $row;
        $totalPrice += $row['product_price'] * $row['product_quantity'];
    }

    unset($_SESSION['cart']);
    $stmt->close();
}


//account paynow button
if (isset($_POST['pay_nowBtn']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $orderProducts[] = $row;
        $totalPrice += $row['product_price'] * $row['product_quantity'];
    }
    unset($_SESSION['cart']);
    $stmt->close();
} elseif (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $totalPrice += $item['product_price'] * $item['quantity'];
    }
}

$stmt5 = $conn->prepare("SELECT * FROM shipping_fee");
$stmt5->execute();
$shippingFee_gcashNumber = $stmt5->get_result();



if (isset($_POST['payGcash_btn'])) {
    $reference_number = $_POST['reference_number'] ?? '';
    $order_id = $_POST['order_ids'] ?? '';

    if (!empty($reference_number) && !empty($order_id)) {
        // Update the order
        $stmt = $conn->prepare("UPDATE orders SET order_status = 'Pending', reference = ? WHERE order_id = ?");
        $stmt->bind_param("si", $reference_number, $order_id);

        if ($stmt->execute()) {
            $success = "Payment completed successfully. Order is now pending. Waiting for confirmation";
        } else {
            $error = "Error updating payment status. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Reference number or Order ID is missing.";
    }
}

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Discover quality motorcycle parts and services at WebMotorShop. Shop online for the best deals and book your service today!" />
    <meta name="keywords" content="motorcycle parts, moto shop, motorbike accessories, LianmotoTech, online moto store, bike service" />
    <meta name="author" content="LianmotoTech" />
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css2?family=League+Spartan&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia&effect=neon|outline|emboss|shadow-multiple|fire">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
    <title>Payment page</title>

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark py-3 fixed-top">
        <div class="container">
            <div class="logoAndName">
                <img
                    src="assets/images/logo.png"
                    alt="Lianmototech Logo"
                    class="Motopart_Logo" />
                <h4 class="Shop_name text-dark">
                    Lian<span class="RedColorName">mototech </span>Motoparts
                </h4>
            </div>

            <div class="d-flex align-items-center d-lg-none">
                <a href="cart.php" class="btn btn-outline-dark me-2 position-relative cart-button">
                    <i class="bi bi-cart3 fs-5"></i>
                </a>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navmenu">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navmenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <div class="d-flex align-items-center">
                            <a href="cart.php" class="btn btn-outline-dark me-2 position-relative d-none d-lg-block cart-button">
                                <i class="bi bi-cart3 fs-5"></i>
                            </a>
                            <a
                                href="index.php#BookNow"
                                class="nav-link NavHovered text-dark p-2 mt-1 mb-1 flex-grow-1">Book now</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#MotorParts"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Motor Parts</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#AboutUs"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">About us</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#History"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">History</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#questions"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Questions</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#Contact"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Contact Us</a>
                    </li>
                    <?php
                    // Check if user is logged in
                    if (isset($_SESSION['user_id'])) {
                        // User is logged in, show logout button
                        echo '<div class="Log_SignButton d-flex flex-wrap justify-content--around align-items-start">
                    <li class="nav-item">
                      <a href="account.php">
                        <button class="btn btn-success">
                      <i class="bi bi-person-check-fill"></i>Account
                        </button>
                      </a>
                    </li>
                  </div>';
                    } else {
                        // User is not logged in, show login and signup buttons
                        echo '<div class="Log_SignButton d-flex flex-wrap justify-content--around align-items-start">
                        <li class="nav-item">
                            <a href="login.php"><button class="loginButton bg-success">Log in</button></a>
                        </li>
                    </div>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Order Summary -->
    <div class="col-lg-5 align-self-start mt-5 pt-5 mb-5 mx-auto">
        <div class="card shadow-lg border-0 rounded-4 p-4 sticky-summary bg-light">
            <h2 class="fw-bold text-primary mb-4 border-bottom pb-2">
                <i class="bi bi-receipt me-2"></i>Order Summary
            </h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="order-items mb-4">
                <?php if (!empty($orderProducts)) { ?>
                    <?php foreach ($orderProducts as $item): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <small class="text-muted">Qty: <?php echo $item['product_quantity']; ?></small>
                            </div>
                            <div class="text-end">
                                <div>₱<?php echo number_format($item['product_price'], 2); ?></div>
                                <div class="fw-semibold">₱<?php echo number_format($item['product_price'] * $item['product_quantity'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php } elseif (!empty($_SESSION['cart'])) { ?>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <div class="text-end">
                                <div>₱<?php echo number_format($item['product_price'], 2); ?></div>
                                <div class="fw-semibold">₱<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php } ?>
            </div>

            <?php while ($row1 = $shippingFee_gcashNumber->fetch_assoc()): ?>
                <div class="price-summary border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold">₱<?php echo number_format($totalPrice, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping Fee:</span>
                        <span class="fw-semibold">₱<?php echo number_format($row1['fee'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 mt-3 pt-2 border-top">
                        <span>Total:</span>
                        <span class="text-success">₱<?php echo number_format($totalPrice + $row1['fee'], 2); ?></span>
                    </div>
                </div>

                <!-- GCash Payment Form -->
                <div class="payment-method-card p-4 rounded-4 shadow-sm bg-white">
                    <h4 class="text-dark fw-bold mb-3">
                        <i class="bi bi-phone me-2 text-primary"></i>GCash Payment
                    </h4>

                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Please complete your payment within 48 hours to avoid order cancellation.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2">Payment Details</label>
                        <div class="payment-details bg-light p-3 rounded-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">GCash Number:</span>
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold text-primary me-2" id="gcashNumber"><?php echo $row1['gcash_number']; ?></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Account Name:</span>
                                <span class="fw-semibold"><?php echo htmlspecialchars($row1['gcash_name']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="mb-4">
                <label class="form-label fw-semibold">Send payment confirmation to:</label>
                <div class="d-flex flex-column gap-2">
                    <a href="https://www.facebook.com/share/1AhgrxWfAc/?mibextid=qi2Omg" target="_blank"
                        class="btn text-primary text-start d-flex align-items-center">
                        <i class="bi bi-facebook me-2"></i>lianmototech motorparts
                    </a>
                    <a href="https://www.facebook.com/share/1EFtJpKMf3/?mibextid=qi2Omg" target="_blank"
                        class="btn text-primary text-start d-flex align-items-center">
                        <i class="bi bi-facebook me-2"></i>Ryan Ilarde
                    </a>
                </div>
            </div>

            <form action="" method="POST" class="mt-4" id="gcashForm">
                <input type="hidden" name="order_ids" value="<?php echo $order_id; ?>">

                <div class="mb-4">
                    <label for="referenceNumber" class="form-label fw-semibold">Reference Number</label>
                    <input type="text" class="form-control" id="referenceNumber" name="reference_number"
                        placeholder="Enter GCash reference number" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="submitPaymentBtn" name="payGcash_btn"
                        class="btn btn-primary py-3 fw-semibold shadow-sm">
                        <i class="bi bi-wallet2 me-2"></i>Complete Payment
                    </button>
                    <a href="account.php" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-2"></i>Cancel Order
                    </a>
                </div>
            </form>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous">
    </script>




</body>

</html>