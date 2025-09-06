<?php 

namespace common\components\ftp;

use backend\components\ApiBCCR;
use backend\models\business\Customer;
use backend\models\business\Invoice;
use backend\models\business\ItemInvoice;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\nomenclators\UtilsConstants;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser;
use Yii;

class DataProcessor
{

    public function processPdfData($filePath)
    {        
        // Leer el contenido del PDF
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        // Convertir el texto en líneas
        $lines = explode("\n", $text);

        // Líneas que se deben eliminar del texto
        $linesToRemove = [            
            'Total empresa:',
            'Pagos empresa:',
            'Neto a pagar por la empresa:',
            'Pague este monto:',
            'IVA',
            'Total conductor:',
            'Pagos conductor:',
            'Neto a pagar conductor:'
        ];

        // Extraer la segunda fecha encontrada
        $invoice_date = $this->extractSecondDate($text);

        // Filtrar las líneas específicas
        $filteredData = array_filter($lines, function($line) use ($linesToRemove) {
            foreach ($linesToRemove as $lineToRemove) {
                if (stripos($line, $lineToRemove) !== false) {
                    return false;
                }
            }
            return true;
        });

        // Elimino todas las lineas que se encuentran despues de Total de cargos
        $filteredLines = [];
        foreach ($filteredData as $line) {
            if (strpos($line, 'Total de cargos') !== false) {
                break;
            }
            $filteredLines[] = $line;
        }
        unset($filteredData);
        $filteredData = $filteredLines;

        //Yii::info('Datos filtrados del PDF: ' . implode("\n", $filteredData),  'application');
            
        $invoiceNumber = NULL;
        // Procesar las líneas filtradas y buscar el número de contrato
        foreach ($filteredData as $line) {
            //Yii::info('Línea de texto del PDF: ' . $line,  'application');

            // Usa una expresión regular para extraer el número de la factura            
            if (preg_match('/Factura\s+(\d+)/i', $line, $matches)) {
                $invoiceNumber = trim($matches[1]);
                // Aquí puedes guardar el número de la factura en la base de datos o hacer otra cosa con él
                Yii::info('Número de factura: ' . $invoiceNumber,  'application');                
            }
            else
            if (preg_match('/Factura de cliente\s+(\d+)/i', $line, $matches)) {
                $invoiceNumber = trim($matches[1]);
                // Aquí puedes guardar el número de la factura en la base de datos o hacer otra cosa con él
                Yii::info('Número de factura: ' . $invoiceNumber,  'application');     
            }
            else            
            if (preg_match('/N\.º contrato:\s*(\d+)/i', $line, $matches)) {
                $invoiceNumber = trim($matches[1]);

                // Aquí puedes guardar el número de contrato en la base de datos o hacer otra cosa con él
                Yii::info('Número de contrato: ' . $invoiceNumber, 'application');
            }
        }
        
        if (!is_null($invoiceNumber))
        {
            $cliente = $this->CreateCliente($text);
           
            //$invoice = Invoice::find()->where(['contract'=>$invoiceNumber, 'status_hacienda'=>UtilsConstants::HACIENDA_STATUS_NOT_SENT])->one();
            $count = Invoice::find()
                                ->where(['contract' => $invoiceNumber])
                                ->andWhere(['status_hacienda'=>[UtilsConstants::HACIENDA_STATUS_NOT_SENT, UtilsConstants::HACIENDA_STATUS_RECEIVED, 
                                                                 UtilsConstants::HACIENDA_STATUS_ACCEPTED]])
                                ->count();
            if ($count <= 2) 
            {
                $condition_sale_id = 1; // CONTADO

                if (!$invoice_date)
                $invoice_date = date('Y-m-d H:i:s');
                else
                $invoice_date = date('Y-m-d H:i:s', strtotime($invoice_date));

                $invoice = new Invoice();
                $invoice->branch_office_id = 1;
                $invoice->box_id = 1;
                if (!is_null($cliente))
                    $invoice->customer_id = $cliente->id;
                else
                    $invoice->customer_id = 1;
                $invoice->currency_id = 2;
                $invoice->status = UtilsConstants::INVOICE_STATUS_PENDING;
                $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
                $invoice->condition_sale_id = $condition_sale_id;
                $invoice->invoice_type =  $cliente->pre_invoice_type;  //UtilsConstants::PRE_INVOICE_TYPE_TICKET;
                $invoice->change_type = ApiBCCR::getChangeTypeOfIssuer();
                $invoice->emission_date = $invoice_date;
                $invoice->contract = $invoiceNumber;
                $invoice->payment_methods = 1;
                //$invoice->confirmation_number = $confirmation_number;
                //$invoice->consecutive = $invoice->generateConsecutive();
                $invoice->user_id = Yii::$app->user->id;
                
                if ($invoice->save()){
                    Yii::info('FACTURA: ' . $invoice->id . ' Guardada ');
                    $payment = new PaymentMethodHasInvoice;
                    $payment->invoice_id = $invoice->id;
                    $payment->payment_method_id = 1; // Efectivo
                    $payment->save();
                    Yii::info('METODO DE PAGO DE FACTURA: ' . $invoice->id . ' Guardada ', 'application');
                }

                if (!is_null($invoice))
                {
                    // Procesar las líneas filtradas
                    $code = 1;   
                    $discount = 0;
                    $discount_nature = '';
                    foreach ($filteredData as $line) {
                        Yii::info('Línea de texto del PDF: ' . $line,  'application');

                        // Usa una expresión regular para extraer la descripción y el precio
                        //if (preg_match('/^(.*\D)(\d+\.\d{2})$/', $line, $matches)) {                            
                        if (preg_match('/^(.*?)(-?\d+\.\d{2})$/', $line, $matches)) {  
                            $description = trim($matches[1]);
                            $price = trim($matches[2]);
                            if ($price < 0){
                                $discount += abs($price);
                                $discount_nature = $description;                                
                            }
                            else
                            {                            
                                $itemInvoice = new ItemInvoice;
                                $itemInvoice->invoice_id = $invoice->id;
                                $itemInvoice->code = str_pad($code, 5, '0', STR_PAD_LEFT);
                                $itemInvoice->description = $description;
                                $itemInvoice->service_id = 1;
                                $itemInvoice->quantity = 1;
                                $itemInvoice->price_unit = $price;
                                $itemInvoice->subtotal = $price;
                                $itemInvoice->tax_amount = $price * 13 / 100;
                                $itemInvoice->discount_amount = 0;
                                $itemInvoice->exonerate_amount = 0;
                                $itemInvoice->price_total = $price;
                                $itemInvoice->user_id = Yii::$app->user->id;
                                $itemInvoice->price_type = 1;
                                $itemInvoice->unit_type_id = 1;
                                $itemInvoice->nature_discount = NULL;
                                $itemInvoice->tax_type_id = 1;
                                $itemInvoice->tax_rate_type_id = 8;
                                $itemInvoice->exoneration_document_type_id = NULL;
                                $itemInvoice->number_exoneration_doc = NULL;
                                $itemInvoice->name_institution_exoneration = NULL;
                                $itemInvoice->exoneration_date = NULL;
                                $itemInvoice->exoneration_purchase_percent = NULL;
                                $itemInvoice->tax_rate_percent = 13;
                                $itemInvoice->created_at = date('Y-m-d H:i:s');
                                $itemInvoice->updated_at = date('Y-m-d H:i:s');
                                $itemInvoice->save();
                                $code++;
                                // Aquí puedes guardar la descripción y el precio en la base de datos o hacer otra cosa con ellos
                                Yii::info('Descripción: ' . $description . ' - Precio: ' . $price,  'application');
                            }
                        }
                    }
                    if ($discount > 0){
                        $this->applyDiscountToItems($invoice->id, $discount, $discount_nature);
                        
                    }
                    if ($invoice->save())
                        return "Factura: ".$invoiceNumber. " guardada";
                    else
                        return "Factura: ".$invoiceNumber. " No guardada";
                }
            }
            else
            {
                Yii::info('Invoice: ' . $invoiceNumber . ' Duplicada ');
                return "Factura: ".$invoiceNumber. " No guardada";
            }
        }
        else
            return "Factura no encontrada";
    }

