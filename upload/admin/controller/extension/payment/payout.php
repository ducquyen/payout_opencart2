<?php
/*
 * The MIT License
 *
 * Copyright (c) 2019 Payout, s.r.o. (https://payout.one/)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Class ControllerExtensionPaymentPayout
 *
 * The Payout payment module for OpenCart 2
 *
 * @version    1.0.0
 * @copyright  2019 Payout, s.r.o.
 * @author     Neotrendy s. r. o.
 * @link       https://github.com/payout-one/payout_opencart2
 */
class ControllerExtensionPaymentPayout extends Controller {
    protected $payout_config = array(
        'version' => '1.0.0',
        'compatibility' => array('2.3.0.2'),
        'routes' => array(
            'checkout' => 'extension/payment/payout/checkout',
            'notification' => 'extension/payment/payout/notification'
        )
    );

    private $error = array();

    public function index() {
        $this->load->language('extension/payment/payout');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $this->request->post['payout_config'] = serialize($this->payout_config);

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payout', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Heading
        $data['heading_title'] = $this->language->get('heading_title');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/payout', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/payout', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_edit'] = $this->language->get('text_edit');

        // Tabs
        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_order_statuses'] = $this->language->get('tab_order_statuses');
        $data['tab_about'] = $this->language->get('tab_about');

        // Notification URL
        if ($this->ssl) {
            $url = HTTPS_CATALOG . 'index.php?route=';
        } else {
            $url = HTTP_CATALOG . 'index.php?route=';
        }

        $data['text_notification_url'] = $this->language->get('text_notification_url');
        $data['notification_url'] = $url . $this->payout_config['routes']['notification'];

        // General
        $data['entry_client_id'] = $this->language->get('entry_client_id');
        $data['help_client_id'] = $this->language->get('help_client_id');

        if (isset($this->request->post['payout_client_id'])) {
            $data['payout_client_id'] = $this->request->post['payout_client_id'];
        } else {
            $data['payout_client_id'] = $this->config->get('payout_client_id');
        }

        if (isset($this->error['client_id'])) {
            $data['error_client_id'] = $this->error['client_id'];
        } else {
            $data['error_client_id'] = '';
        }

        $data['entry_client_secret'] = $this->language->get('entry_client_secret');
        $data['help_client_secret'] = $this->language->get('help_client_secret');

        if (isset($this->request->post['payout_client_secret'])) {
            $data['payout_client_secret'] = $this->request->post['payout_client_secret'];
        } else {
            $data['payout_client_secret'] = $this->config->get('payout_client_secret');
        }

        if (isset($this->error['client_secret'])) {
            $data['error_client_secret'] = $this->error['client_secret'];
        } else {
            $data['error_client_secret'] = '';
        }

        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_test'] = $this->language->get('entry_test');
        $data['help_test'] = $this->language->get('help_test');

        if (isset($this->request->post['payout_sandbox'])) {
            $data['payout_sandbox'] = $this->request->post['payout_sandbox'];
        } else {
            $data['payout_sandbox'] = $this->config->get('payout_sandbox');
        }

        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['help_debug'] = $this->language->get('help_debug');

        if (isset($this->request->post['payout_debug'])) {
            $data['payout_debug'] = $this->request->post['payout_debug'];
        } else {
            $data['payout_debug'] = $this->config->get('payout_debug');
        }

        $data['entry_total'] = $this->language->get('entry_total');
        $data['help_total'] = $this->language->get('help_total');

        if (isset($this->request->post['payout_total'])) {
            $data['payout_total'] = $this->request->post['payout_total'];
        } else {
            $data['payout_total'] = $this->config->get('payout_total');
        }

        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payout_geo_zone_id'])) {
            $data['payout_geo_zone_id'] = $this->request->post['payout_geo_zone_id'];
        } else {
            $data['payout_geo_zone_id'] = $this->config->get('payout_geo_zone_id');
        }

        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        if (isset($this->request->post['payout_status'])) {
            $data['payout_status'] = $this->request->post['payout_status'];
        } else {
            $data['payout_status'] = $this->config->get('payout_status');
        }

        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        if (isset($this->request->post['payout_sort_order'])) {
            $data['payout_sort_order'] = $this->request->post['payout_sort_order'];
        } else {
            $data['payout_sort_order'] = $this->config->get('payout_sort_order');
        }

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['entry_processing_status'] = $this->language->get('entry_processing_status');
        $data['help_processing_status'] = $this->language->get('help_processing_status');

        if (isset($this->request->post['payout_processing_status_id'])) {
            $data['payout_processing_status_id'] = $this->request->post['payout_processing_status_id'];
        } else {
            $data['payout_processing_status_id'] = $this->config->get('payout_processing_status_id');
        }

        $data['entry_success_status'] = $this->language->get('entry_success_status');
        $data['help_success_status'] = $this->language->get('help_success_status');

        if (isset($this->request->post['payout_success_status_id'])) {
            $data['payout_success_status_id'] = $this->request->post['payout_success_status_id'];
        } else {
            $data['payout_success_status_id'] = $this->config->get('payout_success_status_id');
        }

        $data['entry_expired_status'] = $this->language->get('entry_expired_status');
        $data['help_expired_status'] = $this->language->get('help_expired_status');

        if (isset($this->request->post['payout_expired_status_id'])) {
            $data['payout_expired_status_id'] = $this->request->post['payout_expired_status_id'];
        } else {
            $data['payout_expired_status_id'] = $this->config->get('payout_expired_status_id');
        }

        $data['entry_failed_status'] = $this->language->get('entry_failed_status');
        $data['help_failed_status'] = $this->language->get('help_failed_status');

        if (isset($this->request->post['payout_failed_status_id'])) {
            $data['payout_failed_status_id'] = $this->request->post['payout_failed_status_id'];
        } else {
            $data['payout_failed_status_id'] = $this->config->get('payout_failed_status_id');
        }

        $data['entry_notify'] = $this->language->get('entry_notify');

        if (isset($this->request->post['payout_notify'])) {
            $data['payout_notify'] = $this->request->post['payout_notify'];
        } else {
            $data['payout_notify'] = $this->config->get('payout_notify');
        }

        // About
        $data['text_title'] = $this->language->get('text_title');
        $data['text_version'] = $this->language->get('text_version');
        $data['info_version'] = $this->payout_config['version'];
        if (!in_array(VERSION, $this->payout_config['compatibility'])) {
            $data['info_version'] .= $this->language->get('info_version');
        }
        $data['text_compatibility'] = $this->language->get('text_compatibility');
        $data['info_compatibility'] = sprintf($this->language->get('info_compatibility'), implode(',', $this->payout_config['compatibility']));
        $data['text_documentation'] = $this->language->get('text_documentation');
        $data['info_documentation'] = $this->language->get('info_documentation');
        $data['text_license'] = $this->language->get('text_license');
        $data['info_license'] = sprintf($this->language->get('info_license'), date('Y'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/payout', $data));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/payout')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payout_client_id']) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        }

        if (!$this->request->post['payout_client_secret']) {
            $this->error['client_secret'] = $this->language->get('error_client_secret');
        }

        return !$this->error;
    }
}
