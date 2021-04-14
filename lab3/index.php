<style>
    body {
        font-family: monospace;
    }

    td {
        background: aliceblue;
        text-align: center;
        padding: 8px 16px;
    }

    th {
        background: beige;
        text-align: center;
        padding: 8px 16px;
    }

    form {
        margin: 0;
    }

    input[type="text"], input[type="date"] {
        display: block;
        margin: 0 0 8px 0;
        border: none;
        background: #EAEAEA;
        padding: 8px;
        border-radius: calc(50vh);
        outline: none;
    }

    .mt-16px {
        margin-top: 16px !important;
    }

    .selected-page {
        background: deepskyblue !important;
    }

    .round-button {
        border: none;
        outline: none;
        padding: 8px;
        width: 16px;
        height: 16px;
        box-sizing: content-box;
        border-radius: calc(50vh);
        background: #DEDEDE;
    }

    .round-button:hover {
        cursor: pointer;
        background: lightblue;
    }
</style>
<?php
include 'TaskRepository.php';
$repository = new TaskRepository();
$tasks = $repository->getTasks();

function getPagesCount($allElementsCount, $pageElementsCount)
{
    return ceil($allElementsCount / $pageElementsCount);
}

function getVisiblePageIndices($pagesCount, $visiblePagesCount, $pageIndex)
{
    $visiblePagesCount = min($pagesCount, $visiblePagesCount);
    $indices = array($visiblePagesCount);

    $centerIndex = $pageIndex - 1;
    $ifStartIndexBias = min($centerIndex, ceil(($visiblePagesCount - 1) / 2));
    $ifEndIndexBias = min($pagesCount - 1 - $centerIndex, floor(($visiblePagesCount - 1) / 2));

    $leftPagesCount = $ifStartIndexBias + floor(($visiblePagesCount - 1) / 2) - $ifEndIndexBias;
    $rightPagesCount = $visiblePagesCount - 1 - $leftPagesCount;
    for ($i = 0; $i < $leftPagesCount; $i++) {
        $indices[$i] = $centerIndex - $leftPagesCount + $i;
    }
    $indices[$leftPagesCount] = $centerIndex;
    for ($i = 0; $i < $rightPagesCount; $i++) {
        $indices[$leftPagesCount + $i + 1] = $centerIndex + $i + 1;
        $indices[$i] = $centerIndex - $leftPagesCount + $i;
    }
    return $indices;
}

function clamp($value, $min, $max)
{
    return min($max, max($value, $min));
}

$pageElementsCount = max($_GET['pageElementsCount'] ?? 10, 1);
$visiblePagesCount = 7;
$pagesCount = getPagesCount(count($tasks), $pageElementsCount);
$pageIndex = clamp($_GET['pageIndex'] ?? 1, 1, $pagesCount);

$visiblePageIndices = getVisiblePageIndices($pagesCount, $visiblePagesCount, $pageIndex);

$elementsRange = range($pageElementsCount * ($pageIndex - 1), min($pageElementsCount * $pageIndex, count($tasks)) - 1);

?>

<div style="margin-top: 24px">
    <form method="post" action="db.php">
        <div style="display:block;">
            <label title="Name">
                <input placeholder="Enter name" name="name" type="text">
            </label>
            <label title="Description">
                <input placeholder="Enter description" name="description" type="text">
            </label>
            <label title="End date">
                <input name="end_date" type="date">
            </label>
            <input type="submit" value="Add">
            <input type='hidden'
                   name='action'
                   value='CREATE_TASK'>
        </div>
    </form>
</div>

<div class="mt-16px">
    <form method="get">
        <label for="pageElementsCount">
            Page elements count
        </label>
        <input id="pageElementsCount"
               name="pageElementsCount"
               value=<?= $pageElementsCount ?>>
        <input name="pageIndex"
               type="hidden"
               value=<?= $pageIndex ?>>
    </form>
</div>

<div class="mt-16px">All elements count: <?= count($tasks) ?></div>

<div class="mt-16px">
    <?php if ($pagesCount > 0) foreach ($visiblePageIndices as $pageNumber): ?>
        <form method="get" style="display: inline">
            <input type="submit" class="round-button<?php if ($pageNumber + 1 == $pageIndex) echo ' selected-page' ?>"
                   name="pageIndex"
                   value=<?= clamp($pageNumber + 1, 1, $pagesCount) ?>>
            <input name="pageElementsCount"
                   type="hidden"
                   value=<?= $pageElementsCount ?>>
        </form>
    <?php endforeach; ?>
</div>


<table class="mt-16px">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Description</th>
        <th>End date</th>
        <th>Actions</th>
    </tr>
    <?php if ($pagesCount > 0) foreach ($elementsRange as $pageNumber): ?>
        <?php
        $task = $tasks[$pageNumber];
        $taskId = $task['idTask'];
        ?>
        <tr>
            <td><?= $taskId ?></td>
            <td><?= $task['name'] ?></td>
            <td><?= $task['description'] ?></td>
            <td><?= $task['endDate'] ?></td>
            <td>
                <form method='post' action='db.php'>
                    <input type='submit' value='Delete'>
                    <input type='hidden'
                           name='deleteTaskId'
                           value='<?= $taskId ?>'>
                    <input type='hidden'
                           name='action'
                           value='DELETE_TASK'>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>


