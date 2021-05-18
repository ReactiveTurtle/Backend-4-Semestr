<?php
session_start();

require_once 'base/TaskApi.php';
include 'base/Method.php';
include 'base/Route.php';

try {
    $api = new TaskApi(new TaskRepository(
        'demo',
        'root',
        'root',
        'localhost'
    ));
    $api->addRoute(new Route(
        "tasks",
        Method::GET,
        "tasksTablePage"
    ));
    $api->addRoute(new Route(
        "tasks/add",
        Method::GET,
        "addTaskPage"
    ));
    $api->addRoute(new Route(
        "tasks/:id/update",
        Method::GET,
        "updateTaskPage"
    ));
    $api->addRoute(new Route(
        "api/tasks",
        Method::GET,
        "getAllTasks"
    ));
    $api->addRoute(new Route(
        "api/tasks/:id",
        Method::GET,
        "getTask"
    ));
    $api->addRoute(new Route(
        "api/tasks/addTaskAndRedirect",
        Method::POST,
        "addTaskAndRedirect"
    ));
    $api->addRoute(new Route(
        "api/tasks/add",
        Method::POST,
        "addTask"
    ));
    $api->addRoute(new Route(
        "api/tasks/:id/updateTaskAndRedirect",
        Method::PUT,
        "updateTaskAndRedirect"
    ));
    $api->addRoute(new Route(
        "api/tasks/:id/update",
        Method::PUT,
        "updateTask"
    ));
    $api->addRoute(new Route(
        "api/tasks/deleteTasksAndRedirect",
        Method::DELETE,
        "deleteTasksAndRedirect"
    ));
    $api->addRoute(new Route(
        "api/tasks/delete",
        Method::DELETE,
        "deleteTasks"
    ));
    echo $api->run();
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}
?>