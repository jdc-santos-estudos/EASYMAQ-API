<?php 
	namespace App\Models;

	use CodeIgniter\Model;

	class Usuario_model extends Model {
		
    public function teste() {
      
      // $dns = 'MySQLi://xjdtdafc_admin:@teste123@jdc.profrodolfo.com.br:3306/xjdtdafc_easymaq?charset=utf8&DBCollat=utf8_general_ci';

      // $db = $this->load->database('SELECT * FROM usuarios');
      $sql = "Select * from usuarios";    
      $query = $this->db->query($sql);
      print_r($query->getResult());
    }
	}