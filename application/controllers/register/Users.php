<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
 
class Users extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('user_model', 'UserModel');
    }
    /**
     * User Register
     * @link : user/register
     */
    public function register_post()
    {
        $this->form_validation->set_rules('fullname', 'Full Name', 'required|max_length[50]');
        $this->form_validation->set_rules('username', 'Username', 'required|alpha_numeric|max_length[20]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[80]');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[100]');

        if ($this->form_validation->run() == FALSE)
        {
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => "Please Fill all fields"
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            $email_exist = $this->UserModel->check_email_exists($this->post('email'));
            if (!$email_exist) {
                $insert_data = [
                    'fullname' => $this->post('fullname'),
                    'email' => $this->post('email'),
                    'username' => $this->post('username'),
                    'password' => md5($this->post('password')),
                ];

                $output = $this->UserModel->insert_user($insert_data);
                if ($output)
                {
                    $message = [
                        'status' => true,
                        'message' => "User registration successful."
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    $message = [
                        'status' => FALSE,
                        'message' => "Something went to wrong. Please try again later."
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
                $message = [
                    'status' => FALSE,
                    'message' => "Your Mail already Registered"
                ];
                $this->response($message, REST_Controller::HTTP_UNAUTHORIZED);
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
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[80]');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[100]');
        if ($this->form_validation->run() == FALSE)
        {
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => "Please Fill all fields"
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        }
        else
        {
            $email=$this->post('email');
            $password= $this->post('password');
            $output = $this->UserModel->user_login($email,$password);
            if ($output)
            {
                $date = new DateTime();
                $token['user_id']=$output->user_id;
                $token['username']=$username;
                $token['password']=$password;
                $token['full_name']=$output->fullname;
                $token['email']=$output->email;
                $token['iat']=$date->getTimestamp();
                $token['exp']=$date->getTimestamp()+60*60*24;
                $res= JWT::encode($token, JWT_KEY);
                
                $return_data = [
                    'full_name' => $output->fullname,
                    'email' => $output->email,
                    'token'=> $res,
                ];

                $message = [
                    'status' => true,
                    'data' => $return_data,
                ];
                $this->response($message, REST_Controller::HTTP_OK);
            } else
            {
                $message = [
                    'status' => FALSE,
                    'message' => "Invalid Username or Password"
                ];
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}