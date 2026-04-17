<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="admin-layout">

    <div class="sidebar">
        <h2>Admin</h2>
        <ul>
            <li onclick="show('dashboard')">Dashboard</li>
            <li onclick="show('complaints')">Complaints</li>
        </ul>
    </div>

    <div class="main">

        <div id="dashboard">
            <h2>Dashboard</h2>
            <div class="card">Total: 10</div>
        </div>

        <div id="complaints" style="display:none;">
            <h2>Complaints</h2>

            <table>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>Rahul</td>
                    <td>Pending</td>
                </tr>
            </table>
        </div>

    </div>

</div>

<script src="assets/js/script.js"></script>

</body>
</html>
