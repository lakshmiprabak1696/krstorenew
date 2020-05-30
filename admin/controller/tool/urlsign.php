<?php

class ControllerToolurlsign extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('tool/upload');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('tool/upload');

        $this->getList();
    }

    public function url( ) {
         $resVal = array();
        
        $location_to_key_file ="d:/stutzen/stutzenme-55d6bec60d20.p12";
        $serviceAccountName = "stutzendev@stutzenme.iam.gserviceaccount.com";
        $expiration = "60000";
        $bucketName ="albumzen";
        
        $verb = $this->request->get['verb']; 
        $contentype =''; 
        $id = $this->request->get['objName']; 
        $privateKey = '';

      
        
        if (file_exists($location_to_key_file)) {
            $fh = fopen($location_to_key_file, "r");
            while (!feof($fh)) {
                $privateKey .= fgets($fh);
            }
            fclose($fh);
        }
        else
        {
            $resVal['message'] = 'P12 file is not found';
            $resVal['success'] = FALSE;
            return $resVal;
        }

        $signer = new Google_P12Signer($privateKey, "notasecret");
        $ttl = time() + 3600;
        $stringToSign = $verb . "\n\n" . $contentype . "\n" . $ttl . "\n" . '/' . $bucketName . '/' . $id;
        $signature = $signer->sign($stringToSign);
        // echo '';
        $finalSignature = base64_encode($signature);
        $host = "https://storage.googleapis.com/" . $bucketName;
 
        $urlArray = array();
 

        $url = $host . "/" . $id . "?Expires=" . $ttl . "&GoogleAccessId=" .
                $serviceAccountName . "&Signature=" . urlencode($finalSignature);
 
        $urlArray['url'] = $url;
        //echo $url;
        $json['success'] = true;
        $json['data'] = $urlArray;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}

abstract class Google_Signer {

    /**
     * Signs data, returns the signature as binary data.
     */
    abstract public function sign($data);
}

/**
 * Signs data.
 *
 * Only used for testing.
 *
 * @author Brian Eaton <beaton@google.com>
 */
class Google_P12Signer extends Google_Signer {

    // OpenSSL private key resource
    private $privateKey;

    // Creates a new signer from a .p12 file.
    function __construct($p12, $password) {
        if (!function_exists('openssl_x509_read')) {
            throw new Exception(
            'The Google PHP API library needs the openssl PHP extension');
        }

        // This throws on error
        $certs = array();
        if (!openssl_pkcs12_read($p12, $certs, $password)) {
            throw new Google_AuthException("Unable to parse the p12 file.  " .
            "Is this a .p12 file?  Is the password correct?  OpenSSL error: " .
            openssl_error_string());
        }
        // TODO(beaton): is this part of the contract for the openssl_pkcs12_read
        // method?  What happens if there are multiple private keys?  Do we care?
        if (!array_key_exists("pkey", $certs) || !$certs["pkey"]) {
            throw new Google_AuthException("No private key found in p12 file.");
        }
        $this->privateKey = openssl_pkey_get_private($certs["pkey"]);
        if (!$this->privateKey) {
            throw new Google_AuthException("Unable to load private key in ");
        }
    }

    function __destruct() {
        if ($this->privateKey) {
            openssl_pkey_free($this->privateKey);
        }
    }

    function sign($data) {
        
        
        if (!openssl_sign($data, $signature, $this->privateKey, "sha256")) {
            throw new Google_AuthException("Unable to sign data");
        }

        return $signature;
    }

}
