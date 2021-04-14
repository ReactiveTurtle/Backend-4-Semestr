<?php
include 'TaskRepository.php';
include 'MathExtensions.php';
include 'Pagination.php';
include 'SearchPattern.php';
?>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./index.css">
    <title>Таблица</title>
</head>
<body>
<?php
function getSortDirChar($sortDir)
{
    switch ($sortDir) {
        case 'ASC':
        default:
            return '↑';
        case 'DESC':
            return '↓';
    }
}

function invertSortDir($sortDir)
{
    switch ($sortDir) {
        case 'ASC':
        default:
            return 'DESC';
        case 'DESC':
            return 'ASC';
    }
}

function getSearchPattern(): SearchPattern
{
    return new SearchPattern(
        $_GET['nameFilter'],
        $_GET['descriptionFilter'],
        $_GET['startDateFilter'],
        $_GET['endDateFilter'],
        $_GET['priorityFilter']);
}

$repository = new TaskRepository();

$tasksCount = $repository->getTasksCount();

$pageElementsCount = max($_GET['pageElementsCount'] ?? 10, 1);
$visiblePagesCount = 7;

$pagination = new Pagination($tasksCount, $pageElementsCount, $visiblePagesCount);
$pagesCount = $pagination->getPagesCount();
$pageIndex = MathExtensions::clamp($_GET['pageIndex'] ?? 1, 1, $pagesCount);

$visiblePageIndices = $pagination->getVisiblePageIndices($pageIndex);

$orderByField = $_GET['orderByField'] ?? 'idTask';
$sortDir = $_GET['sortDir'] ?? 'ASC';
$searchPattern = getSearchPattern();
$tasks = $repository->getTasks(
    $pageIndex - 1,
    $pageElementsCount,
    $orderByField,
    $sortDir,
    $searchPattern);
const DECLARED_PRIORITIES = ['low', 'medium', 'high'];
?>

<div style="margin-top: 24px; display: flex">
    <form method="post" action="db.php">
        <div style="display:block;">
            <label title="Name">
                <input placeholder="Enter name" name="name" type="text">
            </label>
            <label title="Description">
                <input placeholder="Enter description" name="description" type="text">
            </label>
            <label title="Start date">
                <input name="start_date" type="date">
            </label>
            <label title="End date">
                <input name="end_date" type="date">
            </label>
            <label>
                <select name="priority">
                    <option value=""></option>
                    <?php foreach (DECLARED_PRIORITIES as $priority): ?>
                        <option value="<?= $priority ?>"><?= $priority ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <input type="submit" value="Add">
            <input type='hidden'
                   name='action'
                   value='CREATE_TASK'>
        </div>
    </form>
    <form method="get" style="margin-left: 8px">
        <input name="pageElementsCount"
               type="hidden"
               value=<?= $pageElementsCount ?>>
        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
        <label>
            <input class="mt-16px" type="text" value="<?= $searchPattern->getName() ?>"
                   name="nameFilter" placeholder="Название">
        </label>
        <label>
            <input type="text" value="<?= $searchPattern->getDescription() ?>"
                   name="descriptionFilter" placeholder="Описание">
        </label>
        <label>
            <input type="date" value="<?= $searchPattern->getStartDate() ?>"
                   name="startDateFilter" placeholder="Дата начала">
        </label>
        <label>
            <input type="date" value="<?= $searchPattern->getEndDate() ?>"
                   name="endDateFilter" placeholder="Дата окончания">
        </label>
        <label>
            <select name="priorityFilter">
                <option value=""></option>
                <?php foreach (DECLARED_PRIORITIES as $priority): ?>
                    <option value="<?= $priority ?>"
                        <?= $priority == $searchPattern->getPriority() ? 'selected' : '' ?>>
                        <?= $priority ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <input type="submit">
    </form>
</div>

<div class="mt-16px">
    <form method="get">
        <label for="pageElementsCount">
            Page elements count
        </label>
        <input id="pageElementsCount"
               name="pageElementsCount"
               value="<?= $pageElementsCount ?>">
        <input name="pageIndex"
               type="hidden"
               value="<?= $pageIndex ?>">
        <input type="hidden" value="<?= $orderByField ?>" name="orderByField">
        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
    </form>
