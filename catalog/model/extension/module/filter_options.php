<?php 
class ModelExtensionModuleFilterOptions extends Model{
    
    public function getCategoryOptions($category_id) {
        $sql = '
            SELECT DISTINCT od.option_id, od.name, o.type 
            	FROM `'. DB_PREFIX .'product_option_value` pov
                INNER JOIN `'. DB_PREFIX .'product_to_category` pc on pov.`product_id` = pc.`product_id`
                INNER JOIN `'. DB_PREFIX .'option_description` od on od.`option_id` = pov.`option_id`
            	INNER JOIN `'. DB_PREFIX .'category_path` cp on cp.category_id = pc.category_id
                INNER JOIN `'. DB_PREFIX .'option` o ON o.option_id = pov.option_id
            WHERE 
        	`pov`.`quantity` > 0
            and cp.path_id = '.(int)$category_id;
        
        $query = $this->db->query($sql);

        $options = array();
        foreach ($query->rows as $result) {
            $sql = 'SELECT distinct od.option_value_id,od.name FROM `'. DB_PREFIX .'option_value_description` od
                    INNER JOIN `'. DB_PREFIX .'product_option_value` pov ON pov.option_value_id = od.option_value_id
                    INNER JOIN `'. DB_PREFIX .'product_to_category` pc on pov.`product_id` = pc.`product_id`
            	    INNER JOIN `'. DB_PREFIX .'category_path` cp on cp.category_id = pc.category_id
                    WHERE od.option_id = '.$result['option_id'].'
                    and language_id ='. (int)$this->config->get('config_language_id')
                    .' and `pov`.`quantity` > 0'
                    .' and cp.path_id = '.(int)$category_id
                    .' order by od.name';
            $option_values_query = $this->db->query($sql);
            
            $option_values = array();
            foreach ($option_values_query->rows as $value) {
                $option_values[]=array('option_value_id'=>$value['option_value_id'],'name'=>$value['name']);
            }
            
            $options[] = array(
                'option_id' =>$result['option_id'],
                'name'=>$result['name'],
                'type'=>$result['type'],
                'option_values'=>$option_values
            );
            
        }
        
        return $options;
    }
    
}
?>