<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>PharmaTrack Reservations</title>

<link rel="stylesheet" href="reservation.css">

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="left-nav">

        <!-- LOGO -->

        <div class="logo">

            <div class="logo-box">
                +
            </div>

            <span>PharmaTrack</span>

        </div>

        <!-- NAVIGATION -->

        <div class="nav-links">

            <a href="medicine.php" class="nav-btn">
                Medicine
            </a>

            <a href="reservation.php" class="nav-btn active">
                My Reservations
            </a>

        </div>

    </div>

    <!-- RIGHT NAV -->

    <div class="right-nav">

        <button class="logout-btn">
            Log out
        </button>

        <div class="profile">
            JD
        </div>

    </div>

</div>

<!-- HEADER -->

<div class="header">

    <h2>My Reservations</h2>

    <p>
        Mga gamot na iyong pinareserba
    </p>

</div>

<!-- CONTENT -->

<div class="container">

    <!-- CARDS -->

    <div class="cards">

        <div class="card">
            <h3>Total Reservation</h3>
            <h1>12</h1>
            <p class="green">All orders</p>
        </div>

        <div class="card">
            <h3>Pendings</h3>
            <h1>2</h1>
            <p class="orange">
                Waiting for approval
            </p>
        </div>

        <div class="card">
            <h3>Approved</h3>
            <h1>9</h1>
            <p class="green">
                Approved reservations
            </p>
        </div>

        <div class="card">
            <h3>Cancelled</h3>
            <h1>1</h1>
            <p class="red">
                Cancelled reservations
            </p>
        </div>

    </div>

    <!-- FILTERS -->

    <div class="filters">

        <div class="search-btn">
            ⌕
        </div>

        <div class="select-box">

            <span>ALL</span>

            <i class="arrow"></i>

        </div>

        <div class="select-box">

            <span>SORT BY:</span>

            <i class="arrow"></i>

        </div>

    </div>

    <!-- TAGS -->

    <div class="tags">

        <div class="tag green-tag">
            All (234)
        </div>

        <div class="tag light-green">
            In Stock (321)
        </div>

        <div class="tag orange-tag">
            Low Stock (21)
        </div>

        <div class="tag red-tag">
            Out of Stock (81)
        </div>

    </div>

    <!-- TABLE -->

    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Product</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <tr>

                    <td class="product-name">
                        Biogesic <br>
                        <span>Analgesic</span>
                    </td>

                    <td>April 11, 2026</td>

                    <td>0 pcs</td>

                    <td>P 6.50</td>

                    <td>
                        <span class="status pending">
                            Pending
                        </span>
                    </td>

                    <td>3x a day</td>

                    <td>
                        <button class="update-btn">
                            Update
                        </button>
                    </td>

                </tr>

                <tr>

                    <td class="product-name">
                        Biogesic <br>
                        <span>Analgesic</span>
                    </td>

                    <td>April 11, 2026</td>

                    <td>0 pcs</td>

                    <td>P 6.50</td>

                    <td>
                        <span class="status approved">
                            Approved
                        </span>
                    </td>

                    <td>every 4 hours</td>

                    <td>
                        <button class="update-btn">
                            Update
                        </button>
                    </td>

                </tr>

                <tr>

                    <td class="product-name">
                        Biogesic <br>
                        <span>Analgesic</span>
                    </td>

                    <td>April 11, 2026</td>

                    <td>0 pcs</td>

                    <td>P 6.50</td>

                    <td>
                        <span class="status approved">
                            Approved
                        </span>
                    </td>

                    <td>every 4 hours</td>

                    <td>
                        <button class="update-btn">
                            Update
                        </button>
                    </td>

                </tr>

                <tr>

                    <td class="product-name">
                        Biogesic <br>
                        <span>Analgesic</span>
                    </td>

                    <td>April 11, 2026</td>

                    <td>0 pcs</td>

                    <td>P 6.50</td>

                    <td>
                        <span class="status pending">
                            Pending
                        </span>
                    </td>

                    <td>every 4 hours</td>

                    <td>
                        <button class="update-btn">
                            Update
                        </button>
                    </td>

                </tr>

                <tr>

                    <td class="product-name">
                        Biogesic <br>
                        <span>Analgesic</span>
                    </td>

                    <td>April 11, 2026</td>

                    <td>0 pcs</td>

                    <td>P 6.50</td>

                    <td>
                        <span class="status pending">
                            Pending
                        </span>
                    </td>

                    <td>3x a day</td>

                    <td>
                        <button class="update-btn">
                            Update
                        </button>
                    </td>

                </tr>

                <tr>

                    <td class="product-name">
                        Biogesic <br>
                        <span>Analgesic</span>
                    </td>

                    <td>April 11, 2026</td>

                    <td>0 pcs</td>

                    <td>P 6.50</td>

                    <td>
                        <span class="status pending">
                            Pending
                        </span>
                    </td>

                    <td>3x a day</td>

                    <td>
                        <button class="update-btn">
                            Update
                        </button>
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>