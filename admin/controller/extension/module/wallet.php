<?php

/**
 * Created by PhpStorm.
 * User: chmaeera.lakshitha212@gmail.com
 * Date: 5/29/2018
 * Time: 1:59 PM
 */
class ControllerExtensionModuleWallet extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/wallet');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_wallet', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');


            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/wallet', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/wallet', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);


        if (isset($this->request->post['module_wallet_status'])) {
            $data['module_wallet_status'] = $this->request->post['module_wallet_status'];
        } else {
            $data['module_wallet_status'] = $this->config->get('module_wallet_status');
        }

        $data['module_wallet_status'] = $this->config->get('module_wallet_status');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }


        $data['module_shopping_cart_rest_api_status'] = $this->model_setting_setting->getSettingValue('module_shopping_cart_rest_api_status', $store_id);

        if ($data['module_shopping_cart_rest_api_status'] == 0)
            $data['error_warning'] = $this->language->get('error_permission');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/wallet', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/wallet')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {

        $this->db->query(" CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "customer_wallet (
  wallet_id int(11) NOT NULL AUTO_INCREMENT,
  customer_id int(11) DEFAULT NULL,
  order_id int(11) DEFAULT NULL,
  order_product_id int(11) DEFAULT NULL,
  amount decimal(30,2) DEFAULT '0.00',
  type varchar(64) NOT NULL,
  status int(1) DEFAULT '0',
  date_added datetime DEFAULT NULL,
  date_modified datetime DEFAULT NULL,
  PRIMARY KEY (wallet_id),
  KEY customer_id (customer_id),
  KEY order_id (order_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; ");
    }

 

}
