<?php

require_once(DIR_SYSTEM . 'engine/restcontroller.php');

class ControllerRestWallet extends RestController {

    public function save() {
        $this->load->model('setting/setting');

        $this->checkPlugin();
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $wallet_status = $this->model_setting_setting->getSettingValue('module_wallet_status', $store_id);
        if ($wallet_status == 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $post = $this->getPost();
                $this->saveOrderToDatabase($post);
            } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $post = $this->getPost();
                $this->confirmOrder($post);
            } else {
                $this->statusCode = 405;
                $this->allowedHeaders = array("PUT", "POST");
            }
        } else {
            $this->statusCode = 400;
            $this->json['error'][] = "Must Enable Wallet Extenstion";
        }

        return $this->sendResponse();
    }

    public function confirmOrder($post) {

        $this->checkPlugin();

        // Validate minimum quantity requirements.
        $products = $this->getProducts($post);

        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
                $price = $product['price'];
            }

            if ($product['minimum'] > $product_total) {
                $this->json["error"][] = "Product minimum is greater than product total";
                break;
            }
        }


        if (empty($this->json['error'])) {
            $order_data = array();
            $totals = array();

            $order_data['totals'] = array();

            $totals[] = array(
                "title" => 'Total',
                'code' => 'total',
                'sort_order' => 1,
                'value' => $price
            );

            $total_data = array(
                'totals' => $price,
                'taxes' => '',
                'total' => $price
            );

            $order_data['totals'] = $totals;


            $this->load->language('checkout/checkout');

            $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
            $order_data['store_id'] = $this->config->get('config_store_id');
            $order_data['store_name'] = $this->config->get('config_name');

            if ($this->request->server['HTTPS']) {
                $order_data['store_url'] = HTTPS_SERVER;
            } else {
                $order_data['store_url'] = HTTP_SERVER;
            }

            $payment_address = array();
            if ($this->customer->isLogged()) {
                $this->load->model('account/customer');

                $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

                $order_data['customer_id'] = $this->customer->getId();
                $order_data['customer_group_id'] = $customer_info['customer_group_id'];
                $order_data['firstname'] = $customer_info['firstname'];
                $order_data['lastname'] = $customer_info['lastname'];
                $order_data['email'] = $customer_info['email'];
                $order_data['telephone'] = $customer_info['telephone'];
                $order_data['fax'] = $customer_info['fax'];

                $order_data['custom_field'] = json_decode($customer_info['custom_field'], true);

                $this->load->model('account/address');


                $payment_address = $this->model_account_address->getAddress($this->customer->getAddressId());

                if (empty($payment_address)) {
                    $payment_address = $this->model_account_address->getAddresses();
                    if (count($payment_address) > 0) {
                        $index = max(array_keys($payment_address));
                        $payment_address = $payment_address[$index];
                    } else {
                        $payment_address = '';
                    }
                }
            }

            if (empty($this->json['error'])) {
                if (!empty($payment_address)) {
                    $order_data['payment_firstname'] = $payment_address['firstname'];
                    $order_data['payment_lastname'] = $payment_address['lastname'];
                    $order_data['payment_phone'] = $payment_address['phone'];
                    $order_data['payment_company'] = $payment_address['company'];
                    $order_data['payment_address_1'] = $payment_address['address_1'];
                    $order_data['payment_address_2'] = $payment_address['address_2'];
                    $order_data['payment_city'] = $payment_address['city'];
                    $order_data['payment_postcode'] = $payment_address['postcode'];
                    $order_data['payment_zone'] = $payment_address['zone'];
                    $order_data['payment_zone_id'] = $payment_address['zone_id'];
                    $order_data['payment_country'] = $payment_address['country'];
                    $order_data['payment_country_id'] = $payment_address['country_id'];
                    $order_data['payment_address_format'] = $payment_address['address_format'];
                    $order_data['payment_custom_field'] = $payment_address['custom_field'];

                    $order_data['shipping_firstname'] = $payment_address['firstname'];
                    $order_data['shipping_lastname'] = $payment_address['lastname'];
                    $order_data['shipping_phone'] =  $payment_address['phone'];
                    $order_data['shipping_company'] =  $payment_address['company'];
                    $order_data['shipping_address_1'] =  $payment_address['address_1'];
                    $order_data['shipping_address_2'] = $payment_address['address_2'];
                    $order_data['shipping_city'] = $payment_address['city'];
                    $order_data['shipping_postcode'] =$payment_address['postcode'];
                    $order_data['shipping_zone'] = $payment_address['zone'];
                    $order_data['shipping_zone_id'] =$payment_address['zone_id'];
                    $order_data['shipping_country'] = $payment_address['country'];
                    $order_data['shipping_country_id'] = $payment_address['country_id'];
                    $order_data['shipping_address_format'] = $payment_address['address_format'];
                    $order_data['payment_custom_field'] = $payment_address['custom_field'];
                    $order_data['shipping_method'] = '';
                    $order_data['shipping_code'] = '';
                } else {
                    $order_data['shipping_firstname'] = '';
                    $order_data['shipping_lastname'] = '';
                    $order_data['shipping_company'] = '';
                    $order_data['shipping_address_1'] = '';
                    $order_data['shipping_address_2'] = '';
                    $order_data['shipping_city'] = '';
                    $order_data['shipping_postcode'] = '';
                    $order_data['shipping_zone'] = '';
                    $order_data['shipping_zone_id'] = '';
                    $order_data['shipping_country'] = '';
                    $order_data['shipping_country_id'] = '';
                    $order_data['shipping_address_format'] = '';
                    $order_data['shipping_custom_field'] = array();
                    $order_data['shipping_method'] = '';
                    $order_data['shipping_code'] = '';
                    
                     $order_data['payment_firstname'] = '';
                    $order_data['payment_lastname'] = '';
                    $order_data['payment_phone'] =  '';
                    $order_data['payment_company'] = '';
                    $order_data['payment_address_1'] =  '';
                    $order_data['payment_address_2'] =  '';
                    $order_data['payment_city'] =  '';
                    $order_data['payment_postcode'] =  '';
                    $order_data['payment_zone'] =  '';
                    $order_data['payment_zone_id'] =  '';
                    $order_data['payment_country'] =  '';
                    $order_data['payment_country_id'] = '';
                    $order_data['payment_address_format'] =  '';
                    $order_data['payment_custom_field'] =   array();

                }
                
                $order_data['payment_method'] =$post['payment_method'];
                $order_data['payment_code'] =$post['payment_code'];

                 
                $order_data['products'] = array();

                foreach ($this->getProducts($post) as $product) {
                    $option_data = array();


                    $order_data['products'][] = array(
                        'product_id' => $product['product_id'],
                        'name' => $product['name'],
                        'model' => $product['model'],
                        'option' => $option_data,
                        'download' => $product['download'],
                        'quantity' => $product['quantity'],
                        'subtract' => $product['subtract'],
                        'price' => $product['price'],
                        'total' => $product['total'],
                        'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
                        'reward' => $product['reward']
                    );
                }

                // Gift Voucher
                $order_data['vouchers'] = array();


                $order_data['comment'] = isset($this->session->data['comment']) ? $this->session->data['comment'] : "";
                $order_data['total'] = $total_data['total'];


                $order_data['affiliate_id'] = 0;
                $order_data['commission'] = 0;
                $order_data['marketing_id'] = 0;
                $order_data['tracking'] = '';


                $order_data['language_id'] = $this->config->get('config_language_id');
                $order_data['currency_id'] = $this->currency->getId($this->currency->getRestCurrencyCode());
                $order_data['currency_code'] = $this->currency->getRestCurrencyCode();
                $order_data['currency_value'] = $this->currency->getValue($this->currency->getRestCurrencyCode());

                $order_data['ip'] = $this->request->server['REMOTE_ADDR'];

                if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                    $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
                } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                    $order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
                } else {
                    $order_data['forwarded_ip'] = '';
                }

                if (isset($this->request->server['HTTP_USER_AGENT'])) {
                    $order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
                } else {
                    $order_data['user_agent'] = '';
                }

                if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                    $order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
                } else {
                    $order_data['accept_language'] = '';
                }

                $this->load->model('checkout/order');

                $this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);


                $data['products'] = array();

                foreach ($this->getProducts($post) as $product) {
                    $option_data = array();

                    foreach ($product['option'] as $option) {
                        if ($option['type'] != 'file') {
                            $value = $option['value'];
                        } else {
                            $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                            if ($upload_info) {
                                $value = $upload_info['name'];
                            } else {
                                $value = '';
                            }
                        }

                        $option_data[] = array(
                            'name' => $option['name'],
                            'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                        );
                    }

                    $recurring = '';

                    if ($product['recurring']) {
                        $frequencies = array(
                            'day' => $this->language->get('text_day'),
                            'week' => $this->language->get('text_week'),
                            'semi_month' => $this->language->get('text_semi_month'),
                            'month' => $this->language->get('text_month'),
                            'year' => $this->language->get('text_year'),
                        );

                        if ($product['recurring']['trial']) {
                            $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                        }

                        if ($product['recurring']['duration']) {
                            $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                        } else {
                            $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                        }
                    }

                    //product image
                    $this->load->model('tool/image');

                    if (isset($product['image']) && !empty($product['image']) && file_exists(DIR_IMAGE . $product['image'])) {
                        $image = $this->model_tool_image->resize($product['image'], $this->config->get('config_shopping_cart_rest_api_image_width'), $this->config->get('config_shopping_cart_rest_api_image_height'));
                        $original_image = $this->urlPrefix . 'image/' . $product['image'];
                    } else {
                        $image = $this->model_tool_image->resize('no_image.png', $this->config->get('config_shopping_cart_rest_api_image_width'), $this->config->get('config_shopping_cart_rest_api_image_height'));
                        $original_image = $this->urlPrefix . 'image/no_image.png';
                    }

                    $data['products'][] = array(
                        'key' => isset($product['cart_id']) ? $product['cart_id'] : (isset($product['key']) ? $product['key'] : ""),
                        'product_id' => $product['product_id'],
                        'name' => $product['name'],
                        'image' => $image,
                        'original_image' => $original_image,
                        'model' => $product['model'],
                        'option' => $option_data,
                        'recurring' => $recurring,
                        'quantity' => $product['quantity'],
                        'subtract' => $product['subtract'],
                        'price' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->currency->getRestCurrencyCode()),
                        'total' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->currency->getRestCurrencyCode()),
                        'href' => ""
                    );
                }

                // Gift Voucher
                $data['vouchers'] = array();

                $data['totals'] = array();

                foreach ($order_data['totals'] as $total) {
                    $data['totals'][] = array(
                        'title' => $total['title'],
                        'text' => $this->currency->format($total['value'], $this->currency->getRestCurrencyCode()),
                    );
                }


                $data['order_id'] = $this->session->data['order_id'];
                $this->session->data['order_total'] = $price;

                $this->json["order_id"] = $data['order_id'];
                $this->json["data"] = $data;
            }
        }
    }

    public function saveOrderToDatabase($post) {

        $this->checkPlugin();

        $this->load->model('checkout/order');
        $this->load->model('account/order');

        if (isset($this->session->data['order_id'])) {
            $order_status_id = 1;

            $cod_status = $this->config->get('payment_cod_order_status_id');

            if (!empty($cod_status)) {
                $order_status_id = $cod_status;
            }

            if (!isset($this->session->data['payment_method']) || empty($this->session->data['payment_method'])) {
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, isset($this->session->data['comment']) ? $this->session->data['comment'] : '');
            } else {
                $status = $this->model_account_order->getOrderStatusById($this->session->data['order_id']);
                if (empty($status)) {
                    $defaultStatus = $this->config->get("payment_" . $this->session->data['payment_method']['code'] . '_order_status_id');
                    $defaultStatus = is_null($defaultStatus) ? $order_status_id : $defaultStatus;

                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $defaultStatus, isset($this->session->data['comment']) ? $this->session->data['comment'] : '');
                }
            }

            if (isset($this->session->data['order_id'])) {
                $this->json["data"]["order_id"] = $this->session->data['order_id'];

                $wallet_data = array(
                    "customer_id" => $this->customer->getId(),
                    'order_id' => $this->session->data['order_id'],
                    'order_product_id' => $post['product_id'],
                    'amount' => $this->session->data['order_total'],
                    'type' => 'topup'
                );

                $this->load->model('extension/module/wallet');

                $walletId = $this->model_extension_module_wallet->save($wallet_data);

                $this->json["data"]["wallet_id"] = $walletId;

                unset($this->session->data['order_id']);
                unset($this->session->data['order_total']);
            }
        } else {
            $this->statusCode = 400;
            $this->json['error'][] = "No order in session";
        }
    }

    public function getProducts($post) {
        $product_data = array();
        $option = array();


        $product_det[] = array(
            "product_id" => $post['product_id'],
            "quantity" => 1,
            "option" => $option
        );


        foreach ($product_det as $cart) {
            $stock = true;

            $product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int) $this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int) $cart['product_id'] . "' AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND p.date_available <= NOW()  ");

            if ($product_query->num_rows && ($cart['quantity'] > 0)) {
                $option_price = 0;
                $option_points = 0;
                $option_weight = 0;

                $option_data = array();


                $price = $product_query->row['price'];

                // Product Discounts
                $discount_quantity = 0;

                foreach ($product_det as $cart_2) {
                    if ($cart_2['product_id'] == $cart['product_id']) {
                        $discount_quantity += $cart_2['quantity'];
                    }
                }

                $product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int) $cart['product_id'] . "' AND customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int) $discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

                if ($product_discount_query->num_rows) {
                    $price = $product_discount_query->row['price'];
                }

                // Product Specials
                $product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int) $cart['product_id'] . "' AND customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

                if ($product_special_query->num_rows) {
                    $price = $product_special_query->row['price'];
                }

                // Reward Points
                $product_reward_query = $this->db->query("SELECT points FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int) $cart['product_id'] . "' AND customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "'");

                if ($product_reward_query->num_rows) {
                    $reward = $product_reward_query->row['points'];
                } else {
                    $reward = 0;
                }

                // Downloads
                $download_data = array();

                $download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download p2d LEFT JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int) $cart['product_id'] . "' AND dd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

                foreach ($download_query->rows as $download) {
                    $download_data[] = array(
                        'download_id' => $download['download_id'],
                        'name' => $download['name'],
                        'filename' => $download['filename'],
                        'mask' => $download['mask']
                    );
                }

                // Stock
                if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < $cart['quantity'])) {
                    $stock = false;
                }


                $recurring = false;


                $product_data[] = array(
                    'product_id' => $product_query->row['product_id'],
                    'name' => $product_query->row['name'],
                    'model' => $product_query->row['model'],
                    'shipping' => $product_query->row['shipping'],
                    'image' => $product_query->row['image'],
                    'option' => $option_data,
                    'download' => $download_data,
                    'quantity' => $cart['quantity'],
                    'minimum' => $product_query->row['minimum'],
                    'subtract' => $product_query->row['subtract'],
                    'stock' => $stock,
                    'price' => ($price + $option_price),
                    'total' => ($price + $option_price) * $cart['quantity'],
                    'reward' => $reward * $cart['quantity'],
                    'points' => ($product_query->row['points'] ? ($product_query->row['points'] + $option_points) * $cart['quantity'] : 0),
                    'tax_class_id' => $product_query->row['tax_class_id'],
                    'weight' => ($product_query->row['weight'] + $option_weight) * $cart['quantity'],
                    'weight_class_id' => $product_query->row['weight_class_id'],
                    'length' => $product_query->row['length'],
                    'width' => $product_query->row['width'],
                    'height' => $product_query->row['height'],
                    'length_class_id' => $product_query->row['length_class_id'],
                    'recurring' => $recurring
                );
            }
        }

        return $product_data;
    }

    public function getTaxes($post) {
        $tax_data = array();

        foreach ($this->getProducts($post) as $product) {
            if ($product['tax_class_id']) {
                $tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);

                foreach ($tax_rates as $tax_rate) {
                    if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
                        $tax_data[$tax_rate['tax_rate_id']] = ($tax_rate['amount'] * $product['quantity']);
                    } else {
                        $tax_data[$tax_rate['tax_rate_id']] += ($tax_rate['amount'] * $product['quantity']);
                    }
                }
            }
        }

        return $tax_data;
    }

    public function getCustomerWalletAmount() {
        $this->load->model('setting/setting');

        $this->checkPlugin();
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->load->model('extension/module/wallet');
        $wallet_status = $this->model_setting_setting->getSettingValue('module_wallet_status', $store_id);
        if ($wallet_status == 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $customer_id = $this->customer->getId();
                $wallet_amount = $this->model_extension_module_wallet->getCustomerWalletAmount($customer_id);
                $this->json['amount'] = $wallet_amount['wallet_amount'];
            } else {
                $this->statusCode = 405;
                $this->allowedHeaders = array("GET");
            }
        } else {
            $this->statusCode = 400;
            $this->json['error'][] = "Must Enable Wallet Extenstion";
        }

        return $this->sendResponse();
    }

    public function topUpProductList() {
        $this->load->model('setting/setting');

        $this->checkPlugin();
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->load->model('extension/module/wallet');
        $wallet_status = $this->model_setting_setting->getSettingValue('module_wallet_status', $store_id);
        if ($wallet_status == 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $customer_id = $this->customer->getId();
                $productList = $this->model_extension_module_wallet->getTopUpProductList();
                $this->json['data'] = $productList;
            } else {
                $this->statusCode = 405;
                $this->allowedHeaders = array("GET");
            }
        } else {
            $this->statusCode = 400;
            $this->json['error'][] = "Must Enable Wallet Extenstion";
        }

        return $this->sendResponse();
    }

}
