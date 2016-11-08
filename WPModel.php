<?php

/**
 * WPModel
 *
 * A simple drop-in abstract class for creating active
 * record style eloquent-esque models of Wordpress Posts.
 *
 * @todo Relationships, delete method, document funtions, Support data types: Array, Integer
 * @author     AnthonyBudd <anthonybudd94@gmail.com>
 */
Abstract Class WPModel
{
	protected $attributes = [];
	protected $booted = FALSE;
	public $dirty = FALSE;
	public $ID = FALSE;
	public $title;
	public $content;
	public $_post;

	public function __construct(Array $insert = [])
	{    
		$this->check();

		if(count($insert) !== 0){
			foreach($insert as $attribute => $value){
				if(in_array($attribute, $this->attributes)){
					$this->set($attribute, $value);
				}
			}
		}
	}

	protected function check()
	{
		$unallowedAttributes = ['_id', 'ID', 'title', 'content', '_post'];

		foreach($this->attributes as $attribute){	
			if(in_array($attribute, $unallowedAttributes)){
				throw new Exception("The attribute name: {$attribute}, is reserved for the WPModel");
			}
		}
	}

	protected function boot()
	{
		$this->triggerEvent('booting');
		$this->_post = get_post($this->ID);
		$this->title = $this->_post->post_title;
		$this->content = $this->_post->post_content;

		foreach($this->attributes as $attribute){
			$this->$attribute = get_post_meta($this->ID, $attribute, TRUE);
		}

		$this->booted = true;
		$this->triggerEvent('booted');
	}

	protected function triggerEvent($event)
	{
		if(method_exists($this, $event)){
			$this->$event($this);
		}
	}

	protected function getName(){
		$class = get_called_class();
		return ( new ReflectionClass($class) )->getProperty('name')->getValue( (new $class) );
	}

	public static function register($args = [])
	{
		$postTypeName = $this->getName();

		$defualts = [
			'public' => true,
			'label' => ucfirst($postTypeName)
		];

		register_post_type($postTypeName, array_merge($defualts, $args));
	}

	public static function exists($id, $type = TRUE)
	{	
		if($type){
			if(
				(get_post_status($id) !== FALSE) &&
				(get_post_type($id) == $this->getName())){
				return TRUE;
			}
		}else{
			return (get_post_status($id) !== FALSE);
		}

		return FALSE;
	}

	public function get($attribute)
	{
		return $this->$attribute;
	}

	public function set($attribute, $value)
	{
		$this->$attribute = $value;
	}


	//-----------------------------------------------------
	// RELATIONSHIPS 
	//-----------------------------------------------------
	public static function hasMany($model, $forignKey, $localKey)
	{
		if(in_array($localKey, ['id', 'ID', 'post_id'])){
			$localKey = '_id';
		}
		return $model::where($forignKey, $this->get($localKey));
	}


	//-----------------------------------------------------
	// MAGIC METHODS
	// -----------------------------------------------------
	public function __set($name, $value)
	{
		if($this->booted){
			$this->dirty = true;
		}

		$this->$name = $value;
	}

	public function __get($name)
	{
		if(property_exists($this, $name)){
			// Security issue, Permissons not respected
			return $this->$name;
		}else if(method_exists($this, $name)){
			$clone = Self::findBypassBoot($this->ID);
			$relationship = $clone->$name();
			
			if(is_array($relationship)){
				return $relationship;
			}
		}
	}


	//-----------------------------------------------------
	// FINDERS
	// -----------------------------------------------------
	public static function find($ID)
	{
		$className = get_called_class();
		$class = new $className();
		$class->ID = $ID;

		if(Self::exists($ID)){
			$class->boot();
		}

		return $class;
	}

	public static function findBypassBoot($ID)
	{
		$className = get_called_class();
		$class = new $className();
		$class->ID = $ID;
		return $class;
	}

	/**
	 * [findOrFail description]
	 * @throws  \Exception
	 * @param  Integer $id post ID
	 * @return Self
	 */
	public static function findOrFail($ID)
	{
		if(!Self::exists($ID)){
			throw new Exception("Post not found");
		}

		$class = get_called_class();
		$class->ID = $ID;
		$class->boot();
		return new $class($ID);
	}

	
	// -----------------------------------------------------
	// Where
	// -----------------------------------------------------
	public static function where($key, $value = FALSE)
	{
		if(is_array($key)){
			$params = [
				'meta_query' => []
			];


			foreach($key as $meta) {
				$params['meta_query'][] = [
					'key'       => $meta['key'],
					'value'     => $meta['value'],
					'compare'   => isset($meta['compare'])? $meta['compare'] : '=',
					'type'      => isset($meta['type'])? $meta['type'] : 'CHAR'
				];
			}

			$query = new WP_Query($params);
		}else{
			$query = new WP_Query([
				'meta_query'        => [
					[
						'key'       => $key,
						'value'     => $value,
						'compare'   => '=',
					],
				]
			]);
		}

		$arr = [];
		foreach($query->get_posts() as $key => $post){
			$arr[] = Self::find($post->ID); 
		}

		return $arr;
	}

	public static function in($ids = [])
	{
		$results = [];
		if(!is_array($ids)){
			$ids = func_get_args();
		}

		foreach($ids as $key => $id){
			if(Self::exists($id)){
				$results[] = Self::find($id); 
			}
		}

		return $results;
	}


	//-----------------------------------------------------
	// SAVE
	// -----------------------------------------------------
	public function save($args = [])
	{
		$this->triggerEvent('saving');

		$overwrite = [
			'post_type' => $this->name
		];

		if(is_integer($this->ID)){
			$defualts = [
				'ID'           => $this->ID,
				'post_title'   => $this->title,
				'post_content' => $this->content,
			];

			wp_update_post(array_merge($defualts, $args, $overwrite));
		}else{
			$this->triggerEvent('inserting');
			$defualts = [
				'post_status'  => 'publish',
				'post_title'   => $this->title,
				'post_content' => $this->content,
			];

			$this->ID = wp_insert_post(array_merge($defualts, $args, $overwrite));
			$this->triggerEvent('inserted');
		}

		foreach($this->attributes as $attribute){
			update_post_meta($this->ID, $attribute, $this->$attribute);
		}
		
		update_post_meta($this->ID, '_id', $this->ID);
		$this->triggerEvent('saved');
		$this->dirty = FALSE;
	}


	// -----------------------------------------------------
	// Delete
	// -----------------------------------------------------
	public function delete(){
		$this->triggerEvent('deleting');
		wp_delete_post($this->ID);
		$this->triggerEvent('deleted');
	}

	public function hardDelete(){
		$this->triggerEvent('hardDeleting');

		foreach($this->attributes as $attribute){
			delete_post_meta($this->ID, $attribute);
			$this->$attribute = NULL;
		}

		wp_delete_post($this->ID);
		$this->triggerEvent('hardDeleted');
	}
}

?>