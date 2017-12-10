<?php

/**
 * Created by PhpStorm.
 * User: eaveryanau
 * Date: 12/9/17
 * Time: 1:26 PM
 */
class Naqel {
	const API_DESTINATION_URL = 'http://api.naqelexpress.com/NaqelAPIDemo/xMLShippingService.asmx';
	const API_NAMESPACE = 'http://tempuri.org/';

	/**
	 * @return bool
	 */
	public static function printPageWaybillsList() {

		$args = array(
			'post_type' => ['shop_order'],
			'post_status' => array_keys(wc_get_order_statuses()),
			'posts_per_page' => '-1',
			'meta_query' => array(
				array(
					'key' => 'naqel_flag',
					'value' => '1',
					'compare' => '=',
				)
			)
		);
		$query = new WP_Query($args);
		print "<table><thead><tr><th>order</th><th>waybillNo</th><th>is hold</th><th>rto waybill</th><th>waybill actions</th></tr><tbody>";
		while ($query->have_posts()) {
			$query->the_post();
			$order = new WC_Order(get_the_ID());
			print "<tr><td><a href='/wp-admin/post.php?post=" . $order->get_id() . "&action=edit'>".$order->get_id()."</a></td>"
			. "<td>".get_post_meta($order->get_id(),'naqel_waybillNo',true)."</td>"
			. "<td>".get_post_meta($order->get_id(),'naqel_ishold',true)."</td>"
			. "<td>".get_post_meta($order->get_id(),'naqel_rto_waybillNo',true)."</td>"
			. "<td><a href='/wp-admin/admin.php?page=naqel_waybill_rto&order_id=".$order->get_id()."'>RTO</a>&nbsp;&nbsp;<a href='/wp-admin/admin.php?page=naqel_waybills_sticker_print&order_id=".$order->get_id()."'>Sticker</a>&nbsp;&nbsp;<a href='/wp-admin/admin.php?page=naqel_waybill_hold&order_id=".$order->get_id()."'>Hold</a></td></tr>";
		}
		print "</tbody></table>";
		print "<style>table{width: 100%;border: 1px solid #ccc;text-align: center;border-collapse: collapse;}td{border:1px solid #ccc;padding:10px;}</style>";

		return true;
	}

