<?php


class SearchPattern
{
    private $name;
    private $description;
    private $startDateStart;
    private $startDateEnd;
    private $endDateStart;
    private $endDateEnd;
    private $priority;

    public function __construct(
        $name,
        $description,
        $startDateStart,
        $startDateEnd,
        $endDateStart,
        $endDateEnd,
        $priority)
    {
        $this->name = $name;
        $this->description = $description;
        $this->startDateEnd = $startDateEnd;
        $this->startDateStart = $startDateStart;
        $this->endDateStart = $endDateStart;
        $this->endDateEnd = $endDateEnd;
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

    public function getStartDateStart()
    {
        return $this->startDateStart;
    }

    public function getStartDateEnd()
    {
        return $this->startDateEnd;
    }

    public function getEndDateStart()
    {
        return $this->endDateStart;
    }

    public function getEndDateEnd()
    {
        return $this->endDateEnd;
    }

    public function getPriority()
    {
        return $this->priority;
    }
}