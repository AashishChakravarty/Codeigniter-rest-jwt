<?php defined('BASEPATH') OR exit('No direct script access allowed');
class User_Model extends CI_Model
{
    protected $user_table = 'users_table';
    
    public function check_email_exists($email) {
        $this->db->select('id')->from($this->user_table);
        $this->db->where('email',$email);

        $result = $this->db->get()->row();

        if ($result) {
            return $result;
        } else {
            return 0;
        }
        
    }

    public function insert_user($data) {
        $this->db->insert($this->user_table, $data);
        $result = $this->db->insert_id();

        if ($result) {
            return $result;
        } else {
            return 0;
        }
        
    }
    
    public function user_login($email, $password)
    {
        $this->db->where('email', $email);
        $this->db->where('password',md5($password));
        $result = $this->db->get($this->user_table)->row();
        if($result){
            return $result;
     	}else{
     		return 0;
     	}
    }
}