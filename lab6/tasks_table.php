<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/style/body.css">
    <link rel="stylesheet" href="/style/table.css">
    <link rel="stylesheet" href="/style/input.css">
    <link rel="stylesheet" href="/style/button.css">
    <link rel="stylesheet" href="/style/card.css">
    <link rel="stylesheet" href="/style/index.css">
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
        $_GET['nameFilter'] ?? null,
        $_GET['descriptionFilter'] ?? null,
        $_GET['startDateFilterStart'] ?? null,
        $_GET['startDateFilterEnd'] ?? null,
        $_GET['endDateFilterStart'] ?? null,
        $_GET['endDateFilterEnd'] ?? null,
        $_GET['priorityFilter'] ?? null);
}

$repository = new TaskRepository(
    'demo',
    'root',
    'root',
    'localhost'
);

$searchPattern = getSearchPattern();
$tasksCount = $repository->getTasksCount($searchPattern);

$pageElementsCount = max($_GET['pageElementsCount'] ?? 10, 1);
$visiblePagesCount = 7;
$orderByField = $_GET['orderByField'] ?? 'idTask';
$sortDir = $_GET['sortDir'] ?? 'ASC';

$pagination = new Pagination($tasksCount, $pageElementsCount, $visiblePagesCount);
$pagesCount = $pagination->getPagesCount();
$pageIndex = MathExtensions::clamp($_GET['pageIndex'] ?? 1, 1, $pagesCount);

$tasks = $repository->getTasks(
    $pageIndex - 1,
    $pageElementsCount,
    $orderByField,
    $sortDir,
    $searchPattern);


$visiblePageIndices = $pagination->getVisiblePageIndices($pageIndex);
const DECLARED_PRIORITIES = ['low', 'medium', 'high'];
?>

