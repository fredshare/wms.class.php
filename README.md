# wms.class.php
//商品信息同步
    function SkuDataSynSrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
                array(
                    "sku_id" => "431",
                    "sku_name" => "测试商品431"  
                ),
                array(
                    "sku_id" => "432",
                    "sku_name" => "测试商品432"  
                )
            );
                
        $resp = "";
        $ret = $wms->SkuDataSynSrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }

    function CustDataSynSrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            array(
                "type" => "CO",
                "consignee_name" => "测试用户3",        
                "consignee_tel" => "18789099876",
                "consignee_address" => "虚拟地址"
                ),
            array(
                "type" => "CO",
                "consignee_name" => "测试用户2",        
                "consignee_tel" => "18789099876",
                "consignee_address" => "虚拟地址"
                )              
            );
        $resp = "";
        $ret = $wms->CustDataSynSrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
    //入库单同步
    function AsnOrderSynSrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "serials_id" => "20170515114534088889",               
            "type" => "CGRK",
            "sku_info" => array(
                array(
                    "sku_id" => "43",  
                    "stock_id" => "110",  
                    "num" => "1459"
                    ),
                array(
                    "sku_id" => "34",  
                    "stock_id" => "110",  
                    "num" => "1459"
                    )
                )         
            );
        $resp = "";
        $ret = $wms->AsnOrderSynSrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
    //入库单查询
    function AsnOrderQuerySrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "serials_id" => "2017051511453408888"
            );
        $resp = "";
        $ret = $wms->AsnOrderQuerySrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
    //入口单取消
    function AsnOrderCancelSrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "serials_id" => "2017050911453498888",
            "sku_id" => "1000131",  
            "stock_id" => "12312",      
            "type" => "CGRK",
            "num" => "1459"
            );
        $resp = "";
        $ret = $wms->AsnOrderCancelSrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
    //出库单取消
    function SaleOrderCancelSrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "serials_id" => "2017650912453498882",
            "sku_id" => "1000131",  
            "stock_id" => "12312",      
            "type" => "CGRK",
            "num" => "1459"
            );
        $resp = "";
        $ret = $wms->SaleOrderCancelSrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
    
    
    //出库单同步
    function SaleOrderSynSrv()
    {
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "serials_id" => "2017650912453498882",
            "deal_id" => "12312",
            "consignee_name" => "测试用户",        
            "consignee_tel" => "18789099876",
            "consignee_address" => "虚拟地址1212",
            "user_id" => "10027",
            "sku_info" => array(
                array(
                    "sku_id" => "34",        
                    "num" => 1
                    )/*,
                array(
                    "sku_id" => "1000131",         
                    "num" => 1
                    )*/
                )
            );
        $resp = "";
        $ret = $wms->SaleOrderSynSrv($req,$resp);
        var_dump($ret);
        echo "@@@";
        print_r($resp);
    }
    //库存查询
    function WmsInvQrySrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "sku_id" => "34"
            );
        $resp = "";
        $ret = $wms->WmsInvQrySrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
    //库存冻结
    function InvHoldReleaseSrv(){
        require_once APPPATH."libraries/wms.class.php";
        $wms = new Wmsclass();
        $req = array(
            "sku_id" => "34",
            "action" => "CANCELHOLD",
            "serials_id" => "0000000033"
            );
        $resp = "";
        $ret = $wms->InvHoldReleaseSrv($req,$resp);
        var_dump($ret);
        print_r($resp);
    }
