<?php

class TaskRepository
{
    protected $pdo;
    private $minDate = '1900-01-01';
    private $maxDate = '2099-01-01';

    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "CREATE DATABASE IF NOT EXISTS demo";
            $this->pdo->exec($sql);
            unset($this->pdo);

            $this->pdo = new PDO("mysql:host=localhost;port=3306;dbname=demo", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo($e);
            die("ERROR: Could not connect. " . $e->getMessage());
        }

        $this->initTable();
    }

    private function initTable()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS Tasks(
                    idTask INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(50) NOT NULL,
                    description VARCHAR(255) NOT NULL,
                    startDate DATE NOT NULL,
                    endDate DATE NOT NULL,
                    priority VARCHAR(10) NOT NULL)";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("ERROR: Could not able to execute $sql. " . $e->getMessage());
        }
    }

    public function addTask($name, $description, $startDate, $endDate, $priority)
    {
        try {
            $sql = "INSERT INTO Tasks (name, description, startDate, endDate, priority)
                    VALUES (:name, :description, STR_TO_DATE(:startDate, '%Y-%m-%d'), STR_TO_DATE(:endDate, '%Y-%m-%d'), :priority)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':priority', $priority);
            $stmt->execute();
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteTask($id)
    {
        try {
            $sql = "DELETE FROM Tasks WHERE idTask = $id";
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getTasks($pageNumber, $pageElementsCount, $orderByField, $sortDir, SearchPattern $searchPattern)
    {
        $orderByField = $orderByField ?? 'idTask';
        if ($orderByField != 'idTask' && $orderByField != 'name' &&
            $orderByField != 'description' && $orderByField != 'endDate'
            && $orderByField != 'startDate' && $orderByField != 'priority') {
            $orderByField = 'idTask';
        }
        if ($sortDir != 'ASC' && $sortDir != 'DESC') {
            $sortDir = 'ASC';
        }
        try {
            $case = "(CASE WHEN $orderByField like '[a-z]%' THEN 0 ELSE 1 END),";
            $startRange = max((int)$pageNumber * (int)$pageElementsCount, 0);
            $sql = "SELECT * FROM Tasks 
                    {$this->buildFilterQuery($searchPattern)}
                    ORDER BY $case $orderByField $sortDir 
                    LIMIT {$pageElementsCount} OFFSET {$startRange};";
            $stmt = $this->pdo->prepare($sql);
            $this->bindValues($stmt, $searchPattern);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
        }
        return [];
    }


    public function getTasksCount(SearchPattern $searchPattern)
    {
        $sql = "SELECT count(*) FROM Tasks
                {$this->buildFilterQuery($searchPattern)}";
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $searchPattern);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function bindValues($stmt, SearchPattern $searchPattern)
    {
        $nameFilter = $searchPattern->getName() ?? '';
        if ($nameFilter != '') {
            $nameFilter = '%' . $nameFilter . '%';
            echo $nameFilter . '<br>';
            $stmt->bindValue(':nameFilter', $nameFilter, PDO::PARAM_STR);
        }
        $descriptionFilter = $searchPattern->getDescription() ?? '';
        if ($descriptionFilter != '') {
            $descriptionFilter = '%' . $descriptionFilter . '%';
            echo $descriptionFilter . '<br>';
            $stmt->bindValue(':descriptionFilter', $descriptionFilter, PDO::PARAM_STR);
        }
        if ($searchPattern->getStartDateStart() != null || $searchPattern->getStartDateEnd() != null) {
            $startDateFilterStart = $searchPattern->getStartDateStart() != null ? $searchPattern->getStartDateStart() : $this->minDate;
            $startDateFilterEnd = $searchPattern->getStartDateEnd() != null ? $searchPattern->getStartDateEnd() : $this->maxDate;
            $stmt->bindValue(':startDateFilterStart', $startDateFilterStart);
            $stmt->bindValue(':startDateFilterEnd', $startDateFilterEnd);
        }
        if ($searchPattern->getEndDateStart() != null || $searchPattern->getEndDateEnd() != null) {
            $endDateFilterStart = $searchPattern->getEndDateStart() != null ? $searchPattern->getEndDateStart() : $this->minDate;
            $endDateFilterEnd = $searchPattern->getEndDateEnd() != null ? $searchPattern->getEndDateEnd() : $this->maxDate;
            $stmt->bindValue(':endDateFilterStart', $endDateFilterStart);
            $stmt->bindValue(':endDateFilterEnd', $endDateFilterEnd);
        }
        $priorityFilter = $searchPattern->getPriority() ?? '';
        if ($priorityFilter != '') {
            echo $priorityFilter . '<br>';
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
        if ($searchPattern->getStartDateStart() != null || $searchPattern->getStartDateEnd() != null) {
            if ($where != '') {
                $where .= ' AND ';
            }
            $where .= "startDate BETWEEN :startDateFilterStart AND :startDateFilterEnd";
        }
        if ($searchPattern->getEndDateStart() != null || $searchPattern->getEndDateEnd() != null) {
            if ($where != '') {
                $where .= ' AND ';
            }
            $where .= "endDate BETWEEN :endDateFilterStart AND :endDateFilterEnd";
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