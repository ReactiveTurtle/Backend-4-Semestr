<?php

class TaskRepository
{
    protected $pdo;

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
                    VALUES (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority),
                    (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority),
                    (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority),
                    (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority),
                    (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority),
                    (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority),
                    (:name, :description, STR_TO_DATE('$startDate', '%Y-%m-%d'), STR_TO_DATE('$endDate', '%Y-%m-%d'), :priority)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
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
            $orderByField != 'description' && $orderByField != 'endDate') {
            $orderByField = 'idTask';
        }
        if ($sortDir != 'ASC' && $sortDir != 'DESC') {
            $sortDir = 'ASC';
        }
        try {
            $startRange = (int)$pageNumber * (int)$pageElementsCount;
            $sql = "SELECT * FROM Tasks 
                    {$this->buildFilterQuery($searchPattern)}
                    ORDER BY $orderByField $sortDir 
                    LIMIT {$pageElementsCount} OFFSET {$startRange};";
            $stmt = $this->pdo->prepare($sql);
            $nameFilter = $searchPattern->getName() ?? '';
            if ($nameFilter != '') {
                $nameFilter = '%' . $nameFilter . '%';
                $stmt->bindParam(':nameFilter', $nameFilter, PDO::PARAM_STR);
            }
            $descriptionFilter = $searchPattern->getDescription() ?? '';
            if ($descriptionFilter != '') {
                $descriptionFilter = '%' . $descriptionFilter . '%';
                $stmt->bindParam(':descriptionFilter', $descriptionFilter, PDO::PARAM_STR);
            }
            $priorityFilter = $searchPattern->getPriority() ?? '';
            if ($priorityFilter != '') {
                $stmt->bindParam(':priorityFilter', $priorityFilter, PDO::PARAM_STR);
            }
            echo $sql . "<br>";
            $stmt->execute();
            return $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo $e;
            return [];
        }
    }

    public function getTasksCount()
    {
        $sql = "SELECT count(*) FROM Tasks";
        $result = $this->pdo->prepare($sql);
        $result->execute([$sql]);
        return $result->fetchColumn();
    }

    private function buildFilterQuery($searchPattern): string
    {
        $where = "";
        if ($searchPattern->getName() != null) {
            $where .= 'name LIKE :nameFilter';
        }
        if ($searchPattern->getDescription() != null) {
            if ($where != "") {
                $where .= " AND ";
            }
            $where .= "description LIKE :descriptionFilter";
        }
        if ($searchPattern->getPriority() != null) {
            if ($where != "") {
                $where .= " AND ";
            }
            $where .= "priority = :priorityFilter";
        }
        if ($where != "") {
            $where = "WHERE " . $where;
        }
        echo $where . '<br>';
        return $where;
    }
}