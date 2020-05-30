<?php

class ModelExtensionModuleWallet extends Model {

    public function save($data) {

        if (isset($data['type']))
            $type = $data['type'];
        else
            $type = "Purchase";
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_wallet` SET customer_id = '" . $this->db->escape($data['customer_id']) . "', order_id = '" . (int) $data['order_id'] . "', order_product_id = '" . $this->db->escape($data['order_product_id']) . "', amount = '" . $this->db->escape($data['amount']) . "', type = ' " . $type . " ', status = '1' , date_added = NOW(), date_modified = NOW()");

        $wallet_id = $this->db->getLastId();

        return $wallet_id;
    }

    public function getCustomerWalletAmount($customer_id) {


        $query = $this->db->query("SELECT COALESCE(SUM(amount),0)  AS wallet_amount   FROM " . DB_PREFIX . "customer_wallet WHERE  customer_id = '" . $customer_id . "' ");

         return $query->row;
       
    }
    
    public function getTopUpProductList() {


        $query = $this->db->query("SELECT *  FROM " . DB_PREFIX . "product WHERE  topupYN = '1' ");

         return $query->rows;
       
    }

}
