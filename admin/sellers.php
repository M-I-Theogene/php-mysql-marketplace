<?php include 'check.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.ico">
    <title>Cypadi Admin | Sellers</title>
    <?php include 'includes/css.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>

        <?php
        include 'includes/aside.php';
        active('users', 'sellers');
        ?>

        <div class="content-wrapper">
            <?php
            $arr = array(
                ["title" => "Home", "url" => "./"],
                ["title" => "Sellers", "url" => "#"],
            );
            pagePath('Sellers', $arr);
            ?>

            <section class="content">
                <div class="container-fluid">

                    <?php
                    $pending = $query->select(
                        'accounts',
                        'id, name, number, email, business_name, country, id_number, seller_status, registration_date',
                        "where role = 'seller' and seller_status = 'pending'"
                    );
                    ?>

                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Awaiting review (<?= count($pending); ?>)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pending)): ?>
                                <p class="text-muted mb-0">No seller applications are waiting for review.</p>
                            <?php else: ?>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Business</th>
                                            <th>Country</th>
                                            <th>ID number</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Decision</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['name']); ?></td>
                                                <td><?= htmlspecialchars($row['business_name'] ?? '-'); ?></td>
                                                <td><?= htmlspecialchars($row['country'] ?? '-'); ?></td>
                                                <td><?= htmlspecialchars($row['id_number'] ?? '-'); ?></td>
                                                <td><?= htmlspecialchars($row['number']); ?></td>
                                                <td><?= htmlspecialchars($row['email']); ?></td>
                                                <td style="white-space:nowrap;">
                                                    <a class="btn btn-success btn-sm"
                                                        href="change_status.php?userId=<?= (int) $row['id']; ?>&sellerStatus=approved">Approve</a>
                                                    <a class="btn btn-danger btn-sm"
                                                        href="change_status.php?userId=<?= (int) $row['id']; ?>&sellerStatus=rejected&note=Details+did+not+match+our+records">Reject</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All sellers</h3>
                        </div>
                        <div class="card-body">
                            <table id="sellers" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Business</th>
                                        <th>Country</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Review</th>
                                        <th>Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sellers = $query->select(
                                        'accounts',
                                        'id, name, number, email, business_name, country, seller_status, status',
                                        "where role = 'seller' order by id desc"
                                    );

                                    foreach ($sellers as $row) {
                                        $sellerStatus = $row['seller_status'] ?? 'approved';
                                        if ($sellerStatus === 'approved') {
                                            $badge = 'success';
                                        } elseif ($sellerStatus === 'rejected') {
                                            $badge = 'danger';
                                        } else {
                                            $badge = 'warning';
                                        }

                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['business_name'] ?? '-') . '</td>';
                                        echo '<td>' . htmlspecialchars($row['country'] ?? '-') . '</td>';
                                        echo '<td>' . htmlspecialchars($row['number']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                        echo '<td><span class="badge badge-' . $badge . '">' . ucfirst($sellerStatus) . '</span></td>';
                                        echo '<td>';
                                        if ($row['status'] == 'active') {
                                            echo '<a class="btn btn-outline-danger btn-sm" href="change_status.php?userId=' . (int) $row['id'] . '&newStatus=blocked&userrole=seller">Block</a>';
                                        } else {
                                            echo '<a class="btn btn-outline-success btn-sm" href="change_status.php?userId=' . (int) $row['id'] . '&newStatus=active&userrole=seller">Unblock</a>';
                                        }
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script src="../src/js/jquery.min.js"></script>
    <script src="../src/js/adminlte.js"></script>
    <script src="../src/js/bootstrap.bundle.min.js"></script>
    <script src="../src/js/jquery.dataTables.min.js"></script>
    <script src="../src/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function () {
            $('#sellers').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>

</body>

</html>
