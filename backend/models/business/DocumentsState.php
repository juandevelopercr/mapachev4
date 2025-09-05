<?php

namespace backend\models\business;

use Yii;

/**
 * This is the model class for table "documents_state".
 *
 * @property int $id
 * @property string $name
 *
 * @property Documents[] $documents
 */
class DocumentsState extends \yii\db\ActiveRecord
{
	public $estado;	
	const ACEPTADO_RECEPTOR = 1;
	const ACEPTADO_PARCIAL_RECEPTOR = 2;
	const RECHAZADO_RECEPTOR = 3;	
	
	const ACEPTADO_HACIENDA	= 4;
	const ACEPTADO_PARCIAL_HACIENDA	= 5;
	const RECHAZADO_HACIENDA = 6;	
	
	const RECIBIDO_HACIENDA = 7; // Para indicar que fue recibido por hacienda			
	const RECIBIDO_PARCIAL_HACIENDA	 = 8;
	const RECIBIDO_RECHAZADO_HACIENDA = 9;    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documents_state';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Documents::className(), ['state_id' => 'id']);
    }
}
