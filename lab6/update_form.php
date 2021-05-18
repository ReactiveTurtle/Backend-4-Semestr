<?php
const DECLARED_PRIORITIES = ['low', 'medium', 'high'];
$task = $GLOBALS['task'];
?>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/style/body.css">
    <link rel="stylesheet" href="/style/input.css">
    <link rel="stylesheet" href="/style/button.css">
    <link rel="stylesheet" href="/style/card.css">
    <title>Таблица</title>
</head>
<body>
<div class="card" style="width: 300px; margin: 180px auto auto;">
    <h1 class="card-title">
        Update task
    </h1>
    <form style="align-content: center" method="post" action="/api/tasks/<?= $task['idTask'] ?>/updateTaskAndRedirect">
        <div class="input-wrapper">
            <input id="taskName"
                   type="text"
                <?= $task['taskName'] != "" ? 'class="dirty"' : '' ?>
                   name="taskName"
                   value="<?= $task['taskName'] ?>">
            <label for="taskName">Name</label>
        </div>
        <div class="input-wrapper">
            <input id="description"
                   type="text"
                <?= $task['description'] != "" ? 'class="dirty"' : '' ?>
                   name="description"
                   value="<?= $task['description'] ?>">
            <label for="description">Description</label>
        </div>
        <div class="input-wrapper">
            <input id="start_date"
                   type="date"
                   class="dirty"
                   name="start_date"
                   value="<?= $task['startDate'] ?>">
            <label for="start_date">Start date</label>
        </div>
        <div class="input-wrapper">
            <input class="dirty"
                   id="end_date"
                   type="date"
                   name="end_date"
                   value="<?= $task['endDate'] ?>">
            <label for="end_date">End date</label>
        </div>
        <div class="input-wrapper">
            <select class="dirty" name="priority" id="priority">
                <option style="color: black" value="">Select value</option>
                <?php foreach (DECLARED_PRIORITIES as $priority): ?>
                    <option style="color: black"
                            value="<?= $priority ?>" <?= $priority == $task['priority'] ? 'selected' : '' ?>><?= $priority ?></option>
                <?php endforeach; ?>
            </select>
            <label for="priority">Priority</label>
        </div>
        <input type='hidden'
               name='action'
               value='UPDATE_TASK'>
        <div style="margin: 25px 0 0 0;">
            <input class="button" type="submit" value="Update">
        </div>
    </form>

    <script type="text/javascript">
        const inputs = document.querySelectorAll('input');

        inputs.forEach(el => {
            el.addEventListener('blur', e => {
                if (e.target.type !== 'text')
                    return;
                if (e.target.value) {
                    e.target.classList.add('dirty');
                } else {
                    e.target.classList.remove('dirty');
                }
            })
        })
    </script>
</div>