    /*
    public function applyDiscount($invoice_id, $discount, $discount_nature){
        // Buscar el item que contenga la descripción RATE CHARGE con mayor valor y aplicarle el descuento
        $item = ItemInvoice::find()->where(['invoice_id'=>$invoice_id])
                                    ->andWhere(['>', 'price_unit', $discount])
                                    ->orderBy(['price_unit' => SORT_DESC])
                                    ->one();

        if (!is_null($item)){
            $item->discount_amount = $discount;
            $item->nature_discount = $discount_nature;
            $item->subtotal = $item->price_unit * $item->quantity - $discount;
            $item->tax_amount = $item->getMontoImpuesto();
            $item->price_total = $item->subtotal;
            $item->save();
        }
    }
    */
    public function applyDiscountToItems($invoiceId, $discountAmount, $discountNature)
    {        
        // Obtener los items de la factura
        $items = ItemInvoice::find()
            ->where(['invoice_id' => $invoiceId])
            ->andWhere(['>', 'price_unit', 0])
            ->all();

        //die(var_dump($invoiceId.' - '. $discountAmount. ' - '. $discountNature));            
    
        // Buscar si hay un item con un precio mayor al descuento
        $itemWithGreaterPrice = null;
        foreach ($items as $item) {
            if ($item->price_unit > $discountAmount) {
                $itemWithGreaterPrice = $item;
                break;
            }
        }
    
        if ($itemWithGreaterPrice) {
            // Asignar el descuento completo a este item
            $itemWithGreaterPrice->discount_amount = $discountAmount;
            $itemWithGreaterPrice->nature_discount = $discountNature;
            $itemWithGreaterPrice->subtotal = $itemWithGreaterPrice->price_unit * $itemWithGreaterPrice->quantity - $discountAmount;
            $itemWithGreaterPrice->tax_amount = $itemWithGreaterPrice->getMontoImpuesto();
            $itemWithGreaterPrice->price_total = $itemWithGreaterPrice->subtotal;
            $itemWithGreaterPrice->save();
        } else {
            // Prorratear el descuento entre todos los items
            $totalUnitPrice = array_sum(array_column($items, 'unit_price'));
    
            foreach ($items as $item) {
                $item->discount = ($item->unit_price / $totalUnitPrice) * $discountAmount;
                $item->discount_amount = $item->discount;
                $item->nature_discount = $discountNature;
                $item->subtotal = $item->price_unit * $item->quantity - $item->discount;
                $item->tax_amount = $item->getMontoImpuesto();
                $item->price_total = $item->subtotal;
                $item->save();
            }
        }
    
        return true;
    }
    

