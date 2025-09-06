<?php

namespace common\components\ApiV43;

use backend\models\business\DebitNote;
use backend\models\business\CreditNote;
use backend\models\business\Invoice;
use backend\models\business\ItemDebitNote;
use backend\models\business\ItemCreditNote;
use backend\models\business\ItemInvoice;
use backend\models\business\PaymentMethodHasDebitNote;
use backend\models\business\PaymentMethodHasCreditNote;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\UnitType;
use backend\models\settings\Issuer;

class ApiXML
{
    /**
     * Función para generar el XML de facturas y tiquetes
     *
     * @param Issuer $emisor
     * @param Invoice $factura
     * @param ItemInvoice[] $factura_detalles
     * @return string
     */
	function genXMLFe($emisor, $factura, $factura_detalles)
	{        
		//$fecha = date('c');		
		// Establece la zona horaria a la zona horaria esperada por la API
		$timezone = new \DateTimeZone('America/Costa_Rica'); // Cambia esto a la zona horaria correcta
        $ProveedorSistema = '3101615166';

		// Crea un objeto DateTime con la fecha de emisión de la factura
		$fechaEmision = new \DateTime($factura->emission_date, $timezone);

		// Convierte la fecha a formato ISO 8601
		$fecha = $fechaEmision->format('c');

		$plazo_credito = $factura->conditionSale->code == '02' ? $factura->creditDays->name: '0';
               
		$doc  = new \DomDocument('1.0','UTF-8');
		$doc->formatOutput = true;
        
        if ($factura->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE){
            $root = $doc->createElementNS(
                'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica','FacturaElectronica');
            $doc->appendChild($root);
            $root->setAttributeNS(
                'http://www.w3.org/2000/xmlns/' ,
                'xmlns:xsd',
                'http://www.w3.org/2001/XMLSchema');
            $root->setAttributeNS(
                'http://www.w3.org/2000/xmlns/' ,
                'xmlns:xsi',
                'http://www.w3.org/2001/XMLSchema-instance');
            $root->setAttributeNS(
                'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation',
                'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/facturaElectronica.xsd'
            );
        }
        else
        {                        
            $root = $doc->createElementNS(
                'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico','TiqueteElectronico');
            $doc->appendChild($root);
            $root->setAttributeNS(
                'http://www.w3.org/2000/xmlns/' ,
                'xmlns:xsd',
                'http://www.w3.org/2001/XMLSchema');
            $root->setAttributeNS(
                'http://www.w3.org/2000/xmlns/' ,
                'xmlns:xsi',
                'http://www.w3.org/2001/XMLSchema-instance');
            $root->setAttributeNS(
                'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation',
                'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/tiqueteElectronico.xsd'
            );
        }

        $nodo = $doc->createElement('Clave', $factura->key);
        $root->appendChild($nodo);

        $nodo = $doc->createElement('ProveedorSistemas', $ProveedorSistema);
        $root->appendChild($nodo);
        
		$nodo = $doc->createElement('CodigoActividadEmisor', $emisor->code_economic_activity);
		$root->appendChild($nodo);

		$nodo = $doc->createElement('NumeroConsecutivo', $factura->consecutive);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$root->appendChild($nodo);
		
		// Datos del Emisor
		$nodoemisor = $doc->createElement('Emisor');
		$root->appendChild($nodoemisor);
		
		$nodo = $doc->createElement('Nombre', htmlspecialchars($emisor->name));
		$nodoemisor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$nodoemisor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($emisor->identificationType->code));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($emisor->identification));
		$identificacion->appendChild($nodo);
		
		if (!is_null($emisor->name) && !empty($emisor->name))
		{
			$nodo = $doc->createElement('NombreComercial', htmlspecialchars($emisor->name));
			$nodoemisor->appendChild($nodo);
		}

		$ubicacion = $doc->createElement('Ubicacion');
		$nodoemisor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $emisor->province->code);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($emisor->canton->code, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($emisor->disctrict->code, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($emisor->other_signs) && !empty($emisor->other_signs)){
			$nodo = $doc->createElement('OtrasSenas', htmlspecialchars($emisor->other_signs));
			$ubicacion->appendChild($nodo);
		}
		else
		{
			$nodo = $doc->createElement('OtrasSenas', 'Otras señas');
			$ubicacion->appendChild($nodo);
		}
		
		if (!is_null($emisor->country_code_phone) && !empty($emisor->country_code_phone) && !is_null($emisor->phone) && !empty($emisor->phone)){
			$telefono = $doc->createElement('Telefono');
			$nodoemisor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $emisor->country_code_phone);
			$telefono->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->phone);
			$telefono->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $emisor->email);
		$nodoemisor->appendChild($nodo);
		
		
		// Datos Receptor
        if ($factura->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE)
        {
            $receptor = $doc->createElement('Receptor');
            $root->appendChild($receptor);
            
            $nodo = $doc->createElement('Nombre', htmlspecialchars($factura->customer->name));
            $receptor->appendChild($nodo);

        
            $campo_tipo_identificacion = trim($factura->customer->identificationType->code);
            $campo_identificacion = trim($factura->customer->identification);

            $identificacion = $doc->createElement('Identificacion');
            $receptor->appendChild($identificacion);

            $nodo = $doc->createElement('Tipo', $campo_tipo_identificacion);
            $identificacion->appendChild($nodo);

            $nodo = $doc->createElement('Numero', $campo_identificacion);
            $identificacion->appendChild($nodo);    

            /*
            if (!is_null($factura->customer->commercial_name) && !empty($factura->customer->commercial_name))
            {
                $nodo = $doc->createElement('NombreComercial', htmlspecialchars($factura->customer->commercial_name));
                $receptor->appendChild($nodo);
            }
            */    
            
            if (!is_null($factura->customer->province_id) && !is_null($factura->customer->canton_id) && !is_null($factura->customer->disctrict_id))
            {
                $ubicacion = $doc->createElement('Ubicacion');
                $receptor->appendChild($ubicacion);
                
                $nodo = $doc->createElement('Provincia', $factura->customer->province->code);
                $ubicacion->appendChild($nodo);
                
                $nodo = $doc->createElement('Canton', str_pad($factura->customer->canton->code, 2, '0', STR_PAD_LEFT));
                $ubicacion->appendChild($nodo);
                
                $nodo = $doc->createElement('Distrito', str_pad($factura->customer->disctrict->code, 2, '0', STR_PAD_LEFT));
                $ubicacion->appendChild($nodo);
                
                if (!is_null($factura->customer->other_signs) && !empty($factura->customer->other_signs)){
                    $nodo = $doc->createElement('OtrasSenas', htmlspecialchars($factura->customer->other_signs));
                    $ubicacion->appendChild($nodo);		
                }
                else
                {
                    $nodo = $doc->createElement('OtrasSenas', 'Otras Señas');
                    $ubicacion->appendChild($nodo);
                }
            }
            
            if (!is_null($factura->customer->country_code_phone) && !empty($factura->customer->country_code_phone) && !is_null($factura->customer->phone) && !empty($factura->customer->phone)){
                $telefono = $doc->createElement('Telefono');
                $receptor->appendChild($telefono);
            
                $nodo = $doc->createElement('CodigoPais', $factura->customer->country_code_phone);
                $telefono->appendChild($nodo);
            
                $nodo = $doc->createElement('NumTelefono', $factura->customer->phone);
                $telefono->appendChild($nodo);
            }
            
            
            if (!is_null($factura->customer->email) && !empty($factura->customer->email))
            {
                $nodo = $doc->createElement('CorreoElectronico', $factura->customer->email);
                $receptor->appendChild($nodo);
            }
        }

		// Otros elementos
		$nodo = $doc->createElement('CondicionVenta', $factura->conditionSale->code);
		$root->appendChild($nodo);

		$condition_sale_id = (int) $factura->condition_sale_id;
		
		if ($condition_sale_id === ConditionSale::getIdCreditConditionSale()) // Crédito
		{
		    $credit_days = (int) $factura->creditDays->name;
			$nodo = $doc->createElement('PlazoCredito', $credit_days);
			$root->appendChild($nodo);
		}

        /*
		$paymentMethods = PaymentMethodHasInvoice::find()->where(['invoice_id' => $factura->id])->all();

		$i = 1;
	    foreach ($paymentMethods AS $idx => $mp)
	    {
			if ($i <= 4)
			{
				$nodo = $doc->createElement('MedioPago', $mp->paymentMethod->code);
				$root->appendChild($nodo);
			}
			$i++;
		}						
        */    

		// Datos Del servicio
		$detalle = $doc->createElement('DetalleServicio');
		$root->appendChild($detalle);
		
		$i = 1;

		if ($factura->currency_id === Currency::getCurrencyIdByCode('USD'))
        {
            $strmoneda = 'DOLARES';
        }
		else
        {
            $strmoneda = 'COLONES';
        }

		foreach ($factura_detalles as $fdetalle)
		{		
			$linea = $doc->createElement('LineaDetalle');
			$detalle->appendChild($linea);
		
			$nodo = $doc->createElement('NumeroLinea', $i);
			$linea->appendChild($nodo);
            
			if (!is_null($fdetalle->product_id) && !empty($fdetalle->product_id))
			{
				$nodo = $doc->createElement('Codigo', $fdetalle->product->cabys->code);
				$linea->appendChild($nodo);
			}
            else
            if (!is_null($fdetalle->service_id) && !empty($fdetalle->service_id))
			{
				$nodo = $doc->createElement('CodigoCABYS', $fdetalle->service->cabys->code);
				$linea->appendChild($nodo);
			}
			
			$codigo = $doc->createElement('CodigoComercial');
			$linea->appendChild($codigo);
			
			$nodo = $doc->createElement('Tipo', '04');
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $fdetalle->code);
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Cantidad', number_format($fdetalle->quantity, 3, '.', ''));
			$linea->appendChild($nodo);

            $price_unit = $fdetalle->price_unit;

            $unit_type_code = $fdetalle->unitType->code;

            $nodo = $doc->createElement('UnidadMedida', $unit_type_code);
            $linea->appendChild($nodo);   

            if ($factura->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE){
                $nodo = $doc->createElement('TipoTransaccion', '01');
                $linea->appendChild($nodo);   
            }    
            
			$str = $fdetalle->description;

			$nodo = $doc->createElement('Detalle', htmlspecialchars($str));
			$linea->appendChild($nodo);

			$nodo = $doc->createElement('PrecioUnitario', number_format($price_unit, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('MontoTotal', number_format($fdetalle->getMonto(), 5, '.', ''));
			$linea->appendChild($nodo);
			
            /*
			if (!is_null($fdetalle->discount_amount) && $fdetalle->discount_amount > 0 && !empty($fdetalle->nature_discount) && !is_null($fdetalle->nature_discount)){
				$descuento = $doc->createElement('Descuento');
				$linea->appendChild($descuento);
			
				$nodo = $doc->createElement('MontoDescuento', number_format($fdetalle->discount_amount, 5, '.', ''));
				$descuento->appendChild($nodo);
			
				$nodo = $doc->createElement('NaturalezaDescuento', $fdetalle->nature_discount);
				$descuento->appendChild($nodo);
			}
            */    
			
			//$subtotal = $fdetalle->subtotal - $fdetalle->discount_amount;
            $subtotal = $fdetalle->getSubtotal();
			$nodo = $doc->createElement('SubTotal', number_format($subtotal, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('BaseImponible', number_format($subtotal, 5, '.', ''));
			$linea->appendChild($nodo);

            if ((!is_null($fdetalle->tax_rate_percent) && $fdetalle->tax_rate_percent >= 0) || (!is_null($fdetalle->exoneration_purchase_percent) && $fdetalle->exoneration_purchase_percent >= 0)) {
                $impuesto = $doc->createElement('Impuesto');
                $linea->appendChild($impuesto);

                $monto_impuesto = $fdetalle->getMontoImpuesto($strmoneda);

                if (!is_null($fdetalle->tax_type_id)) {
                    $nodo = $doc->createElement('Codigo', $fdetalle->taxType->code);
                    $impuesto->appendChild($nodo);

                    $tax_rate_type_code = (isset($fdetalle->taxRateType->code) && !empty($fdetalle->taxRateType->code))? $fdetalle->taxRateType->code : 0;
                    $nodo = $doc->createElement('CodigoTarifaIVA', $tax_rate_type_code);
                    $impuesto->appendChild($nodo);

                    $nodo = $doc->createElement('Tarifa', number_format($fdetalle->tax_rate_percent, 2, '.', ''));
                    $impuesto->appendChild($nodo);

                    $nodo = $doc->createElement('Monto', number_format($monto_impuesto, 4, '.', ''));
                    $impuesto->appendChild($nodo);
                }
                /*
                if (!is_null($fdetalle->exoneration_document_type_id) && $fdetalle->exoneration_purchase_percent >= 0) {
                    $montoExonerado = $fdetalle->getMontoImpuestoExonerado($strmoneda);
                    
                    if ($montoExonerado > 0)
                    {
                        $exoneracion = $doc->createElement('Exoneracion');
                        $impuesto->appendChild($exoneracion);

                        $nodo = $doc->createElement('TipoDocumento', $fdetalle->exonerationDocumentType->code);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('NumeroDocumento', $fdetalle->number_exoneration_doc);

                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('NombreInstitucion', $fdetalle->name_institution_exoneration);
                        $exoneracion->appendChild($nodo);

                        $fecha_exonerado = date('c', strtotime($fdetalle->exoneration_date));
                        $nodo = $doc->createElement('FechaEmision', $fecha_exonerado);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('PorcentajeExoneracion', $fdetalle->exoneration_purchase_percent);
                        $exoneracion->appendChild($nodo);
                        
                        $nodo = $doc->createElement('MontoExoneracion', number_format($montoExonerado, 5, '.', ''));
                        $exoneracion->appendChild($nodo);
                    }
                    $impuestoneto = $fdetalle->getMontoImpuestoNeto();

                    $nodo = $doc->createElement('ImpuestoNeto', number_format($impuestoneto, 5, '.', ''));
                    $linea->appendChild($nodo);
                } else {
                    $nodo = $doc->createElement('ImpuestoNeto', number_format($monto_impuesto, 5, '.', ''));
                    $linea->appendChild($nodo);
                }
                */    
            }
            
            $nodo = $doc->createElement('ImpuestoAsumidoEmisorFabrica', number_format(0, 5, '.', ''));
			$linea->appendChild($nodo);

            $nodo = $doc->createElement('ImpuestoNeto', number_format($monto_impuesto, 5, '.', ''));
			$linea->appendChild($nodo);

			$nodo = $doc->createElement('MontoTotalLinea', number_format($fdetalle->getMontoTotalLinea(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			$i++;
		}

		// Resumen de la factura
		$resumen = $doc->createElement('ResumenFactura');
		$root->appendChild($resumen);

		$codigo = $doc->createElement('CodigoTipoMoneda');
		$resumen->appendChild($codigo);
		
		$nodo = $doc->createElement('CodigoMoneda', $factura->currency->symbol);
		$codigo->appendChild($nodo);

        if ($factura->currency->symbol == 'CRC')
            $change_type = 1;
        else
            $change_type = $factura->change_type;
		
		$nodo = $doc->createElement('TipoCambio', number_format($change_type, 5, '.', ''));
		$codigo->appendChild($nodo);		

		$nodo = $doc->createElement('TotalServGravados', number_format($factura->totalServGravados, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExentos', number_format($factura->totalServExentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExonerado', number_format($factura->totalMontoServExonerado, 5, '.', ''));
		$resumen->appendChild($nodo);		
		
        $nodo = $doc->createElement('TotalServNoSujeto', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);		

		$nodo = $doc->createElement('TotalMercanciasGravadas', number_format($factura->totalMercanciasGravadas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasExentas', number_format($factura->totalMercanciasExentas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercExonerada', number_format($factura->totalMontoMercExonerado, 5, '.', ''));
		$resumen->appendChild($nodo);		
                
        $nodo = $doc->createElement('TotalMercNoSujeta', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);					
		
		$nodo = $doc->createElement('TotalGravado', number_format($factura->totalGravado, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExento', number_format($factura->totalExento, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExonerado', number_format($factura->totalExonerado, 5, '.', ''));
		$resumen->appendChild($nodo);		
		
        $nodo = $doc->createElement('TotalNoSujeto', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);		

		
		$nodo = $doc->createElement('TotalVenta', number_format($factura->totalVenta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalDescuentos', number_format($factura->totalDescuentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVentaNeta', number_format($factura->totalVentaNeta, 5, '.', ''));
		$resumen->appendChild($nodo);

        $desgloseImpuestos = $factura->getDesgloseImpuesto();        
        
        foreach ($desgloseImpuestos as $d){
            $desglose = $doc->createElement('TotalDesgloseImpuesto');
            $resumen->appendChild($desglose);
            
            $nodo = $doc->createElement('Codigo', $d['Codigo']);
            $desglose->appendChild($nodo);

            $nodo = $doc->createElement('CodigoTarifaIVA', $d['CodigoTarifaIVA']);
            $desglose->appendChild($nodo);
            
            $nodo = $doc->createElement('TotalMontoImpuesto', $d['TotalMontoImpuesto']);
            $desglose->appendChild($nodo);
        }

		$nodo = $doc->createElement('TotalImpuesto', number_format($factura->totalImpuesto, 5, '.', ''));
		$resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalImpAsumEmisorFabrica', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);
        
		$nodo = $doc->createElement('TotalIVADevuelto', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalOtrosCargos', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);        
		
        $paymentMethods = PaymentMethodHasInvoice::find()->where(['invoice_id' => $factura->id])->all();

        $medioPago = $doc->createElement('MedioPago');
        $resumen->appendChild($codigo);
        
        $nodo = $doc->createElement('TipoMedioPago', $paymentMethods[0]->paymentMethod->code);
        $medioPago->appendChild($nodo);

        $nodo = $doc->createElement('TotalMedioPago', $factura->total_comprobante);
        $medioPago->appendChild($nodo);

        $nodo = $doc->createElement('TotalComprobante', number_format($factura->total_comprobante, 5, '.', ''));
		$resumen->appendChild($nodo);

        /*
		// Aqui se coloca la información de referencia en caso de emitir una factura de contingencia
		if (($factura->contingency === 1 && !empty($factura->reference_number)) || ($factura->correct_invoice === 1 && !empty($factura->reference_number)))
		{
			$referencia = $doc->createElement('InformacionReferencia');
			$root->appendChild($referencia);

			$nodo = $doc->createElement('TipoDoc', '01');
			$referencia->appendChild($nodo);

			$nodo = $doc->createElement('Numero', $factura->reference_number);
			$referencia->appendChild($nodo);
			
			$fecha_r = date('Y-m-d', strtotime($factura->reference_emission_date));
			$fecha_referencia = date('c', strtotime($fecha_r));			
			$nodo = $doc->createElement('FechaEmision', $fecha_referencia);
			$referencia->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $factura->reference_code);
			$referencia->appendChild($nodo);
			
			$nodo = $doc->createElement('Razon', $factura->reference_reason);
			$referencia->appendChild($nodo);
		}
        */    
		$xml = $doc->saveXML();

		return base64_encode($xml);				
	}

    /**
     * Función para generar el XML de notas de credito
     *
     * @param Issuer $emisor
     * @param CreditNote $credit_note
     * @param ItemCreditNote[] $credit_note_items
     * @return string
     */
    function genXMLNC($emisor, $factura, $credit_note_items)
    {
        $fecha = date('c');
        $plazo_credito = $factura->conditionSale->code == '02' ? $factura->creditDays->name: '0';
        $ProveedorSistema = '3101615166';
        $doc  = new \DomDocument('1.0','UTF-8');
        $doc->formatOutput = true;

        $root = $doc->createElementNS('https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica', 'NotaCreditoElectronica');
        $doc->appendChild($root);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/facturaElectronica.xsd');


        $nodo = $doc->createElement('Clave', $factura->key);
        $root->appendChild($nodo);

        $nodo = $doc->createElement('ProveedorSistemas', $ProveedorSistema);
        $root->appendChild($nodo);
        
		$nodo = $doc->createElement('CodigoActividadEmisor', $emisor->code_economic_activity);
		$root->appendChild($nodo);

		$nodo = $doc->createElement('NumeroConsecutivo', $factura->consecutive);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$root->appendChild($nodo);
		
		// Datos del Emisor
		$nodoemisor = $doc->createElement('Emisor');
		$root->appendChild($nodoemisor);
		
		$nodo = $doc->createElement('Nombre', htmlspecialchars($emisor->name));
		$nodoemisor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$nodoemisor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($emisor->identificationType->code));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($emisor->identification));
		$identificacion->appendChild($nodo);
		
		if (!is_null($emisor->name) && !empty($emisor->name))
		{
			$nodo = $doc->createElement('NombreComercial', htmlspecialchars($emisor->name));
			$nodoemisor->appendChild($nodo);
		}

		$ubicacion = $doc->createElement('Ubicacion');
		$nodoemisor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $emisor->province->code);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($emisor->canton->code, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($emisor->disctrict->code, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($emisor->other_signs) && !empty($emisor->other_signs)){
			$nodo = $doc->createElement('OtrasSenas', htmlspecialchars($emisor->other_signs));
			$ubicacion->appendChild($nodo);
		}
		else
		{
			$nodo = $doc->createElement('OtrasSenas', 'Otras señas');
			$ubicacion->appendChild($nodo);
		}
		
		if (!is_null($emisor->country_code_phone) && !empty($emisor->country_code_phone) && !is_null($emisor->phone) && !empty($emisor->phone)){
			$telefono = $doc->createElement('Telefono');
			$nodoemisor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $emisor->country_code_phone);
			$telefono->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->phone);
			$telefono->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $emisor->email);
		$nodoemisor->appendChild($nodo);


        // Datos Receptor
		$receptor = $doc->createElement('Receptor');
		$root->appendChild($receptor);
		
		$nodo = $doc->createElement('Nombre', htmlspecialchars($factura->customer->name));
		$receptor->appendChild($nodo);

    
        $campo_tipo_identificacion = trim($factura->customer->identificationType->code);
        $campo_identificacion = trim($factura->customer->identification);

        $identificacion = $doc->createElement('Identificacion');
        $receptor->appendChild($identificacion);

        $nodo = $doc->createElement('Tipo', $campo_tipo_identificacion);
        $identificacion->appendChild($nodo);

        $nodo = $doc->createElement('Numero', $campo_identificacion);
        $identificacion->appendChild($nodo);    

		/*
		if (!is_null($factura->customer->commercial_name) && !empty($factura->customer->commercial_name))
		{
			$nodo = $doc->createElement('NombreComercial', htmlspecialchars($factura->customer->commercial_name));
			$receptor->appendChild($nodo);
		}
        */    
		
		if (!is_null($factura->customer->province_id) && !is_null($factura->customer->canton_id) && !is_null($factura->customer->disctrict_id))
		{
			$ubicacion = $doc->createElement('Ubicacion');
			$receptor->appendChild($ubicacion);
			
			$nodo = $doc->createElement('Provincia', $factura->customer->province->code);
			$ubicacion->appendChild($nodo);
			
			$nodo = $doc->createElement('Canton', str_pad($factura->customer->canton->code, 2, '0', STR_PAD_LEFT));
			$ubicacion->appendChild($nodo);
			
			$nodo = $doc->createElement('Distrito', str_pad($factura->customer->disctrict->code, 2, '0', STR_PAD_LEFT));
			$ubicacion->appendChild($nodo);
			
			if (!is_null($factura->customer->other_signs) && !empty($factura->customer->other_signs)){
				$nodo = $doc->createElement('OtrasSenas', htmlspecialchars($factura->customer->other_signs));
				$ubicacion->appendChild($nodo);		
			}
			else
			{
				$nodo = $doc->createElement('OtrasSenas', 'Otras Señas');
				$ubicacion->appendChild($nodo);
			}
		}
		
		if (!is_null($factura->customer->country_code_phone) && !empty($factura->customer->country_code_phone) && !is_null($factura->customer->phone) && !empty($factura->customer->phone)){
			$telefono = $doc->createElement('Telefono');
			$receptor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $factura->customer->country_code_phone);
			$telefono->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $factura->customer->phone);
			$telefono->appendChild($nodo);
		}
		
		
        if (!is_null($factura->customer->email) && !empty($factura->customer->email))
        {
		    $nodo = $doc->createElement('CorreoElectronico', $factura->customer->email);
		    $receptor->appendChild($nodo);
        }

		// Otros elementos
		$nodo = $doc->createElement('CondicionVenta', $factura->conditionSale->code);
		$root->appendChild($nodo);

		$condition_sale_id = (int) $factura->condition_sale_id;
		
		if ($condition_sale_id === ConditionSale::getIdCreditConditionSale()) // Crédito
		{
		    $credit_days = (int) $factura->creditDays->name;
			$nodo = $doc->createElement('PlazoCredito', $credit_days);
			$root->appendChild($nodo);
		}

        /*
		$paymentMethods = PaymentMethodHasInvoice::find()->where(['invoice_id' => $factura->id])->all();

		$i = 1;
	    foreach ($paymentMethods AS $idx => $mp)
	    {
			if ($i <= 4)
			{
				$nodo = $doc->createElement('MedioPago', $mp->paymentMethod->code);
				$root->appendChild($nodo);
			}
			$i++;
		}						
        */    

		// Datos Del servicio
		$detalle = $doc->createElement('DetalleServicio');
		$root->appendChild($detalle);
		
		$i = 1;

		if ($factura->currency_id === Currency::getCurrencyIdByCode('USD'))
        {
            $strmoneda = 'DOLARES';
        }
		else
        {
            $strmoneda = 'COLONES';
        }

		foreach ($factura_detalles as $fdetalle)
		{		
			$linea = $doc->createElement('LineaDetalle');
			$detalle->appendChild($linea);
		
			$nodo = $doc->createElement('NumeroLinea', $i);
			$linea->appendChild($nodo);

            /*
			if (!is_null($fdetalle->product_id) && !empty($fdetalle->product_id))
			{
				$nodo = $doc->createElement('Codigo', $fdetalle->product->cabys->code);
				$linea->appendChild($nodo);
			}
            else
            if (!is_null($fdetalle->service_id) && !empty($fdetalle->service_id))
			{
				$nodo = $doc->createElement('Codigo', $fdetalle->service->cabys->code);
				$linea->appendChild($nodo);
			}
            */    
			
			$codigo = $doc->createElement('CodigoComercial');
			$linea->appendChild($codigo);
			
			$nodo = $doc->createElement('Tipo', '04');
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $fdetalle->code);
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Cantidad', number_format($fdetalle->quantity, 3, '.', ''));
			$linea->appendChild($nodo);

            $price_unit = $fdetalle->price_unit;

            $unit_type_code = $fdetalle->unitType->code;

            $nodo = $doc->createElement('UnidadMedida', $unit_type_code);
            $linea->appendChild($nodo);   

            $nodo = $doc->createElement('TipoTransaccion', '01');
            $linea->appendChild($nodo);   
            
			$str = $fdetalle->description;

			$nodo = $doc->createElement('Detalle', htmlspecialchars($str));
			$linea->appendChild($nodo);

			$nodo = $doc->createElement('PrecioUnitario', number_format($price_unit, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('MontoTotal', number_format($fdetalle->getMonto(), 5, '.', ''));
			$linea->appendChild($nodo);
			
            /*
			if (!is_null($fdetalle->discount_amount) && $fdetalle->discount_amount > 0 && !empty($fdetalle->nature_discount) && !is_null($fdetalle->nature_discount)){
				$descuento = $doc->createElement('Descuento');
				$linea->appendChild($descuento);
			
				$nodo = $doc->createElement('MontoDescuento', number_format($fdetalle->discount_amount, 5, '.', ''));
				$descuento->appendChild($nodo);
			
				$nodo = $doc->createElement('NaturalezaDescuento', $fdetalle->nature_discount);
				$descuento->appendChild($nodo);
			}
            */    
			
			//$subtotal = $fdetalle->subtotal - $fdetalle->discount_amount;
            $subtotal = $fdetalle->getSubtotal();
			$nodo = $doc->createElement('SubTotal', number_format($subtotal, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('BaseImponible', number_format($subtotal, 5, '.', ''));
			$linea->appendChild($nodo);

            if ((!is_null($fdetalle->tax_rate_percent) && $fdetalle->tax_rate_percent >= 0) || (!is_null($fdetalle->exoneration_purchase_percent) && $fdetalle->exoneration_purchase_percent >= 0)) {
                $impuesto = $doc->createElement('Impuesto');
                $linea->appendChild($impuesto);

                $monto_impuesto = $fdetalle->getMontoImpuesto($strmoneda);

                if (!is_null($fdetalle->tax_type_id)) {
                    $nodo = $doc->createElement('Codigo', $fdetalle->taxType->code);
                    $impuesto->appendChild($nodo);

                    $tax_rate_type_code = (isset($fdetalle->taxRateType->code) && !empty($fdetalle->taxRateType->code))? $fdetalle->taxRateType->code : 0;
                    $nodo = $doc->createElement('CodigoTarifaIVA', $tax_rate_type_code);
                    $impuesto->appendChild($nodo);

                    $nodo = $doc->createElement('Tarifa', number_format($fdetalle->tax_rate_percent, 2, '.', ''));
                    $impuesto->appendChild($nodo);

                    $nodo = $doc->createElement('Monto', number_format($monto_impuesto, 4, '.', ''));
                    $impuesto->appendChild($nodo);
                }
                /*
                if (!is_null($fdetalle->exoneration_document_type_id) && $fdetalle->exoneration_purchase_percent >= 0) {
                    $montoExonerado = $fdetalle->getMontoImpuestoExonerado($strmoneda);
                    
                    if ($montoExonerado > 0)
                    {
                        $exoneracion = $doc->createElement('Exoneracion');
                        $impuesto->appendChild($exoneracion);

                        $nodo = $doc->createElement('TipoDocumento', $fdetalle->exonerationDocumentType->code);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('NumeroDocumento', $fdetalle->number_exoneration_doc);

                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('NombreInstitucion', $fdetalle->name_institution_exoneration);
                        $exoneracion->appendChild($nodo);

                        $fecha_exonerado = date('c', strtotime($fdetalle->exoneration_date));
                        $nodo = $doc->createElement('FechaEmision', $fecha_exonerado);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('PorcentajeExoneracion', $fdetalle->exoneration_purchase_percent);
                        $exoneracion->appendChild($nodo);
                        
                        $nodo = $doc->createElement('MontoExoneracion', number_format($montoExonerado, 5, '.', ''));
                        $exoneracion->appendChild($nodo);
                    }
                    $impuestoneto = $fdetalle->getMontoImpuestoNeto();

                    $nodo = $doc->createElement('ImpuestoNeto', number_format($impuestoneto, 5, '.', ''));
                    $linea->appendChild($nodo);
                } else {
                    $nodo = $doc->createElement('ImpuestoNeto', number_format($monto_impuesto, 5, '.', ''));
                    $linea->appendChild($nodo);
                }
                */    
            }
            
            $nodo = $doc->createElement('ImpuestoAsumidoEmisorFabrica', number_format(0, 5, '.', ''));
			$linea->appendChild($nodo);

            $nodo = $doc->createElement('ImpuestoNeto', number_format($monto_impuesto, 5, '.', ''));
			$linea->appendChild($nodo);

			$nodo = $doc->createElement('MontoTotalLinea', number_format($fdetalle->getMontoTotalLinea(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			$i++;
		}

		// Resumen de la factura
		$resumen = $doc->createElement('ResumenFactura');
		$root->appendChild($resumen);

		$codigo = $doc->createElement('CodigoTipoMoneda');
		$resumen->appendChild($codigo);
		
		$nodo = $doc->createElement('CodigoMoneda', $factura->currency->symbol);
		$codigo->appendChild($nodo);

        if ($factura->currency->symbol == 'CRC')
            $change_type = 1;
        else
            $change_type = $factura->change_type;
		
		$nodo = $doc->createElement('TipoCambio', number_format($change_type, 5, '.', ''));
		$codigo->appendChild($nodo);		

		$nodo = $doc->createElement('TotalServGravados', number_format($factura->totalServGravados, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExentos', number_format($factura->totalServExentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExonerado', number_format($factura->totalMontoServExonerado, 5, '.', ''));
		$resumen->appendChild($nodo);		
		
        $nodo = $doc->createElement('TotalServNoSujeto', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);		

		$nodo = $doc->createElement('TotalMercanciasGravadas', number_format($factura->totalMercanciasGravadas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasExentas', number_format($factura->totalMercanciasExentas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercExonerada', number_format($factura->totalMontoMercExonerado, 5, '.', ''));
		$resumen->appendChild($nodo);		
                
        $nodo = $doc->createElement('TotalMercNoSujeta', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);					
		
		$nodo = $doc->createElement('TotalGravado', number_format($factura->totalGravado, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExento', number_format($factura->totalExento, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExonerado', number_format($factura->totalExonerado, 5, '.', ''));
		$resumen->appendChild($nodo);		
		
        $nodo = $doc->createElement('TotalNoSujeto', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);		

		
		$nodo = $doc->createElement('TotalVenta', number_format($factura->totalVenta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalDescuentos', number_format($factura->totalDescuentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVentaNeta', number_format($factura->totalVentaNeta, 5, '.', ''));
		$resumen->appendChild($nodo);

        
        $desglose = $doc->createElement('TotalDesgloseImpuesto');
		$resumen->appendChild($codigo);
		
		$nodo = $doc->createElement('Codigo', $fdetalle->taxType->code);
		$desglose->appendChild($nodo);

        $tax_rate_type_code = (isset($fdetalle->taxRateType->code) && !empty($fdetalle->taxRateType->code))? $fdetalle->taxRateType->code : 0;

        $nodo = $doc->createElement('CodigoTarifaIVA', $tax_rate_type_code);
		$desglose->appendChild($nodo);
        
        $nodo = $doc->createElement('TotalMontoImpuesto', $factura->totalImpuesto);
		$desglose->appendChild($nodo);

		$nodo = $doc->createElement('TotalImpuesto', number_format($factura->totalImpuesto, 5, '.', ''));
		$resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalImpAsumEmisorFabrica', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);
        
		$nodo = $doc->createElement('TotalIVADevuelto', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalOtrosCargos', number_format(0, 5, '.', ''));
		$resumen->appendChild($nodo);        
		
        $paymentMethods = PaymentMethodHasInvoice::find()->where(['invoice_id' => $factura->id])->all();

        $medioPago = $doc->createElement('MedioPago');
        $resumen->appendChild($codigo);
        
        $nodo = $doc->createElement('TipoMedioPago', $paymentMethods[0]->paymentMethod->code);
        $medioPago->appendChild($nodo);

        $nodo = $doc->createElement('TotalMedioPago', $factura->total_comprobante);
        $medioPago->appendChild($nodo);

        $nodo = $doc->createElement('TotalComprobante', number_format($factura->total_comprobante, 5, '.', ''));
		$resumen->appendChild($nodo);


        // Aqui se coloca la información de referencia en caso de emitir una factura de contingencia

        $referencia = $doc->createElement('InformacionReferencia');
        $root->appendChild($referencia);

        $nodo = $doc->createElement('TipoDoc', '01');
        $referencia->appendChild($nodo);

        $nodo = $doc->createElement('Numero', $factura->reference_number);
        $referencia->appendChild($nodo);

        $fecha_r = date('Y-m-d', strtotime($factura->reference_emission_date));
        $fecha_referencia = date('c', strtotime($fecha_r));
        $nodo = $doc->createElement('FechaEmision', $fecha_referencia);
        $referencia->appendChild($nodo);

        $nodo = $doc->createElement('Codigo', $factura->reference_code);
        $referencia->appendChild($nodo);

        $nodo = $doc->createElement('Razon', $factura->reference_reason);
        $referencia->appendChild($nodo);


        $xml = $doc->saveXML();

        return base64_encode($xml);
    }

    /**
     * Función para generar el XML de notas de debito
     *
     * @param Issuer $emisor
     * @param DebitNote $debit_note-
     * @param ItemDebitNote[] $debit_note_items
     * @return string
     */
    function genXMLND($emisor, $debit_note, $debit_note_items)
    {
        $fecha = date('c');
        $plazo_credito = $debit_note->conditionSale->code == '02' ? $debit_note->creditDays->name: '0';

        $doc  = new \DomDocument('1.0','UTF-8');
        $doc->formatOutput = true;

        $root = $doc->createElementNS('https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaDebitoElectronica', 'NotaDebitoElectronica');
        $doc->appendChild($root);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaDebitoElectronica'.' '.'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.3/notaDebitoElectronica.xsd');

        $nodo = $doc->createElement('Clave', $debit_note->key);
        $root->appendChild($nodo);

        $nodo = $doc->createElement('CodigoActividad', $emisor->code_economic_activity);
        $root->appendChild($nodo);

        $nodo = $doc->createElement('NumeroConsecutivo', $debit_note->consecutive);
        $root->appendChild($nodo);

        $nodo = $doc->createElement('FechaEmision', $fecha);
        $root->appendChild($nodo);

        // Datos del Emisor
        $nodoemisor = $doc->createElement('Emisor');
        $root->appendChild($nodoemisor);

        $nodo = $doc->createElement('Nombre', htmlspecialchars($emisor->name));
        $nodoemisor->appendChild($nodo);

        $identificacion = $doc->createElement('Identificacion');
        $nodoemisor->appendChild($identificacion);

        $nodo = $doc->createElement('Tipo', trim($emisor->identificationType->code));
        $identificacion->appendChild($nodo);

        $nodo = $doc->createElement('Numero', trim($emisor->identification));
        $identificacion->appendChild($nodo);

        if (!is_null($emisor->name) && !empty($emisor->name))
        {
            $nodo = $doc->createElement('NombreComercial', htmlspecialchars($emisor->name));
            $nodoemisor->appendChild($nodo);
        }

        $ubicacion = $doc->createElement('Ubicacion');
        $nodoemisor->appendChild($ubicacion);

        $nodo = $doc->createElement('Provincia', $emisor->province->code);
        $ubicacion->appendChild($nodo);

        $nodo = $doc->createElement('Canton', str_pad($emisor->canton->code, 2, '0', STR_PAD_LEFT));
        $ubicacion->appendChild($nodo);

        $nodo = $doc->createElement('Distrito', str_pad($emisor->disctrict->code, 2, '0', STR_PAD_LEFT));
        $ubicacion->appendChild($nodo);

        if (!is_null($emisor->other_signs) && !empty($emisor->other_signs)){
            $nodo = $doc->createElement('OtrasSenas', htmlspecialchars($emisor->other_signs));
            $ubicacion->appendChild($nodo);
        }
        else
        {
            $nodo = $doc->createElement('OtrasSenas', '-');
            $ubicacion->appendChild($nodo);
        }

        if (!is_null($emisor->country_code_phone) && !empty($emisor->country_code_phone) && !is_null($emisor->phone) && !empty($emisor->phone)){
            $telefono = $doc->createElement('Telefono');
            $nodoemisor->appendChild($telefono);

            $nodo = $doc->createElement('CodigoPais', $emisor->country_code_phone);
            $telefono->appendChild($nodo);

            $nodo = $doc->createElement('NumTelefono', $emisor->phone);
            $telefono->appendChild($nodo);
        }

        if (!is_null($emisor->country_code_fax) && !empty($emisor->country_code_fax) && !is_null($emisor->fax) && !empty($emisor->fax)){
            $fax = $doc->createElement('Fax');
            $nodoemisor->appendChild($fax);

            $nodo = $doc->createElement('CodigoPais', $emisor->country_code_fax);
            $fax->appendChild($nodo);

            $nodo = $doc->createElement('NumTelefono', $emisor->fax);
            $fax->appendChild($nodo);
        }

        $nodo = $doc->createElement('CorreoElectronico', $emisor->email);
        $nodoemisor->appendChild($nodo);


        // Datos Receptor
        $receptor = $doc->createElement('Receptor');
        $root->appendChild($receptor);

        $nodo = $doc->createElement('Nombre', htmlspecialchars($debit_note->customer->name));
        $receptor->appendChild($nodo);

        if ($debit_note->customer->identificationType->code == '05') // Pasaporte entonces poner el código 
        {
            $nodo = $doc->createElement('IdentificacionExtranjero', htmlspecialchars($debit_note->customer->foreign_identification));
            $receptor->appendChild($nodo);
        }
        else		
        {
            $campo_tipo_identificacion = trim($debit_note->customer->identificationType->code);
            $campo_identificacion = trim($debit_note->customer->identification);

            $identificacion = $doc->createElement('Identificacion');
            $receptor->appendChild($identificacion);
    
            $nodo = $doc->createElement('Tipo', $campo_tipo_identificacion);
            $identificacion->appendChild($nodo);
    
            $nodo = $doc->createElement('Numero', $campo_identificacion);
            $identificacion->appendChild($nodo);    
        }

        if (!is_null($debit_note->customer->commercial_name) && !empty($debit_note->customer->commercial_name))
        {
            $nodo = $doc->createElement('NombreComercial', htmlspecialchars($debit_note->customer->commercial_name));
            $receptor->appendChild($nodo);
        }

        if (!is_null($debit_note->customer->province_id) && !is_null($debit_note->customer->canton_id) && !is_null($debit_note->customer->disctrict_id))
        {
            $ubicacion = $doc->createElement('Ubicacion');
            $receptor->appendChild($ubicacion);

            $nodo = $doc->createElement('Provincia', $debit_note->customer->province->code);
            $ubicacion->appendChild($nodo);

            $nodo = $doc->createElement('Canton', str_pad($debit_note->customer->canton->code, 2, '0', STR_PAD_LEFT));
            $ubicacion->appendChild($nodo);

            $nodo = $doc->createElement('Distrito', str_pad($debit_note->customer->disctrict->code, 2, '0', STR_PAD_LEFT));
            $ubicacion->appendChild($nodo);

            if (!is_null($debit_note->customer->other_signs) && !empty($debit_note->customer->other_signs)){
                $nodo = $doc->createElement('OtrasSenas', htmlspecialchars($debit_note->customer->other_signs));
                $ubicacion->appendChild($nodo);
            }
            else
            {
                $nodo = $doc->createElement('OtrasSenas', '-');
                $ubicacion->appendChild($nodo);
            }
        }

        if (!is_null($debit_note->customer->country_code_phone) && !empty($debit_note->customer->country_code_phone) && !is_null($debit_note->customer->phone) && !empty($debit_note->customer->phone)){
            $telefono = $doc->createElement('Telefono');
            $receptor->appendChild($telefono);

            $nodo = $doc->createElement('CodigoPais', $debit_note->customer->country_code_phone);
            $telefono->appendChild($nodo);

            $nodo = $doc->createElement('NumTelefono', $debit_note->customer->phone);
            $telefono->appendChild($nodo);
        }

        if (!is_null($debit_note->customer->country_code_fax) && !empty($debit_note->customer->country_code_fax) && !is_null($debit_note->customer->fax) && !empty($debit_note->customer->fax)){
            $fax = $doc->createElement('Fax');
            $receptor->appendChild($fax);

            $nodo = $doc->createElement('CodigoPais', $debit_note->customer->country_code_fax);
            $fax->appendChild($nodo);

            $nodo = $doc->createElement('NumTelefono', $debit_note->customer->fax);
            $fax->appendChild($nodo);
        }

        if (!is_null($debit_note->customer->email) && !empty($debit_note->customer->email))  
        {      
            $nodo = $doc->createElement('CorreoElectronico', $debit_note->customer->email);
            $receptor->appendChild($nodo);
        }

        // Otros elementos
        $nodo = $doc->createElement('CondicionVenta', $debit_note->conditionSale->code);
        $root->appendChild($nodo);

        $condition_sale_id = (int) $debit_note->condition_sale_id;

        if ($condition_sale_id === ConditionSale::getIdCreditConditionSale()) // Crédito
        {
            $credit_days = (int) $debit_note->creditDays->name;
            $nodo = $doc->createElement('PlazoCredito', $credit_days);
            $root->appendChild($nodo);
        }

        $paymentMethods = PaymentMethodHasDebitNote::find()->where(['debit_note_id' => $debit_note->id])->all();

        $i = 1;
        foreach ($paymentMethods AS $idx => $mp)
        {
            if ($i <= 4)
            {
                $nodo = $doc->createElement('MedioPago', $mp->paymentMethod->code);
                $root->appendChild($nodo);
            }
            $i++;
        }

        // Datos Del servicio
        $detalle = $doc->createElement('DetalleServicio');
        $root->appendChild($detalle);

        $i = 1;

        if ($debit_note->currency_id === Currency::getCurrencyIdByCode('USD'))
        {
            $strmoneda = 'DOLARES';
        }
        else
        {
            $strmoneda = 'COLONES';
        }

        foreach ($debit_note_items as $fdetalle)
        {
            $linea = $doc->createElement('LineaDetalle');
            $detalle->appendChild($linea);

            $nodo = $doc->createElement('NumeroLinea', $i);
            $linea->appendChild($nodo);

            if (!is_null($fdetalle->product_id) && !empty($fdetalle->product_id))
            {
                $nodo = $doc->createElement('Codigo', $fdetalle->product->cabys->code);
                $linea->appendChild($nodo);
            }
            else
            if (!is_null($fdetalle->service_id) && !empty($fdetalle->service_id))
			{
				$nodo = $doc->createElement('Codigo', $fdetalle->service->cabys->code);
				$linea->appendChild($nodo);
			}

            $codigo = $doc->createElement('CodigoComercial');
            $linea->appendChild($codigo);

            $nodo = $doc->createElement('Tipo', '01');
            $codigo->appendChild($nodo);

            $nodo = $doc->createElement('Codigo', $fdetalle->code);
            $codigo->appendChild($nodo);

            $nodo = $doc->createElement('Cantidad', number_format($fdetalle->quantity, 3, '.', ''));
            $linea->appendChild($nodo);

            //$unit_type_code = 'Unid';
            $price_unit = $fdetalle->price_unit;

            $unit_type_code = $fdetalle->unitType->code;
            /*
            if(isset($fdetalle->unitType->code) && !empty($fdetalle->unitType->code))
            {
                $unit_type_code = $fdetalle->unitType->code;

                if($unit_type_code == 'UND' || $unit_type_code == 'Unid')
                {
                    $unit_type_code = 'Unid';
                }
                else
                {
                    if(isset($fdetalle->product_id) && !empty($fdetalle->product_id))
                    {
                        if($unit_type_code == 'CAJ' || $unit_type_code == 'CJ')
                        {
                            $quantity_by_box = (isset($fdetalle->product->quantity_by_box) && !empty($fdetalle->product->quantity_by_box) && $fdetalle->product->quantity_by_box > 0 )? $fdetalle->product->quantity_by_box : 1;
                            if(isset($fdetalle->product->quantity_by_box))
                            {
                                $price_unit *= $quantity_by_box;
                            }
                        }
                        elseif($unit_type_code == 'BULT' || $unit_type_code == 'PAQ')
                        {
                            $quantity_by_package = (isset($fdetalle->product->package_quantity) && !empty($fdetalle->product->package_quantity) && $fdetalle->product->package_quantity > 0 )? $fdetalle->product->package_quantity : 1;

                            if(isset($fdetalle->product->package_quantity))
                            {
                                $price_unit *= $quantity_by_package;
                            }
                        }
                    }

                    $unit_type_code = 'Otros';
                }
            }
            */
            /*
            if ($unit_type_code != 'Unid'){
                $nodo = $doc->createElement('UnidadMedida', UnitType::CODE_OTROS);
                $linea->appendChild($nodo);
    
                $nodo = $doc->createElement('UnidadMedidaComercial', $unit_type_code);
                $linea->appendChild($nodo);
            }
            else
            {
                $nodo = $doc->createElement('UnidadMedida', $unit_type_code);
                $linea->appendChild($nodo);   
            }   
            */         
            $nodo = $doc->createElement('UnidadMedida', $unit_type_code);
            $linea->appendChild($nodo);   

            $str = $fdetalle->description;

            $nodo = $doc->createElement('Detalle', htmlspecialchars($str));
            $linea->appendChild($nodo);

            $nodo = $doc->createElement('PrecioUnitario', number_format($price_unit, 5, '.', ''));
            $linea->appendChild($nodo);

            $nodo = $doc->createElement('MontoTotal', number_format($fdetalle->getMonto(), 5, '.', ''));
            $linea->appendChild($nodo);

            if (!is_null($fdetalle->discount_amount) && $fdetalle->discount_amount > 0 && !empty($fdetalle->nature_discount) && !is_null($fdetalle->nature_discount)){
                $descuento = $doc->createElement('Descuento');
                $linea->appendChild($descuento);

                $nodo = $doc->createElement('MontoDescuento', number_format($fdetalle->discount_amount, 5, '.', ''));
                $descuento->appendChild($nodo);

                $nodo = $doc->createElement('NaturalezaDescuento', $fdetalle->nature_discount);
                $descuento->appendChild($nodo);
            }

            //$subtotal = $fdetalle->subtotal - $fdetalle->discount_amount;
            $subtotal = $fdetalle->getSubtotal();
            $nodo = $doc->createElement('SubTotal', number_format($subtotal, 5, '.', ''));
            $linea->appendChild($nodo);

            $nodo = $doc->createElement('BaseImponible', number_format($subtotal, 5, '.', ''));
            $linea->appendChild($nodo);

            if ((!is_null($fdetalle->tax_amount) && $fdetalle->tax_amount >= 0) || (!is_null($fdetalle->exonerate_amount) && $fdetalle->exonerate_amount >= 0)) {
                $impuesto = $doc->createElement('Impuesto');
                $linea->appendChild($impuesto);

                $monto_impuesto = $fdetalle->getMontoImpuesto($strmoneda);

                if (!is_null($fdetalle->tax_type_id)) {
                    $nodo = $doc->createElement('Codigo', $fdetalle->taxType->code);
                    $impuesto->appendChild($nodo);

                    $tax_rate_type_code = (isset($fdetalle->taxRateType->code) && !empty($fdetalle->taxRateType->code))? $fdetalle->taxRateType->code : 0;
                    $nodo = $doc->createElement('CodigoTarifa', $tax_rate_type_code);
                    $impuesto->appendChild($nodo);

                    $nodo = $doc->createElement('Tarifa', number_format($fdetalle->tax_rate_percent, 2, '.', ''));
                    $impuesto->appendChild($nodo);

                    /*
                    $nodo = $doc->createElement('FactorIVA', number_format(13, 2, '.', ''));
                    $impuesto->appendChild($nodo);
                    */

                    $nodo = $doc->createElement('Monto', number_format($monto_impuesto, 4, '.', ''));
                    $impuesto->appendChild($nodo);
                }

                if (!is_null($fdetalle->exoneration_document_type_id) && $fdetalle->exoneration_purchase_percent >= 0) {
                    $montoExonerado = $fdetalle->getMontoImpuestoExonerado($strmoneda);

                    if ($montoExonerado > 0)
                    {
                        $exoneracion = $doc->createElement('Exoneracion');
                        $impuesto->appendChild($exoneracion);

                        $nodo = $doc->createElement('TipoDocumento', $fdetalle->exonerationDocumentType->code);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('NumeroDocumento', $fdetalle->number_exoneration_doc);

                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('NombreInstitucion', $fdetalle->name_institution_exoneration);
                        $exoneracion->appendChild($nodo);

                        $fecha_exonerado = date('c', strtotime($fdetalle->exoneration_date));
                        $nodo = $doc->createElement('FechaEmision', $fecha_exonerado);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('PorcentajeExoneracion', $fdetalle->exoneration_purchase_percent);
                        $exoneracion->appendChild($nodo);

                        $nodo = $doc->createElement('MontoExoneracion', number_format($montoExonerado, 5, '.', ''));
                        $exoneracion->appendChild($nodo);
                    }

                    $impuestoneto = $fdetalle->getMontoImpuestoNeto();

                    $nodo = $doc->createElement('ImpuestoNeto', number_format($impuestoneto, 5, '.', ''));
                    $linea->appendChild($nodo);
                } else {
                    $nodo = $doc->createElement('ImpuestoNeto', number_format($monto_impuesto, 5, '.', ''));
                    $linea->appendChild($nodo);
                }
            }

            $nodo = $doc->createElement('MontoTotalLinea', number_format($fdetalle->getMontoTotalLinea(), 5, '.', ''));
            $linea->appendChild($nodo);

            $i++;
        }

        // Resumen de la nota
        $resumen = $doc->createElement('ResumenFactura');
        $root->appendChild($resumen);

        $codigo = $doc->createElement('CodigoTipoMoneda');
        $resumen->appendChild($codigo);

        $nodo = $doc->createElement('CodigoMoneda', $debit_note->currency->symbol);
        $codigo->appendChild($nodo);

        $nodo = $doc->createElement('TipoCambio', number_format($debit_note->change_type, 5, '.', ''));
        $codigo->appendChild($nodo);

        $nodo = $doc->createElement('TotalServGravados', number_format($debit_note->totalServGravados, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalServExentos', number_format($debit_note->totalServExentos, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalServExonerado', number_format($debit_note->totalMontoServExonerado, 5, '.', ''));
        $resumen->appendChild($nodo);


        $nodo = $doc->createElement('TotalMercanciasGravadas', number_format($debit_note->totalMercanciasGravadas, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalMercanciasExentas', number_format($debit_note->totalMercanciasExentas, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalMercExonerada', number_format($debit_note->totalMontoMercExonerado, 5, '.', ''));
        $resumen->appendChild($nodo);


        $nodo = $doc->createElement('TotalGravado', number_format($debit_note->totalGravado, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalExento', number_format($debit_note->totalExento, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalExonerado', number_format($debit_note->totalExonerado, 5, '.', ''));
        $resumen->appendChild($nodo);


        $nodo = $doc->createElement('TotalVenta', number_format($debit_note->totalVenta, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalDescuentos', number_format($debit_note->totalDescuentos, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalVentaNeta', number_format($debit_note->totalVentaNeta, 5, '.', ''));
        $resumen->appendChild($nodo);


        $nodo = $doc->createElement('TotalImpuesto', number_format($debit_note->totalImpuesto, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalIVADevuelto', number_format(0, 5, '.', ''));
        $resumen->appendChild($nodo);

        $nodo = $doc->createElement('TotalComprobante', number_format($debit_note->total_comprobante, 5, '.', ''));
        $resumen->appendChild($nodo);

        // Aqui se coloca la información de referencia en caso de emitir una factura de contingencia

        $referencia = $doc->createElement('InformacionReferencia');
        $root->appendChild($referencia);

        $nodo = $doc->createElement('TipoDoc', '01');
        $referencia->appendChild($nodo);

        $nodo = $doc->createElement('Numero', $debit_note->reference_number);
        $referencia->appendChild($nodo);

        $fecha_r = date('Y-m-d', strtotime($debit_note->reference_emission_date));
        $fecha_referencia = date('c', strtotime($fecha_r));
        $nodo = $doc->createElement('FechaEmision', $fecha_referencia);
        $referencia->appendChild($nodo);

        $nodo = $doc->createElement('Codigo', $debit_note->reference_code);
        $referencia->appendChild($nodo);

        $nodo = $doc->createElement('Razon', $debit_note->reference_reason);
        $referencia->appendChild($nodo);

        $xml = $doc->saveXML();

        return base64_encode($xml);
    }

    public function genXMLMr($documento, $receptor)
	{
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');		
		

		$doc  = new \DomDocument('1.0','UTF-8');
		$doc->formatOutput = true;		       
		
		$root = $doc->createElementNS('https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/mensajeReceptor', 'MensajeReceptor');
		$doc->appendChild($root);
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');	
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/mensajeReceptor'.' '.'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.3/mensajeReceptor.xsd');		

		$nodo = $doc->createElement('Clave', $documento->key);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroCedulaEmisor', $documento->transmitter_identification);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmisionDoc', $fecha);
		$root->appendChild($nodo);
		
		$mensaje = 1;
		if ($documento->status == 1) // Aceptado
			$mensaje = 1;
			
		if ($documento->status == 2) // Aceptado Parcial
			$mensaje = 2;

		if ($documento->status == 3) // Rechazado
			$mensaje = 3;
			
		$nodo = $doc->createElement('Mensaje', $mensaje);
		$root->appendChild($nodo);

		if (!is_null($documento->message_detail) && !empty($documento->message_detail))
		{
			$nodo = $doc->createElement('DetalleMensaje', $documento->message_detail);
			$root->appendChild($nodo);
		}

		if (!is_null($documento->total_tax) && !empty($documento->total_tax) && $documento->total_tax >= 0)
		{
			$nodo = $doc->createElement('MontoTotalImpuesto', $documento->total_tax);
			$root->appendChild($nodo);
		}

		$nodo = $doc->createElement('CodigoActividad', $receptor->code_economic_activity);
		$root->appendChild($nodo);	

		$nodo = $doc->createElement('CondicionImpuesto', '01');
		$root->appendChild($nodo);	
		
		$nodo = $doc->createElement('TotalFactura', $documento->total_invoice);
		$root->appendChild($nodo);		
		
		$nodo = $doc->createElement('NumeroCedulaReceptor', $receptor->identification);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroConsecutivoReceptor', $documento->consecutive);
		$root->appendChild($nodo);
		
		$xml = $doc->saveXML();
		return base64_encode($xml);				
	}    
}

