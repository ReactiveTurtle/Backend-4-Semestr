<?php


class Route
{
    private $url;
    private $method;
    private $classMethodName;

    public function __construct($url, $method, $classMethodName)
    {
        $this->url = $url;
        $this->method = $method;
        $this->classMethodName = $classMethodName;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getClassMethodName()
    {
        return $this->classMethodName;
    }
}