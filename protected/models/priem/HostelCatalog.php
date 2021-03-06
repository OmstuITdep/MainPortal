<?php

/**
 * This is the model class for table "hostel_catalog".
 *
 * The followings are the available columns in table 'hostel_catalog':
 * @property integer $id
 * @property string $description
 * @property string $parent
 *
 * The followings are the available model relations:
 * @property HostelAgreement[] $hostelAgreements
 * @property HostelContract[] $hostelContracts
 * @property HostelCost[] $hostelCosts
 */
class HostelCatalog extends CActiveRecord
{
	const TYPE_BUDGET = 1;         	//бюджетный договор
	const TYPE_COMMERCIAL = 2;      //коммерческий договор
	const TYPE_EXPRESS = 3;         //срочный договор
	const TYPE_CHANGE_COST = 4;     //доп соглашние на изменение цены
	const TYPE_SOCIAL = 5;         	//доп соглашние при налчии соц.стипендии
	const TYPE_ALL_CONTRACT = 6; 	//все типы договоров

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'hostel_catalog';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('description', 'required'),
			array('description, parent', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, description, parent', 'safe', 'on'=>'search'),
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
			'hostelAgreements' => array(self::HAS_MANY, 'HostelAgreement', 'agrType'),
			'hostelContracts' => array(self::HAS_MANY, 'HostelContract', 'contType'),
			'hostelCosts' => array(self::HAS_MANY, 'HostelCost', 'contType'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Уникальный ключ',
			'description' => 'Описание ключа',
			'parent' => 'Значение родитель'
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
		$criteria->compare('description',$this->description,true);
		$criteria->compare('parent',$this->description,true);

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
	 * @return HostelCatalog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
