<?php
include 'connect.php';

if (isset($_POST['archerId']) && isset($_POST['equipmentId'])) {
    $archerId = intval($_POST['archerId']);
    $equipmentId = intval($_POST['equipmentId']);

    // Step 1: Get archer's gender and age
    $archerRes = mysqli_query($conn, "SELECT gender, age FROM Archer WHERE archerId = $archerId");
    $archer = mysqli_fetch_assoc($archerRes);

    if (!$archer) {
        echo '<option value="">Invalid archer</option>';
        exit;
    }

    $gender = $archer['gender'];
    $age = (int)$archer['age'];

    // Step 2: Match age + gender to Class
    $classRes = mysqli_query($conn, "SELECT classId, className FROM Class");
    $matchedClassId = null;

    while ($row = mysqli_fetch_assoc($classRes)) {
        $className = $row['className'];
        $classId = $row['classId'];

        if (stripos($className, $gender) !== false) {
            // Extract numeric age bracket from className
            if (preg_match('/(\d+)/', $className, $matches)) {
                $limit = (int)$matches[1];

                if (stripos($className, 'Under') !== false && $age < $limit) {
                    $matchedClassId = $classId;
                    break;
                } elseif (stripos($className, '+') !== false && $age >= $limit) {
                    $matchedClassId = $classId;
                    break;
                }
            } elseif (stripos($className, 'Open') !== false) {
                $matchedClassId = $classId; // fallback
            }
        }
    }

    if (!$matchedClassId) {
        echo '<option value="">No matching class found</option>';
        exit;
    }

    // Step 3: Find matching category for classId and equipmentId
    $catRes = mysqli_query($conn, "
        SELECT categoryId FROM Category
        WHERE classId = $matchedClassId AND equipmentId = $equipmentId
    ");
    $catIds = [];
    while ($row = mysqli_fetch_assoc($catRes)) {
        $catIds[] = $row['categoryId'];
    }

    if (empty($catIds)) {
        echo '<option value="">No categories match class/equipment</option>';
        exit;
    }

    $catList = implode(',', $catIds);

    // Step 4: Fetch eligible rounds from CompRoundCategory
    $roundQuery = "
        SELECT DISTINCT cr.roundNo, cr.roundName
        FROM CompRound cr
        JOIN CompRoundCategory crc ON cr.roundNo = crc.roundNo
        WHERE crc.categoryId IN ($catList)
        ORDER BY cr.roundName ASC
    ";

    $roundRes = mysqli_query($conn, $roundQuery);

    echo '<option value="">-- Select Round --</option>';
    while ($row = mysqli_fetch_assoc($roundRes)) {
        echo "<option value='{$row['roundNo']}'>{$row['roundName']}</option>";
    }
} else {
    echo '<option value="">Missing archer or equipment</option>';
}
?>