    public function processExcelData($data)
    {
        $index = 0;    
        foreach ($data as $row) {
            // Asumiendo que el Excel tiene columnas 'A' para 'customer_id' y 'B' para 'invoice_id'
            $estado = trim(strtolower($row['CV']));
            //Yii::info('Fila: '. $index.'  ' . $row['N']. ' Estado: '. $estado,  'application');
            $index++;
            if ($index > 1 && $estado == 'cerrado')
            {      
                Yii::info('Fila: '. $index.'  ' . $row['N']. ' Estado: '. $estado,  'application');          
                $name = $row['N'] . ' '. $row['M'];
                $identification = $row['K'];
                $pais = $row['S']; // Si pais es CRI es costa rica nacional
                $address = $row['O'];
                $phone = $row['T'];
                $email = strtolower($row['U']);
                $pre_invoice_type =  UtilsConstants::PRE_INVOICE_TYPE_TICKET;
                $condition_sale_id = 1; // CONTADO

                $customer = null;
                
                if (!empty($email))
                    $customer = Customer::find()->where(['email'=>$email])->one();
                
                if (is_null($customer)){
                    $customer = new Customer();
                    $customer->name = $name;
                    $customer->code = $identification;
                    
                    if (!empty($pais) && strtoupper($pais) != 'CRI'){  // Es extranjero
                        $customer->foreign_identification = $identification;
                        $customer->identification_type_id = 10; // Pasaporte
                        $customer->identification = '999999999';
                    }
                    else
                    {
                        $customer->identification = $identification;
                        $customer->identification_type_id = 6; // Cédula fisica
                    }

                    $customer->address = $address;
                    $customer->phone   = $phone;
                    $customer->email   = $email;
                    $customer->pre_invoice_type = $pre_invoice_type;
                    $customer->condition_sale_id = $condition_sale_id;    
                    //if ($index == 2)        
                    //    die(var_dump($customer));                    
                    if ($customer->save())
                        Yii::info('Cliente: ' . $customer->id . ' Guardado ');
                    else
                    {
                      Yii::info('Cliente: ' . $customer->id . ' No Guardado ');
                      $customer = NULL;
                      //die(var_dump($customer->getErrors()));
                    }
                }
               
                if (!is_null($customer)){                    
                    
                    $contract = trim($row['B']);
                    $confirmation_number = trim($row['A']);
                    $invoice = Invoice::find()->where(['contract'=>$contract, 'confirmation_number'=>$confirmation_number])->one();
                    
                    if (is_null($invoice)) {                        
                        $invoice = new Invoice();
                        $invoice->branch_office_id = 1;
                        $invoice->box_id = 1;
                        $invoice->customer_id = 1;
                        $invoice->currency_id = 2;
                        $invoice->status = UtilsConstants::INVOICE_STATUS_PENDING;
                        $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
                        $invoice->condition_sale_id = $condition_sale_id;
                        $invoice->invoice_type = $pre_invoice_type;
                        $invoice->change_type = ApiBCCR::getChangeTypeOfIssuer();
                        $invoice->emission_date = date('Y-m-d H:i:s');
                        $invoice->contract = $contract;
                        $invoice->payment_methods = 1;
                        $invoice->confirmation_number = $confirmation_number;
                        //$invoice->consecutive = $invoice->generateConsecutive();
                        $invoice->user_id = Yii::$app->user->id;
                       
                        if ($invoice->save()){
                            Yii::info('FACTURA: ' . $invoice->id . ' Guardada ');
                            $payment = new PaymentMethodHasInvoice;
                            $payment->invoice_id = $invoice->id;
                            $payment->payment_method_id = 1; // Efectivo
                            $payment->save();
                            Yii::info('METODO DE PAGO DE FACTURA: ' . $invoice->id . ' Guardada ');
                        }
                    }
                        
                }
                    
            }            
        }
    }

