<?php

namespace T4\Mvc;

use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Http\Request;

class Router
{
    use TSingleton;

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
     * @param \T4\Http\Request $request
     * @return \T4\Mvc\Route
     */
    public function parseRequest(Request $request)
    {
        $fullPath = $request->getFullPath();
        return $this->parseRequestPath($fullPath);
    }

    /**
     * TODO: fix tests!
     * @param string $requestPath
     * @throws RouterException
     * @return \T4\Mvc\Route
     */
    protected function parseRequestPath($requestPath)
    {
        $request = $this->splitRequestPath($requestPath);
        if (!empty($this->config)) {
            foreach ($this->config as $template => $internalPath) {
                if (false !== $params = $this->matchPathTemplate($template, $request)) {

                    if ($internalPath instanceof \Closure) {
                        $route = $internalPath($request, $params);
                        if (false === $route) {
                            continue;
                        }
                        if (!($route instanceof Route)) {
                            $route = new Route($route);
                        }
                        if (empty($route->format)) {
                            $route->format = $request->extension ?: $this->allowedExtensions[0];
                        }
                        return $route;
                    }

                    $internalPath = preg_replace_callback(
                        '~\<(\d+)\>~',
                        function ($m) use ($params) {
                            return $params[$m[1]];
                        },
                        $internalPath
                    );
                    $route = new Route($internalPath);
                    $route->format = $request->extension ?: $this->allowedExtensions[0];
                    return $route;
                }
            }
        }
        return $this->guessInternalPath($request);
    }

    public function getFormatByExtension($extension)
    {
        return in_array($extension, $this->allowedExtensions) ? $extension : $this->allowedExtensions[0];
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
                'format' => $url->extension ?: 'html',
            ]);
        }

        if (1 == count($urlParts)) {
            if ($app->existsModule($urlParts[0]))
                return new Route([
                    'module' => ucfirst($urlParts[0]),
                    'controller' => self::DEFAULT_CONTROLLER,
                    'action' => self::DEFAULT_ACTION,
                    'params' => [],
                    'format' => $url->extension ?: 'html',
                ]);
            elseif ($app->existsController('', $urlParts[0]))
                return new Route([
                    'module' => '',
                    'controller' => ucfirst($urlParts[0]),
                    'action' => self::DEFAULT_ACTION,
                    'params' => [],
                    'format' => $url->extension ?: 'html',
                ]);
            else
                return new Route([
                    'module' => '',
                    'controller' => self::DEFAULT_CONTROLLER,
                    'action' => ucfirst($urlParts[0]),
                    'params' => [],
                    'format' => $url->extension ?: 'html',
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
                        'format' => $url->extension ?: 'html',
                    ]);
                } else {
                    return new Route([
                        'module' => ucfirst($urlParts[0]),
                        'controller' => self::DEFAULT_CONTROLLER,
                        'action' => ucfirst($urlParts[1]),
                        'params' => [],
                        'format' => $url->extension ?: 'html',
                    ]);
                }
            } elseif ($app->existsController('', $urlParts[0])) {
                return new Route([
                    'module' => '',
                    'controller' => ucfirst($urlParts[0]),
                    'action' => ucfirst($urlParts[1]),
                    'params' => [],
                    'format' => $url->extension ?: 'html',
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
                    'format' => $url->extension ?: 'html',
                ]);
            }
        }

        throw new RouterException('Route to path \'' . $url->basepath . '\' is not found');
    }

}