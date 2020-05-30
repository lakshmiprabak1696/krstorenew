<?php
    
    class ModelSlotTimeslot extends Model {
        
        
        public function index(){
            
            $this->setTables();
            
        }
    public function setSlot($start_time,$end_time,$intervals,$lead_time ,$date_range_from , $date_range_to , $order_per_slot)
        {
                
            
                $this->setTables();
                $makeSlot = $this->makeSlot($start_time, $end_time , $intervals , $lead_time , $date_range_from , $date_range_to);
                
               
                
                if($makeSlot!=""){
                    
                    $this->SaveSlot($makeSlot,$start_time , $end_time, $order_per_slot);
                
                    
                }

        }

        public function makeSlot($start , $end , $intervals , $lead_time , $date_range_from , $date_range_to){
            
                // choose time zone 
                date_default_timezone_set('Asia/Karachi');
                $starting_month = 0; 
                
                $exp_to = explode("-", $date_range_to);
                $exp_from = explode("-", $date_range_from);
               
                
                // This is TO year where ends the slots
                $exp_to[2];
                
                
                // This is from month where slot will be starts
                $exp_from[1];
                
                
                
                $date1 = new DateTime($date_range_from);
                $date2 = new DateTime($date_range_to);
                $interval = $date1->diff($date2);
                
                $starting_month = $exp_from[1];
                $total_month_diff = $interval->m;
                $starting_year = $exp_from[0];
                $ending_year = $exp_to[0];
                $ending_month = $exp_to[1];
                $ending_date = $exp_to[1];
                $total = $interval->days;
               
                for($i=0;$i <= $total; $i++)
                {
                  
                    $datetime = new DateTime($date_range_from);
                    $datetime->modify("+$i day");
                    
                    $this->load->language('english');
                    $date_format_short = $this->language->get('date_format_short');
                   
                    
                    
                    $day =  $datetime->format("y-m-d");
                    
                    $exc_day = explode("-", $day);
                    $datas[] = $this->makeOneDaySlots($start,$end , $intervals , $lead_time ,$exc_day[2] , $exc_day[1],$date_range_to , $date_range_from,$exc_day[0]);
                    
                //}
                }
                return $datas;
                
        }

        
        
//         public function daysInMonth($month,$year) {
//               $dates = array();
//
//
//                $num = "";
//
//                for($i = 1; $i <= 12; $i++) {
//
//                if($i>=$month){ 
//
//                $num = cal_days_in_month(CAL_GREGORIAN, $i, $year); 
//                $dates['m'][] = $i;
//
//                }
//
//                for($a = 1; $a < $num+1; $a++) {
//
//                $dates['d'][$i][] = $a;
//
//                }
//                }
//                return $dates;
//        }
        
        
        // make slots of one year    
        public function makeOneDaySlots($start, $end, $interval, $lead_time, $day, $month ,$date_range_to,$date_range_from,$year )
        {
            
            
             $data = array();
                
             $starts = $date_range_from." ".$start;
               
             $ends = $date_range_from." ".$end;
              
              
             // start date
              $starts = date("Y-$month-$day H:i:s", strtotime($starts));
               // end date
               $ends = date("Y-$month-$day H:i:s", strtotime($ends));
              
               //echo $start = date_create($starts);

                $end = date_create($ends);
               
                
                $date1 = new DateTime($starts);
                $date2 = new DateTime($ends);
                $diff = $date1->diff($date2);
               
                // create diffrence between start date and end date
                //$diff=date_diff($ends,$start);

                 $hour = $diff->h;

                 
                 
                $get_result = ceil($hour/$interval);

                $inter = 0;

                $inter = $inter -$interval;
                
                //$interval +=$lead_time;

                for($i=1; $i<=$get_result; $i++):
                    
                    
                    
                    
                    

                    $inter +=$interval;

                    $new_val =+ $inter + $interval;
                    //echo $starts;
                 
                   
                     $save = date("$year-$month-$day H:i:s", strtotime("$starts + " .$inter. " hours"))." "
                    . "- ".date("$year-$month-$day H:i:s", strtotime("$starts + " .$new_val. " hours"));
             
                    $ToDate =  date("$year-$month-$day H:i:s", strtotime("$starts + " .$inter. " hours"));
                    $Date   =  date("$year-$month-$day");
                    $fromDate = date("$year-$month-$day H:i:s", strtotime("$starts + " .$new_val. " hours"));
                    $data['slots'][] = $save;
                    $data['ToDate'][] = $ToDate;
                    $data['FromDate'][] = $fromDate;
                    $data['Date'][] = $Date;
                
                endfor;
                
                
                return $data;




        }


        public function SaveSlot($arr , $start_time , $end_time , $lead_time){
            
                
                // First Truncate the table
                $query = $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "one_year_slot");
                
                
                
                foreach ($arr as $val):
                $i=0; 
                foreach ($val['slots'] as $vals):
                    $from = $val['FromDate'][$i];  
                    $to = $val['ToDate'][$i];
                    $slot_timing = $vals;
                    $dates =  $val['Date'][$i];
                    $query = $this->db->query("INSERT  " . DB_PREFIX . "one_year_slot (slot_timing , to_date_time , from_date_time , date , max_no) values('".$slot_timing."' , '".$from."' , '".$to."' , '".$dates."' , '".$lead_time."' )");
                    $i++;
                endforeach;


                endforeach;
             

        }    

        public function SaveExcluseDates($too , $frm)
        {       
            
                $check_from_all_slot = $this->db->query("SELECT * from " . DB_PREFIX . "one_year_slot WHERE to_date_time ='".$too."' or from_date_time='".$frm."'");
                if($check_from_all_slot->num_rows<=0){
                    
                    return "2";
                    die();
                    
                }else{
                
            
            
                
                
                $check = $this->db->query("SELECT * from " . DB_PREFIX . "exclude_dates WHERE exc_date_time_to ='".$too."' and exc_date_time_from='".$frm."'");
                if($check->num_rows<=0){

                $to  = date("Y-m-d H:i:s", strtotime($too));
                $query = $this->db->query("INSERT INTO " . DB_PREFIX . "exclude_dates (exc_date_time_to , exc_date_time_from) VALUES ('".$to."' , '".$frm."')");
                return true;}else{return  false;}
                
                }

        }

        public function DeleteExceed($id){


                $this->db->query("DELETE FROM " . DB_PREFIX . "exclude_dates WHERE exc_id=$id");

        }



        public function ShowExc($date_exclude){

                $this->setTables();
                $Slots = $this->db->query("SELECT * FROM " . DB_PREFIX . "exclude_dates");
                echo "<br><b>".$date_exclude."</b><br><br>"; 
                foreach ($Slots->rows as $res) {

                $val = $res['exc_date_time_from']."  ".$res['exc_date_time_to'];
                $id = $res['exc_id'];
                echo '<div onclick="move('.$id.')" name=id class="glyphicon glyphicon-remove cross"></div>';
                echo  "<li class='list'>".$val."</li><br>";


                }
        }


        public function WeeklyStatus($day){


                $query = $this->db->query("SELECT $day FROM " . DB_PREFIX . "weekly_day_status");
                $status = $query->row["$day"];
                if($status=='OPEN'){
                                    
                                    $status="CLOSE";
                }
                else{

                        $status="OPEN";
                
                }


                $this->db->query("UPDATE " . DB_PREFIX . "weekly_day_status SET $day='".$status."'");     

                if($status=="OPEN"){

                $class= "btn-primary";

                }else{

                $class = "btn-danger";
                }
                echo $day."_res_".$status."_".$class;
        }
        
        
        public function SingleStatus($status,$id){
           
             if($status=='ENABLE'){
              
                 $status = 'DISABLE';
             }else{
              
              $status = 'ENABLE';
              
             }
             
         
              $this->db->query("UPDATE " . DB_PREFIX . "one_year_slot SET Status='".$status."' WHERE one_slot_id='".$id."'");
              
              echo $status."_".$id;
             
        }

        public function SelectWeek(){

                $data = array();
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "weekly_day_status");


                foreach ($query->rows as $key=>$res) {


                $data['days'] = $res;


                }

                return $data;
        }





        public  function excludeDates(){


                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exclude_dates");

                foreach ($query->rows as $result) {

                $data['exc_date'][] = $result['exc_date_time_to'];
                $data['exc_time'][] = $result['exc_date_time_from'];


                }

                return $data;


        }

        public function checkExcludeDate(){





                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "exclude_dates");



                foreach ($query->rows as $result) {


                $to = $result['exc_date_time_to'] ; 
                $from = $result['exc_date_time_from'];

                $q = " DELETE FROM 
                " . DB_PREFIX . "one_year_slot
                WHERE to_date_time BETWEEN '".$from."' AND '".$to."'";

                $Slots = $this->db->query($q);

                $q1 = " DELETE FROM 
                " . DB_PREFIX . "one_year_slot
                WHERE from_date_time BETWEEN '".$from."' AND '".$to."'";

                $Slots = $this->db->query($q1); 


        }

        }  
       


        public function getSlot() {


                $setting_data = array();

                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "slot_setting");

                foreach ($query->rows as $result) {

                $setting_data['start_time'] = $result['start_time'];
                $setting_data['end_time'] = $result['end_time'];
                $setting_data['intervals'] = $result['intervals'];
                $setting_data['delivery_starts'] = $result['delivery_starts'];
                $setting_data['showSlots'] = $result['show_slots'];
                $setting_data['order_per_slot'] = $result['order_per_slot'];
                $setting_data['time_format'] = $result['time_format'];
                $setting_data['date_range_from'] = $result['range_date_from'];
                $setting_data['date_range_to'] = $result['range_date_to'];
                $setting_data['date_format'] = $result['date_format'];
                $setting_data['time_zone'] = $result['time_zone'];


                }

                return $setting_data;



        }


        public function SaveIntervelSettings($start_time,$end_time,$Intervals,$lead_time,$SaveIntervelSettings,$order_per_slot,$time_format , $date_range_from , $date_range_to , $date_format , $time_zone){


                $Slots = $this->db->query("SELECT * FROM " . DB_PREFIX . "slot_setting");

                if($Slots->num_rows>0)
                {

                $this->db->query("UPDATE " . DB_PREFIX . "slot_setting SET end_time='".$end_time."' , intervals='".$Intervals."', delivery_starts='".$lead_time."', start_time='".$start_time."' , show_slots='".$SaveIntervelSettings."' , order_per_slot='".$order_per_slot."' , time_format='".$time_format."' , "
                        . "range_date_from ='".$date_range_from."' , range_date_to='".$date_range_to."' , time_zone='".$time_zone."' , date_format='".$date_format."' WHERE slot_id=1");

                }else{

                $this->db->query("insert into " . DB_PREFIX . "slot_setting(`end_time`,`intervals`,`delivery_starts`,`start_time`,`show_slots`,"
                        . "`order_per_slot` ,`time_format` ,`date_range_from` , `date_range_to`, `date_format` ,`time_zone` ) "
                . "values ( '".$end_time."','".$Intervals."','".$lead_time."','".$start_time."','".$SaveIntervelSettings."','".$order_per_slot."' , '".$time_format."' , '".$date_range_from."' , '".$date_range_to."' , '".$date_format."' , '".$time_zone."' )");
                }
 
        }

        public function editSetting($code, $data, $store_id = 0) {
               $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");


        foreach ($data as $key => $value) {
                if (substr($key, 0, strlen($code)) == $code) {
                if (!is_array($value)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
                }
                }
                }
        }

        public function deleteSetting($code, $store_id = 0) {
               $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        }

        public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
                if (!is_array($value)) {
                $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
                } else {
                $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
                }
        }


        public function RemoveWeekDays(){


                $start = strtotime("today"); // your start/end dates here
                $end = strtotime("today + 1 years");


                $data = $this->db->query("SELECT * FROM " . DB_PREFIX . "weekly_day_status");

                foreach ($data->rows as $val):


                if($val['w_sat']=='CLOSE'){
                $this->findDate('Saturday');

                }

                if($val['w_sun']=='CLOSE'){
                $this->findDate('Sunday');

                }
                if($val['w_mon']=='CLOSE'){
                $this->findDate('Monday');

                }
                if($val['w_tue']=='CLOSE'){
                $this->findDate('Tuesday');

                }
                if($val['w_wed']=='CLOSE'){
                $this->findDate('Wednesday');

                }
                if($val['w_thu']=='CLOSE'){
                $this->findDate('Thursday');

                }
                if($val['w_fri']=='CLOSE'){
                $this->findDate('Friday');

                }


                endforeach;


        }

        public function findDate($date){

                $start = strtotime("today"); // your start/end dates here
                $end = strtotime("today + 1 years");

                $friday = strtotime($date, $start);
                while($friday <= $end) {

                $date =  date("Y-m-d", $friday);
                $this->RemoveDate($date);
                $friday = strtotime("+1 weeks", $friday);
                }

        }


        public function RemoveDate($date){

               $this->db->query("DELETE FROM " . DB_PREFIX . "one_year_slot WHERE date = '".$date."'");


        }
        
          public function GeneratedSlots(){

               $query = $this->db->query("SELECT  p.one_slot_id,p.slot_timing, p.max_no,p.Status,
                (SELECT COUNT(*) FROM " . DB_PREFIX . "delivery_time v WHERE times LIKE p.slot_timing ) as count
                FROM    " . DB_PREFIX . "one_year_slot p");
               return $query;
               
        }
        
        
        
        public function SaveMax($max,$id){
            
             $this->db->query("UPDATE " . DB_PREFIX . "one_year_slot SET max_no='".$max."' WHERE one_slot_id='".$id."'");
            
        }

        

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
                . "`Status` varchar (1000) DEFAULT 'ENABLE' ,"
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
             
            
        $this->db->query("insert into " . DB_PREFIX . "slot_setting(`slot_id`,`end_time`,`intervals`,`delivery_starts`,`start_time`,`show_slots`,`order_per_slot`,`time_format` , `range_date_from` , `range_date_to` , `date_format` , `time_zone`) values ( NULL,'22:00:00','2','2','01:00:00','10','1','12 ( AM/PM and O clock )','2015-11-04','2015-11-07','yy-mm-dd','Asia/karachi')");
        
        }
        
        
             
    }    
        

    }
