<?php
include 'TaskRepository.php';

$repository = new TaskRepository();

echo 'Action \'' . $_POST['action'] . '\'';
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case "CREATE_TASK":
            $repository->addTask($_POST['name'], $_POST['description'], $_POST['start_date'], $_POST['end_date'], $_POST['priority']);
            break;
        case "DELETE_TASK":
            $repository->deleteTask($_POST['deleteTaskId']);
            break;
    }
}
?>

<script type="text/javascript">window.location = "index.php"</script>

