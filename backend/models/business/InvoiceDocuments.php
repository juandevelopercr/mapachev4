<?php

namespace backend\models\business;

use Yii;
//use mohorev\file\UploadBehavior;
/**
 * This is the model class for table "casos_documentos".
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $descripcion
 * @property string $documento
 * @property string $fecha
 *
 * @property Casos $caso
 */
class InvoiceDocuments extends \yii\db\ActiveRecord
{	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_documents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id'], 'integer'],
            //[['descripcion', 'adjuntar_a_factura'], 'required'],
            [['fecha'], 'safe'],
            [['descripcion'], 'string', 'max' => 100],
			['documento', 'file', 'extensions' => 'doc, docx, pdf, xls, xlsx, jpg, jpeg, bmp, tif, png, gif', 'on' => ['default', 'insert', 'update']],		
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_id' => 'Factura',
            'descripcion' => 'Descripción',
            'documento' => 'Documento',
            'fecha' => 'Fecha',
			'adjuntar_a_factura'=> 'Adjujntar a factura',
        ];
    }

    /**
     * @inheritdoc
     */
    /*
    function behaviors()
    {
        return [
            [			   
                'class' => UploadBehavior::class,
                'attribute' => 'documento',
                'scenarios' => ['default', 'insert', 'update'],
                'path' => '@webroot/documentos',
                'url' => '@web/documentos',
            ],
        ];
    }
    */


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFactura()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }
	
	function afterFind()
	{
		if (!is_null($this->fecha) && !empty($this->fecha))
		{
			$this->fecha = date("d-m-Y", strtotime($this->fecha));	
		}
        return true;
	}
	    
	public function beforeSave($insert)
	{
		parent::beforeSave($insert);
        if (is_null($this->fecha) && empty($this->fecha)){                        
            $this->fecha = date("Y-m-d");	                
        }
        else
            $this->fecha = date("Y-m-d", strtotime($this->fecha));	                
        return true;
	}
    
    public function afterSave( $insert, $changedAttributes ){
        parent::afterSave($insert, $changedAttributes);
        if(!$insert){            
            if (isset($changedAttributes['documento']))
            {
                $olddocumento = $changedAttributes['documento'];       
                //die(var_dump($this));    
                //do something here with the old email value
                if ($olddocumento != $this->documento && file_exists(Yii::getAlias('@backend') . "/web/documentos/".$olddocumento))
                    @unlink(Yii::getAlias('@backend') . "/web/documentos/".$olddocumento);	
            }
        }
        return true;
    }

}