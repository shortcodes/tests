<?php

namespace Shortcodes\Tests\Blueprints;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

abstract class FormRequestTest extends TestCase
{
    protected $model;
    private $request;
    private $errors;
    protected $asUser = null;
    protected $headers = [];

    public function setHeaders(array $headers)
    {
        $this->headers = $this->transformHeadersToServerVars($headers);

        return $this;
    }

    public function getHeaders()
    {
        if ($this->headers) {
            return $this->headers;
        }

        if (method_exists($this, 'usingHeaders')) {
            return $this->transformHeadersToServerVars($this->usingHeaders());
        }

        return [];
    }

    public function as($user)
    {
        $this->asUser = $user;

        return $this;
    }

    public function getUser()
    {
        if ($this->asUser) {
            return $this->asUser;
        }

        if (method_exists($this, 'asUser')) {
            return $this->asUser();
        }

        return null;
    }

    public function assertValidRequest()
    {
        try {
            $this->request->validateResolved();

            $this->assertTrue(true);
        } catch (ValidationException $exception) {
            $this->assertTrue(false);
        }

        return $this;
    }

    public function assertInvalidRequest()
    {
        try {

            $this->request->validateResolved();

            $this->assertFalse(true);
        } catch (ValidationException $exception) {

            $this->errors = $exception->errors();

            $this->assertTrue(true);
        } catch (AuthorizationException $exception){
            $this->errors = $exception->getMessage();

            $this->assertTrue(true);
        }

        return $this;
    }

    public function assertInvalidParameter($invalidParameters)
    {
        foreach (is_array($invalidParameters) ? $invalidParameters : [$invalidParameters] as $k => $rule) {

            if (is_array($rule)) {
                $rule = $k;
            }

            if (!isset($this->errors[$rule])) {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(true);


        return $this;
    }

    public function assertValidParameter($invalidParameters)
    {
        foreach (is_array($invalidParameters) ? $invalidParameters : [$invalidParameters] as $k => $rule) {

            if (is_array($rule)) {
                $rule = $k;
            }

            if (isset($this->errors[$rule])) {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(true);


        return $this;
    }

    public function prepareRequest($payload, $model = null)
    {

        $this->request = new $this->model([], [], [], [], [], ($model ? array_merge(['REQUEST_URI' => $this->getModelPath() . '/' . $model->id], $this->getHeaders())  : $this->getHeaders()));
        $this->request->setContainer(app());
        $this->request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $this->request->setUserResolver(function () {
            return $this->getUser();
        });

        $this->request->merge($payload);

        if ($model) {
            $this->resolveRoute();
        }

        return $this;
    }

    protected function transformHeadersToServerVars(array $headers)
    {
        return collect(array_merge($this->defaultHeaders, $headers))->mapWithKeys(function ($value, $name) {
            $name = strtr(strtoupper($name), '-', '_');

            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }

    protected function formatServerHeaderKey($name)
    {
        if (!Str::startsWith($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_' . $name;
        }

        return $name;
    }

    private function getModelPath()
    {
        return Str::kebab(Str::plural($this->getModelClassName()));
    }

    private function getModelClassName()
    {
        $explodedName = explode('_', Str::snake(class_basename($this->model)));
        unset($explodedName[0], $explodedName[count($explodedName)]);

        return implode('_', $explodedName);
    }

    private function resolveRoute()
    {
        $this->request->setRouteResolver(function () {
            return (new Route('PATCH', $this->getModelPath() . '/{' . $this->getModelClassName() . '}', []))->bind($this->request);
        });
    }


}
