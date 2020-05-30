<?php
class ControllerExtensionModuleTimeslot extends Controller {

            private $error = array(); // This is used to set the errors, if any.
 
            public function index() 
                    
            {   
               
                // Default function 
                $this->load->language('extension/module//timeslot'); // Loading the language file of timeslot
                
                $this->document->setTitle($this->language->get('heading_title')); // Set the title of the page to the heading title in the Language file i.e., Time slot
                $this->load->model('slot/timeslot');
                $this->load->model('setting/setting');
                $this->model_slot_timeslot->setTables();

               
                
                if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
                {
                   /*--------------------Post Details----------------------------*/
                  $date_range_from  = $this->request->post['date_range_from'];  
                  $date_range_to  = $this->request->post['date_range_to'];
                  $start_time  = $this->request->post['Delivery_start_time'];
                  $end_time = $this->request->post['end_time'];
                  $Intervals = $this->request->post['Intervals'];
                  $lead_time = $this->request->post['lead_time'];        
                  $numberofslots = $this->request->post['numberofslots'];
                  $order_per_slot = $this->request->post['Order_per_slot'];
                  $time_format = $this->request->post['time_format'];
                  $date_format = $this->request->post['date_format'];
                  $time_zone = $this->request->post['time_zone'];
                  
                  
                   /*------------------------------------------------*/
                  $this->load->model('setting/setting');
                  
                  $setting_data['time_slot_status'] = $this->request->post['time_slot_status'];
                   $this->model_setting_setting->editSetting('time_slot',$setting_data);

                   $this->session->data['success'] = $this->language->get('text_success');


             
                  
                  /*-------------------Save Settings in database -----------------------------*/
                  $this->model_slot_timeslot->SaveIntervelSettings($start_time,$end_time,$Intervals,$lead_time,$numberofslots,$order_per_slot,$time_format ,$date_range_from , $date_range_to ,$date_format , $time_zone );
                  /*------------------------------------------------*/
                  
                  /*-------------------Make all Slots in database -----------------------------*/
                  $this->model_slot_timeslot->setSlot($start_time, $end_time , $Intervals , $lead_time , $date_range_from , $date_range_to, $order_per_slot);
                  /*------------------------------------------------*/
                  
                  /*-------------------Remove off days-----------------------------*/
                  $this->model_slot_timeslot->RemoveWeekDays();
                  /*------------------------------------------------*/
                  
                  
                  /*-------------------Remove Exckude range days-----------------------------*/
                  $this->model_slot_timeslot->checkExcludeDate();
                  
                  
                  $data['success'] = "successfully";
                  
                  
                  
                  $this->response->redirect($this->url->link('extension/module/timeslot&success=successfully saved', 'user_token=' . $this->session->data['user_token'], 'SSL'));
                  //header("location:$location");
               
                  	
		}
                
                /*---------get Slot setting Configuration-------------*/    
                $slot = $this->model_slot_timeslot->getSlot();
                $data['time_slot_status'] = $this->config->get('time_slot_status');

                
                
                
                if(!empty($slot) && $slot!=""):
                    
                    $data['config_start_time'] = $slot['start_time'];
                    $data['config_end_time'] = $slot['end_time'];
                    $data['intervals'] = $slot['intervals'];
                    $data['delivery_starts'] = $slot['delivery_starts'];
                    $data['showSlots'] = $slot['showSlots'];
                    $data['order_slot'] = $slot['order_per_slot'];
                    $data['time_format'] = $slot['time_format'];
                    $data['date_range_from'] = $slot['date_range_from'];
                    $data['date_range_to'] = $slot['date_range_to'];
                    $data['date_format'] = $slot['date_format'];
                    $data['time_zone'] = $slot['time_zone'];
                    
                endif;
                
                
               $generated_slots = $this->model_slot_timeslot->GeneratedSlots();
                if(!empty($generated_slots) && $generated_slots!=""){
                    
                    $data['generated_slots'] = $generated_slots;
                    
                }
                
                
               
               
                
                /*---------get Slot off days setting Configuration-------------*/    
                $Week = $this->model_slot_timeslot->SelectWeek();
                    
                if(!empty($Week) && $Week!=""):
                    
                    $data['w_sat'] = $Week['days']['w_sat'];
                    $data['w_sun'] = $Week['days']['w_sun'];
                    $data['w_mon'] = $Week['days']['w_mon'];
                    $data['w_tue'] = $Week['days']['w_tue'];
                    $data['w_wed'] = $Week['days']['w_wed'];
                    $data['w_thu'] = $Week['days']['w_thu'];
                    $data['w_fri'] = $Week['days']['w_fri'];
                endif;
                
                    /*Assign the language data for parsing it to view*/
                    $data['heading_title'] = $this->language->get('heading_title');
                    $data['Shop_start_time'] = $this->language->get('Shop_start_time');
                    $data['start_time'] = $this->language->get('start_time');
                    $data['Shop_End_time'] = $this->language->get('Shop_End_time');
                    $data['end_time'] = $this->language->get('end_time');
                    $data['Intervals'] = $this->language->get('Intervals');
                    $data['lead_time'] = $this->language->get('lead_time');
                    $data['NO_of_Slots'] = $this->language->get('NO_of_Slots');
                    $data['numberofslots'] = $this->language->get('numberofslots');
                    $data['Exclude_Time_and_Dates'] = $this->language->get('Exclude_Time_and_Dates');
                    $data['From'] = $this->language->get('From');
                    $data['example_title'] = $this->language->get('example_title');
                    $data['To'] = $this->language->get('To');
                    $data['Save_Exclude_Time'] = $this->language->get('Save_Exclude_Time');
                    $data['Shop_start_time'] = $this->language->get('Shop_start_time');
                    $data['Shop_start_time'] = $this->language->get('Shop_start_time');
                    $data['order_per_slot'] = $this->language->get('order_per_slot');
                    $data['text_enabled'] = $this->language->get('text_enabled');
                    $data['text_disabled'] = $this->language->get('text_disabled');
                    $data['text_content_top'] = $this->language->get('text_content_top');
                    $data['text_content_bottom'] = $this->language->get('text_content_bottom');      
                    $data['text_column_left'] = $this->language->get('text_column_left');
                    $data['text_column_right'] = $this->language->get('text_column_right');
                    $data['entry_code'] = $this->language->get('entry_code');
                    $data['entry_layout'] = $this->language->get('entry_layout');
                    $data['entry_position'] = $this->language->get('entry_position');
                    $data['entry_status'] = $this->language->get('entry_status');
                    $data['entry_sort_order'] = $this->language->get('entry_sort_order');
                    $data['button_save'] = $this->language->get('button_save');
                    $data['button_cancel'] = $this->language->get('button_cancel');
                    $data['button_add_module'] = $this->language->get('button_add_module');
                    $data['button_remove'] = $this->language->get('button_remove');
                    
                    // tool tip
                    
                    $data['help_start_time'] = $this->language->get('help_start_time');
                    $data['help_end_time'] = $this->language->get('help_end_time');
                    $data['help_date_range_from'] = $this->language->get('help_date_range_from');
                    $data['help_date_range_to'] = $this->language->get('help_date_range_to');
                    $data['help_slot_duration'] = $this->language->get('help_slot_duration');
                    $data['help_lead_time'] = $this->language->get('help_lead_time');
                    $data['help_slot_display'] = $this->language->get('help_slot_display');
                    $data['help_maximum_order_per_slot'] = $this->language->get('help_maximum_order_per_slot');
                    $data['help_shop_opening_days'] = $this->language->get('help_shop_opening_days');
                    $data['help_time_format'] = $this->language->get('help_time_format');
                    
                    $data['help_exclude_date_time_from'] = $this->language->get('help_exclude_date_time_from');
                      
                    /*This Block returns the warning if any*/
                    if (isset($this->error['warning'])) {
                        $data['error_warning'] = $this->error['warning'];
                    } else {
                        $data['error_warning'] = '';
                    }
                    /*End Block*/

                    /*This Block returns the error code if any*/
                    if (isset($this->error['code'])) {
                        $data['error_code'] = $this->error['code'];
                    } else {
                        $data['error_code'] = '';
                    }
                    /*End Block*/


                    /* Making of Breadcrumbs to be displayed on site*/
                    $data['breadcrumbs'] = array();

                    $data['breadcrumbs'][] = array(
                        'text'      => $this->language->get('text_home'),
                        'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                        'separator' => false
                    );

                    $data['breadcrumbs'][] = array(
                        'text'      => $this->language->get('text_module'),
                        'href'      => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                        'separator' => ' :: '
                    );

                    $data['breadcrumbs'][] = array(
                        'text'      => $this->language->get('heading_title'),
                        'href'      => $this->url->link('module/timeslot', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                        'separator' => ' :: '
                    );

                    /* End Breadcrumb Block*/

                    $data['action'] = $this->url->link('extension/module/timeslot', 'user_token=' . $this->session->data['user_token'], 'SSL'); // URL to be directed when the save button is pressed

                    $data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL'); // URL to be redirected when cancel button is pressed

                    $data['user_token'] = $this->session->data['user_token'];
                    
                    //$data['success'] = "Success";

                    /* This block checks, if the time slot text field is set it parses it to view otherwise get the default time slot text field from the database and parse it*/

                    if (isset($this->request->post['timeslot_text_field'])) {
                       
                    } else {
                       
                    }   
                    /* End Block*/

                    $data['modules'] = array();

                    /* This block parses the Module Settings such as Layout, Position,Status & Order Status to the view*/
                    if (isset($this->request->post['timeslot_module'])) {
                        $data['modules'] = $this->request->post['timeslot_module'];
                    } elseif ($this->config->get('timeslot_module')) { 
                        $data['modules'] = $this->config->get('timeslot_module');
                    }
                    /* End Block*/         

                    $this->load->model('design/layout'); // Loading the Design Layout Models

                    $data['layouts'] = $this->model_design_layout->getLayouts(); // Getting all the Layouts available on system

                    $data['header'] = $this->load->controller('common/header');

                    $data['column_left'] = $this->load->controller('common/column_left');

                    $data['footer'] = $this->load->controller('common/footer');

                    $this->response->setOutput($this->load->view('extension/module/timeslot', $data));
            }
            
            
            
            public function install() {  
                $this->load->model('user/user_group');
                $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'module/timeslot');
                $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'module/timeslot');
            }

            /* Function that validates the data when Save Button is pressed */
            protected function validate() 
                    
            {
                    /* Block to check the user permission to manipulate the module*/
                    if (!$this->user->hasPermission('modify', 'module/timeslot')) {
                        $this->error['warning'] = $this->language->get('error_permission');
                    }
                    if (!$this->error) {
                        return true;
                    } else {
                        return false;
                    }   
                    /* End Block*/
                }
                /* End Validation Function*/
                
                
            public function addExceed(){
                
                
                
                    
          
                   $to =  $this->request->post['to'];
                   $froms =  $this->request->post['from'];        

                   $fromm  = date("Y-m-d H:00:00", strtotime($froms));
                   $too  = date("Y-m-d H:00:00", strtotime($to));
                 
                   
                   $this->load->model('slot/timeslot');
                   $data = $this->model_slot_timeslot->SaveExcluseDates($too , $fromm); 
                   echo $data; 
            
            }
        
            public function showExceed(){
                
                
                   $this->load->language('module/timeslot');
                   $data = $this->language->get('Dates_are_excluded'); 
                   
                   $this->load->model('slot/timeslot');
                   echo $this->model_slot_timeslot->ShowExc($data);
            
            }
        
            public function WeeklychangeStatus(){
            
                   $day = $this->request->post['day'];
                   $this->load->model('slot/timeslot');
                   $this->model_slot_timeslot->WeeklyStatus($day);
            
            }
            
            public function SingleSlotchangeStatus(){
             
                  $status = $this->request->post['status'];
                  $id = $this->request->post['id'];
                  $this->load->model('slot/timeslot');
                  $this->model_slot_timeslot->SingleStatus($status,$id);
             
             
            }

            
            public function DeleteExceed(){
           
                   $id = $this->request->post['id'];
                   $this->load->model('slot/timeslot');
                   $data = $this->model_slot_timeslot->DeleteExceed($id);
                   $this->load->language('module/timeslot');
                   $exc = $this->language->get('Dates_are_excluded'); 
                   
                   echo $data = $this->model_slot_timeslot->ShowExc($exc);
                   $this->model_slot_timeslot->checkExcludeDate(); 
            
            }
            
            public function changeTable(){
               
                
                $id = $_POST['pk'];
                $maximum =  $_POST['value'];
                $this->load->model('slot/timeslot');
                $data = $this->model_slot_timeslot->SaveMax($maximum,$id);
            }
            
}