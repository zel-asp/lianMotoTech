<?php
session_start();
//the php for insert in db is in placeOrder.php


// Redirect if cart is empty or checkout not triggered
if (empty($_SESSION['cart'])) {
    header("Location: index.php#MotorParts");
    exit();
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['product_price'] * $item['quantity'];
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

    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700&family=Orbitron:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/checkout.css" />
    <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
    <title>Checkout | Lianmototech Motoparts</title>
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

    <div class="container checkout-container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 mt-5 pt-4 mb-5">
                <!-- Checkout Card -->
                <div class="card checkout-card">
                    <!-- Header -->
                    <div class="checkout-header">
                        <h3 class="checkout-title">COMPLETE YOUR PURCHASE</h3>
                        <p class="checkout-subtitle">Fast, secure, and convenient payments</p>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4 p-md-5">
                        <!-- Customer information form -->
                        <form action="server/placeOrder.php" method="POST">
                            <input type="hidden" name="total" value="<?php echo $total; ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                            <h5 class="fw-bold mb-3">Customer Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" required placeholder="Juan Dela Cruz" />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" required placeholder="0912 345 6789" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="your.email@example.com" />
                            </div>


                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" required placeholder="City/Municipality" />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" required placeholder="Street, Barangay" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="note" rows="3" placeholder="Additional instructions (optional)"></textarea>
                            </div>

                            <div class="d-flex justify-content-center mt-4">
                                <button type="submit" name="place_order" class="btn btn-checkout px-5 py-3 fw-semibold">
                                    <i class="bi bi-lightning-charge-fill me-2"></i> Complete Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security assurance -->
                <div class="text-center mt-4">
                    <p class="text-muted small">
                        <i class="bi bi-lock-fill me-1"></i> Your information is securely encrypted and will not be shared with third parties.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>