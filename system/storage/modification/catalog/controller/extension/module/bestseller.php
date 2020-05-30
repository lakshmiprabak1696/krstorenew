<?php
class ControllerExtensionModuleBestSeller extends Controller {
	public function index($setting) {

        /*=======Show Themeconfig=======*/
        $this->load->model('extension/soconfig/general');
        $this->load->language('extension/soconfig/soconfig');
        $data['objlang'] = $this->language;
        $data['soconfig'] = $this->soconfig;
        $data['theme_directory'] = $this->config->get('theme_default_directory');
        $data['our_url'] = $this->registry->get('url');
		/*=======url query parameters=======*/ 
		 $data['url_sidebarsticky'] = isset($this->request->get['sidebarsticky']) ? $this->request->get['sidebarsticky'] : '' ; 
		 $data['url_cartinfo'] = isset($this->request->get['cartinfo']) ? $this->request->get['cartinfo'] : '' ; 
		 $data['url_thumbgallery'] = isset($this->request->get['thumbgallery']) ? $this->request->get['thumbgallery'] : '' ; 
		 $data['url_listview'] = isset($this->request->get['listview']) ? $this->request->get['listview'] : '' ; 
		 $data['url_asidePosition'] = isset($this->request->get['asidePosition']) ? $this->request->get['asidePosition'] : '' ; 
		 $data['url_asideType'] = isset($this->request->get['asideType']) ? $this->request->get['asideType'] : '' ; 
		 $data['url_layoutbox'] = isset($this->request->get['layoutbox']) ? $this->request->get['layoutbox'] : '' ; 
		
		$this->load->language('extension/module/bestseller');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

 
		 /*======Image Galleries=======*/ 
		 $data['image_galleries'] = array(); 
		 $image_galleries = $this->model_catalog_product->getProductImages($result['product_id']); 
		 foreach ($image_galleries as $image_gallery) { 
		 $data['image_galleries'][] = array( 
		 'cart' => $this->model_tool_image->resize($image_gallery['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width')), 
		 'thumb' => $this->model_tool_image->resize($image_gallery['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width')) 
		 ); 
		 } 
		 $data['first_gallery'] = array( 
		 'cart' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width')), 
		 'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width')) 
		 ); 
		 /*======Check New Label=======*/ 
		 if ((float)$result['special']) $discount = '-'.round((($result['price'] - $result['special'])/$result['price'])*100, 0).'%'; 
		 else $discount = false; 
		 
		 $sold = 0; 
		 if($this->model_extension_soconfig_general->getUnitsSold($result['product_id'])){ 
		 $sold = $this->model_extension_soconfig_general->getUnitsSold($result['product_id']); 
		 } 
		 
		 $data['orders'] = sprintf($this->language->get('text_product_orders'),$sold); 
		 $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$result['reviews']); 
		 
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
'special_end_date' => $this->model_extension_soconfig_general->getDateEnd($result ['product_id']), 
		 'image_galleries' => $data['image_galleries'], 
		 'first_gallery' => $data['first_gallery'], 
		 'discount' => $discount, 
		 'stock_status' => $result['stock_status'], 
		 'orders' => $data['orders'], 
		 'reviews' => $data['reviews'], 
		 'href_quickview' => htmlspecialchars_decode($this->url->link('extension/soconfig/quickview&product_id='.$result['product_id'] )), 
		 'quantity' => $result['quantity'],
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			return $this->load->view('extension/module/bestseller', $data);
		}
	}
}
