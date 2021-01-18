<?php

namespace Shortcodes\Tests\Blueprints;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Shortcodes\Media\Models\MediaLibrary;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function apiRequest($method, $uri, $data = [], $headers = [], $driver = null)
    {
        if (!$this->user) {
            $this->user = factory(User::class)->create();
        }

        $query = $this
            ->actingAs($this->user, $driver)
            ->withHeaders([
                'X-App-Token' => env('AUTH_KEY'),
            ])
            ->json($method, $uri, $data, $headers);

        return $query;
    }

    protected function createFakeMedia($quantity = 1)
    {
        $mediaCollection = collect();

        foreach (range(1, $quantity) as $item) {
            $mediaLibrary = new MediaLibrary();
            $media = $mediaLibrary->addMedia(UploadedFile::fake()->image('image_' . $item . '.jpg')->size(5))->toMediaCollection();
            $mediaCollection->push($media);
        }

        return $mediaCollection->count() === 1 ? $mediaCollection->first() : $mediaCollection;

    }
}
