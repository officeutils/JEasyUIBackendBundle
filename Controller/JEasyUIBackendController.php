<?php

namespace OfficeUtils\JEasyUIBackendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
*   Backend controller class for JEasyUI library (http://jeasyui.com)
*   Generate json replies for library controls and render list and details page.
* 
*/
class JEasyUIBackendController extends Controller
{
    /**
    * ActiveRecord\Model::find options for finder method, i.e. conditions, limit, order...
    * 
    * @var mixed
    */
    protected $options;
    
    /**
    * Instance of ActiveRecord\Model successor to retrieve data
    * 
    * @var object  
    */
    protected $model;
    
    /**
     * Render default index page for object
     * 
     * @Route("/{object}/list", name="objects_list")
     * 
     * @param string $object Name of ActiveRecord\Model successor class
     */
    public function listAction($object)
    {
        return $this->render('OMIRussiaTargetBundle:Default:'. $object .'.list.html.twig',['object' => $object, 'action' => 'list', 'id' => $this->getRequest()->get('id')]);
    }

    /**
     * Render default details page for object
     * 
     * @Route("/{object}/details", name="object_details")
     * 
     * @param string $object Name of \ActiveRecord\Model successor class
     */
    public function detailsAction($object)
    {
        //DONE: Pass id from request
        return $this->render('OMIRussiaTargetBundle:Default:'. $object .'.details.html.twig', ['object' => $object, 'action' => 'details', 'id' => $this->getRequest()->get('id')]);
    }

    /**
    * Get one record by primary key from $_REQUEST[primarykey]
    * 
    * @Route("/{object}/get")
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function apiGet($object)
    {   
        $this->getModel($object);
        
        $result = [];                                
        
        if(!empty($this->model))
        {
            $key = $this->model->primary_key[0];
            
            $id = $this->container->get('request_stack')->getCurrentRequest()->get($key);
            
            if(!empty($id) && $this->model->exists(intval($id)))
            {             
                $result = $this->model->find(intval($id));
            }
        }
        
        return new Response(
            empty($result) ? json_encode($result, JSON_FORCE_OBJECT) : $result->to_json(),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
    * Add new object to storage
    * 
    * @Route("/{object}/add")
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function apiAdd($object)
    {
        $this->getModel($object);
        
        try
        {
            $this->setNewValues();
            $this->model->save();
        }
        catch(PDOException $e)
        {
            $result =  [ "error" => "Record creation error."];    
        }   
        
        $key = $this->model->primary_key[0];
        
        $result = [ "error" => "success", "{$this->model->primary_key[0]}" => $this->model->$key ];
        
        return new Response(
            json_encode($result, JSON_FORCE_OBJECT),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
    * Delete one row from storage
    * Row is identified by primary key from $_REQUEST[primarykey] 
    * @Route("/{object}/delete")
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function apiDelete($object)
    {
        $result = [ "error" => "fail" ];

        $this->getModel($object);

        if(!empty($this->model))
        {
            $key = $this->model->primary_key[0];

            $id = $this->container->get('request_stack')->getCurrentRequest()->get($key);
            
            if(!empty($id) && $this->model->exists(intval($id)))
            {             
                $o = $this->model->find(intval($id));
                if($o->delete())
                {
                    $result = [ "error" => "success" ];
                }
            }
        }                
            
        return new Response(
            json_encode($result, JSON_FORCE_OBJECT),
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
    * Update one row in storage
    * Row is identified by primary key from $_REQUEST[primarykey] 
    * All new values from $_REQUEST[attribute_name] will be passed to corresponding model attributes.
    * Another $_REQUEST values are ignored.
    * @Route("/{object}/update")
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function apiUpdate($object)
    {
        $result = [ "error" => "fail" ];

        $this->getModel($object);

        if(!empty($this->model))
        {
            $key = $this->model->primary_key[0];
            
            $id = $this->container->get('request_stack')->getCurrentRequest()->get($key);
            
            if(!empty($id) && $this->model->exists(intval($id)))
            {
                $this->model = $this->model->find(intval($id));
             
                $this->setNewValues();
                
                if( $this->model->save())
                {
                    $result = [ "error" => "success" ];
                }
            }
        
        return new Response(
            json_encode($result, JSON_FORCE_OBJECT),
            200,
            array('Content-Type' => 'application/json')
        );
                
        }
    }

    
    /**
    * Get data for JEasyUI Datagrid according conditons, limit, order and etc.
    * 
    * @Route("/{object}/datagrid")
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function apiDatagrid($object)
    {
        $rows = $this->getRows($object);
        
        $result = '';
        
        foreach($rows as $row)
        {                
            $result = $result != '' ? ($result . ', ' . $row->to_json()) : $row->to_json();
        }
        $result =  "{\"total\" : " . $this->getTotal() . ", \"rows\": [{$result}]}";

        return new Response(
            $result,
            200,
            array('Content-Type' => 'application/json')
        );
    }

    /**
    * Get data for JEasyUI Combobox according conditons, limit, order and etc.
    * 
    * @Route("/{object}/combobox")
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function apiCombobox($object)
    {
        $rows = $this->getRows($object);
        
        $result = '';
                    
        foreach($rows as $row)
        {                
            $result = $result != '' ? ($result . ', ' . $row->to_json()) : $row->to_json();
        }   
        $result = "[{$result}]";
        
        return new Response(
            $result,
            200,
            array('Content-Type' => 'application/json')
        );    }
    
    /**
    * Create object and set $this->model property according to name of Model
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    */
    protected function getModel($object)
    {
        $model_name = $this->container->get('ar_service')->cfg[ 'model_namespace' ] . $object;
        $this->model = class_exists($model_name) ?  new $model_name() : null;
    }
    
    /**
    * Service function - get total for DataGrid with $this->options
    * 
    * @return integer 
    */
    protected function getTotal()
    {
        if(!empty($this->model))
        {
                $this->options = [];
                $this->getConditions();
                $this->options['select'] = 'count(*) total';

                $rows = $this->model->all($this->options); 
        
                return $rows[0]->total;
        }
        else
        {
            return 0;
        }
    }

    /**
    * Service function - fill $this->options['join']
    * 
    */
    protected function getJoins()
    {            
    }

    /**
    * Service function - fill $this->options['select']
    * 
    */
    protected function getSelect()
    {            
    }

    /**
    * Service function - fill $this->options['order']
    * 
    */
    protected function getOrder()
    {
        $this->options['order'] = $this->model->primary_key[0];
    }

    /**
    * Service function - fill $this->options['offset']
    * 
    */    
    protected function getOffset()
    { 
        $this->options['offset'] = empty($_REQUEST['page']) ? 0 : (intval($_REQUEST['page']) - 1) * (empty($_REQUEST['rows']) ? 10 : intval($_REQUEST['rows']));           
    }

    /**
    * Service function - fill $this->options['limit']
    * 
    */    
    protected function getLimit()
    {            
        $this->options['limit'] = empty($_REQUEST['rows']) ? 10 : intval($_REQUEST['rows']);            
    }

    /**
    * Service function - fill $this->options['conditions']
    * 
    */    
    protected function getConditions()
    {   
    }

    /**
    * Service function - fill $this->options['groups']
    * 
    */    
    protected function getGroups()
    {   
    }

    /**
    * Service function - run sql query and return result
    * 
    * @param string $object Name of \ActiveRecord\Model successor class
    * 
    * @return array of rows
    */    
    public function getRows($object)
    {
        $this->getModel($object);
        
        if(!empty($this->model))
        {
            $this->getJoins();
            $this->getSelect();
            $this->getOrder();   
            $this->getLimit();
            $this->getOffset();
            $this->getConditions();
            $this->getGroups();
                                          
            $rows = $this->model->all($this->options);
        }
        else
        {
            $rows = [];    
        }
        
        return $rows;
    }
            
    /**
    * Set new values for model
    * Attributes are retrieved from $_REQUEST['attributename']
    * 
    */
    protected function setNewValues()
    {
        $keys = array_keys( $this->model->attributes() );
        foreach( $keys as $key )
        {
            $newValue = $this->container->get('request_stack')->getCurrentRequest()->get($key);
            if( isset($newValue) && ($key != $this->model->primary_key[0]))
            {
                $this->model->$key = $newValue;
            }
        }            
    }
    
}
