<?php

/**
 * This is the model class for table "gal_up_load".
 *
 * The followings are the available columns in table 'gal_up_load':
 * @property integer $id
 * @property string $nrec
 * @property integer $lectureroid
 * @property string $auditoriumoids
 * @property string $contname
 */
class GalUpLoad extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'gal_up_load';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, nrec, lectureroid, auditoriumoids, contname', 'required'),
			array('id, lectureroid', 'numerical', 'integerOnly'=>true),
			array('nrec', 'length', 'max'=>8),
			array('auditoriumoids, contname', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, nrec, lectureroid, auditoriumoids, contname', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'nrec' => 'Nrec',
			'lectureroid' => 'Lectureroid',
			'auditoriumoids' => 'Auditoriumoids',
			'contname' => 'Contname',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('nrec',$this->nrec,true);
		$criteria->compare('lectureroid',$this->lectureroid);
		$criteria->compare('auditoriumoids',$this->auditoriumoids,true);
		$criteria->compare('contname',$this->contname,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection()
	{
		return Yii::app()->db2;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return GalUpLoad the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
