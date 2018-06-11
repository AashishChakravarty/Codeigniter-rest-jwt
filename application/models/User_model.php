<?php defined('BASEPATH') OR exit('No direct script access allowed');
class User_Model extends CI_Model
{
    protected $user_table = 'users_table';
    
    
    // Register
    public function insert_user(array $data) {
        $this->db->insert($this->user_table, $data);
        return $this->db->insert_id();
    }
    

    //Login
    public function user_login($username, $password)
    {
        $this->db->where('username', $username);
        $q = $this->db->get($this->user_table);
        if( $q->num_rows() ) 
        {
            $user_pass = $q->row('password');
            if(md5($password) === $user_pass) {
                return $q->row();
            }
            return FALSE;
        }else{
            return FALSE;
        }
    }
}