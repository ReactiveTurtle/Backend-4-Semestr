<?php

class TaskRepository
{
    protected $pdo;

    public function __construct(
        string $dbName,
        string $username,
        string $password,
        string $host,
        int $port = 3306)
    {
        $this->pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "CREATE DATABASE IF NOT EXISTS $dbName";
        $this->pdo->exec($sql);

        $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->initTable();
    }

    private function initTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS Tasks(
                    idTask INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    taskName VARCHAR(50) NOT NULL,
                    description VARCHAR(255) NOT NULL,
                    startDate VARCHAR(255) NOT NULL,
                    endDate VARCHAR(255) NOT NULL,
                    priority VARCHAR(10) NOT NULL)";
        $this->pdo->exec($sql);
    }

    public function getTasks($pageNumber, $pageElementsCount, $orderByField, $sortDir, SearchPattern $searchPattern)
    {
        $orderByField = $orderByField ?? 'idTask';
        if ($orderByField != 'idTask' && $orderByField != 'taskName' &&
            $orderByField != 'description' && $orderByField != 'endDate'
            && $orderByField != 'startDate' && $orderByField != 'priority') {
            $orderByField = 'idTask';
        }
        if ($sortDir != 'ASC' && $sortDir != 'DESC') {
            $sortDir = 'ASC';
        }
        $case = "(CASE WHEN $orderByField like '[a-z]%' THEN 0 ELSE 1 END),";
        $startRange = max((int)$pageNumber * (int)$pageElementsCount, 0);
        $sql = "SELECT * FROM Tasks 
                {$this->buildFilterQuery($searchPattern)}
                ORDER BY $case $orderByField $sortDir 
                LIMIT {$pageElementsCount} OFFSET {$startRange};";
        $stmt = $this->pdo->prepare($sql);
        $this->bindFilterValues($stmt, $searchPattern);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    public function getTaskById($idTask)
    {
        try {
            if (!is_numeric($idTask)) {
                return null;
            }
            $sql = "SELECT * FROM Tasks 
                WHERE idTask = $idTask";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        } catch (PDOException $e) {
        }
        return null;
    }

    public function getTasksCount(SearchPattern $searchPattern)
    {
        $sql = "SELECT count(*) FROM Tasks
                {$this->buildFilterQuery($searchPattern)}";
        $stmt = $this->pdo->prepare($sql);
        $this->bindFilterValues($stmt, $searchPattern);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function addTask($taskName, $description, $startDate, $endDate, $priority): void
    {
        try {
            $sql = "INSERT INTO Tasks (taskName, description, startDate, endDate, priority)
                    VALUES (:taskName, :description, :startDate, :endDate, :priority)";
            $stmt = $this->pdo->prepare($sql);
            $this->bindValues($stmt, $taskName, $description, $startDate, $endDate, $priority);
            $stmt->execute();
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
        }
    }

    public function updateTask($taskId, $taskName, $description, $startDate, $endDate, $priority)
    {
        if (!is_numeric($taskId)) {
            return;
        }
        try {
            $sql = "UPDATE Tasks SET taskName=:taskName, 
                description=:description, 
                startDate=:startDate, 
                endDate=:endDate, 
                priority=:priority
                WHERE idTask=$taskId";
            $stmt = $this->pdo->prepare($sql);
            $this->bindValues($stmt, $taskName, $description, $startDate, $endDate, $priority);
            $stmt->execute();
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
        }
    }

    public function deleteTasksByIds($ids): void
    {
        if (!is_array($ids) || count($ids) == 0) {
            return;
        }
        $where = '';
        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                return;
            }
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= "idTask = $id";
        }
        if ($where != '') {
            $where = " WHERE " . $where;
        }

        $sql = "DELETE FROM Tasks" . $where;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $this->pdo->exec($sql);
    }

    private function bindValues(
        PDOStatement $stmt,
        string $taskName,
        string $description,
        string $startDate,
        string $endDate,
        string $priority)
    {
        $stmt->bindParam(':taskName', $taskName);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':priority', $priority);
    }

    private function bindFilterValues(PDOStatement $stmt, SearchPattern $searchPattern)
    {
        $nameFilter = $searchPattern->getName() ?? '';
        if ($nameFilter != '') {
            $nameFilter = '%' . $nameFilter . '%';
            $stmt->bindValue(':nameFilter', $nameFilter, PDO::PARAM_STR);
        }
        $descriptionFilter = $searchPattern->getDescription() ?? '';
        if ($descriptionFilter != '') {
            $descriptionFilter = '%' . $descriptionFilter . '%';
            $stmt->bindValue(':descriptionFilter', $descriptionFilter, PDO::PARAM_STR);
        }
        if ($searchPattern->getStartDateStart() != null) {
            $stmt->bindValue(':startDateFilterStart', $searchPattern->getStartDateStart());
        }
        if ($searchPattern->getStartDateEnd() != null) {
            $stmt->bindValue(':startDateFilterEnd', $searchPattern->getStartDateEnd());
        }
        if ($searchPattern->getEndDateStart() != null || $searchPattern->getEndDateEnd() != null) {
            $stmt->bindValue(':endDateFilterStart', $searchPattern->getEndDateStart());
        }
        if ($searchPattern->getEndDateStart() != null || $searchPattern->getEndDateEnd() != null) {
            $stmt->bindValue(':endDateFilterEnd', $searchPattern->getEndDateEnd());
        }
        $priorityFilter = $searchPattern->getPriority() ?? '';
        if ($priorityFilter != '') {
            $stmt->bindValue(':priorityFilter', $priorityFilter, PDO::PARAM_STR);
        }
    }

    private function buildFilterQuery(SearchPattern $searchPattern): string
    {
        $where = '';
        if ($searchPattern->getName() != null) {
            $where .= 'name LIKE :nameFilter';
        }
        if ($searchPattern->getDescription() != null) {
            if ($where != '') {
                $where .= ' AND ';
            }
            $where .= 'description LIKE :descriptionFilter';
        }
        if ($searchPattern->getStartDateStart() != null ||
            $searchPattern->getStartDateEnd() != null ||
            $where != '') {
            $where .= ' AND startDate ';
        }
        if ($searchPattern->getStartDateStart() != null && $searchPattern->getStartDateEnd() != null) {
            $where .= "BETWEEN :startDateFilterStart AND :startDateFilterEnd";
        } elseif ($searchPattern->getStartDateStart() != null || $searchPattern->getStartDateEnd() != null) {
            if ($searchPattern->getStartDateStart() == null) {
                $where .= "<= :startDateFilterEnd";
            } elseif ($searchPattern->getStartDateEnd() == null) {
                $where .= ">= :startDateFilterStart";
            }
        }

        if ($searchPattern->getEndDateStart() != null ||
            $searchPattern->getEndDateEnd() != null ||
            $where != '') {
            $where .= ' AND endDate ';
        }
        if ($searchPattern->getEndDateStart() != null && $searchPattern->getEndDateEnd() != null) {
            $where .= "BETWEEN :endDateFilterStart AND :endDateFilterEnd";
        } elseif ($searchPattern->getEndDateStart() != null || $searchPattern->getEndDateEnd() != null) {
            if ($searchPattern->getEndDateStart() == null) {
                $where .= "<= :endDateFilterEnd";
            } elseif ($searchPattern->getEndDateEnd() == null) {
                $where .= ">= :endDateFilterStart";
            }
        }
        if ($searchPattern->getEndDateStart() != null && $searchPattern->getEndDateEnd() != null) {
            if ($where != '') {
                $where .= ' AND ';
            }
            $where .= "endDate  BETWEEN :endDateFilterStart AND :endDateFilterEnd";
        }
        if ($searchPattern->getPriority() != null) {
            if ($where != '') {
                $where .= ' AND ';
            }

            $where .= 'priority = :priorityFilter';
        }
        if ($where != '') {
            $where = 'WHERE ' . $where;
        }
        return $where;
    }
}