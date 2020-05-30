<?php
 
require_once(DIR_SYSTEM . 'engine/restcontroller.php');

class ControllerRestWishlist extends RestController
{

    public function wishlist()
    {

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            //get wishlist
            $this->loadWishlist();
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //add item to wishlist
            if (isset($this->request->get['id']) && ctype_digit($this->request->get['id'])) {
                $this->addWishlist($this->request->get['id']);
            } else {
                $this->statusCode = 400;
                $this->json['error'][] = 'Invalid id';
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //delete item from wishlist
            if (isset($this->request->get['id']) && ctype_digit($this->request->get['id'])) {
                $this->deleteWishlist($this->request->get['id']);
            } else {
                $this->statusCode = 400;
                $this->json['error'][] = 'Invalid id';
            }
        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("GET", "POST", "DELETE");
        }

        return $this->sendResponse();

    }

    public function loadWishlist()
    {


        $this->load->language('account/wishlist');

        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $wishlist = array();

        if ($this->opencartVersion < 2100 || !$this->customer->isLogged()) {
            if (isset($this->session->data['wishlist'])) {
                $wishlist = $this->session->data['wishlist'];
            }
        } else {
            if ($this->customer->isLogged()) {
                $this->load->model('account/wishlist');
                $list = $this->model_account_wishlist->getWishlist();
                foreach ($list as $item) {
                    $wishlist[$item['product_id']] = $item['product_id'];
                }
            }
        }

        foreach ($wishlist as $key => $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_shopping_cart_rest_api_image_width'), $this->config->get('config_shopping_cart_rest_api_image_height'));
                } else {
                    $image = "";
                }

                if ($product_info['quantity'] <= 0) {
                    $stock = $product_info['stock_status'];
                } elseif ($this->config->get('config_stock_display')) {
                    $stock = $product_info['quantity'];
                } else {
                    $stock = $this->language->get('text_instock');
                }

                if ( ($this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                    
                    $this->log->write('WOOOWWOWOWO');
                    $this->log->write($product_info['price']);
                    $this->log->write($product_info['tax_class_id']);
                    $this->log->write($this->config->get('config_tax'));
                    $this->log->write($this->currency->getRestCurrencyCode());
                    
                    
                    
                    $prod_price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), "USD",'', false);
                } else {
                    $this->log->write('ACHO');
                    $prod_price = 0;
                }

                if ((float)$product_info['special']) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->currency->getRestCurrencyCode());
                } else {
                    $special = 0;
                }

                $product_seo_url = $this->model_catalog_product->getProductSeoUrls($product_info['product_id']);
				$options=array(); 
                 //options
                foreach ($this->model_catalog_product->getProductOptions($product_info['product_id']) as $option) {
                    $product_option_value_data = array();
                    foreach ($option['product_option_value'] as $option_value) {
                        if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                            if ((($this->customer->isLogged() && $this->config->get('config_customer_price')) || !$this->config->get('config_customer_price')) && (float) $option_value['price']) {
                                $price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), "USD", '', false);
                                $price_excluding_tax = $this->currency->format($option_value['price'], "USD", '', false);
                                $price_excluding_tax_formated = $this->currency->format($option_value['price'], "USD");
                                $price_formated = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), "USD");
                            } else {
                                $price = 0;
                                $price_excluding_tax = 0;
                                $price_excluding_tax_formated = 0;
                                $price_formated = 0;
                            }
                            if (isset($option_value['image']) && file_exists(DIR_IMAGE . $option_value['image'])) {
                                $option_image = $this->model_tool_image->resize($option_value['image'], $this->config->get('config_shopping_cart_rest_api_image_width'), $this->config->get('config_shopping_cart_rest_api_image_height'));
                            } else {
                                $option_image = $this->model_tool_image->resize('no_image.png', $this->config->get('config_shopping_cart_rest_api_image_width'), $this->config->get('config_shopping_cart_rest_api_image_height'));
                            }

                            $product_option_value_data[] = array(
                                'image' => $option_image,
                                'price' => $price,
                                'price_formated' => $price_formated,
                                'price_excluding_tax' => $price_excluding_tax,
                                'price_excluding_tax_formated' => $price_excluding_tax_formated,
                                'price_prefix' => $option_value['price_prefix'],
                                'product_option_value_id' => (int) $option_value['product_option_value_id'],
                                'option_value_id' => (int) $option_value['option_value_id'],
                                'name' => $option_value['name'],
                                'quantity' => !empty($option_value['quantity']) ? (int) $option_value['quantity'] : 0
                            );
                        }
                    }

                    $options[] = array(
                        'product_option_id' => (int) $option['product_option_id'],
                        'option_value' => $product_option_value_data,
                        'option_id' => (int) $option['option_id'],
                        'name' => $option['name'],
                        'type' => $option['type'],
                        'value' => $option['value'],
                        'required' => $option['required']
                    );
                }


                
                $this->json["data"][] = array(
                    'product_id' => $product_info['product_id'],
                    'quantity' => $product_info['quantity'],
                    'product_seo_url' => $product_seo_url,
                    'thumb' => $image,
                    'name' => $product_info['name'],
                    'model' => $product_info['model'],
                    'stock' => $stock,
                    'price' => $prod_price,
                    'special' => $special,
                    'option' => $options
                );
            } else {

                if ($this->opencartVersion < 2100 || !$this->customer->isLogged()) {
                    unset($this->session->data['wishlist'][$key]);
                } else {
                    if ($this->customer->isLogged()) {
                        $this->model_account_wishlist->deleteWishlist($product_id);
                    }
                }
            }
        }

        if($this->includeMeta) {
            $this->response->addHeader('X-Total-Count: ' . count($this->json['data']));
            $this->response->addHeader('X-Pagination-Limit: '.count($this->json['data']));
            $this->response->addHeader('X-Pagination-Page: 1');
//            $data = $this->json['data'];
//
//            $this->json['data'] = array(
//                'totalrowcount' => count($data),
//                'pagenumber'    => 1,
//                'pagesize'      => count($data),
//                'items'         => $data
//            );
        }
    }


    public function addWishlist($productId)
    {

        $this->load->language('account/wishlist');

        if (!empty($productId)) {
            $product_id = $productId;
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            if ($this->opencartVersion < 2100 || !$this->customer->isLogged()) {
                if (!isset($this->session->data['wishlist'])) {
                    $this->session->data['wishlist'] = array();
                }
                $this->session->data['wishlist'][] = $product_id;
                $this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

                if ($this->opencartVersion >= 2200){
                    $this->statusCode = 400;
                    $this->json['error'][] = 'You must login or create an account to save item to your wish list';
                }

            } else {
                if ($this->customer->isLogged()) {
                    $this->load->model('account/wishlist');
                    $this->model_account_wishlist->addWishlist($product_id);
                }
            }
        } else {
            $this->statusCode = 404;
            $this->json['error'][] = 'Product not found';
        }
    }


    public function deleteWishlist($productId)
    {

        $this->load->language('account/wishlist');

        if ($this->opencartVersion < 2100 || !$this->customer->isLogged()) {
            $key = false;
            if (isset($this->session->data['wishlist'])) {
                $key = array_search($productId, $this->session->data['wishlist']);
            }
            if ($key !== false) {
                unset($this->session->data['wishlist'][$key]);
            } else {
                $this->json["error"][] = "Product not found!";
            }
        } else {
            if ($this->customer->isLogged()) {
                $this->load->model('account/wishlist');
                $this->model_account_wishlist->deleteWishlist($productId);
            }
        }

    }
}