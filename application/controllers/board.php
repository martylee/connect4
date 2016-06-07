<?php
class Board extends CI_Controller {
    function __construct() {
		// Call the Controller constructor
    	parent::__construct();
    	session_start();
    } 
    
    public function _remap($method, $params = array()) {
    	// enforce access control to protected functions
		if (!isset($_SESSION['user']))
			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
    	
    	return call_user_func_array(array($this, $method), $params);
    }
    
    function index() {
		$user = $_SESSION['user'];	
    	$this->load->model('user_model');
    	$this->load->model('invite_model');
    	$this->load->model('match_model');
    	
    	$user = $this->user_model->get($user->login);

    	$invite = $this->invite_model->get($user->invite_id);
    	
    	if ($user->user_status_id == User::WAITING) {
    		$invite = $this->invite_model->get($user->invite_id);
    		$otherUser = $this->user_model->getFromId($invite->user2_id);
    	}
    	else if ($user->user_status_id == User::PLAYING) {
    		$match = $this->match_model->get($user->match_id);
    		if ($match->user1_id == $user->id)
    			$otherUser = $this->user_model->getFromId($match->user2_id);
    		else
    			$otherUser = $this->user_model->getFromId($match->user1_id);
    	}
    	
    	$data['user']=$user;
    	$data['otherUser']=$otherUser;
    	
    	switch($user->user_status_id) {
    		case User::PLAYING:	
    			$data['status'] = 'playing';
    			break;
    		case User::WAITING:
    			$data['status'] = 'waiting';
    			break;
    	}
	    	
		
        $this->load->view('match/board',$data);
       
    }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');
 		
 		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');

 			$user = $_SESSION['user'];
 			// check to see if the user is in the match or not
 			$user = $this->user_model->getExclusive($user->login);
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			// message contents sent by views/match/board.php via POST
 			$msg = $this->input->post('msg');
 			
 			if ($match->user1_id == $user->id)  {
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}
 			// encode success msg in json
 			echo json_encode(array('status'=>'success'));
 			return;
 		}
		
 		$errormsg="Missing argument";
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 
	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 		// Make sure the user is actually part of this match
 		$user = $_SESSION['user'];
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode  
 		$this->db->trans_begin();
 			
 		$match = $this->match_model->getExclusive($user->match_id);			
 			
 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}

 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		// if all went well commit changes
 		$this->db->trans_commit();
 		
 		echo json_encode(array('status'=>'success','message'=>$msg));
		return;
		
		transactionerror:
			$this->db->trans_rollback();
		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
	}
	

    //add two functions to connect the board data and database
	function BoardState() {
		$this->load->model('user_model');
 		$this->load->model('match_model');
 		
 		$user = $_SESSION['user'];
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		
 		$match = $this->match_model->get($user->match_id);
 		$blob = $match->board_state;
 		// convert binary data back to arrays
 		$data = unserialize($blob);
 		echo json_encode(array('status'=>'success','turn'=>$data['turn'],
 							'move'=>$data['move'],'result'=>$data['result']));
		return;
 		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
	}
	
	function BoardSet(){
		$this->load->model('user_model');
 		$this->load->model('match_model');
 		$user = $_SESSION['user'];
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		

 		$this->db->trans_begin();
 		$match = $this->match_model->getExclusive($user->match_id);	
 		
 		$turn = $this->input->post('turn');
 		$move = $this->input->post('move');
 		$result = $this->input->post('result');
 		if ($turn=="") { 
 			$errormsg="Missing username";
 			goto error;
 		}
 		$data['turn'] = $turn;
 		$data['move'] = $move;
 		$data['result'] = $result;
 		$this->match_model->updateBoard($user->match_id,$data);
 		
 		if ($result==$user->login) {
 			if ($match->user1_id == $user->id)
 				$this->match_model->updateStatus($user->match_id,2);
 			else
 				$this->match_model->updateStatus($user->match_id,3);
 		} 
 		else if ($result=="tie"){ 
 			$this->match_model->updateStatus($user->match_id,4);
 		}
 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		$this->db->trans_commit();
 		echo json_encode(array('status'=>'success','turn'=>$turn,
 							'move'=>$move,'result'=>$result));
		return;
		transactionerror:
			$this->db->trans_rollback();
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
	}
}
?>
