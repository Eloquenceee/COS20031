<?php
session_start();
include 'connect.php';

// Number of arrows shot per end
const ARROWS_PER_END = 6;

// Initialize or retrieve session data for scoring
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archerId'], $_POST['roundNo'], $_POST['competitionId'])) {
    $_SESSION['archerId'] = $_POST['archerId'];
    $_SESSION['roundNo'] = $_POST['roundNo'];
    $_SESSION['competitionId'] = $_POST['competitionId'];
}

$archerId = $_SESSION['archerId'] ?? null;
$roundNo = $_SESSION['roundNo'] ?? null;
$competitionId = $_SESSION['competitionId'] ?? null;
$recorderId = 1; // Can be changed in future for expanding functionality of UI

if (!$archerId || !$roundNo || !$competitionId) {
    die("Missing required parameters.");
}

// Get or create Score record
$scoreCheck = mysqli_query($conn, "
    SELECT scoreId, totalScore FROM Score 
    WHERE archerId = $archerId AND roundNo = $roundNo AND competitionId = $competitionId
");
if ($row = mysqli_fetch_assoc($scoreCheck)) {
    $scoreId = $row['scoreId'];
    $totalScore = $row['totalScore'];
} else {
    mysqli_query($conn, "
        INSERT INTO Score (archerId, roundNo, competitionId, recorderId, totalScore)
        VALUES ($archerId, $roundNo, $competitionId, $recorderId, 0)
    ");
    $scoreId = mysqli_insert_id($conn);
    $totalScore = 0;
}

// Load all RoundRanges for this round
$rangeRes = mysqli_query($conn, "
    SELECT rr.rangeId, rr.endNo, t.distance, t.targetFace
    FROM RoundRange rr
    JOIN Target t ON rr.targetId = t.targetId
    WHERE rr.roundNo = $roundNo
    ORDER BY t.distance ASC
");

$endsList = [];
while ($row = mysqli_fetch_assoc($rangeRes)) {
    for ($i = 1; $i <= $row['endNo']; $i++) {
        $endsList[] = [
            'rangeId' => $row['rangeId'],
            'endNumber' => $i,
            'distance' => $row['distance'],
            'targetFace' => $row['targetFace']
        ];
    }
}
$totalEnds = count($endsList);

// Determine current progress
$arrowCountRes = mysqli_query($conn, "
    SELECT COUNT(*) as arrowCount FROM ScoreArrow WHERE scoreId = $scoreId
");
$arrowRow = mysqli_fetch_assoc($arrowCountRes);
$arrowsEntered = (int)$arrowRow['arrowCount'];
$currentEndIndex = intdiv($arrowsEntered, ARROWS_PER_END);

if ($currentEndIndex >= $totalEnds) {
    $_SESSION['score_message'] = "Scoring Complete: All $totalEnds ends have been entered. Total score: $totalScore";
    header("Location: index.php");
    exit;
}

$currentEnd = $endsList[$currentEndIndex];
$rangeId = $currentEnd['rangeId'];
$distance = $currentEnd['distance'];
$face = $currentEnd['targetFace'];
$endNo = $currentEnd['endNumber'];

// Process arrow scores if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrow'])) {
    $scores = $_POST['arrow'];
    // Define valid score values (X=10, M=miss)
    $validScores = ['X', '10','9','8','7','6','5','4','3','2','1','M'];
    $total = 0;

    foreach ($scores as $i => $arrowScore) {
        $arrowScore = strtoupper(trim($arrowScore));
        if (!in_array($arrowScore, $validScores)) {
            die("Invalid arrow score: $arrowScore");
        }

        // Convert X and M to numerical values
        $scoreValue = ($arrowScore === 'X') ? 10 : (($arrowScore === 'M') ? 0 : (int)$arrowScore);
        $total += $scoreValue;

        $arrowNo = $i + 1;
        // Insert ScoreArrow record
        mysqli_query($conn, "
            INSERT INTO ScoreArrow (scoreId, rangeId, arrowNo, scoreNo)
            VALUES ($scoreId, $rangeId, $arrowNo, '$arrowScore')
        ");
    }

    // Update totalScore
    mysqli_query($conn, "
        UPDATE Score SET totalScore = totalScore + $total WHERE scoreId = $scoreId
    ");

    // Redirect to same page (next end)
    header("Location: submit.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scoring - End <?= $currentEndIndex + 1 ?> of <?= $totalEnds ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="score-form">
    <h2 class="score-header">Scoring End <?= $currentEndIndex + 1 ?> / <?= $totalEnds ?></h2>
    <div class="score-header">
        Distance: <?= $distance ?>m Target: <?= $face ?>
    </div>

<form method="POST" action="submit.php" id="arrow-form">
    <div class="score-header">Tap numpad to enter scores</div>

    <div id="arrow-scores" class="score-display">
        <?php for ($i = 0; $i < ARROWS_PER_END; $i++): ?>
            <span id="display-arrow<?= $i ?>" class="display-arrow-box">--</span>
        <?php endfor; ?>
    </div>

    <div class="num-pad">
        <?php
        $validInputs = ['X', '10', '9', '8', '7', '6', '5', '4', '3', '2', '1', 'M'];
        foreach ($validInputs as $val) {
            echo "<button type='button' class='num-button' data-value='$val'>$val</button>";
        }
        ?>
    </div>

    <div class="score-display">
        Arrow <span id="current-arrow-no">1</span> of <?= ARROWS_PER_END ?>
    </div>

    <div class="button-row">
        <input type="hidden" name="arrow[]" id="arrow-hidden-0">
        <input type="hidden" name="arrow[]" id="arrow-hidden-1">
        <input type="hidden" name="arrow[]" id="arrow-hidden-2">
        <input type="hidden" name="arrow[]" id="arrow-hidden-3">
        <input type="hidden" name="arrow[]" id="arrow-hidden-4">
        <input type="hidden" name="arrow[]" id="arrow-hidden-5">

        <input type="submit" value="Submit End <?= $currentEndIndex + 1 ?>" id="submit-btn" disabled>
        <button type="button" id="clear-btn">Clear</button>
        <button type="button" onclick="window.location.href='index.php'">Cancel and Return</button>
    </div>
</form>

<script>
    let currentIndex = 0;
    const maxArrows = <?= ARROWS_PER_END ?>;
    const arrowSpans = Array.from({ length: maxArrows }, (_, i) => document.getElementById('display-arrow' + i));
    const hiddenInputs = Array.from({ length: maxArrows }, (_, i) => document.getElementById('arrow-hidden-' + i));
    const currentArrowLabel = document.getElementById('current-arrow-no');
    const submitBtn = document.getElementById('submit-btn');

    function updateDisplay(value) {
        if (currentIndex < maxArrows) {
            // Store value in hidden input
            hiddenInputs[currentIndex].value = value;
            currentIndex++;
            currentArrowLabel.innerText = currentIndex + 1;

            // Get all current values and sort them
            let scores = hiddenInputs.map(input => input.value).filter(val => val !== '');
            scores.sort((a, b) => {
                if (a === 'X') return -1;
                if (b === 'X') return 1;
                if (a === 'M') return 1;
                if (b === 'M') return -1;
                return parseInt(b) - parseInt(a);
            });

            // Update display arrows with sorted values
            arrowSpans.forEach((span, i) => {
                span.innerText = scores[i] || '--';
            });

            if (currentIndex >= maxArrows) {
                submitBtn.disabled = false;
                currentArrowLabel.innerText = maxArrows;
            }
        }
    }

    document.querySelectorAll('.num-button').forEach(button => {
        button.addEventListener('click', () => {
            const val = button.getAttribute('data-value');
            updateDisplay(val);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        currentArrowLabel.innerText = "1";
    });

    let activeInput = null;

    document.querySelectorAll('.arrow-input').forEach(input => {
        input.addEventListener('click', () => {
            if (activeInput) activeInput.classList.remove('active-arrow');
            activeInput = input;
            input.classList.add('active-arrow');
        });
    });

    document.querySelectorAll('.num-button').forEach(button => {
        button.addEventListener('click', () => {
            if (activeInput) {
                activeInput.value = button.getAttribute('data-value');
                const next = activeInput.nextElementSibling;
                if (next && next.classList.contains('arrow-input')) {
                    activeInput.classList.remove('active-arrow');
                    next.classList.add('active-arrow');
                    activeInput = next;
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const firstInput = document.querySelector('.arrow-input');
        if (firstInput) {
            firstInput.classList.add('active-arrow');
            activeInput = firstInput;
        }
    });

    document.getElementById('clear-btn').addEventListener('click', () => {
        // Reset display arrows
        arrowSpans.forEach(span => span.innerText = '--');
        // Reset hidden inputs
        hiddenInputs.forEach(input => input.value = '');
        // Reset current arrow index
        currentIndex = 0;
        currentArrowLabel.innerText = '1';
        // Disable submit button
        submitBtn.disabled = true;
    });
</script>
</div>
</body>
</html>
