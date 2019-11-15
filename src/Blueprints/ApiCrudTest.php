<?php

namespace Shortcodes\Tests\Blueprints;

use App\User;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class ApiCrudTest extends TestCase
{
    protected $model;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     */

    public function i_can_make_index_request_and_get_200_status()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-App-Token' => env('AUTH_KEY')])
            ->json('GET', route($this->getRoutePrefix() . '.index'), method_exists($this, 'getQueryStringParams') ? $this->getQueryStringParams() : []);

        $response->assertStatus(200);
    }

    /**
     * @test
     */

    public function i_can_make_show_request_and_get_200_status()
    {
        $object = $this->objectFactory()->create();

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-App-Token' => env('AUTH_KEY')])
            ->json('GET', route($this->getRoutePrefix() . '.show', [$this->model => $object->id]));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $object->id,
                ],
            ]);
    }

    /**
     * @test
     */

    public function i_can_make_store_request_and_get_201_status()
    {
        $objectToInsert = $this->objectFactory()->make();

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-App-Token' => env('AUTH_KEY')])
            ->json('POST', route($this->getRoutePrefix() . '.store'), $objectToInsert->toArray());

        $response->assertStatus(201);
        $this->assertNotNull($response->getData()->data->id);
    }

    /**
     * @test
     */

    public function i_can_make_update_request_and_get_200_status()
    {
        $object = $this->objectFactory()->create();

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-App-Token' => env('AUTH_KEY')])
            ->json('PATCH', route($this->getRoutePrefix() . '.update', [$this->model => $object->id]), $this->objectFactory()->make()->toArray());

        $response->assertStatus(200);
    }

    /**
     * @test
     */

    public function i_can_make_delete_request_and_get_204_status()
    {
        $object = $this->objectFactory()->create();

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-App-Token' => env('AUTH_KEY')])
            ->json('DELETE', route($this->getRoutePrefix() . '.destroy', [$this->model => $object->id]));

        $response->assertStatus(204);

        $this->assertNull($this->model::find($object->id));
    }

    private function objectFactory()
    {
        return factory($this->model);
    }

    private function getRoutePrefix()
    {
        return Str::kebab(Str::plural(class_basename($this->model)));
    }

}
