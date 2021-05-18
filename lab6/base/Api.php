<?php

abstract class Api
{
    private $routes = [];
    protected $apiName = '';

    protected ?string $method = null; //GET|POST|PUT|DELETE

    protected ?string $requestUrl = null;
    protected ?array $requestUrlParts = null;
    protected ?array $requestRoute = null;
    protected array $requestParams = [];

    protected string $actionMethodName = '';


    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: text/html");

        $url = $_SERVER['REQUEST_URI'];
        $this->rememberRequest($url);
        $this->detectRequestMethod();
    }

    private function rememberRequest(string $url): void
    {
        $urlParts = parse_url($url);
        if ($urlParts && array_key_exists('path', $urlParts)) {
            $this->requestUrl = trim($urlParts['path'], '/');
            $this->requestUrlParts = explode('/', $this->requestUrl);
            $this->requestParams = $_REQUEST;
            foreach ($this->requestParams as $key => $value) {
                if (is_string($value)) {
                    $this->requestParams[$key] = htmlspecialchars($value);
                } else {
                    $this->requestParams[$key] = $value;
                }
            }
        } else {
            throw $this->internalServerError();
        }
    }

    private
    function detectRequestMethod(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $inputParams = [];
        if ($this->method == Method::DELETE || $this->method == Method::PUT) {
            parse_str(file_get_contents('php://input', 'r'), $inputParams);
        } else if ($this->method != Method::POST && $this->method != Method::GET) {
            throw new RuntimeException('Invalid Method');
        }
        foreach ($inputParams as $key => $value) {
            if (is_string($value)) {
                $this->requestParams[$key] = htmlspecialchars($value);
            } else {
                $this->requestParams[$key] = $value;
            }
        }
    }

    public
    function run()
    {
        $parts = $this->requestUrlParts;
        if ($this->endsWith($this->requestUrl, '.css')) {
            header("Content-Type: text/css");
            return file_get_contents(implode("/", $parts));
        }
        $routes = array_filter($this->routes, function ($element) {
                $elementParts = explode('/', $element[0]);
                if (count($elementParts) != count($this->requestUrlParts)) {
                    return false;
                }
                $isEquals = true;
                for ($i = 0; $i < count($elementParts) && $isEquals; $i++) {
                    $partsEqual = $elementParts[$i] == $this->requestUrlParts[$i];
                    if (!$partsEqual) {
                        $partsEqual = $elementParts[$i][0] == ':' &&
                            is_numeric($this->requestUrlParts[$i]);
                    }
                    $isEquals = $partsEqual;
                }
                return $isEquals;
            }) ?? [];
        if (count($routes) > 1) {
            return $this->internalServerError('Duplicate Routes Detected');
        }
        if (count($routes) == 0) {
            throw new RuntimeException('Not Found', 404);
        }

        $this->requestRoute = array_shift($routes);
        if ($this->method != $this->requestRoute[1]) {
            //throw new RuntimeException('Method Not Allowed', 405);
        }

        $GET_BACKUP = $_SESSION[$this->requestRoute[0]]['GET_BACKUP'] ?? [];
        foreach ($GET_BACKUP as $key => $value) {
            if (!in_array($key, array_keys($_GET))) {
                $_GET[$key] = $value;
            }
        }

        $this->actionMethodName = $this->requestRoute[2];

        if (method_exists($this, $this->actionMethodName)) {
            $methodResult = $this->{$this->actionMethodName}();
            $_SESSION[$this->requestRoute[0]] = [];
            $_SESSION[$this->requestRoute[0]]['GET_BACKUP'] = $_GET;
            return $methodResult;
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }

    public
    function addRoute(Route $route)
    {
        $exists = false;
        for ($i = 0; $i < count($this->routes) && !$exists; $i++) {
            $exists = $route->getUrl() == $this->routes[$i][0];
        }
        if ($exists) {
            throw new RuntimeException('Route already exists');
        }
        $this->routes[] = [
            $route->getUrl(),
            $route->getMethod(),
            $route->getClassMethodName()
        ];
    }

    protected
    function response($data, $status = 500)
    {
        header("Content-Type: application/json");
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }

    private
    function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    protected
    function redirect($route): void
    {
        header("Location: $route");
    }

    protected
    function page(string $filename): string
    {
        if ($this->method != Method::GET) {
            throw new RuntimeException($this->requestStatus(405), 405);
        }
        header("Content-Type: text/html");
        if (file_exists($filename)) {
            ob_start();
            include($filename);
            $content = ob_get_contents();
            ob_clean();
            return $content;
        } else {
            return $this->notFound();
        }
    }

    protected
    function getValueFromRoute($valueName)
    {
        $elementParts = explode('/', $this->requestRoute[0]);
        $isEquals = true;
        for ($i = 0; $i < count($elementParts) && $isEquals; $i++) {
            $partsEqual = $elementParts[$i] == $this->requestUrlParts[$i] ||
                $elementParts[$i][0] == ':';
            if ($partsEqual && substr($elementParts[$i], 1) == $valueName) {
                return $this->requestUrlParts[$i];
            }
            $isEquals = $partsEqual;
        }
        return null;
    }

    protected
    function internalServerError(string $message = ''): RuntimeException
    {
        header("Content-Type: text/html");
        return new RuntimeException('Internal Server Error\n' . $message, 500);
    }

    protected
    function notFound(string $message = ''): RuntimeException
    {
        header("Content-Type: text/html");
        return new RuntimeException('Not Found\n' . $message, 404);
    }

    protected
    function badRequest(string $message = ''): RuntimeException
    {
        header("Content-Type: text/html");
        return new RuntimeException('Bad Request\n' . $message, 400);
    }

    protected
    function ok(object|array $body = null)
    {
        return $this->response($body ?? 'Ok', 200);
    }

    private
    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return $length > 0 ? substr($haystack, -$length) === $needle : true;
    }
}
