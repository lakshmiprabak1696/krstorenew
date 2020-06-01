<?php

require_once(DIR_SYSTEM . 'engine/restcontroller.php');

class ControllerRestRazorpay extends RestController {

    public function index() {
        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //save payments information to session
            $post = $this->getPost();
            $this->callback($post);
        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("POST");
        }
        return $this->sendResponse();
    }

    public function callback($post) {
        $this->load->model('checkout/order');
        if (isset($post['razorpay_payment_id']) and isset($post['order_id'])) {
            $razorpay_payment_id = $post['razorpay_payment_id'];
            $order_id = $post['order_id'];
            $key_id = $this->config->get('payment_razorpay_key_id');

            $account = $this->config->get('payment_razorpay_account');
            $key_secret = $this->config->get('payment_razorpay_key_secret');

            $order_info = $this->model_checkout_order->getOrder($order_id);
            $amount = $this->currency->format($order_info['total'] * 100, $order_info['currency_code'], $order_info['currency_value'], false);                        
            $success = false;
            $error = "";

            try {
                 $url = 'https://api.razorpay.com/v1/payments/' . $razorpay_payment_id . '/capture';
                $fields_string = "amount=$amount";

                //cURL Request
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                //execute post
                $result = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $response_array = json_decode($result, true);
                if (key_exists('error', $response_array)) {

                    $success = false;
                    $error = 'Curl error: ' . curl_error($ch);
                } else { 
                   // $response_array = json_decode($result, true);
                    //Check success response
                    if ($http_status === 200 and isset($response_array['error']) === false) {                         
                        $success = true;
                        $url = 'https://api.razorpay.com/v1/payments/' . $razorpay_payment_id . '/transfers';

                        $data[] = array(
                            "account" => $account,
                            "amount" => $amount,
                            "currency" => "INR"
                        );
                        $tranfer['transfers'] = $data;
                       // var_dump($tranfer);
                        $data_param = http_build_query($tranfer);


                        //cURL Request
                        $ch = curl_init();

                        //set the url, number of POST vars, POST data
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_param);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                        //execute post
                        $result = curl_exec($ch);
                        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        $response_array = json_decode($result, true);
                        //var_dump($response_array);
                        if (key_exists('error', $response_array)) {                            
                            $success = false;
                            $error = 'Curl error: ' . curl_error($ch);
                        } else {
                            $response_array = json_decode($result, true);
                            if ($http_status === 200 and isset($response_array['error']) === false) {
                                $success = true;
                            }
                         }
                    } else {
                        $success = false;

                        if (!empty($response_array['error']['code'])) {
                            $error = $response_array['error']['code'] . ":" . $response_array['error']['description'];
                        } else {
                            $error = "RAZORPAY_ERROR:Invalid Response <br/>" . $result;
                        }
                    } 
                }

                //close connection
                curl_close($ch);
            } catch (Exception $e) {

                $success = false;
                $error = "OPENCART_ERROR:Request to Razorpay Failed";
            }

            if ($success === true) {
                if (!$order_info['order_status_id']) {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('razorpay_order_status_id'), 'Payment Successful. Razorpay Payment Id:' . $razorpay_payment_id);
                } else {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('razorpay_order_status_id'), 'Payment Successful. Razorpay Payment Id:' . $razorpay_payment_id);
                }
            } else {
                $this->model_checkout_order->addOrderHistory($post['order_id'], 10, $error . ' Payment Failed! Check Razorpay dashboard for details of Payment Id:' . $razorpay_payment_id);
                $this->statusCode = 400;
                $this->json['error'][] = $response_array['error'];
                $this->json['message'] = "<a href=" . $this->url->link('checkout/failure') . ">link</a>";
            }
        } else {
                $this->statusCode = 400;
                $this->json['error'][] = 'An error occured. Contact site administrator, please!';             
        }
    }

}

?>