    public function processXmlData($data)
    {
        foreach ($data->record as $record) {
            $customerId = (string) $record->customer_id;
            $invoiceId = (string) $record->invoice_id;

            // Actualizar o insertar los datos en las tablas customer e invoice
            // Por ejemplo:
            $customer = Customer::findOne($customerId);
            if (!$customer) {
                $customer = new Customer();
                $customer->id = $customerId;
            }
            $customer->some_field = (string) $record->some_field; // Actualizar otros campos según tus necesidades
            $customer->save();

            $invoice = Invoice::findOne($invoiceId);
            if (!$invoice) {
                $invoice = new Invoice();
                $invoice->id = $invoiceId;
            }
            $invoice->some_field = (string) $record->some_field; // Actualizar otros campos según tus necesidades
            $invoice->save();
        }
    }

    // Definir una función para extraer la segunda fecha encontrada en el texto
    public function extractSecondDate($text) {
        // Buscar todas las fechas en el formato DD/MM/YYYY
        preg_match_all('/\b\d{2}\/\d{2}\/\d{4}\b/', $text, $matches);
        
        // Si hay al menos dos fechas, devolver la segunda
        if (isset($matches[0]) && count($matches[0]) > 1) {
            return $matches[0][1];
        }

        // Devolver null si no se encuentran suficientes fechas
        return null;
    }

