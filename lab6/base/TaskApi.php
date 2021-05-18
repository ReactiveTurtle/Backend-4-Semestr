<?php
include "Api.php";
include "TaskRepository.php";
include "SearchPattern.php";
include "Pagination.php";
include "MathExtensions.php";

class TaskApi extends Api
{
    public $apiName = 'tasks';
    private ?TaskRepository $repository;

    public function __construct(TaskRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function tasksTablePage()
    {
        return $this->page('./tasks_table.php');
    }

    public function addTaskPage()
    {
        return $this->page('./create_form.php');
    }

    public function updateTaskPage()
    {
        $taskId = $this->getValueFromRoute('id');
        if (!is_numeric($taskId)) {
            return $this->notFound('Task id must be a number.');
        }
        $task = $this->repository->getTaskById($taskId);
        if ($task == null) {
            return $this->notFound('Task with id ' . $taskId . ' not exists.');
        }
        $GLOBALS['task'] = $task;
        return $this->page('./update_form.php');
    }

    public function addTaskAndRedirect()
    {
        $this->addTask();
        $this->redirect('/tasks');
    }

    public function addTask()
    {
        $this->repository->addTask(
            $this->requestParams['taskName'] ?? "",
            $this->requestParams['description'] ?? "",
            $this->requestParams['start_date'] ?? "",
            $this->requestParams['end_date'] ?? "",
            $this->requestParams['priority'] ?? "");
        return $this->ok();
    }

    private function getSearchPattern(): SearchPattern
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

    public function getAllTasks()
    {
        $searchPattern = $this->getSearchPattern();
        $tasksCount = $this->repository->getTasksCount($searchPattern);

        $orderByField = $_GET['orderByField'] ?? 'idTask';
        $sortDir = 'ASC';

        return $this->ok($this->repository->getTasks(
            0,
            $tasksCount,
            $orderByField,
            $sortDir,
            $searchPattern));
    }

    public function getTask()
    {
        return $this->ok($this->repository->getTaskById(
            $this->getValueFromRoute('id')));
    }

    public function updateTaskAndRedirect()
    {
        $this->updateTask();
        $this->redirect('/tasks');
    }


    public function updateTask()
    {
        if (!$this->repository->getTaskById($this->getValueFromRoute('id') ?? "")) {
            return $this->badRequest('Task not found');
        }
        $this->repository->updateTask(
            $this->getValueFromRoute('id') ?? "",
            $this->requestParams['taskName'] ?? "",
            $this->requestParams['description'] ?? "",
            $this->requestParams['start_date'] ?? "",
            $this->requestParams['end_date'] ?? "",
            $this->requestParams['priority'] ?? "");
        return $this->ok();
    }

    public function deleteTasksAndRedirect()
    {
        $this->deleteTasks();
        $this->redirect('/tasks');
    }


    public function deleteTasks()
    {
        $this->repository->deleteTasksByIds($this->requestParams['deleteTaskIds'] ?? []);
        return $this->ok();
    }
}