<?php 
	namespace App\Models;

	use CodeIgniter\Model;

	class EM_model extends Model {

    protected $builder;
    protected $allowedFields = [];
		
    public function __construct() {
      parent::__construct();      
    }
	}