<?php

namespace OfficeUtils\JEasyUIBackendBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
* Test for base functionality of JEasyUIBackendController
*/
class JEasyUIBackendControllerTest extends WebTestCase
{
    //private $last_id;
    
    public function setUp()
    {
    }
    
    
    public function testAddResponse()
    {
        $client = static::createClient();

        $client->request('GET', '/fixture/add?name=test fixture');
        
        $data = json_decode($client->getResponse()->getContent());

        $this->assertTrue(!empty($data->fixture_id));        
        
        $this->last_id = $data->fixture_id;
        
        return $data->fixture_id;
    }
    
    /**
     * @depends testAddResponse
     */
    public function testGetResponse($id)
    {
        $client = static::createClient();

        $client->request('GET', '/fixture/get?fixture_id=' . $id);
        
        $json_data = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString(json_encode(['fixture_id' => $id, 'name' => 'test fixture']), $json_data);        
        
        return $id;
    }

    /**
     * @depends testAddResponse
     */
    public function testUpdateResponse($id)
    {
        $client = static::createClient();

        $client->request('GET', '/fixture/update?fixture_id=' . $id . '&name=new test fixture' );        
        $json_data = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'success']), $json_data, 'Test update fail');        
        
        $client->request('GET', '/fixture/get?fixture_id=' . $id);
        $json_data = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode(['fixture_id' => $id, 'name' => 'new test fixture']), $json_data, 'Test data after update mismatch.');  
        
        return $id;      
    }

    /**
     * @depends testUpdateResponse
     */
    public function testDataGridResponse($id)
    {
        $client = static::createClient();

        $client->request('GET', '/fake/datagrid');
        
        $json_data = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString(json_encode(['total' => 0, 'rows' => []]), $json_data, 'Test empty datagrid response fail');        

        $client->request('GET', '/fixture/datagrid');
        
        $json_data = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString('{"total" : 1, "rows" : [ { "fixture_id" :' . $id . ', "name": "new test fixture" } ] }', $json_data, 'Test Fixture datagrid response fail');        
    }
    
    /**
     * @depends testUpdateResponse
     */
    public function testComboboxResponse($id)
    {
        $client = static::createClient();

        $client->request('GET', '/dumb_object/combobox');        
        $json_data = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode([]), $json_data);        

        $client->request('GET', '/fixture/combobox');        
        $json_data = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode([[ 'fixture_id' => $id, 'name' => 'new test fixture' ]]), $json_data, 'Test Fixture datagrid response fail');        
    }

    /**
     * @depends testAddResponse
     */
    public function testDeleteResponse($id)
    {
        $client = static::createClient();

        $client->request('GET', '/fixture/delete?fixture_id=' . $id );        
        $json_data = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'success']), $json_data, 'Test delete fail');        
        
        $client->request('GET', '/fixture/get?fixture_id=' . $id);
        $json_data = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode([], JSON_FORCE_OBJECT), $json_data, 'Test data exists after delete mismatch.');        
    }
    
}
