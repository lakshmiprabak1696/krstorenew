<?php
class ModelSlotTiming extends Model {
    
    
    
    
        public function setTables(){
        
        
              $tbl_slot = "
             CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "slot_setting
            ( `slot_id` int NOT NULL AUTO_INCREMENT , 
             `end_time` varchar(100) , 
             `intervals` int , 
             `delivery_starts` int , 
             `start_time` varchar(100) , 
             `show_slots` int ,
             `order_per_slot` int,
             `time_format` varchar(100) DEFAULT '12 ( AM/PM and O clock )',
             `range_date_from` varchar(100),
             `range_date_to` varchar(100),
             `date_format` varchar(100) DEFAULT 'yy-mm-dd',
             `time_zone` varchar(100) DEFAULT 'Asia/karachi',
             PRIMARY KEY (`slot_id`))";
        
                    $this->db->query($tbl_slot);
 
        
        $table_exc = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "exclude_dates( `exc_id` int NOT NULL AUTO_INCREMENT , "
                . "`exc_date_time_to` datetime , "
                . "`exc_date_time_from` datetime , PRIMARY KEY (`exc_id`))";
        
        $table_one_day_slot = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "one_year_slot( `one_slot_id` int NOT NULL AUTO_INCREMENT , "
                . "`slot_timing` varchar(1000) , "
                . "`to_date_time` datetime , "
                . "`date` date , "
                . "`max_no` int DEFAULT '2' ,"
                . "`Status` varchar (1000) DEFAULT 'ENABLE',"
                . "`from_date_time` datetime , PRIMARY KEY (`one_slot_id`))";      
        
        
        $delivery_time = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "delivery_time( `id` int NOT NULL AUTO_INCREMENT , `order_id` int , `delivery_time` varchar(1000) , times varchar(1000) , PRIMARY KEY (`id`))";
        
        
        $weekly = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "weekly_day_status( `w_id` int NOT NULL AUTO_INCREMENT , `w_sat` varchar(100) DEFAULT 'OPEN' , `w_sun` varchar(100) DEFAULT 'OPEN' , `w_mon` varchar(100) DEFAULT 'OPEN' , `w_tue` varchar(100) DEFAULT 'OPEN' , `w_wed` varchar(100) DEFAULT 'OPEN' , `w_thu` varchar(100) DEFAULT 'OPEN' , `w_fri` varchar(100) DEFAULT 'OPEN' , PRIMARY KEY (`w_id`)) ";
        
        
             $this->db->query($table_exc);
             $this->db->query($table_one_day_slot);
             $this->db->query($delivery_time);
             $this->db->query($weekly);
             
        $q = $this->db->query("SELECT * FROM " . DB_PREFIX . "weekly_day_status");     
        
        if($q->num_rows<=0){
             
        $this->db->query("insert into " . DB_PREFIX . "weekly_day_status(`w_id`,`w_sat`,`w_sun`,`w_mon`,`w_tue`,`w_wed`,`w_thu`,`w_fri`) values ( NULL,'OPEN','OPEN','OPEN','OPEN','OPEN','OPEN','OPEN')");
        
        }
        
        $slot = $this->db->query("SELECT * FROM " . DB_PREFIX . "slot_setting");     
        
        if($slot->num_rows<=0){
             
            
        $this->db->query("insert into " . DB_PREFIX . "slot_setting(`slot_id`,`end_time`,`intervals`,`delivery_starts`,`start_time`,`show_slots`,`order_per_slot`,`time_format` , `range_date_from` , `range_date_to` , `date_format` ,`time_zone`) values ( NULL,'22:00:00','2','2','01:00:00','10','1','12 ( AM/PM and O clock )','2015-11-04','2015-11-07','yy-mm-dd','Asia/karachi')");
        
        }
        
        
        
             
    }    
    
    
	public function getTime() {
            
            
            $sql = "select show_slots,delivery_starts,time_format,date_format,time_zone from " . DB_PREFIX . "slot_setting";
            
          
            
            $query = $this->db->query($sql); 
            
            if( $query->num_rows>0){
              
                $limit = $query->row['show_slots'];
                $lead = $query->row['delivery_starts'];
                $time_format = $query->row['time_format'];
                $date_format = $query->row['date_format'];
                $time_zone = $query->row['time_zone'];
                
                
            }
          
            if($limit==""){ $limit=1;}
            
            
            $_SESSION['time_zone'] = $time_zone;
            
            date_default_timezone_set($time_zone);
             
            
            
            $added_lead_time = date('Y-m-d H:i:s', strtotime("+$lead hour"));
          
            $data = "";
             
            $sql1 = "SELECT * from " . DB_PREFIX . "one_year_slot WHERE from_date_time>='".$added_lead_time."' ORDER BY from_date_time asc LIMIT $limit";
            
            
            
            
            $q = $this->db->query($sql1);
            
            if($q->num_rows>0){
            
            
            $start_id = $q->rows[0]['one_slot_id'];
            
            
            
            
            foreach ($q->rows as $key=>$result) {
               
                
                if($date_format=="dd-mm-yy"){
                    
                    
                    
                    
                    $d = explode("-",$result['date']);
                    
                    $year = $d[0];
                    $month= $d[1];
                    $dat = $d[2];
                    
                    $ress = $dat."-".$month."-".$year;
                    
                }else{
                    
                    $ress = $result['date'];
                    
                }
                
                $data1 = $result['one_slot_id'];
                $data[] = $ress;
                
            }
            
            
            if($data!=""){
            
                $res = array_unique($data);
                $res['last_id'][] = $data1;
                $res['start_id'][] = $start_id;
                return $res;
            }
            }
            
            
            
            
            
	}
        
        
        public function countSlot($slot_time){
            
            $times = explode(" - ",$slot_time);
            
            $date=date_create($times[0]);
            
            $tm1 =  date_format($date,"y-m-d H:i:s");
            
            $date1=date_create($times[1]);
            
            $tm2 =  date_format($date1,"y-m-d H:i:s");
            
            $new_time = $tm1." - ".$tm2;
            
            $slot = "SELECT max_no FROM " . DB_PREFIX . "one_year_slot WHERE slot_timing='".$new_time."'";
           
            $query = $this->db->query($slot); 
            
            if( $query->num_rows>0){
              
               $conf_order_per_slot = $query->row['max_no'];
               
                
            }
            
            $delivery_time = "SELECT * from " . DB_PREFIX . "delivery_time where times LIKE '%".$new_time."%'";
            
            $qu = $this->db->query($delivery_time); 
            
            if( $qu->num_rows>=$conf_order_per_slot){
                
                echo "fulled";
                
            }else{
                
                $this->session->data['delivery_time'] = $slot_time;
                echo "not fulled";
            }
            
        }
        
        
         public function getInstalled($type) {
             
                
                
		$extension_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "' ORDER BY code");

		foreach ($query->rows as $result) {
			$extension_data[] = $result['code'];
		}

		return $extension_data;
	}
        
}