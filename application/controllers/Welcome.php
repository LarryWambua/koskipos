<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Welcome extends CI_Controller{	 
	public function __construct(){
		parent::__construct();
        $this->load->library('session');
		$this->load->library('grocery_CRUD');
		
		$this->load->model('Setup');
	}
	
	public function index(){
	   if($this->session->userdata('username'))
       $this->dashboard();
       else
	   $this->load->view('login');
	}
    public function _hog_output($output = null){
		$this->load->view('main_template',(array)$output);
	}
    
    public function company(){
        $id=$this->session->userdata('id');
        $co=get_that_data('tbl_users', 'id', $id, 'company_id');
        $data=get_data("tbl_companies","where company_id='$co'");
        if ($data){
        foreach($data as $d) {
        $data['cid']=$d['company_id'];
        $data['name']=$d['company_name'];
        $data['tagline']=$d['company_tagline'];
        $data['address']=$d['company_address'];
        $data['code']=$d['company_postal_code'];
        $data['phyaddress']=$d['company_physical_address'];
        $data['email']=$d['company_email'];
        $data['phone']=$d['company_phone'];
        $data['logo']=$d['company_logo'];
           }    
		$this->load->view('company_profile',$data);
        //$this->load->view('new_company');        
    } else {
        $this->load->view('new_company');
    }
    }
    public function login()
    {
        if($this->Setup->login()) {
            
 			$this->dashboard();
        
 
       } else {
            $data['response'] = "Invalid Login. Please try again with correct credentials";
            
            $this->load->view('login', $data);
        }
    } 
    
     public function dashboard(){
        $this->load->view('dashboard');
	}
    public function clients(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('flexigrid')
    			->set_table('tbl_clients')
                ->where("company='$co'")
    			->set_subject('Clients')
    			->columns('name','email','phone','contact_person','creator','create_date')
    		    ->fields('name','email','phone','contact_person','contact_person_phone','contact_person_email','creator','company','updator','create_date','update_date')
                ->field_type('email', 'email')
                ->display_as('create_date','Created On')
                ->display_as('creator','Created By')
                ->display_as('updator','Updated By')
                ->display_as('contact_person_phone','Contact Person Phone Number')
                ->display_as('contact_person_email','Contact Person Email')
                 ->callback_before_update(array($this,'updateor_callback'))
                 ->callback_before_insert(array($this,'creator_callback'));
                
                if( $crud->getstate() == 'edit' || $crud->getstate() == 'add') 
                {
                    $crud->change_field_type('creator', 'invisible')
                         ->change_field_type('updator','invisible')
                         ->change_field_type('company','invisible')
                         ->change_field_type('create_date','invisible')
                         ->change_field_type('update_date','invisible')
                         ->required_fields('name','email','phone','contact_person')
                         ->callback_before_update(array($this,'updateor_callback'))
                    ->callback_before_insert(array($this,'creator_callback'));
                        
                 }
                 else{
                       $crud->set_relation('creator','tbl_users','full_name')
                             ->set_relation('updator','tbl_users','full_name')
                              ->change_field_type('company','invisible')
                        
                             ->callback_before_update(array($this,'updateor_callback'))
                 ->callback_before_insert(array($this,'creator_callback'));                                                          
                        
                 }
			$output = $crud->render();

			$this->_hog_output($output);

	}
    
    public function feedback(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('datatables')
    			->set_table('tbl_feedback')
                ->where("company='$co'")
    			->set_subject('Feedback')
    			->columns('id','client_id','feedback','create_date','status')
                ->field_type('email', 'email')
                ->field_type('status','dropdown',
                       array("0" =>"<p style='color:red'>Pending</p>", "1" => "<p style='color:red'>Addressed</P>"))  
                ->display_as('create_date','Sent On')
                ->display_as('addressor','Addressed By')
                ->display_as('client_id','Client')
                ->unset_add()
                ->unset_edit()
                ->set_relation('addressor','tbl_users','full_name')
                ->set_relation('client_id','tbl_clients','name');
                        
			$output = $crud->render();

			$this->_hog_output($output);

	}
    
    
    public function employees(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('flexigrid')
    			->set_table('tbl_employees')
                ->where("emp_company='$co'")
    			->set_subject('Employees')
    			->columns('number','name','phone','dept_id','gross_salary','position','creator','create_date')
    		    ->fields('name','email','phone','nat_id','kra_pin','nssf_no','nhif_no','gross_salary','position', 'dept_head','dept_id','number','residence','education','creator','emp_company','updator','create_date','update_date')
                ->field_type('email', 'email')
                ->field_type('dept_head','true_false',array('0' => 'NO', '1' => 'YES'))
                ->set_relation('dept_id','tbl_departments','name')
                ->display_as('create_date','Created On')
                ->display_as('creator','Created By')
                ->display_as('updator','Updated By')
                ->display_as('dept_id','Department')
                ->display_as('emp_company','Company')
                ->display_as('nssf_no','NSSF NO')
                ->display_as('nhif_no','NHIF NO')
                ->display_as('gross_salary','Gross Salary')
                ->display_as('number','Employee Number')
                ->display_as('education','Highest Education Level')
                ->display_as('nat_id','National ID')
                ->display_as('kra_pin','KRA Pin')
                ->callback_before_update(array($this,'updateor_callback'))
                ->callback_before_insert(array($this,'emp_creator_callback'))
                ->callback_after_insert(array($this,'emp_callback'));
                
                if( $crud->getstate() == 'edit' || $crud->getstate() == 'add') 
                {
                    $crud->change_field_type('creator', 'invisible')
                         ->change_field_type('updator','invisible')
                         ->change_field_type('emp_company','invisible')
                         ->change_field_type('create_date','invisible')
                         ->change_field_type('update_date','invisible')
                         ->required_fields('name','number','phone','nat_id','gross_salary','position','dept_id')
                         ->callback_before_update(array($this,'updateor_callback'))
                         ->callback_before_insert(array($this,'emp_creator_callback'))
                         ->callback_after_insert(array($this,'emp_callback'));
                        
                 }
                 else{
                       $crud->set_relation('creator','tbl_users','full_name')
                             ->set_relation('updator','tbl_users','full_name')
                             ->set_relation('emp_company','tbl_companies','company_name')
                             ->callback_before_update(array($this,'updateor_callback'))
                             ->callback_before_insert(array($this,'emp_creator_callback'))
                             ->callback_after_insert(array($this,'emp_callback'));                                                          
                        
                 }
			$output = $crud->render();

			$this->_hog_output($output);

	}
    
    public function departments(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('flexigrid')
    			->set_table('tbl_departments')
                ->where("company='$co'")
    			->set_subject('Departments')
                ->callback_before_update(array($this,'updateor_callback'))
                ->callback_before_insert(array($this,'creator_callback'))
    			->columns('name','emp_in_charge','description','creator','create_date')
    		    ->fields('name','emp_in_charge','description','creator','updator','company','create_date','update_date')
                ->display_as('create_date','Created On')
                ->display_as('creator','Created By')
                ->display_as('updator','Updated By')
                ->display_as('emp_in_charge','Employee In Charge')
                ->display_as('name','Department Name');
                
                if( $crud->getstate() == 'edit' || $crud->getstate() == 'add') 
                {
                    $crud->change_field_type('creator', 'invisible')
                         ->change_field_type('updator','invisible')
                         ->change_field_type('company','invisible')
                         ->change_field_type('emp_in_charge','invisible')
                         ->change_field_type('create_date','invisible')
                         ->change_field_type('update_date','invisible')
                         ->required_fields('name');
                         
                 }
                 else{
                       $crud->set_relation('creator','tbl_users','full_name')
                       ->change_field_type('company','invisible')
                       ->set_relation('company','tbl_companies','company_name')
                
                        ->set_relation('emp_in_charge','tbl_employees','name')
                        ->set_relation('updator','tbl_users','full_name');
                        
                 }
			$output = $crud->render();

			$this->_hog_output($output);

	}
    
    
    public function benefits(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('flexigrid')
    			->set_table('tbl_benefits')
                ->where("company='$co'")
    			->set_subject('Benefits and Deductions')
                ->callback_before_update(array($this,'updateor_callback'))
                ->callback_before_insert(array($this,'creator_callback'))
    			->columns('name','amount','description','type','amount_type','creator','create_date')
    		    ->fields('name','amount','description','type','amount_type','creator','updator','company','create_date','update_date')
                ->field_type('type','dropdown',
                       array("1" =>"Benefit", "2" => "Deduction"))                                
                ->field_type('amount_type','dropdown',
                       array("1" =>"Fixed Amount", "2" => "Percentage of gross")) 
                ->display_as('create_date','Created On')
                ->display_as('creator','Created By')
                ->display_as('updator','Updated By')
                ->display_as('amount_type','Amount Type')
                ->display_as('name','Benefit/Deduction Name');
                
                if( $crud->getstate() == 'edit' || $crud->getstate() == 'add') 
                {
                    $crud->change_field_type('creator', 'invisible')
                         ->change_field_type('updator','invisible')
                         ->change_field_type('company','invisible')
                         ->change_field_type('create_date','invisible')
                         ->change_field_type('update_date','invisible')
                         ->required_fields('name');
                         
                 }
                 else{
                       $crud->set_relation('creator','tbl_users','full_name')
                       ->change_field_type('company','invisible')
                      ->set_relation('updator','tbl_users','full_name');
                        
                 }
			$output = $crud->render();

			$this->_hog_output($output);

	}
    
    
    public function products(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('flexigrid')
    			->set_table('tbl_products')
                ->where("product_company='$co'")
    			->set_subject('Products/Services')
                ->callback_before_update(array($this,'updateor_callback'))
                ->callback_before_insert(array($this,'product_creator_callback'))
    			->columns('name','type','price','creator','create_date')
    		    ->fields('type','name','descr','price','creator','updator','product_company','create_date','update_date')
                ->field_type('type','dropdown',
                       array("0" =>"Product", "1" => "Service")) 
                ->display_as('create_date','Created On')
                ->display_as('creator','Created By')
                ->display_as('updator','Updated By')

                ->display_as('descr','Description');
                
                if( $crud->getstate() == 'edit' || $crud->getstate() == 'add') 
                {
                    $crud->change_field_type('creator', 'invisible')
                         ->change_field_type('updator','invisible')
                         ->change_field_type('product_company','invisible')
                         ->change_field_type('create_date','invisible')
                         ->change_field_type('update_date','invisible')
                         ->required_fields('name','type','price');
                         
                 }
                 else{
                       $crud->set_relation('creator','tbl_users','full_name')
                       ->change_field_type('product_company','invisible')
                             ->set_relation('updator','tbl_users','full_name');
                        
                 }
			$output = $crud->render();

			$this->_hog_output($output);

	}
    
    public function expenses_category(){
        $co=$this->session->userdata('companyid');
			$crud = new grocery_CRUD();
			$crud->set_theme('flexigrid')
    			->set_table('tbl_expenses_category')
    			->set_subject('Expenses Category')
                ->callback_before_update(array($this,'updateor_callback'))
                ->callback_before_insert(array($this,'creator_callback'))
    			->columns('name','descr','creator','create_date')
    		    ->fields('name','descr','creator','updator','company','create_date','update_date')
                ->display_as('create_date','Created On')
                ->display_as('creator','Created By')
                ->display_as('updator','Updated By')
                ->display_as('descr','Description')
                ->display_as('name','Category Name');
                
                if( $crud->getstate() == 'edit' || $crud->getstate() == 'add') 
                {
                    $crud->change_field_type('creator', 'invisible')
                         ->change_field_type('updator','invisible')
                         ->change_field_type('company','invisible')
                         ->change_field_type('create_date','invisible')
                         ->change_field_type('update_date','invisible')
                         ->required_fields('name');
                         
                 }
                 else{
                       $crud->set_relation('creator','tbl_users','full_name')
                       ->change_field_type('company','invisible')
                             ->set_relation('updator','tbl_users','full_name');
                        
                 }
			$output = $crud->render();

			$this->_hog_output($output);

	}
      /**
     * Actions::creator_callback()
     * 
     * @return
     */
    
       function creator_callback($post_array) {
   
            $post_array['creator'] = $this->session->userdata('id');
            $post_array['company'] = $this->session->userdata('companyid');
            $post_array['create_date']=date("Y-m-d h:i:s");
            
             
            return $post_array;
            }
            
            function emp_creator_callback($post_array) {
   
            $post_array['creator'] = $this->session->userdata('id');
            $post_array['emp_company'] = $this->session->userdata('companyid');
            $post_array['create_date']=date("Y-m-d h:i:s");
            
             
            return $post_array;
            }
            
            function emp_callback($post_array) {
   
           if($post_array['dept_head']==1){
                 
           $this->Setup->update_dept($post_array['dept_id']);
            }
            
             
            return $post_array;
            }
             /**
     * Actions::creator_callback()
     * 
     * @return
     */
    
    function product_creator_callback($post_array) {
   
            $post_array['creator'] = $this->session->userdata('id');
            $post_array['product_company'] = $this->session->userdata('companyid');
            $post_array['create_date']=date("Y-m-d h:i:s");
            
             
            return $post_array;
            }
            
         
     /**
     * Actions::updateor_callback()
     * 
     * @return
     */
     function updateor_callback($post_array) {
   
            $post_array['updator'] = $this->session->userdata('id');
            $post_array['update_date']=date("Y-m-d h:i:s");
            
            return $post_array;
            }
   public function delete_invoice()
    {
        
        $id=$this->input->post('id');
        if($this->Setup->delete_invoice($id)) {
 			$data['response'] =0;
             $this->load->view('invoices', $data);
    			
        
 
       } else {
            $data['response'] =1;
            $this->load->view('invoices', $data);
        }
    }
            
            
    public function logout()
    {
        $this->session->sess_destroy();
        $this->load->view('login');
    }
    
    public function check_invoices() {
        $co=$this->session->userdata('companyid');
        $data=get_data('tbl_invoices');
        if ($data){
         foreach($data as $d) {
            $crdate=$d['invoice_createdate'];
            $currntdt=strtotime(date("Y-m-d h:i:s"));
            $crdate=strtotime($crdate);
            $days=($currntdt-$crdate)/86400;
            $days=floor($days);
            $period=$d['invoice_period'];
            if($days==$period){
               echo $id =$d['invoice_id'];
               generate_invoice($id);
            }
         
        }
        }
          else echo "NO invoices"; 
    }
    
    public function generate_invoice($id){
         $this->load->library('pdfgenerator');
         $rd=$this->db->query("SELECT * FROM `tbl_invoices` WHERE invoice_id='$id' ORDER by invoice_id DESC LIMIT 1")->row_array();
         $cid=$rd['invoice_client'];
         $user_id= $this->session->userdata('id');
         $company_id=get_that_data('tbl_users', 'id', $user_id, 'company_id'); 
         $company_data=get_row_data('tbl_companies', 'company_id' , $company_id);
         $data['coname']=$company_data['company_name'];
         $data['cotagline']=$company_data['company_tagline'];
         $data['coaddress']=$company_data['company_address'];
         $data['copost']=$company_data['company_postal_code'];
         $data['cophyadd']=$company_data['company_physical_address'];
         $data['coemail']=$company_data['company_email'];
         $data['cophone']=$company_data['company_phone'];
         $data['cologo']=$company_data['company_logo'];
         $data['cdata']=get_row_data('tbl_clients', 'id', $cid); 
         $data['tot']=$rd['invoice_amount'];
         $da=$rd['invoice_datedue'];
         $da=date_create($da);
         $data['ddate']=date_format($da,"M d Y");
         $data['invid']=$rd['invoice_id'];
         $data['surl']=site_url();
         $data['burl']=base_url();
         $html = $this->load->view('pages/invoices_pdf', $data, true);
         $filename = 'invoice_'.$rd['invoice_id']; 
         $path=$this->pdfgenerator->generate_payslip($html, $filename, true, 'A4', 'portrait');      
        }
        public function email_invoice($id)
    {  
         $this->load->library('pdfgenerator');
         $rd=$this->db->query("SELECT * FROM `tbl_invoices` WHERE invoice_client='$id' ORDER by invoice_id DESC LIMIT 1")->row_array();
         $cid=$rd['invoice_client'];
         $user_id= $this->session->userdata('id');
         $company_id=get_that_data('tbl_users', 'id', $user_id, 'company_id'); 
         $company_data=get_row_data('tbl_companies', 'company_id' , $company_id);
         $data['coname']=$company_data['company_name'];
         $data['cotagline']=$company_data['company_tagline'];
         $data['coaddress']=$company_data['company_address'];
         $data['copost']=$company_data['company_postal_code'];
         $data['cophyadd']=$company_data['company_physical_address'];
         $data['coemail']=$company_data['company_email'];
         $data['cophone']=$company_data['company_phone'];
         $data['cologo']=$company_data['company_logo'];
         $data['cdata']=get_row_data('tbl_clients', 'id', $cid); 
         $data['tot']=$rd['invoice_amount'];
         $da=$rd['invoice_datedue'];
         $da=date_create($da);
         $data['ddate']=date_format($da,"M d Y");
         $data['invid']=$rd['invoice_id'];
         $data['surl']=site_url();
         $data['burl']=base_url();        
        $html = $this->load->view('pages/invoices_pdf', $data, true);
        $filename = 'invoice_'.$rd['invoice_id']; 
        $path=$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
        $message= "Kindly find attached your invoice";
        $mailto=get_that_data('tbl_clients', 'id', $cid, 'email');
        $sub="Invoice";
        $url=base_url("assets/invoices/")."/".$filename.".pdf";
        update_url($url,$rd['invoice_id']);
        $this->send_mail($mailto,$message,$sub,$path);
        //$this->load->view('pages/invoices_pdf', $data);  
        if($this->send_mail($mailto,$message,$sub,$path))
            $data['success']='Invoice sent successfully';
            else
            $data['error']='Invoice could not be sent';
            
        $this->load->view('pages/invoices',$data);
     
     
    }        
        public function email_payslip($param)
    {  $this->load->library('pdfgenerator');
        $data['param']=$param;
        $html = $this->load->view('pages/payslip_pdf', $data, true);
        $pay=get_row_data('tbl_payroll', 'payroll_id',$param);
        $empname=get_that_data('tbl_employees','id', $pay['payroll_employeeid'], 'name');
        $filename =$empname.'_'.$pay['payroll_month'].'_'.$pay['payroll_year'].'_payslip'; 
        $path=$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
        $message= "Kindly find attached your payslip";
        $mailto=get_that_data('tbl_employees', 'id', $pay['payroll_employeeid'], 'email');
        $sub="Payslip";
        $url=base_url("assets/invoices/")."/".$filename.".pdf";
        if($this->send_mail($mailto,$message,$sub,$path))
            $data['success']='Payslip sent successfully';
            else
            $data['error']='Payslip could not be sent';
            
        $this->load->view('pages/payroll_view',$data);
     
     
    }
    public function generate_payslip($param)
    {  $this->load->library('pdfgenerator');
        $data['param']=$param;
        $html = $this->load->view('pages/payslip_pdf', $data, true);
        $pay=get_row_data('tbl_payroll', 'payroll_id',$param);
        $empname=get_that_data('tbl_employees','id', $pay['payroll_employeeid'], 'name');
         $filename =$empname.'_'.$pay['payroll_month'].'_'.$pay['payroll_year'].'_payslip'; 
          $path=$this->pdfgenerator->generate_payslip($html, $filename, true, 'A4', 'portrait');
          
    }
        
         public function overdue_pay(){
        $data=get_data('tbl_invoices','where invoice_balance>0');
        if ($data){
         foreach($data as $d) {
            $datedue=$d['invoice_datedue'];
            $currntdt=strtotime(date("Y-m-d h:i:s"));
            $datedue=strtotime($datedue);
            $days=($currntdt-$datedue)/86400;
            $mailto=get_that_data('tbl_clients', 'id', $d['invoice_client'], 'email');
             $days=floor($days);    
            if($days>0){
             if($days>1)
                $message= "Please note that your payment of kshs " .$d['invoice_balance']." 
               for invoice number " .$d['invoice_id'] ." is overdue by " .$days." days";
                
                else 
                 $message= "Please note that your payment of kshs " .$d['invoice_balance']." for invoice number 
                 " .$d['invoice_id']." is overdue by 1 day";
               
               $sub="Invoice reminder";
               $at='';
                $this->send_mail($mailto,$message,$sub,$at);
                
        }
        
      }
      } else echo "NO invoices";
  }
  
  
      public function send_mail($t,$m,$sub,$at) { 
        $co=$this->session->userdata('companyid');
		
        $this->load->config('email');
         $from = $this->config->item('smtp_user');
         //$to_email = $this->input->post('email'); 
   
         //Load email library 
         $this->load->library('email'); 
   
         $this->email->from($from, get_that_data('tbl_companies','company_id',$co,'company_name')); 
         
         $this->email->to($t);
         $this->email->subject($sub); 
         $this->email->message($m); 
         $this->email->attach($at);
          $this->email->set_newline("\r\n");         
         //Send mail 
         if($this->email->send())
         //$this->load->view('pages/invoices'); 
         return 1;
         else 
          //$this->load->view('pages/invoices'); 
          return 0;
        
         //echo "email_sent","Error in sending Email.";
        
      } 
      
      
     public function add_company() {

        //upload file
        $config['upload_path'] = './assets/images/';
       	$config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_filename'] = '255';
        $config['remove_spaces']	= 'TRUE';	
        $config['max_size'] = '5120'; //5 MB
        //$config['name'] = $_FILES['logo']['name'].time();
        
        if (isset($_FILES['logo']['name'])) {
            if (0 < $_FILES['logo']['error']) {
                return array('status' => 0, 'msg' => 'Invoice details  could not be saved '.$_FILES['logo']['error']);
            } else {
               
                    $this->load->library('upload', $config);
                    if (!$this->upload->do_upload('logo')) {
                        echo $this->upload->display_errors();
                    } else {
                    $data=$this->upload->data();
                    $p="http://finq.quadrantsoftwares.com/assets/images/";
	                $img_name=$p.$data['raw_name'].$data['file_ext'];
                     $this->Setup->add_company($img_name);
                 
                    }
                
            }
        } else {
                 return array('status' => 0, 'msg' => 'Please choose a file');
           
        }
    }
 
        
        /**
     * Actions::modal()
     * This method loads bootstrap modals either through jquery.load or href html tags
     * @param mixed $page
     * @param string $param
     * @return CI loaded view
     */
    public function modal($page, $param="")
    {
        if($this->session->userdata('username'))
            $this->load->view('modals/'.$page, array('param' => $param));
        else 
            $this->load->view('login');
    }
    
       /**
     * Actions::act()
     * This method is called by form actions to process through the model and consumed by ajax-forms 
     * @see malsup.com/jquery/form
     * @param string $model
     * @param string $action
     * @return json response
     */
     
    public function act($model, $action)
    {
        if($this->session->userdata('username')) {
            $this->load->model($model);
            $response = $this->$model->$action();
            echo json_encode($response);
        } else {
            echo json_encode(array('status' => 0, 'msg' => 'You must be logged in to perform this action!'));
        }
    }
    public function edit($model, $action, $id)
    {
        if($this->session->userdata('username')) {
            $this->load->model($model);
            $response = $this->$model->$action($id);
            echo json_encode($response);
        } else {
            echo json_encode(array('status' => 0, 'msg' => 'You must be logged in to perform this action!'));
        }
    }
    
     public function view($page, $param="")
    { 
        if($this->session->userdata('username'))
            $this->load->view('pages/'.$page, array('param' => $param));
        else 
            $this->load->view('login');
    }
    public function delete_bene($id)
	{
		if($this->Setup->delete_bene($id)) {
 			$data['response']=0;
             $this->load->view('pages/employee_benefits', $data);
    			
        
 
       } else {
            $data['response'] =1;
            $this->load->view('pages/employee_benefits', $data);
        }
	}
    public function process_payroll()
	{
	   
	   $emp=$this->input->post('emp[]');
       foreach($emp as $employee){
            $month=$this->input->post('month');
            //$savedmonth=get_row_data('tbl_payroll','payroll_month',$month);
            $savedmonth=get_data('tbl_payroll',"WHERE payroll_employeeid='$employee' AND payroll_month='$month'");
            
            $empdata=get_row_data('tbl_employees','id',$employee);
            $gross=$empdata['gross_salary'];
            $benefit=0;                        
            $deduction=0;         
            $bene=get_data('tbl_employee_benefits',"WHERE employee_benefits_empid='$employee'");
            if($savedmonth)
            //return array('status' => 0, 'msg' => 'Payroll already processed for employee.'.$empdata['name'].' the selected month!');
            
            echo 'Error! Payroll already processed for employee. '.$empdata['name'].' for '.$month."<br/>";
            
        else
            {
            $data=$this->db->query("SELECT employee_benefits_amount FROM tbl_employee_benefits WHERE employee_benefits_empid='$employee'")->result_array();
           $nssf1=0;
           $nssf2=0;
           foreach($bene as $b){
            if($b['employee_benefits_type']==1){
                //calc_nssf($gross);
                $deduction+=$b['employee_benefits_amount'];
                
            } else{
                $benefit+=$b['employee_benefits_amount'];
              }
              
              //$t=calc_paye($gross);
              //calculating nssf
            
              }
              $grossben=$gross+$benefit;
              switch ($grossben) {
            case ($grossben<=3000):
            $nssf1=180; 
            break;
            case ($grossben<=4500):
            $nssf1=270;           
            break;
            case ($grossben<=6000):
            $nssf1=360;
            break;          
           default:
           $nssf1=360;
           $n=(0.06*$grossben)-360;
           if($n>720)
            $nssf2=720;
            else
            $nssf2=$n;
                
        }$nhif=$this->calc_nhif($grossben);
             $taxablepay=$grossben-($nssf1+$nssf2);
            $paye=$this->calc_paye($taxablepay);
            $relief=1408;
            if($paye <$relief)
            $net=$taxablepay-$nhif;
            else
            $net=$taxablepay-$paye+$relief-$nhif;
             $salary=$net-$deduction;                        
             //echo "NSSF T1= ".$nssf1." NSSF T2= ".$nssf2." NHIF= ".$nhif." Gross= ".$grossben." Taxable pay= ".$taxablepay." PAYE= ".$paye." Net= ".$salary." deductions= ".$deduction."<br/>";
           
            $data = array(
                'payroll_employeeid' =>$employee,
                'payroll_netpay' => $salary,
                'payroll_paye' => $paye-$relief,
                'payroll_nssf1' => $nssf1,
                'payroll_nssf2' => $nssf2,
                'payroll_nhif' => $nhif,
                'payroll_benefits' => $benefit,
                'payroll_deductions' => $deduction,
                'payroll_tax' => $taxablepay,
                'payroll_month' => $month,
                'payroll_year' => date('Y'),
                'payroll_creator' => $this->session->userdata('id'),
                'payroll_createdate' => date('Y-m-d H:i:s'),
                'payroll_company' => $this->session->userdata('companyid'),
            );
            if($this->db->insert('tbl_payroll', $data))
            echo 'Success';
            
        else
            echo $this->db->error->message;
        }
         }
       }
       public function calc_paye($taxablepay){
            switch ($taxablepay) {
            case ($taxablepay<12299):
            $paye=0.10*$taxablepay;
            return $paye;
            break;
            case ($taxablepay<=23885):
            $paye=1229.8+($taxablepay-12298)*0.15;
            return $paye;
            break;
            case ($taxablepay<=35472):
            $paye=2968+($taxablepay-23885)*0.20;
            return $paye;
            break;
            case ($taxablepay<=47059):
            $paye=5285+($taxablepay-35472)*0.25;
            return $paye;
            break;
            default:
            $paye=8182+($taxablepay-47060)*0.3;
            return $paye;
            }
        
        
       }
       public function calc_nhif($gross){
         switch ($gross) {
            case ($gross<6000):
            return 150;
            break;
            case ($gross<8000):
            return 300;
            case ($gross<12000):
            return 400;
            case ($gross<15000):
            return 500;
            case ($gross<20000):
            return 600;
            case ($gross<25000):
            return 750;
            case ($gross<30000):
            return 850;
            case ($gross<35000):
            return 900;
            case ($gross<40000):
            return 950;
            case ($gross<45000):
            return 1000;
            case ($gross<50000):
            return 1100;
            case ($gross<60000):
            return 1200;
            case ($gross<70000):
            return 1300;
            case ($gross<80000):
            return 1400;
            case ($gross<90000):
            return 1500;
            case ($gross<100000):
            return 1600;
            default:
            return 1700;
            }
            
        
        }
       
    
    
     }