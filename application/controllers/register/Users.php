<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
 
class Users extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();
        // Load User Model
        $this->load->model('user_model', 'UserModel');
    }
    /**
     * User Register
     * @link : user/register
     */
    public function register_post()
    {
        # Form Validation
        $this->form_validation->set_rules('fullname', 'Full Name', 'required|max_length[50]');
        $this->form_validation->set_rules('username', 'Username', 'required|alpha_numeric|max_length[20]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[80]');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[100]');
        if ($this->form_validation->run() == FALSE)
        {
            // Form Validation Errors
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            $insert_data = [
                'fullname' => $this->input->post('fullname'),
                'email' => $this->input->post('email'),
                'username' => $this->input->post('username'),
                'password' => md5($this->input->post('password')),
            ];
            // Insert User in Database
            $output = $this->UserModel->insert_user($insert_data);
            if ($output > 0 AND !empty($output))
            {
                // Success 200 Code Send
                $message = [
                    'status' => true,
                    'message' => "User registration successful"
                ];
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Error
                $message = [
                    'status' => FALSE,
                    'message' => "Not Register Your Account."
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
    /**
     * User Login API
     * --------------------
     * @link: user/login
     */
    public function login_post()
    {
        # Form Validation
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[100]');
        if ($this->form_validation->run() == FALSE)
        {
            // Form Validation Errors
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            // Load Login Function
            $username=$this->input->post('username');
            $password= $this->input->post('password');
            $output = $this->UserModel->user_login($username,$password);
            if ($output != FALSE)
            {
                $date = new DateTime();
                $token['user_id']=$output->user_id;
                $token['username']=$username;
                $token['password']=$password;
                $token['full_name']=$output->fullname;
                $token['email']=$output->email;
                $token['iat']=$date->getTimestamp();
                $token['exp']=$date->getTimestamp()+60*60*24;
                $res= JWT::encode($token, "my Secret key!");
                
                $return_data = [
                    'user_id' => $output->user_id,
                    'full_name' => $output->fullname,
                    'email' => $output->email,
                    'token'=> $res,
                ];

                // Login Success
                $message = [
                    'status' => true,
                    'data' => $return_data,
                    'message' => "User login successful"
                ];
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                // Login Error
                $message = [
                    'status' => FALSE,
                    'message' => "Invalid Username or Password"
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    /**
	 * logout function.
	 */
    public function logout() 
    {
		
		
        if (isset($_SESSION['logged_in']) === true)
        {
			
			// remove session datas
            foreach ($_SESSION as $key => $value) 
            {
				unset($_SESSION[$key]);
			}
			
			// Logout Success
            $message = [
                'status' => true,
                'message' => "User Successfully Logout"
            ];
            $this->response($message, REST_Controller::HTTP_OK);
			
        } else {
            
            // Logout Error
            $message = [
                'status' => FALSE,
                'message' => "Log out Error"
            ];
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
			
		}
		
	}
}