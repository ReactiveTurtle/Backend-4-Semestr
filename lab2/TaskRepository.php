<?php

class TaskRepository
{
    protected $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=lab2", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "CREATE DATABASE IF NOT EXISTS demo";
            $this->pdo->exec($sql);
            unset($this->pdo);

            $this->pdo = new PDO("mysql:host=lab2;dbname=demo", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("ERROR: Could not connect. " . $e->getMessage());
        }

        $this->initTable();
    }

    function initTable()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS Tasks(
                    idTask INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(50) NOT NULL,
                    description VARCHAR(255) NOT NULL,
                    endDate DATE NOT NULL)";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("ERROR: Could not able to execute $sql. " . $e->getMessage());
        }
    }

    function addTask($name, $description, $endDate)
    {
        try {
            $sql = "INSERT INTO Tasks (name, description, endDate)
                    VALUES (:name, :description, STR_TO_DATE('$endDate', '%Y-%m-%d'))";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteTask($id)
    {
        try {
            $sql = "DELETE FROM Tasks WHERE idTask = $id";
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    function getTasks()
    {
        $sql = "SELECT * FROM Tasks";
        $result = $this->pdo->query($sql);
        return $result;
    }
}