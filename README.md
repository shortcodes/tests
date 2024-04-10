# Test tools for laravel application 2 3 4
This is package of test tools to help test laravel app properly

# Tools

#### Api CRUD testing

To test CRUD in your API you need to create test that extends `Shortcodes\Tests\Blueprints\ApiCrudTest` and provide model class in protected property

    class ModelCrudTest extends ApiCrudTest
    {
        protected $model = Model::class;
    }

By default test will perform assertions to test

- i_can_make_index_request_and_get_200_status
- i_can_make_show_request_and_get_200_status
- i_can_make_store_request_and_get_201_status
- i_can_make_update_request_and_get_200_status
- i_can_make_delete_request_and_get_204_status

It is crucial to remember that provided `model` must have factory with valid generated data.

If `index` method requires some query string parameters you can define it in class method

    class ModelCrudTest extends ApiCrudTest
    {
        protected $model = Model::class;
        
        public function getQueryStringParams(){
            return [
                'length' => 10,
                'page' => 1
            ];
        }
    }
    
#### Form Request testing

To test Form Request in your API you need to create test that extends `Shortcodes\Tests\Blueprints\FormRequestTest` and provide request class in protected property

    class IndexModelRequestTest extends FormRequestTest
    {
        protected $model = IndexModelRequest::class;
    }

`FormRequestTest` class allow to use method to validate request like in the example

    class IndexModelRequestTest extends FormRequestTest
    {
        protected $model = IndexModelRequest::class;
        
        public function testValidRequest(){
            $this->prepareRequest(['some_data'=>'value'])->assertValidRequest()
        }
        
        public function testInvalidRequest(){
            $this->prepareRequest(['some_data'=>'value'])->assertInvalidRequest()
        }
        
        public function testInvalidParameterInRequest(){
            $this->prepareRequest(['some_data'=>'value','invalid_parameter'=>'value'])
                ->assertInvalidRequest()
                ->assertInvalidParameter(['invalid_parameter'])
                ->assertInvalidParameter('invalid_parameter')   //or without array
                ->assertInvalidParameter('missing_request_required_parameter')
        }
        
        public function testValidParameterInInvalidRequestRequest(){
            $this->prepareRequest(['some_data'=>'value','invalid_parameter'=>'value'])
                ->assertInvalidRequest()
                ->assertInvalidParameter(['invalid_parameter'])
                ->assertValidParameter(['some_data'])
        }
    }

There is also possibility to test request whit required injected model like with query parameters.

    class IndexModelRequestTest extends FormRequestTest
    {
        protected $model = UpdateModelRequest::class;
        
        public function testTitleDuplicationRequest(){
            $object = Model::first();
            $this->prepareRequest(['title'=>$object->title'], $object)->assertValidRequest() 
        }
    }
    
#### Api request testing

To test Api Request in you need to create test that extends `Shortcodes\Tests\Blueprints\TestCase`. This is a simple wrapper on standard method that allows to write less code.

    $response = $this->apiRequest('POST', url('named.route.name'), $data, $headers);
    
Method create request that act as user defined in class `user` property or create one from factory. And attach header for authorization
`X-App-Token` which is taken from `env('AUTH_KEY')`,