</div>


<div class="full-width">
    <form method="get">
        <input name="pageElementsCount"
               type="hidden"
               value=<?= $pageElementsCount ?>>
        <table class="center mt-16px">
            <tr class="tasks-tr">
                <th class="th-sort">
                    <form method="get">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="hidden" value="idTask" name="orderByField">
                        <input class="th-sort-submit" type="submit" value="Id <?
                        if ($orderByField == 'idTask') {
                            echo getSortDirChar($sortDir);
                        }
                        ?>">
                        <?
                        if ($orderByField == 'idTask') {
                            echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                        }
                        ?>
                    </form>
                </th>
                <th class="th-sort">
                    <form method="get">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="hidden" value="name" name="orderByField">
                        <input class="th-sort-submit" type="submit" value="Name <?
                        if ($orderByField == 'name') {
                            echo getSortDirChar($sortDir);
                        }
                        ?>">
                        <?
                        if ($orderByField == 'name') {
                            echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                        }
                        ?>
                    </form>
                </th>
                <th class="th-sort">
                    <form method="get">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="hidden" value="description" name="orderByField">
                        <input class="th-sort-submit" type="submit" value="Description <?
                        if ($orderByField == 'description') {
                            echo getSortDirChar($sortDir);
                        }
                        ?>">
                        <?
                        if ($orderByField == 'description') {
                            echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                        }
                        ?>
                    </form>
                </th>
                <th class="th-sort">
                    <form method="get">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="hidden" value="startDate" name="orderByField">
                        <input class="th-sort-submit" type="submit" value="Start date <?
                        if ($orderByField == 'startDate') {
                            echo getSortDirChar($sortDir);
                        }
                        ?>">
                        <?
                        if ($orderByField == 'startDate') {
                            echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                        }
                        ?>
                    </form>
                </th>
                <th class="th-sort">
                    <form method="get">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="hidden" value="endDate" name="orderByField">
                        <input class="th-sort-submit" type="submit" value="End date <?
                        if ($orderByField == 'endDate') {
                            echo getSortDirChar($sortDir);
                        }
                        ?>">
                        <?
                        if ($orderByField == 'endDate') {
                            echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                        }
                        ?>
                    </form>

                </th>
                <th class="th-sort">
                    <form method="get">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $pageIndex ?>" name="pageIndex">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="hidden" value="priority" name="orderByField">
                        <input class="th-sort-submit" type="submit" value="Priority <?
                        if ($orderByField == 'priority') {
                            echo getSortDirChar($sortDir);
                        }
                        ?>">
                        <?
                        if ($orderByField == 'priority') {
                            echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                        }
                        ?>
                    </form>
                </th>
                <th class="tasks-th">Actions</th>
            </tr>
            <?php if ($pagesCount > 0) foreach ($tasks as $task): ?>
                <?php
                $taskId = $task['idTask'];
                ?>
                <tr>
                    <td class="tasks-td"><?= $taskId ?></td>
                    <td class="tasks-td"><?= $task['name'] ?></td>
                    <td class="tasks-td"><?= $task['description'] ?></td>
                    <td class="tasks-td"><?= $task['startDate'] ?></td>
                    <td class="tasks-td"><?= $task['endDate'] ?></td>
                    <td class="tasks-td"><?= $task['priority'] ?></td>
                    <td class="tasks-td">
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
        <div class="center mt-16px">All elements count: <?= $tasksCount ?></div>
        <div class="center mt-16px">
            <?php if ($pagesCount > 0) foreach ($visiblePageIndices as $pageNumber): ?>
                <div style="display: inline">
                    <form method="get" style="display: contents">
                        <input type="hidden" value="<?= $pageElementsCount ?>" name="pageElementsCount">
                        <input type="hidden" value="<?= $orderByField ?>" name="orderByField">
                        <input type="hidden" value="<?= $sortDir ?>" name="sortDir">
                        <input type="submit"
                               class="round-button<?php if ($pageNumber + 1 == $pageIndex) echo ' selected-page' ?>"
                               name="pageIndex"
                               value=<?= MathExtensions::clamp($pageNumber + 1, 1, $pagesCount) ?>>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>
</body>
</html>
