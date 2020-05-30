<?php 
class ControllerExtensionModuleFilterOptions extends Controller {
    public function index() {
        
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
        } else {
            $parts = array();
        }
        
        $category_id = end($parts);
        
        if($category_id){
            
            if (isset($this->request->get['option_value'])) {
                $option_values = $this->request->get['option_value'];
            }else $option_values = array();
                
            
            $hidden = array();
            
            foreach (array('route','path','sort','order','limit') as $value) {
                if (isset($this->request->get[$value])) {
                    $hidden[] = array('name'=>$value,'value'=>$this->request->get[$value]);
                }                
            }
            $data['action'] = $this->url->link('/');
            
             $url ='';
            if (isset($this->request->get['manufacturer'])) {
                $url.='&manufacturer=' . $this->request->get['manufacturer'];
            }
             if (isset($this->request->get['pr'])) {
                $url.='&pr=' . $this->request->get['pr'];
            }
            //$data['action'] = $this->url->link('/');
            $data['optaction'] = str_replace('&amp;', '&', $this->url->link('product/category&path=' . $category_id . $url));

            
            $data['hiddens'] = $hidden;
           
            $this->load->language('extension/module/filter_options');
            $this->load->model('extension/module/filter_options');
            
            $option = $this->model_extension_module_filter_options->getCategoryOptions($category_id);
            $data['options'] = $option;
            $data['options_set']=  ($option_values);
            $data['option_count']=count($option);
            return $this->load->view('extension/module/filter_options', $data);
        }
    }
}  
?>