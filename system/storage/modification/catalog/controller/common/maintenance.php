<?php
class ControllerCommonMaintenance extends Controller {
	public function index() {

				/*======Show Themeconfig=======*/
				$this->load->language('extension/soconfig/soconfig');
				$data['objlang'] = $this->language;
				$data['soconfig'] = $this->soconfig;
				if ($this->request->server['HTTPS']) $server = $this->config->get('config_ssl');
				else $server = $this->config->get('config_url');
				$data['config_logo'] = $server.'image/'.$this->config->get('config_logo');
				$data['comingsoon_content'] = html_entity_decode($data['soconfig']->get_settings('comingsoon_content'), ENT_QUOTES, 'UTF-8');	
                $data['content_comingsoon'] = $this->load->controller('extension/soconfig/content_soon');
			
		$this->load->language('common/maintenance');

		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->request->server['SERVER_PROTOCOL'] == 'HTTP/1.1') {
			$this->response->addHeader('HTTP/1.1 503 Service Unavailable');
		} else {
			$this->response->addHeader('HTTP/1.0 503 Service Unavailable');
		}

		$this->response->addHeader('Retry-After: 3600');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_maintenance'),
			'href' => $this->url->link('common/maintenance')
		);

		$data['message'] = $this->language->get('text_message');

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('common/maintenance', $data));
	}
}