	public static function createWaybill( $id ) {

		$order = $order = WC_Order_Factory::get_order( $id );

		$clientID = get_option('naqel_options')['client_id'];
		$password = get_option('naqel_options')['passwd'];

		$codCharge                = '15';
		$declare_value            = '15';
		$generate_pieces_bar_code = 'false';

		$input_xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <CreateWaybill xmlns="' . self::API_NAMESPACE . '">
              <_ManifestShipmentDetails>
                <ClientInfo>
                  <ClientAddress>
                    <PhoneNumber>test phone</PhoneNumber>
                    <POBox>test pobox</POBox>
                    <ZipCode>zip code</ZipCode>
                    <Fax>fax</Fax>
                    <FirstAddress>first adress</FirstAddress>
                    <Location>Location</Location>
                    <CountryCode>KSA</CountryCode>
                    <CityCode>ABT</CityCode>
                  </ClientAddress>
                  <ClientContact>
                    <Name>string</Name>
                    <Email>string</Email>
                    <PhoneNumber>string</PhoneNumber>
                    <MobileNo>string</MobileNo>
                  </ClientContact>
                  <ClientID>' . $clientID . '</ClientID>
                  <Password>' . $password . '</Password>
                  <Version>string</Version>
                </ClientInfo>
                <ConsigneeInfo>
                  <ConsigneeName>' . $order->get_formatted_shipping_full_name() . '</ConsigneeName>
                  <Email>' . $order->get_billing_email() . '</Email>
                  <Mobile></Mobile>
                  <PhoneNumber>' . $order->get_billing_phone() . '</PhoneNumber>
                  <Fax></Fax>
                  <Address>' . $order->get_shipping_address_1() . '</Address>
                  <Near></Near>
                  <CountryCode>KSA</CountryCode>
                  <CityCode>ABT</CityCode>
                </ConsigneeInfo>
                <BillingType>5</BillingType>
                <PicesCount>' . $order->get_item_count() . '</PicesCount>
                <Weight>1</Weight>
                <DeliveryInstruction>string</DeliveryInstruction>
                <CODCharge>' . $codCharge . '</CODCharge>
                <CreateBooking>false</CreateBooking>
                <isRTO>false</isRTO>
                <GeneratePiecesBarCodes>' . $generate_pieces_bar_code . '</GeneratePiecesBarCodes>
                <LoadTypeID>36</LoadTypeID>
                <DeclareValue>' . $declare_value . '</DeclareValue>
                <GoodDesc>string</GoodDesc>
                <RefNo>string</RefNo>
              </_ManifestShipmentDetails>
            </CreateWaybill>
          </soap:Body>
        </soap:Envelope>';

		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API_DESTINATION_URL );
		// Following line is compulsary to add as it is:
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $input_xml );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/xml',
			'SOAPAction: ' . self::API_NAMESPACE . 'CreateWaybill'
		] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 300 );
		$data = curl_exec( $ch );
		curl_close( $ch );

		$clean_xml = str_ireplace( [ 'soap:', 'SOAP:' ], '', $data );

		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML( $clean_xml );

		$hasError = ( ( $xmlDoc->getElementsByTagName( 'HasError' )[0]->nodeValue ) == 'true' ) ? true : false;
		// TODO: make table for logging all requests.

		$waybillNo = $xmlDoc->getElementsByTagName( 'WaybillNo' )[0]->nodeValue;
		$key       = $xmlDoc->getElementsByTagName( 'Key' )[0]->nodeValue;
		$message   = $xmlDoc->getElementsByTagName( 'Message' )[0]->nodeValue;

		if ( ! $hasError ) {
			update_post_meta( $id, 'naqel_waybillNo', $waybillNo );
			update_post_meta( $id, 'naqel_key', $key );
			update_post_meta( $id, 'naqel_flag', 1 );
			update_post_meta( $id, 'naqel_ishold', 0 );

		}

		$response = [
			'WaybillNo' => $waybillNo,
			'Key'       => $key,
			'Message'   => $message
		];

		return $response;
	}

	public static function printSticker($id){

		$clientID = get_option('naqel_options')['client_id'];
		$password = get_option('naqel_options')['passwd'];

		$waybillNo = get_post_meta($id, 'naqel_waybillNo', true);

		$input_xml = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
			<soap:Body>
				<GetWaybillSticker xmlns="' . self::API_NAMESPACE . '">
					<clientInfo>
						<ClientAddress>
							<PhoneNumber>test phone</PhoneNumber>
		                    <POBox>test pobox</POBox>
		                    <ZipCode>zip code</ZipCode>
		                    <Fax>fax</Fax>
		                    <FirstAddress>first adress</FirstAddress>
		                    <Location>Location</Location>
		                    <CountryCode>KSA</CountryCode>
		                    <CityCode>ABT</CityCode>
						</ClientAddress>
						<ClientContact>
							<Name>string</Name>
							<Email>string</Email>
							<PhoneNumber>string</PhoneNumber>
							<MobileNo>string</MobileNo>
						</ClientContact>
						<ClientID>' . $clientID . '</ClientID>
		                <Password>' . $password . '</Password>
						<Version>string</Version>
					</clientInfo>
					<WaybillNo>'.$waybillNo.'</WaybillNo>
					<StickerSize>FourMEightInches</StickerSize>
				</GetWaybillSticker>
			</soap:Body>
		</soap:Envelope>';

		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API_DESTINATION_URL );
		// Following line is compulsary to add as it is:
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $input_xml );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/xml',
			'SOAPAction: ' . self::API_NAMESPACE . 'GetWaybillSticker'
		] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 300 );
		$data = curl_exec( $ch );
		curl_close( $ch );

		$clean_xml = str_ireplace( [ 'soap:', 'SOAP:' ], '', $data );

		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML( $clean_xml );

		$pdf_content = base64_decode($xmlDoc->getElementsByTagName( 'GetWaybillStickerResult' )[0]->nodeValue);

		if (!is_dir(wp_upload_dir()['basedir'] . '/hub_uploads')) {
			mkdir(wp_upload_dir()['basedir'] . '/hub_uploads', 0775);
		}
		$new_image = wp_upload_dir()['basedir'] . '/hub_uploads/invoice-'.$waybillNo.'.pdf';
		// open the output file for writing
		$ifp = fopen($new_image, 'wb');

		// we could add validation here with ensuring count( $data ) > 1
		fwrite($ifp, $pdf_content);

		// clean up the file resource
		fclose($ifp);

		chmod($new_image, 0777);

		if (file_exists($new_image)) {
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="'.basename($new_image).'"');

			readfile($new_image);
			exit;
		}

		return false;
	}


	public static function holdWaybill($id){

		$clientID = get_option('naqel_options')['client_id'];
		$password = get_option('naqel_options')['passwd'];

		$waybillNo = get_post_meta($id, 'naqel_waybillNo', true);

		$input_xml = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
		  <soap:Body>
		    <HoldShipmentFromDelivery xmlns="' . self::API_NAMESPACE . '">
		      <WaybillNo>'.$waybillNo.'</WaybillNo>
		      <ClientInfo>
		        <ClientAddress>
		          <PhoneNumber>test phone</PhoneNumber>
	              <POBox>test pobox</POBox>
	              <ZipCode>zip code</ZipCode>
	              <Fax>fax</Fax>
	              <FirstAddress>first adress</FirstAddress>
	              <Location>Location</Location>
	              <CountryCode>KSA</CountryCode>
	              <CityCode>ABT</CityCode>
		        </ClientAddress>
		        <ClientContact>
		          <Name>string</Name>
		          <Email>string</Email>
		          <PhoneNumber>string</PhoneNumber>
		          <MobileNo>string</MobileNo>
		        </ClientContact>
		        <ClientID>' . $clientID . '</ClientID>
                <Password>' . $password . '</Password>
		        <Version>string</Version>
		      </ClientInfo>
		    </HoldShipmentFromDelivery>
		  </soap:Body>
		</soap:Envelope>';

		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API_DESTINATION_URL );
		// Following line is compulsary to add as it is:
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $input_xml );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/xml',
			'SOAPAction: ' . self::API_NAMESPACE . 'HoldShipmentFromDelivery'
		] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 300 );
		$data = curl_exec( $ch );
		curl_close( $ch );
		$clean_xml = str_ireplace( [ 'soap:', 'SOAP:' ], '', $data );

		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML( $clean_xml );

		$content = $xmlDoc->getElementsByTagName( 'ShipmentHold' )[0]->nodeValue;
		if($content == 'true'){
			update_post_meta( $id, 'naqel_ishold', 1 );
		}
		header('Location: /wp-admin/admin.php?page=naqel_waybills_page');
		die();

	}


	public static function rtoWaybill($id){

		$clientID = get_option('naqel_options')['client_id'];
		$password = get_option('naqel_options')['passwd'];

		$waybillNo = get_post_meta($id, 'naqel_waybillNo', true);

		$input_xml = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
		  <soap:Body>
		    <CreateRTOWaybill xmlns="' . self::API_NAMESPACE . '">
		      <_ClientInfo>
		        <ClientAddress>
		          <PhoneNumber>string</PhoneNumber>
		          <POBox>string</POBox>
		          <ZipCode>string</ZipCode>
		          <Fax>string</Fax>
		          <FirstAddress>string</FirstAddress>
		          <Location>string</Location>
		          <CountryCode>string</CountryCode>
		          <CityCode>string</CityCode>
		        </ClientAddress>
		        <ClientContact>
		          <Name>string</Name>
		          <Email>string</Email>
		          <PhoneNumber>string</PhoneNumber>
		          <MobileNo>string</MobileNo>
		        </ClientContact>
		        <ClientID>' . $clientID . '</ClientID>
                <Password>' . $password . '</Password>
		        <Version>string</Version>
		      </_ClientInfo>
		      <WaybillNo>'.$waybillNo.'</WaybillNo>
		    </CreateRTOWaybill>
		  </soap:Body>
		</soap:Envelope>';

		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API_DESTINATION_URL );
		// Following line is compulsary to add as it is:
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $input_xml );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/xml',
			'SOAPAction: ' . self::API_NAMESPACE . 'CreateRTOWaybill'
		] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 300 );
		$data = curl_exec( $ch );
		curl_close( $ch );
		$clean_xml = str_ireplace( [ 'soap:', 'SOAP:' ], '', $data );

		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML( $clean_xml );

		$hasError = ( ( $xmlDoc->getElementsByTagName( 'HasError' )[0]->nodeValue ) == 'true' ) ? true : false;
		// TODO: make table for logging all requests.

		$waybillNo = $xmlDoc->getElementsByTagName( 'WaybillNo' )[0]->nodeValue;
		$key       = $xmlDoc->getElementsByTagName( 'Key' )[0]->nodeValue;
		$message   = $xmlDoc->getElementsByTagName( 'Message' )[0]->nodeValue;

		if ( ! $hasError ) {
			update_post_meta( $id, 'naqel_rto_waybillNo', $waybillNo );
			update_post_meta( $id, 'naqel_rto_key', $key );
			update_post_meta( $id, 'naqel_rto_flag', 1 );
			update_post_meta( $id, 'naqel_rto_ishold', 0 );

		}

		$response = [
			'WaybillNo' => $waybillNo,
			'Key'       => $key,
			'Message'   => $message
		];

		header('Location: /wp-admin/admin.php?page=naqel_waybills_page');
		die();

	}
}