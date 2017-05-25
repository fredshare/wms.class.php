<?php
class Wmsclass
{
    const REQCLIENT = "";
    const REQCLIENTPWD = "";
    const CUSTOMERID = "";
    const WAREHOUSEID = "";
    const WMS_SERVICE_API= "http://121.33.215.94:8081/gdpeservice/etlservice";
    private $log;

    public function __construct()
    {    
        $this->log = Logger::getLogger(__CLASS__);
        $this->reqModel = array(
            'requestId' => $this->getProductId(), 
            'reqClient' => self::REQCLIENT, 
            'reqClientPwd' => self::REQCLIENTPWD,
            'timestamp' => $this->getMillisecond(),
            'reqBody' => ''
            );
    }
    //商品信息同步 
    public function SkuDataSynSrv($req,&$resp)
    {
        $this->log->debug('----------SkuDataSynSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqs['reqBody'] = array();
        //获取参数
        foreach ($req as $key => $value) {
            array_push($reqs['reqBody'], array(
                'CUSTOMERID' => self::CUSTOMERID,
                'REPORTUOM' => 'EA',
                'ACTIVE_FLAG' => 'Y',
                'KITFLAG' => '1',
                'SHELFLIFETYPE' => 'M', // M/E/R 分别表示生产日期/失效日期/入库日期
                'OUTBOUNDLIFEDAYS' => 0,
                'SKU' => $value['sku_id'],
                'ALTERNATE_SKU2' => $value['bar_code'],
                'DESCR_C' => $value['sku_name'],        
                'SKULENGTH' => !empty($value['length']) ? $value['length'] : 0,
                'SKUWIDTH' => !empty($value['width']) ? $value['width'] : 0,
                'SKUHIGH' => !empty($value['high']) ? $value['high'] : 0
                )
            );
        }    
        return  $this->handel($reqs,$methodName,$resp);  
    }
    //客户资料同步
    public function CustDataSynSrv($req,&$resp)
    {
        $this->log->debug('----------CustDataSynSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqs['reqBody'] = array();
        //获取参数
        foreach ($req as $key => $value) {
            array_push($reqs['reqBody'], array(
                'CUSTOMERID' => self::CUSTOMERID,
                'CUSTOMER_TYPE' => !empty($req['type']) ? $req['type'] : 'CO', //OW-货主;BI-结算人;CA-承运人;CO-收货人;IP-下单方;OT-其他;VE-供应商
                'ACTIVE_FLAG' => 'Y',
                'DESCR_C' => $value['consignee_name'],
                'ASNREF1TOLOT4' => 'N', 
                'ASNREF2TOLOT5' => 'N', 
                'ASNREF3TOLOT6' => 'N', 
                'ASNREF4TOLOT7' => 'N', 
                'ASNREF5TOLOT8' => 'N', 
                'SOREF1TOLOT4' => 'N',
                'SOREF2TOLOT5' => 'N',
                'SOREF3TOLOT6' => 'N',
                'SOREF4TOLOT7' => 'N',
                'SOREF5TOLOT8' => 'N',
                'CONTACT1' => $value['consignee_name'],
                'CONTACT1_TEL1' => $value['consignee_tel'],
                'ADDRESS1' => $value['consignee_address']
                )
            );
        }  
        return  $this->handel($reqs,$methodName,$resp);
    }
    //入库单同步
    public function AsnOrderSynSrv($req,&$resp)
    {
        $this->log->debug('----------AsnOrderSynSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        //获取参数
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'ASNNO' => $req['serials_id'],
            'ASNTYPE' => !empty($req['type']) ? $req['type'] : 'CGRK',  //CCPRK-产成品入库;CGRK-采购收货;CLRK-材料入库;DBRK-调拨入库;HHRK-换货入库;QTRK-其他入库;RT-退货入库;
            'ASNCREATIONTIME' => date('Y-m-d h:i:s'), //创建时间
            'ASNSTATUS' => '00', //预期到货通知 状态
            'OUTBOUNDLIFEDAYS' => 0,  //出库生命周期
            'RESERVE_FLAG' => 'N',//是否已经预约 库位
            'EDISENDFLAG' => 'N',        
            'ARCHIVEFLAG' => 'N',
            'ASNREFERENCE1' => $req['serials_id'],
            'DETAILS' => array()
        );
        foreach ($req['sku_info'] as $key => $value) 
        {
            array_push($reqBody['DETAILS'], array(
                'ASNNO' => (string)$value['stock_id'],
                'ASNLINENO' => (string)$value['stock_id'],
                'CUSTOMERID' => self::CUSTOMERID,
                'SKU' => (string)$value['sku_id'],
                'LINESTATUS' => '00',
                'UOM' => 'EA',
                'PACKID' => 'STANDARD',
                'LOTATT01' => $value['production_date'], //生产日期
                'LOTATT02' => $value['expire_date'],//过期日期
                'USERDEFINE4' => (string)$value['stock_id'],
                'EXPECTEDQTY_EACH' => $value['num'],
                'EXPECTEDQTY' => $value['num'],
                'TOTALCUBIC' => !empty($value['cubic']) ? $value['cubic'] : 0, //总体积
                'TOTALGROSSWEIGHT' => !empty($value['total_weight']) ? $value['total_weight'] : 0,//总重量
                'TOTALNETWEIGHT' => !empty($value['weight']) ? $value['weight'] : 0,//总净重
                'TOTALPRICE' => !empty($value['total_price']) ? $value['total_price'] : 0, //总价格
                'RESERVE_FLAG' => 'N' //是否已经预约库位
                )
            );
        }
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);      
    }
    //入库单查询
    public function AsnOrderQuerySrv($req,&$resp)
    {
        $this->log->debug('----------AsnOrderQuerySrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'ORDERNO' => $req['serials_id'], 
            'ORDERTYPE' => !empty($req['type']) ? $req['type'] : 'CGRK',
            'ENDDATE' => '',
            'BEGINDATE' => ''
            );
            
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);
    }
    //出库单查询
    public function SaleOrderQuerySrv($req,&$resp)
    {
        $this->log->debug('----------SaleOrderQuerySrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'ORDERNO' => $req['serials_id'], 
            'ORDERTYPE' => !empty($req['type']) ? $req['type'] : 'CCPRK',
            'ENDDATE' => '',
            'BEGINDATE' => ''
            );      
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);
    }
    //出库单同步
    public function SaleOrderSynSrv($req,&$resp)
    {
        $this->log->debug('----------SaleOrderSynSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        //获取参数
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'ORDERNO' => $req['serials_id'],
            'ORDERTYPE' => !empty($req['type']) ? $req['type'] : 'XSDD', //销售订单:XSDD 转移出库:ZYCK
            'ORDERTIME' => date('Y-m-d h:i:s'), //订单时间
            'CONSIGNEEID' => $req['user_id'],
            'c_CITY' => '',
            'CONSIGNEENAME' => $req['consignee_name'],
            'c_CONTACT' => $req['consignee_name'],
            'c_ADDRESS1' => $req['consignee_address'],
            'c_TEL1' => $req['consignee_tel'],
            'SOSTATUS' => '00',
            'RELEASESTATUS' => 'Y',
            'CARRIERID' => !empty($req['carrierid']) ? $req['carrierid'] : 'EMS', //EMS 标准快递 YTO 圆通快递 STO 申通快递 YUNDA 韵达快递 TTKD 天天快递 ZTO 中通快递 JD 京东快递
            'EXPECTEDSHIPMENTTIME1' => '',
            'EXPECTEDSHIPMENTTIME2' => '',
            'REQUIREDDELIVERYTIME' => '',
            'SOREFERENCE1' => $req['user_id'],
            'SOREFERENCE2' => $req['user_id'],
            'SOREFERENCE3' => $req['deal_id'],
            'SOREFERENCE4' => '',
            'SOREFERENCE5' => '',
            'PRIORITY' => '',
            'NOTES' => '',
            'c_PROVINCE' => '',
            'c_COUNTRY' => '',
            'c_ZIP' => '',
            'c_ADDRESS2' => '',
            'c_TEL2' => '',
            'c_MAIL' => '',
            'USERDEFINE1' => '',
            'USERDEFINE2' => '',
            'USERDEFINE3' => !empty($req['user_comment']) ? $req['user_comment'] : '', //买家留言
            'USERDEFINE4' => !empty($req['inner_comment']) ? $req['inner_comment'] : '', //卖家留言
            'USERDEFINE5' => '',
            'h_EDI_01' => '',
            'h_EDI_02' => '',
            'h_EDI_03' => '',
            'h_EDI_04' => '',
            'h_EDI_05' => '',
            'h_EDI_06' => '',
            'h_EDI_07' => '',
            'h_EDI_08' => '',
            'h_EDI_09' => '',
            'h_EDI_10' => '',
            'BILLINGID' => '',
            'BILLINGNAME' => '',
            'b_ADDRESS1' => '',
            'b_ADDRESS2' => '',
            'b_ADDRESS3' => '',
            'b_ADDRESS4' => '',
            'b_CITY' => '',
            'b_CONTACT' => '',
            'b_COUNTRY' => '',
            'b_EMAIL' => '',
            'b_FAX' => '',
            'b_PROVINCE' => '',
            'b_TEL1' => '',
            'b_TEL2' => '',
            'b_ZIP' => '',
            'CARRIERADDRESS1' => '',
            'CARRIERADDRESS2' => '',
            'CARRIERADDRESS3' => '',
            'CARRIERADDRESS4' => '',
            'CARRIERCITY' => '',
            'CARRIERCONTACT' => '',
            'CARRIERCOUNTRY' => '',
            'CARRIERFAX' => '',
            'CARRIERMAIL' => '',
            'CARRIERNAME' => '',
            'CARRIERPROVINCE' => '',
            'CARRIERTEL1' => '',
            'CARRIERTEL2' => '',
            'CARRIERZIP' => '',
            'CREATESOURCE' => '',
            'c_ADDRESS3' => '',
            'c_ADDRESS4' => '',
            'c_FAX' => '',
            'DELIVERYTERMS' => '',
            'DELIVERYTERMSDESCR' => '',
            'DOOR' => '',
            'ISSUEPARTYID' => '',
            'ISSUEPARTYNAME' => '',
            'i_ADDRESS1' => '',
            'i_ADDRESS2' => '',
            'i_ADDRESS3' => '',
            'i_ADDRESS4' => '',
            'i_CITY' => '',
            'i_CONTACT' => '',
            'i_COUNTRY' => '',
            'i_FAX' => '',
            'i_MAIL' => '',
            'i_PROVINCE' => '',
            'i_TEL1' => '',
            'i_TEL2' => '',
            'i_ZIP' => '',
            'LASTSHIPMENTTIME' => '',
            'ORDER_PRINT_FLAG' => '',
            'PAYMENTTERMS' => '',
            'PAYMENTTERMSDESCR' => '',
            'PICKING_PRINT_FLAG' => '',
            'PLACEOFDELIVERY' => '',
            'PLACEOFDISCHARGE' => '',
            'REQUIREDELIVERYNO' => 'S',
            'RFGETTASK' => '',
            'ROUTE' => '',
            'STOP' => '',
            'TRANSPORTATION' => '',
            'USERDEFINE6' => '',
            'DETAILS' => array(),
            );
        foreach ($req['sku_info'] as $key => $value) {
            array_push($reqBody['DETAILS'], array(
                'ORDERNO' => $req['deal_id'],
                'ORDERLINENO' => $key+1,
                'SKU' => (string)$value['sku_id'],
                'QTYORDERED_EACH' => $value['num'],
                'QTYORDERED' => $value['num'],
                'CUSTOMERID' => $req['user_id'],
                'LINESTATUS' => '00',
                'LOTATT01' => '',
                'LOTATT02' => '',
                'LOTATT03' => '',
                'LOTATT04' => '',
                'LOTATT05' => '',
                'LOTATT06' => '',
                'LOTATT07' => '',
                'LOTATT08' => '',
                'LOTATT09' => '',
                'LOTATT10' => '',
                'LOTATT11' => '',
                'LOTATT12' => '',
                'USERDEFINE1' => '',
                'USERDEFINE2' => '',
                'USERDEFINE3' => '',
                'USERDEFINE4' => '',
                'USERDEFINE5' => '',
                'd_EDI_01' => '',
                'd_EDI_02' => '',
                'd_EDI_03' => '',
                'd_EDI_04' => '',
                'd_EDI_05' => '',
                'd_EDI_06' => '',
                'd_EDI_07' => '',
                'd_EDI_08' => '',
                'd_EDI_09' => '',
                'd_EDI_10' => '',
                'd_EDI_11' => '',
                'd_EDI_12' => '',
                'd_EDI_13' => '',
                'd_EDI_14' => '',
                'd_EDI_15' => '',
                'd_EDI_16' => '',
                'd_EDI_17' => '',
                'd_EDI_18' => '',
                'd_EDI_19' => '',
                'd_EDI_20' => '',
                'ALLOCATIONRULE' => '',
                'ALTERNATIVESKU' => '',
                'CUBIC' => '',
                'ERPCANCELFLAG' => '',
                'GROSSWEIGHT' => '',
                'KITREFERENCENO' => '',
                'KITSKU' => '',
                'LOCATION' => '',
                'LOTNUM' => '',
                'NETWEIGHT' => '',
                'NOTES' => '',
                'ORDERLINEREFERENCENO' => '',
                'PACKID' => '',
                'PICKZONE' => '',
                'PRICE' => '',
                'QTYALLOCATED' => '',
                'QTYALLOCATED_EACH' => '',
                'QTYPICKED' => '',
                'QTYPICKED_EACH' => '',
                'QTYSHIPPED' => '',
                'QTYSHIPPED_EACH' => '',
                'QTYSOFTALLOCATED' => '',
                'QTYSOFTALLOCATED_EACH' => '',
                'ROTATIONIDH' => '',
                'SOFTALLOCATIONRULE' => '',
                'TRACEID' => '',
                'UOM' => '',
                'USERDEFINE6' => ''
                ));
        }

        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);   
    }
    //入口单取消
    public function AsnOrderCancelSrv($req,&$resp)
    {
        $this->log->debug('----------AsnOrderCancelSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'ORDERNO' => $req['serials_id']
            );
            
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);
    }
    //出库单取消
    public function SaleOrderCancelSrv($req,&$resp)
    {
        $this->log->debug('----------AsnOrderCancelSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'ORDERNO' => $req['serials_id']
            );
            
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);
    }
    //库存查询
    public function WmsInvQrySrv($req,&$resp)
    {
        $this->log->debug('----------AsnOrderCancelSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'SKU' => (string)$req['sku_id']
            );
            
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);
    }
    //库存冻结
    public function InvHoldReleaseSrv($req,&$resp)
    {
        $this->log->debug('----------InvHoldReleaseSrv start----------');
        $reqs = $this->reqModel;
        $methodName = __FUNCTION__;
        $reqs['reqMethod'] = $methodName;
        $reqBody = array(
            'CUSTOMERID' => self::CUSTOMERID,
            'WAREHOUSEID' => self::WAREHOUSEID,
            'SKU' => (string)$req['sku_id'],
            'PROCESSACTION' => $req['action'], //HOLD、CANCELHOLD
            'INVHOLDID' => $req['serials_id'],
            'HOLDBY' => '7'
            );
            
        $reqs['reqBody'] = $reqBody;
        return  $this->handel($reqs,$methodName,$resp);
    }
    private function getProductId()
    {
        return date('Ymdhis',time()).rand(10000,99999);
    }
    private function getSign($pwd,$time,$str)
    {
        return md5($pwd.$time.$str);
    }

    private function getMillisecond() 
    { 
        list($s1, $s2) = explode(' ', microtime()); 
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000); 
    }
    private function handel($reqs,$method,&$resp)
    {
        //生成签名
        $reqs['sign'] = $this->getSign($reqs['reqClientPwd'],$reqs['timestamp'],json_encode($reqs['reqBody'],JSON_UNESCAPED_UNICODE));
        //发送curl数据
        $this->log->debug('post data = '.json_encode($reqs));
        $resp = $this->http_post(self::WMS_SERVICE_API.'?method='.$method,$reqs);
        $this->log->debug('post return data = $resp');
        $resp = json_decode($resp,true);
        //解析数据并返回
        if($resp['respCode'] == '0000' && $resp['respDesc'] == 'OK' && (isset($resp['respBody']['respCode']) ? $resp['respBody']['respCode'] == 'A000' : true)){
            $this->log->debug('操作成功');
            return 0;
        }
        return -1;
    }
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$param,$post_file=false)
    {
        $oCurl = curl_init();
        if(stripos($url,'https://')!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $strPOST = json_encode($param,JSON_UNESCAPED_UNICODE);
        }
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(                   
            'Content-Type: application/json', 
            'Expect:', //此配置可保curl数据超过1024字节
            'Content-Length: ' . strlen($strPOST))           
        ); 
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        //增加代理
        curl_setopt($oCurl, CURLOPT_PROXY, HTTP_PROXY_IP_PORT);
        $sContent = curl_exec($oCurl);
        //var_dump($sContent);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus['http_code'])==200){
            return $sContent;
        }else{
            return false;
        }
    }
}

