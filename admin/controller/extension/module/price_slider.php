<?php
class ControllerExtensionModulePriceSlider extends Controller {

    private $error = array();

    public function index() {

        $this->load->language('extension/module/price_slider');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_price_slider', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->error['ps_heading'])) {
            $data['error_ps_heading'] = $this->error['ps_heading'];
        } else {
            $data['error_ps_heading'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/price_slider', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/price_slider', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_price_slider_heading'])) {
            $data['module_price_slider_heading'] = $this->request->post['module_price_slider_heading'];
        } else {
            $data['module_price_slider_heading'] = $this->config->get('module_price_slider_heading');
        }

        if (isset($this->request->post['module_price_slider_status'])) {
            $data['module_price_slider_status'] = $this->request->post['module_price_slider_status'];
        } else {
            $data['module_price_slider_status'] = $this->config->get('module_price_slider_status');
        }

        if (isset($this->request->post['module_price_slider_range'])) {
            $data['module_price_slider_range'] = $this->request->post['module_price_slider_range'];
        } else {
            $data['module_price_slider_range'] = $this->config->get('module_price_slider_range');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/price_slider', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/price_slider')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['module_price_slider_status']) && $this->request->post['module_price_slider_status'] == 1 && empty($this->request->post['module_price_slider_heading'])) {
            $this->error['ps_heading'] = $this->language->get('error_required');
        }
        return !$this->error;
    }

}

