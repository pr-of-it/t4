<?php

namespace T4\Mvc;

use T4\Core\Std;
use T4\Core\TSingleton;

class Router
{
    use TSingleton;

    const INTERNAL_PATH_PATTERN = '~^\/([^\/]*?)\/([^\/]*?)\/([^\/]*?)\/?(\((.*)\))?$~i';

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'Default';

    /**
     * @var \T4\Core\Config
     */
    protected $config = [];

    /**
     * Allowed URL extensions
     * @var array
     */
    protected $allowedExtensions = ['html', 'json'];

    /**
     * @param \T4\Core\Std $config
     * @return \T4\Mvc\Router $this
     */
    public function setConfig(Std $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $requestPath
     * @throws RouterException
     * @return \T4\Mvc\Route
     */
    public function parseRequestPath($requestPath)
    {
        $request = $this->splitRequestPath($requestPath);
        if (!empty($this->config)) {
            foreach ($this->config as $template => $internalPath) {
                if (false !== $params = $this->matchPathTemplate($template, $request)) {
                    $internalPath = preg_replace_callback(
                        '~\<(\d+)\>~',
                        function ($m) use ($params) {
                            return $params[$m[1]];
                        },
                        $internalPath
                    );
                    $route = $this->splitInternalPath($internalPath);
                    $route->format = $request->extension ?: $this->allowedExtensions[0];
                    return $route;
                }
            }
        }
        return $this->guessInternalPath($request);
    }

    /**
     * Splits canonical request path into domain, path and extension
     * @param string $path
     * @return \T4\Mvc\Route
     */
    protected function splitRequestPath($path)
    {
        // Domain part extract
        $parts = explode('!', $path);
        if (count($parts) > 1) {
            $domain = $parts[0];
            $path = $parts[1];
        } else {
            $domain = null;
        }

        $parts = parse_url($path);
        $basePath = isset($parts['path']) ? $parts['path'] : null;

        if (empty($basePath)) {
            $extension = null;
        } else {
            $extension = pathinfo($basePath, PATHINFO_EXTENSION);
            $basePath = preg_replace('~\.' . $extension . '$~', '', $basePath);
        }

        if (!in_array($extension, $this->allowedExtensions)) {
            $extension = '';
        }

        return new Route([
            'domain' => $domain,
            'basepath' => $basePath,
            'extension' => $extension,
        ]);
    }

    /**
     * Check if canonical path (splitted into Route object) is mathing to template from route config
     * Returns false if no matches
     * or array of request params elsewhere
     * @param string $template
     * @param \T4\Mvc\Route $path
     * @return array|bool
     */
    protected function matchPathTemplate($template, Route $path)
    {
        $templateParts = explode('!', $template);

        if (count($templateParts) > 1) {
            $domainTemplate = $templateParts[0];
            $basepathTemplate = $templateParts[1];
        } else {
            $domainTemplate = null;
            $basepathTemplate = $templateParts[0];
        }

        if (!empty($domainTemplate)) {
            $domainMatches = $this->getTemplateMatches($domainTemplate, $path->domain);
            if (false === $domainMatches) {
                return false;
            }
        } else {
            $domainMatches = [];
        }
        $basepathMatches = $this->getTemplateMatches($basepathTemplate, $path->basepath);
        if (false === $basepathMatches) {
            return false;
        }

        return $domainMatches + $basepathMatches;
    }

    /**
     * Checks if $path is mathes to $template
     * Returns array of matched params (like <1>)
     * or false if no matches found
     * @param string $template Route template
     * @param string $path part of request path string
     * @return array|boolean array
     */
    protected function getTemplateMatches($template, $path)
    {
        $template = '~^' . preg_replace('~\<(\d+)\>~', '(?<p_$1>.+?)', $template) . '$~i';
        if (!preg_match($template, $path, $m)) {
            return false;
        } else {
            $matches = [];
            foreach ($m as $key => $value) {
                if (substr($key, 0, 2) == 'p_') {
                    $matches[substr($key, 2)] = $value;
                }
            }
            return $matches;
        }
    }

    /**
     * Splits internal framework path like /module/controller/action(params)
     * @param string $path
     * @return \T4\Mvc\Route
     * @throws \T4\Mvc\RouterException
     */
    public function splitInternalPath($path)
    {
        if (!preg_match(self::INTERNAL_PATH_PATTERN, $path, $m)) {
            throw new RouterException('Invalid route \'' . $path . '\'');
        };

        $params = isset($m[5]) ? $m[5] : '';
        if (!empty($params)) {
            $params = explode(',', $params);
            $p = [];
            foreach ($params as $pair) {
                list($name, $value) = explode('=', $pair);
                $p[$name] = $value;
            }
            $params = $p;
        } else $params = [];

        return new Route([
            'module' => ucfirst($m[1]),
            'controller' => ucfirst($m[2]) ? : self::DEFAULT_CONTROLLER,
            'action' => ucfirst($m[3]) ? : self::DEFAULT_ACTION,
            'params' => $params
        ]);

    }

    /**
     * Возвращает канонический внутренний путь, построенный из объекта роутинга
     * Не учитывает параметры
     * @param Route $route
     * @return string
     */
    public function makeInternalPath(Route $route)
    {
        return '/' . $route->module . '/' .
        ($route->controller == self::DEFAULT_CONTROLLER ? '' : $route->controller) . '/' .
        ($route->action == self::DEFAULT_ACTION ? '' : $route->action);
    }

    /**
     * Пытается подобрать соответствующий роутинг для URL, отсутствующего в конфиге роутинга
     * @param \T4\Mvc\Route $url
     * @return Route
     * @throws RouterException
     */
    protected function guessInternalPath($url)
    {
        $urlParts = preg_split('~/~', $url->basepath, -1, PREG_SPLIT_NO_EMPTY);
        $app = \T4\Mvc\Application::getInstance();

        if (0 == count($urlParts)) {
            return new Route([
                'module' => '',
                'controller' => self::DEFAULT_CONTROLLER,
                'action' => self::DEFAULT_ACTION,
                'params' => [],
                'format' => $url->extension ? : 'html',
            ]);
        }

        if (1 == count($urlParts)) {
            if ($app->existsModule($urlParts[0]))
                return new Route([
                    'module' => ucfirst($urlParts[0]),
                    'controller' => self::DEFAULT_CONTROLLER,
                    'action' => self::DEFAULT_ACTION,
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            elseif ($app->existsController('', $urlParts[0]))
                return new Route([
                    'module' => '',
                    'controller' => ucfirst($urlParts[0]),
                    'action' => self::DEFAULT_ACTION,
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            else
                return new Route([
                    'module' => '',
                    'controller' => self::DEFAULT_CONTROLLER,
                    'action' => ucfirst($urlParts[0]),
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
        }

        if (2 == count($urlParts)) {
            if ($app->existsModule($urlParts[0])) {
                if ($app->existsController($urlParts[0], $urlParts[1])) {
                    return new Route([
                        'module' => ucfirst($urlParts[0]),
                        'controller' => ucfirst($urlParts[1]),
                        'action' => self::DEFAULT_ACTION,
                        'params' => [],
                        'format' => $url->extension ? : 'html',
                    ]);
                } else {
                    return new Route([
                        'module' => ucfirst($urlParts[0]),
                        'controller' => self::DEFAULT_CONTROLLER,
                        'action' => ucfirst($urlParts[1]),
                        'params' => [],
                        'format' => $url->extension ? : 'html',
                    ]);
                }
            } elseif ($app->existsController('', $urlParts[0])) {
                return new Route([
                    'module' => '',
                    'controller' => ucfirst($urlParts[0]),
                    'action' => ucfirst($urlParts[1]),
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            }
        }

        if (3 == count($urlParts)) {
            if ($app->existsModule($urlParts[0]) && $app->existsController($urlParts[0], $urlParts[1])) {
                return new Route([
                    'module' => ucfirst($urlParts[0]),
                    'controller' => ucfirst($urlParts[1]),
                    'action' => ucfirst($urlParts[2]),
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            }
        }

        throw new RouterException('Route to path \'' . $url->basepath . '\' is not found');
    }

}