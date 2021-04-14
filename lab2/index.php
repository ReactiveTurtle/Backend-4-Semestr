<style>
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
</style>
<?php
include 'TaskRepository.php';
$repository = new TaskRepository();
$tasks = $repository->getTasks();
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

<table style="margin-top: 24px">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Description</th>
        <th>End date</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($tasks as $task): ?>
        <?php $taskId = $task['idTask']; ?>
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
