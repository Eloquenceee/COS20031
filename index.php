<?php
session_start();
include 'connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archer Round Selection</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="main-container">
    <h2>Start Scoring</h2>

    <form method="POST" action="submit.php">
        <!-- Archer Dropdown -->
        <label for="archerId">Select Archer</label>
        <select name="archerId" id="archerId" required>
            <option value="">-- Select Archer --</option>
            <?php
            $result = mysqli_query($conn, "SELECT archerId, CONCAT(firstName, ' ', lastName) AS name FROM Archer");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['archerId']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <!-- Equipment Dropdown -->
        <label for="equipmentId">Select Equipment</label>
        <select name="equipmentId" id="equipmentId" required>
            <option value="">-- Select Equipment --</option>
            <?php
            $result = mysqli_query($conn, "SELECT equipmentId, equipmentName FROM Equipment");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['equipmentId']}'>{$row['equipmentName']}</option>";
            }
            ?>
        </select>

        <!-- Competition Dropdown -->
        <label for="competitionId">Select Competition</label>
        <select name="competitionId" id="competitionId" required>
            <option value="">-- Select Competition --</option>
            <?php
            $result = mysqli_query($conn, "SELECT competitionId, competitionName FROM Competition");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['competitionId']}'>{$row['competitionName']}</option>";
            }
            ?>
        </select>

        <!-- Round Dropdown -->
        <label for="roundSelect">Select Round</label>
        <select name="roundNo" id="roundSelect" required>
            <option value="">-- Select Round --</option>
            <!-- AJAX populates this -->
        </select>

        <input type="submit" value="Start Scoring">
    </form>
</div>

<script>
    function fetchRounds() {
        const archerId = $('#archerId').val();
        const equipmentId = $('#equipmentId').val();

        if (archerId && equipmentId) {
            $.ajax({
                type: 'POST',
                url: 'fetch_rounds.php',
                data: {
                    archerId: archerId,
                    equipmentId: equipmentId
                },
                success: function (response) {
                    $('#roundSelect').html(response);
                },
                error: function () {
                    $('#roundSelect').html('<option value="">Error loading rounds</option>');
                }
            });
        } else {
            $('#roundSelect').html('<option value="">-- Select Round --</option>');
        }
    }

    $(document).ready(function () {
        $('#archerId, #equipmentId').on('change', fetchRounds);
    });
</script>
</body>
</html>
