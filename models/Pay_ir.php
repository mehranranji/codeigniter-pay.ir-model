<?php
/**
 * Created by PhpStorm.
 * User: Mehran
 * Date: 2018/09/11
 * Time: 15:42

defined('BASEPATH') OR exit('No direct script access allowed');

class Pay_ir extends CI_Model
{

    protected static $API_KEY = 'test'; //'f0197bd729ee337029aebe31ac3334f1';

    public $error = '';
    public $error_msg = '';

    public function pay($amount, $redirect, $mobile ='', $invoiceid = '', $description =''){

        $request = $this->request($amount, $redirect, $mobile, $invoiceid, $description);
        $request = json_decode($request);

        if( $request->status == 1 ){
            $this->redirect($request->transId);
        }else{
            $this->error = $request->errorCode;
            $this->error_msg = $request->errorMessage;
        }

        return false;
    }

    private function request($amount, $redirect, $mobile ='', $invoiceid = '', $description ='')
    {

        $url = 'https://pay.ir/payment/send';
        $fields = array(
            'api' => self::$API_KEY,
            'amount' => $amount,
            'redirect' => $redirect,
            'mobile' => $mobile,
            'factorNumber' => $invoiceid,
            'description	' => $description,
        );

        $fields_string = '';
        //url-ify the data for the POST
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        return $result;
    }

    public function verify($transid){

        $url = 'https://pay.ir/payment/verify';
        $fields = array(
            'api'       => self::$API_KEY,
            'transId'   => $transid,
        );

        $fields_string = '';
        //url-ify the data for the POST
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        $result = json_decode($result);

        if( $result->status == 1 ){
            return $result->amount;
        }

        $this->error = $result->errorCode;
        $this->error_msg = $result->errorMessage;

        return false;

    }

    private function redirect($transid){
        redirect( 'https://pay.ir/payment/gateway/'.$transid );
    }

    public function error_code(){
        return $this->error;
    }

    public function error_message(){
        return $this->error_msg;
    }

}