<div style="display: flex; margin: 60px auto auto;">
    <div style="margin: auto; display: flex">
        <form class="card" style="margin: 0 32px 0 0" method="get" action="/tasks">
            <h1 class="card-title">Filter</h1>
            <div class="input-wrapper">
                <input name="nameFilter"
                       id="nameFilter"
                    <?= $searchPattern->getName() != null ? 'class="dirty"' : '' ?>
                       type="text"
                       value="<?= $searchPattern->getName() ?>">
                <label class="label" for="nameFilter">Название</label>
            </div>
            <div class="input-wrapper">
                <input type="text"
                       id="descriptionFilter"
                    <?= $searchPattern->getDescription() != null ? 'class="dirty"' : '' ?>
                       name="descriptionFilter"
                       value="<?= $searchPattern->getDescription() ?>">
                <label for="descriptionFilter">Описание</label>
            </div>
            <div class="form-group">
                <div class="input-wrapper">
                    <input id="startDateFilterStart"
                           type="date"
                           class="dirty"
                           value="<?= $searchPattern->getStartDateStart() ?>"
                           name="startDateFilterStart">
                    <label for="startDateFilterStart">Дата начала. Начало диапазона</label>
                </div>
                <div class="input-wrapper">
                    <input id="startDateFilterEnd"
                           type="date"
                           class="dirty"
                           value="<?= $searchPattern->getStartDateEnd() ?>"
                           name="startDateFilterEnd">
                    <label for="startDateFilterEnd">Дата начала. Конец диапазона</label>
                </div>
            </div>
            <div>
                <div class="input-wrapper">
                    <input id="endDateFilterStart"
                           type="date"
                           class="dirty"
                           value="<?= $searchPattern->getEndDateStart() ?>"
                           name="endDateFilterStart">
                    <label for="endDateFilterStart">Дата окончания. Начало диапазона</label>
                </div>
                <div class="input-wrapper">
                    <input id="endDateFilterEnd"
                           type="date"
                           class="dirty"
                           value="<?= $searchPattern->getEndDateEnd() ?>"
                           name="endDateFilterEnd">
                    <label for="endDateFilterEnd">Дата окончания. Конец диапазона</label>
                </div>
            </div>
            <div class="input-wrapper">
                <select id="priorityFilter" class="dirty" name="priorityFilter">
                    <option style="color: black" value="">Not selected</option>
                    <?php foreach (DECLARED_PRIORITIES as $priority): ?>
                        <option style="color: black" value="<?= $priority ?>"
                            <?= $priority == $searchPattern->getPriority() ? 'selected' : '' ?>>
                            <?= $priority ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="priorityFilter">Priority</label>
            </div>
            <div>
                <input class="button" type="submit" style="margin: 15px 0" value="Filter tasks">
            </div>
        </form>

        <div class="card" style="margin: 0 0 0 32px">
            <form style="width: 200px" method="get">
                <div class="input-wrapper">
                    <input id="pageElementsCount"
                           type="number"
                           class="dirty"
                           value="<?= $pageElementsCount ?>"
                           name="pageElementsCount">
                    <label for="pageElementsCount">Page elements count</label>
                </div>
            </form>
            <form method="get" action="tasks/add">
                <input class="button" type="submit" value="Add new task">
            </form>
            <form method="post" action="/api/tasks/deleteTasksAndRedirect">
                <table class="content-table">
                    <thead>
                    <tr>
                        <th class="th-sort">
                            <form>
                            </form>
                            <form method="get">
                                <input type="hidden" value="idTask" name="orderByField">
                                <input type="submit" value="Id <?
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
                                <input type="hidden" value="taskName" name="orderByField">
                                <input type="submit" value="Name <?
                                if ($orderByField == 'taskName') {
                                    echo getSortDirChar($sortDir);
                                }
                                ?>">
                                <?
                                if ($orderByField == 'taskName') {
                                    echo '<input type="hidden" value=' . invertSortDir($sortDir) . ' name="sortDir">';
                                }
                                ?>
                            </form>
                        </th>
                        <th class="th-sort">
                            <form method="get">
                                <input type="hidden" value="description" name="orderByField">
                                <input type="submit" value="Description <?
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
                                <input type="hidden" value="startDate" name="orderByField">
                                <input type="submit" value="Start date <?
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
                                <input type="hidden" value="endDate" name="orderByField">
                                <input type="submit" value="End date <?
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
                                <input type="hidden" value="priority" name="orderByField">
                                <input type="submit" value="Priority <?
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
                        <th class="th-actions-label">Check</th>
                        <th class="th-actions-label">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($pagesCount > 0) foreach ($tasks as $task): ?>
                        <?php
                        $taskId = $task['idTask'];
                        ?>
                        <tr>
                            <td><?= $taskId ?></td>
                            <td><?= $task['taskName'] ?></td>
                            <td><?= $task['description'] ?></td>
                            <td><?= $task['startDate'] ?></td>
                            <td><?= $task['endDate'] ?></td>
                            <td><?= $task['priority'] ?></td>
                            <td><input style="padding: 8px" type="checkbox" name="deleteTaskIds[]"
                                       value="<?= $taskId ?>" aria-label=""></td>
                            <td>
                                <div style="display: flex">
                                    <form style="margin: 0 8px 0 0" method='get'
                                          action='/tasks/<?= $taskId ?>/update'>
                                        <input class="button" type='submit' value='Update'>
                                    </form>
                                    <form style="margin: 0" method='post' action='/api/tasks/deleteTasksAndRedirect'>
                                        <input class="button delete-button" type='submit' value='Delete'>
                                        <input type='hidden'
                                               name='deleteTaskIds[]'
                                               value='<?= $taskId ?>'>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="display: flex">
                    <input class="button delete-button" type='submit' value='Delete several'>
                    <div style="color: white; margin: 0 auto 0 auto">All elements count: <?= $tasksCount ?></div>
                    <div style="width: max-content; margin: 0 16px 0 0">
                        <?php if ($pagesCount > 0) foreach ($visiblePageIndices as $pageNumber): ?>
                            <div style="display: inline">
                                <form method="get" style="display: contents">
                                    <input type="submit"
                                           class="round-button<?php if ($pageNumber + 1 == $pageIndex) echo ' selected-page' ?>"
                                           name="pageIndex"
                                           value=<?= MathExtensions::clamp($pageNumber + 1, 1, $pagesCount) ?>>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>

<script type="text/javascript">
    const inputs = document.querySelectorAll('input');

    inputs.forEach(el => {
        el.addEventListener('blur', e => {
            if (e.target.type !== 'text' && e.target.type !== 'number')
                return;
            if (e.target.value) {
                e.target.classList.add('dirty');
            } else {
                e.target.classList.remove('dirty');
            }
        })
    })
</script>
</html>
