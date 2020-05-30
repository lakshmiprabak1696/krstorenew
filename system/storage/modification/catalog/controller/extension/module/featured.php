<?php
class ControllerExtensionModuleFeatured extends Controller {
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
		
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}

 
		 /*======Image Galleries=======*/ 
		 $data['image_galleries'] = array(); 
		 $image_galleries = $this->model_catalog_product->getProductImages($product_info['product_id']); 
		 foreach ($image_galleries as $image_gallery) { 
		 $data['image_galleries'][] = array( 
		 'cart' => $this->model_tool_image->resize($image_gallery['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width')), 
		 'thumb' => $this->model_tool_image->resize($image_gallery['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width')) 
		 ); 
		 } 
		 $data['first_gallery'] = array( 
		 'cart' => $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width')), 
		 'thumb' => $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width')) 
		 ); 
		 /*======Check New Label=======*/ 
		 if ((float)$product_info['special']) $discount = '-'.round((($product_info['price'] - $product_info['special'])/$product_info['price'])*100, 0).'%'; 
		 else $discount = false; 
		 
		 $sold = 0; 
		 if($this->model_extension_soconfig_general->getUnitsSold($product_info['product_id'])){ 
		 $sold = $this->model_extension_soconfig_general->getUnitsSold($product_info['product_id']); 
		 } 
		 
		 $data['orders'] = sprintf($this->language->get('text_product_orders'),$sold); 
		 $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']); 
		 
					$data['products'][] = array(
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
'special_end_date' => $this->model_extension_soconfig_general->getDateEnd($product_info['product_id']), 
		 'image_galleries' => $data['image_galleries'], 
		 'first_gallery' => $data['first_gallery'], 
		 'discount' => $discount, 
		 'stock_status' => $product_info['stock_status'], 
		 'orders' => $data['orders'], 
		 'reviews' => $data['reviews'], 
		 'href_quickview' => htmlspecialchars_decode($this->url->link('extension/soconfig/quickview&product_id='.$product_info['product_id'] )), 
		 'quantity' => $product_info['quantity'],
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/module/featured', $data);
		}
	}
}