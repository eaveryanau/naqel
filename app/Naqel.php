<?php

/**
 * Created by PhpStorm.
 * User: eaveryanau
 * Date: 12/9/17
 * Time: 1:26 PM
 */
class Naqel
{

    const API_DESTINATION_URL = 'http://api.naqelexpress.com/NaqelAPIDemo/xMLShippingService.asmx';
    const API_NAMESPACE = 'http://tempuri.org/';

    /**
     * @return bool
     */
    public static function printPageWaybillsList(){

        waybills_list();
        return true;
    }
    
}