    public function CreateCliente($text)
    {
        $data = $this->extractClientInfo($text);        
        $name = $data['cliente'];        
        //$phone = $data['telefono'];
        //$address = substr($data['direccion'], 0, 220); // Extrae los primeros 220 caracteres;

        //$identification = '-';
        //$pais = '-'; // Si pais es CRI es costa rica nacional
        
        //$email = '';
        $pre_invoice_type =  UtilsConstants::PRE_INVOICE_TYPE_TICKET;
        $condition_sale_id = 1; // CONTADO

        $cliente_contado = Customer::find()->where(['id'=>1])->one();  // Cliente contado
        $tempname = $cliente_contado->name . ' - '. trim($name);

        $customer = Customer::find()->where(['name'=>$tempname])->one();
                
        if (is_null($customer)){
            
            $customer = new Customer();
            $customer->attributes = $cliente_contado->attributes;
            $customer->code = $customer->generateCode();
            $customer->name = $cliente_contado->name . ' - '.  $name;                        
            /*
            if (!empty($pais) && strtoupper($pais) != 'CRI'){  // Es extranjero
                $customer->foreign_identification = $identification;
                $customer->identification_type_id = 10; // Pasaporte
                $customer->identification = '999999999';
            }
            else
            {
                $customer->identification = $identification;
                $customer->identification_type_id = 6; // Cédula fisica
            }
            */

            //$customer->address = $address;
            //$customer->phone   = $phone;
            //$customer->email   = $email;
            $customer->pre_invoice_type = $pre_invoice_type;
            $customer->condition_sale_id = $condition_sale_id;    
            if ($customer->save())
                Yii::info('Cliente: ' . $customer->id . ' Guardado ');
            else
            {      
                Yii::info('Cliente: ' . $customer->id . ' No Guardado ');
                $customer = NULL;
            }
        }
        return $customer;
    }

    // Función para extraer el nombre del cliente y el teléfono después de "Fecha factura:"
    function extractClientInfo($text) {
        $lines = explode("\n", $text);
    
        $clientInfo = [
            'cliente' => null,
            'direccion' => '',
            'telefono' => null
        ];
    
        $capturingAddress = false;
        $addressStartIndex = -1;
    
        // Buscar el nombre del cliente y la dirección
        foreach ($lines as $index => $line) {
            // Limpiar espacios en blanco alrededor de la línea
            $line = trim($line);
    
            // Si ya estamos capturando la dirección y encontramos "Tel.:", dejamos de capturar
            if ($capturingAddress && strpos($line, "Tel.:") !== false) {
                $clientInfo['telefono'] = preg_replace('/[^0-9\+]/', '', substr($line, strpos($line, ':') + 1));
                break;
            }
    
            // Si estamos capturando la dirección, añadir la línea a la dirección
            if ($capturingAddress && strpos($line, "Tel.:") === false) {
                if (!empty($line)) {
                    $clientInfo['direccion'] .= $line . "\n";
                }
            }
    
            // Buscar el nombre del cliente
            if (strpos($line, "Fecha factura:") !== false) {
                // El nombre del cliente está en la siguiente línea
                $clientInfo['cliente'] = trim($lines[$index + 1]);
                $capturingAddress = true; // Empezar a capturar la dirección desde la línea siguiente
                $addressStartIndex = $index + 2; // La dirección comienza en la línea siguiente al nombre del cliente
                continue;
            }
        }
    
        // Limpiar la dirección de posibles saltos de línea al final
        $clientInfo['direccion'] = trim($clientInfo['direccion']);
    
        // Eliminar el nombre del cliente de la dirección si se ha incluido
        if ($addressStartIndex != -1) {
            $lines = explode("\n", $clientInfo['direccion']);
            $clientInfo['direccion'] = implode("\n", array_slice($lines, 1));
            // Eliminar los saltos de línea de la dirección
            $clientInfo['direccion'] = str_replace("\n", " ", $clientInfo['direccion']);
        }
            
        return $clientInfo;
    }    
}

?>