<?php


class SearchPattern
{
    private $name;
    private $description;
    private $startDate;
    private $endDate;
    private $priority;
    public function __construct($name, $description, $startDate, $endDate, $priority)
    {
        $this->name = $name;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->priority = $priority;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getPriority()
    {
        return $this->priority;
    }
}