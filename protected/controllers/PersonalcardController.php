<?php

class PersonalcardController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index', 'updateFieldCheckRight', 'updateDataField', 'school', 'eduDoc', 'eduLevel',
                    'address', 'addressFull', 'gr', 'passport', 'familystate', 'print', 'choiseBook'
                ),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionchoiseBook() {
        $galIdMassive = Yii::app()->user->getGalIdMass();
        if (null === $galIdMassive) {
            return $this->redirect(['student/nolink']);
        }

        foreach ($galIdMassive as $galid){
            $info[] = Yii::app()->db2->createCommand()
                ->select('tp.fio F$FIO, tc.code F$CODE, tc.name F$NAME, tuc.wformed F$WFORMED, td.sfld#1# F$SFLD#1#, tus.sdepcode F$SDEPCODE, tp.nrec ID')
                ->from(uStudent::model()->tableName() . ' tus')
                ->leftJoin(uCurriculum::model()->tableName() . ' tuc', 'tus.ccurr = tuc.nrec')
                ->leftJoin(Catalog::model()->tableName() . ' tc', 'tc.nrec=tuc.cspeciality')
                ->leftJoin(Person::model()->tableName() . ' tp', 'tp.nrec=tus.cpersons')
                ->leftJoin(Dopinfo::model()->tableName() . ' td', 'td.cperson = tp.nrec')
                ->where('tp.nrec=:id', [':id' => $galid['Galid']])
                ->queryRow();
        }

        if (empty($info)) {
            return $this->redirect(['student/nolink']);
        }
        $this->layout = '//layouts/column1';
        return $this->render('choiseBook', array('infos' => $info));
    }

	/**
	 * -----------------------------------------------------------------------------------------------------------------
	 */
	public function actionIndex($id=null)
    {
        $galId = Yii::app()->user->getGalId();
        if (null === $galId) {
            return $this->redirect(['student/nolink']);
        }
        if(!isset($_SESSION)){
            session_start();
        }
        $galIdMassive = Yii::app()->user->getGalIdMass();
        if(isset($id)){
            $prov = 0;
            foreach ($galIdMassive as $galunid){
                if($galunid['Galid'] == $id){$prov = 1;}
            }
            if($prov == 1) {
                $galId = $id;
                $_SESSION['ID'] = $id;
            }
        }else{
            if(isset($_SESSION['ID'])) {
                $galId = $_SESSION['ID'];
            }else{
                $_SESSION['ID'] = $galId;
            }
        }

        $sql = "SELECT
CONCAT(fac.LONGNAME, ', ', tus.SDEPARTMENT, ', ', CASE WHEN bup.wformed = 0 THEN '??????????'
 WHEN bup.wformed = 1 THEN '??????????????'
 WHEN bup.wformed = 2 THEN '????????????????'
 end) AS 'placeOfStudy',

 tp.FIO AS 'fio',
 tp.nrec AS 'pnrec',
 CONCAT(spec.code, ' ', spec.name) AS 'spec',
 ts.name AS 'fin',
td.`sfld#1#` as 'contNmb', 
td.`DFLD#1#` as 'contBegin',
cel.`SFLD#1#` AS 'entName',
tp.sex AS 'sex',
tp.borndate AS 'borndate',
 gr.name AS 'gr',
 passtype.name AS 'passVid',
 tp1.nrec as 'passNrec',
  tp1.ser AS 'pser',
  tp1.nmb AS 'pnmb',
  tp1.givenby AS 'givenby',
tp1.givendate AS 'givendate',
  tp1.todate AS 'todate',
  tp1.givenpodr AS 'givenpodr',
  edu.name AS 'eduLevel',
  tc.name as 'eduDoc',
  te.series AS 'eduSeria',
  te.DIPLOMNMB AS 'eduNmb',
  te.DIPLOMDATE as 'eduDipDate',
  tus1.SNAME AS 'eduPlace',
  fam.name as 'familystate',
-- rup.COURSE '????????',
 
case
when uz1.wtype = 0 then uz.SADDRESS1
when uz2.wtype = 0 then CONCAT_WS(', ', uz1.SNAME, uz.SADDRESS1)
when uz3.wtype = 0 then CONCAT_WS(', ', uz2.SNAME, uz1.SNAME , uz.SADDRESS1)
when uz4.wtype = 0 then CONCAT_WS(', ', uz3.SNAME , uz2.SNAME , uz1.SNAME , uz.SADDRESS1)
when uz5.wtype = 0 then CONCAT_WS(', ', uz4.SNAME , uz3.SNAME , uz2.SNAME , uz1.SNAME , uz.SADDRESS1)
WHEN uz6.wtype = 0 then CONCAT_WS(', ', uz5.SNAME , uz4.SNAME , uz3.SNAME , uz2.SNAME , uz1.SNAME , uz.SADDRESS1)
 END 'eduAddr',


case
when ab1.wtype = 0 then ab.SNAME
when ab2.wtype = 0 then CONCAT_WS(', ', ab1.SNAME , ab.SNAME)
when ab3.wtype = 0 then CONCAT_WS(', ', ab2.SNAME , ab1.SNAME , ab.SNAME)
when ab4.wtype = 0 then CONCAT_WS(', ', ab3.SNAME , ab2.SNAME , ab1.SNAME , ab.SNAME)
when ab5.wtype = 0 then CONCAT_WS(', ', ab4.SNAME , ab3.SNAME , ab2.SNAME , ab1.SNAME , ab.SNAME)
 END 'bornAddr',

case
when ter.wtype = 0 then pass.SADDRESS1
when ter1.wtype = 0 then CONCAT_WS(', ', ter.SNAME , pass.SADDRESS1)
when ter2.wtype = 0 then CONCAT_WS(', ', ter1.SNAME , ter.SNAME , pass.SADDRESS1)
when ter3.wtype = 0 then CONCAT_WS(', ', ter2.SNAME , ter1.SNAME , ter.SNAME , pass.SADDRESS1)
when ter4.wtype = 0 then CONCAT_WS(', ', ter3.SNAME , ter2.SNAME , ter1.SNAME , ter.SNAME , pass.SADDRESS1)
when ter41.wtype = 0 then CONCAT_WS(', ', ter4.SNAME , ter3.SNAME , ter2.SNAME , ter1.SNAME , ter.SNAME , pass.SADDRESS1)
 END 'passAddr',

case
when ter10.wtype = 0 then live.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(', ', ter10.SNAME , live.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(', ', ter11.SNAME , ter10.SNAME , live.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(', ', ter12.SNAME , ter11.SNAME , ter10.SNAME , live.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(', ', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , live.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(', ', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , live.SADDRESS1)
 END 'liveAddr', 


case
when ter20.wtype = 0 then vrem.SADDRESS1
when ter21.wtype = 0 then CONCAT_WS(', ', ter20.SNAME , vrem.SADDRESS1)
when ter22.wtype = 0 then CONCAT_WS(', ', ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
when ter23.wtype = 0 then CONCAT_WS(', ', ter22.SNAME , ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
when ter24.wtype = 0 then CONCAT_WS(', ', ter23.SNAME , ter22.SNAME , ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
when ter25.wtype = 0 then CONCAT_WS(', ', ter24.SNAME , ter23.SNAME , ter22.SNAME , ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
 END 'tempAddr',
 
 (SELECT
 GROUP_CONCAT(tel1.ADDR )
 FROM gal_COMMUNICATIONS tel1 WHERE tp.nrec = tel1.PERSON and tel1.COMTYPE in (0x800000000000022A, 0x800000000000022B, 0x80000000000003A7)) as 'phone',

 (SELECT  GROUP_CONCAT(mail.EMAIL)  FROM gal_COMMUNICATIONS mail WHERE tp.nrec = mail.PERSON and mail.COMTYPE = 0x80000000000003A6) as 'email'



FROM gal_PERSONS tp
LEFT JOIN gal_U_STUDENT tus on tus.CPERSONS = tp.nrec
LEFT JOIN gal_CATALOGS gr on gr.nrec = tp.GR
LEFT JOIN gal_PASSPORTS tp1 ON tp1.nrec = tp.passprus

LEFT JOIN gal_CATALOGS passtype ON passtype.nrec = tp1.DOCNAME
LEFT JOIN gal_CATALOGS fam ON fam.nrec = tp.familystate
LEFT JOIN gal_EDUCATION te ON te.PERSON = tp.nrec AND te.IATTR = 1
LEFT JOIN gal_U_SCHOOL tus1 on tus1.nrec = te.NAME
LEFT JOIN gal_CATALOGS edu ON edu.nrec = te.LEVEL
LEFT JOIN gal_CATALOGS tc ON te.seqnmb = tc.CODE AND tc.CPARENT = 0x80000000000002D6
LEFT JOIN gal_APPOINTMENTS ta ON ta.nrec = CASE WHEN tp.APPOINTCUR = 0x8000000000000000 THEN tp.APPOINTLAST ELSE tp.APPOINTCUR END
LEFT JOIN gal_U_STUD_FINSOURCE tusf ON tusf.nrec = ta.CREF2 -- ??????.????????????
LEFT JOIN gal_SPKAU ts ON ts.nrec = tusf.CFINSOURCE -- ???? APPOINTMENTS
LEFT JOIN gal_STAFFSTRUCT st on ta.STAFFSTR = st.nrec
LEFT JOIN gal_U_CURRICULUM bup on bup.nrec = st.CSTR
-- LEFT JOIN gal_U_CURRICULUM rup ON rup.nrec = ta.CDOPINF
LEFT JOIN gal_CATALOGS spec ON spec.nrec = bup.CSPECIALITY
LEFT JOIN gal_CATALOGS fac ON fac.nrec = bup.CFACULTY
LEFT JOIN gal_DOPINFO td ON td.nrec = (SELECT cont.nrec from gal_dopinfo cont WHERE cont.CPERSON = tp.nrec AND cont.CDOPTBL = 0x8001000000000007 ORDER BY cont.`DFLD#1#` DESC LIMIT 1)
LEFT JOIN gal_DOPINFO za4 ON za4.CPERSON = tp.nrec AND za4.CDOPTBL = 0x8001000000000003
LEFT JOIN gal_DOPINFO cel ON cel.CPERSON = tp.nrec AND cel.CDOPTBL = 0x8001000000000001
LEFT JOIN gal_CATALOGS opk ON opk.nrec = cel.`CFLD#1#` AND opk.CPARENT = 0x8001000000002697

-- ?????????????? ??????????????????
LEFT JOIN gal_U_SCHOOL us ON us.nrec = te.NAME
LEFT JOIN gal_U_SETTLEMENTS adr1 ON us.CCITY = adr1.nrec -- ?????? ??????????
LEFT JOIN gal_U_COUNTRY tuc ON us.CCOUNTRY = tuc.nrec -- ????????????
LEFT JOIN gal_U_COUNTRY tuc1 ON us.CREGION = tuc1.nrec -- ????????????
LEFT JOIN gal_CATALOGS tc1 ON tc1.CODE = te.SEQNMB AND tc1.CPARENT = 0x80000000000002D6 -- ?????? ?????????????????? ???? ??????????????????????

-- ?????????? ???? 2 ???????????????? ????????????????
LEFT JOIN gal_ADDRESSN uz ON uz.nrec = te.LEARNADDR
left join gal_sterr uz1 on uz1.nrec = uz.CSTERR
left join gal_sterr uz2 on uz2.nrec = uz1.CPARENT
left join gal_sterr uz3 on uz3.nrec = uz2.CPARENT
left join gal_sterr uz4 on uz4.nrec = uz3.CPARENT
left join gal_sterr uz5 on uz5.nrec = uz4.CPARENT
left join gal_sterr uz6 on uz6.nrec = uz5.CPARENT

-- ???????????? ????????????
LEFT JOIN gal_ADDRESSN born ON tp.BORNADDR = born.nrec -- ?????????? ????????????????
left join gal_ADDRESSN live on tp.LIVEADDR = live.nrec -- ?????????? ????????????????????
left join gal_ADDRESSN pass on tp.PASSPADDR = pass.nrec -- ?????????? ????????????????
left join gal_ADDRESSN vrem on vrem.CPERSON = tp.nrec and vrem.OBJTYPE = 55 -- ?????????? ????.??????????????????????

-- ?????????? ????????????????
left join gal_sterr ab on ab.nrec = born.CSTERR
left join gal_sterr ab1 on ab1.nrec = ab.CPARENT
left join gal_sterr ab2 on ab2.nrec = ab1.CPARENT
left join gal_sterr ab3 on ab3.nrec = ab2.CPARENT
left join gal_sterr ab4 on ab4.nrec = ab3.CPARENT
left join gal_sterr ab5 on ab5.nrec = ab4.CPARENT

-- ????????????????
left join gal_sterr ter on ter.nrec = pass.CSTERR
left join gal_sterr ter1 on ter1.nrec = ter.CPARENT
left join gal_sterr ter2 on ter2.nrec = ter1.CPARENT
left join gal_sterr ter3 on ter3.nrec = ter2.CPARENT
left join gal_sterr ter4 on ter4.nrec = ter3.CPARENT
left join gal_sterr ter41 on ter41.nrec = ter4.CPARENT

-- ????????????????????
left join gal_sterr ter10 on ter10.nrec = live.CSTERR
left join gal_sterr ter11 on ter11.nrec = ter10.CPARENT
left join gal_sterr ter12 on ter12.nrec = ter11.CPARENT
left join gal_sterr ter13 on ter13.nrec = ter12.CPARENT
left join gal_sterr ter14 on ter14.nrec = ter13.CPARENT
left join gal_sterr ter141 on ter141.nrec = ter14.CPARENT

-- ?????????????????? ??????????????????????
left join gal_sterr ter20 on ter20.nrec = vrem.CSTERR
left join gal_sterr ter21 on ter21.nrec = ter20.CPARENT
left join gal_sterr ter22 on ter22.nrec = ter21.CPARENT
left join gal_sterr ter23 on ter23.nrec = ter22.CPARENT
left join gal_sterr ter24 on ter24.nrec = ter23.CPARENT
left join gal_sterr ter25 on ter25.nrec = ter24.CPARENT

WHERE tus.warch = 0
 AND tp.nrec = 0x" . bin2hex($galId);

        $data = Yii::app()->db2->createCommand($sql)->queryRow();

        $inn = Yii::app()->db2->createCommand()
            ->select('inn.nrec nrec, inn.nmb innNmb')
            ->from('gal_passports inn')
            ->where('inn.person = :id AND inn.docname = 0x8000000000000227', [':id' => $galId])
            ->order('inn.nrec')
            ->limit(1)
            ->queryRow();

        $snils = Yii::app()->db2->createCommand()
            ->select('snils.nrec nrec, snils.nmb snilsNmb')
            ->from('gal_passports snils')
            ->where('snils.person = :id AND snils.docname = 0x8000000000000223', [':id' => $galId])
            ->order('snils.nrec')
            ->limit(1)
            ->queryRow();


        $medPolicy = Yii::app()->db2->createCommand()
            ->select('medPolicy.nrec nrec, medPolicy.nmb medPolicyNmb,
                  medPolicy.givenby medPolicyGivenby,
                  medPolicy.givendate medPolicyGivendate,
                  medPolicy.todate medPolicyTodate')
            ->from('gal_passports medPolicy')
            ->where('medPolicy.person = :id AND medPolicy.docname = 0x8001000000002695', [':id' => $galId])
            ->order('medPolicy.nrec')
            ->limit(1)
            ->queryRow();

        $socialProtection = Yii::app()->db2->createCommand()
            ->select('socialProtection.nrec nrec, socialProtection.nmb socialProtectionNmb,
                  socialProtection.givenby socialProtectionGivenby,
                  socialProtection.givendate socialProtectionGivendate,
                  socialProtection.todate socialProtectionTodate')
            ->from('gal_passports socialProtection')
            ->where('socialProtection.person = :id AND socialProtection.docname = 0x8000000000000228', [':id' => $galId])
            ->order('socialProtection.nrec')
            ->limit(1)
            ->queryRow();

        $residence = Yii::app()->db2->createCommand()
            ->select('residence.nrec nrec, residence.nmb residenceNmb,                  
                  residence.givendate residenceGivendate,
                  residence.todate residenceTodate')
            ->from('gal_passports residence')
            ->where('residence.person = :id AND residence.docname = 0x8001000000001BA4', [':id' => $galId])
            ->order('residence.nrec')
            ->limit(1)
            ->queryRow();

        $migration = Yii::app()->db2->createCommand()
            ->select('migration.nrec nrec, migration.nmb migrationNmb,                  
                  migration.givendate migrationGivendate,
                  migration.todate migrationTodate')
            ->from('gal_passports migration')
            ->where('migration.person = :id AND migration.docname = 0x8001000000002773', [':id' => $galId])
            ->order('migration.nrec')
            ->limit(1)
            ->queryRow();


        $linkType = ($data['sex'] == '??') ? '8001000000002C08':'80000000000015E7';

        $husbandWife = Yii::app()->db2->createCommand()
            ->select('
                    p.nrec nrec,
                    p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                 
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = :link', [':id' => $galId, ':link' => hex2bin($linkType)])
            ->order('p.nrec DESC')
            ->limit(1)
            ->queryRow();


        $mother = Yii::app()->db2->createCommand()
            ->select('p.nrec nrec, p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                 
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = 0x80000000000015E5', [':id' => $galId])
            ->order('p.nrec DESC')
            ->limit(1)
            ->queryRow();


            $father = Yii::app()->db2->createCommand()
                ->select('p.nrec, p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                 
                  ')
                ->from('gal_psnlinks p')
                ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
                ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
                ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
                ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
                ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
                ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
                ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
                ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
                ->where('p.fromperson = :id AND p.linktype = 0x80000000000015E6', [':id' => $galId])
                ->order('p.nrec DESC')
                ->limit(1)
                ->queryRow();

        $kinder = Yii::app()->db2->createCommand()
            ->select('p.nrec, p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = 0x80000000000002CE', [':id' => $galId])
            ->order('p.nrec DESC')
            ->limit(3)
            ->queryAll();


        $dataForEdit = GalStudentPersonalcard::model()->find('pnrec = :pnrec', ['pnrec' => $data['pnrec']]);
        if (!$dataForEdit instanceof GalStudentPersonalcard) {
            $model = new GalStudentPersonalcard();
            $model->pnrec = $data['pnrec'];
            $model->save();
            $dataForEdit = $model;
        }
            $dataForEdit->innFromDB = $inn['nrec'];
            $dataForEdit->snilsFromDB = $snils['nrec'];
            $dataForEdit->medPolicyFromDB = $medPolicy['nrec'];
            $dataForEdit->socialProtectionFromDB = $socialProtection['nrec'];
            $dataForEdit->motherFromDB = $mother['nrec'];
            $dataForEdit->fatherFromDB = $father['nrec'];
            $dataForEdit->residenceFromDB = $residence['nrec'];
            $dataForEdit->migrationFromDB = $migration['nrec'];
            $dataForEdit->husbandWifeFromDB = $husbandWife['nrec'];
            $dataForEdit->passFromDB = $data['passNrec'];
            isset($kinder[0]) ? ($dataForEdit->kinder1FromDB = $kinder[0]['nrec']) : '';
            isset($kinder[1]) ? ($dataForEdit->kinder2FromDB = $kinder[1]['nrec']) : '';
            isset($kinder[2]) ? ($dataForEdit->kinder3FromDB = $kinder[2]['nrec']) : '';
            $dataForEdit->save();


        $dataForEdit->pnrec = bin2hex($dataForEdit->pnrec);


        foreach ($dataForEdit as $field => $value) {
            if (stripos($field, 'date') > 0 && $value) {
                $dataForEdit->$field = date('d.m.Y', strtotime($value));
            }
        }

        foreach ($dataForEdit as $field => $value) {
            try{
                if (mb_stripos($field, 'Manual', null, Yii::app()->charset) > 0 && $value) {
                    $val = bin2hex($value);


                    if (mb_stripos($val, '8000', null,  Yii::app()->charset) === 0 || mb_stripos($val, '8001', null,  Yii::app()->charset) === 0) {
                        $dataForEdit->$field = $val;

                    }
                }
            } catch (Exception $e){

            }
        }

        $gal_person = Person::model()->find('nrec = :nrec', [':nrec' => $data['pnrec']]);
        if ($gal_person instanceof Person){
            $isConfirmed = $gal_person->ddop1;
            $dateConfirmed = CMisc::fromGalDate($gal_person->ddop2);
        } else {
            $isConfirmed = null;
            $dateConfirmed = null;
        }


        $this->layout = '//layouts/column1';
        $this->render('index', array(
            'data' => $data,
            'inn' => $inn,
            'snils' => $snils,
            'medPolicy' => $medPolicy,
            'residence' => $residence,
            'migration' => $migration,
            'husbandWife' => $husbandWife,
            'mother' => $mother,
            'father' => $father,
            'kinder' => $kinder,
            'socialProtection' =>$socialProtection,
            'dataForEdit' => $dataForEdit,
            'isConfirmed' => $isConfirmed,
            'dateConfirmed' => $dateConfirmed,
            'id' => $galId
        ));
    }

	public function actionUpdateFieldCheckRight(){
        $result = null;
        if (isset($_POST)) {
            foreach ($_POST as $key => $value){
                $data = GalStudentPersonalcard::model()->find('pnrec = :pnrec', ['pnrec' => hex2bin($key)]);
                if ($data instanceof GalStudentPersonalcard){
                    $fieldFromModel = $value . 'IsRight';
                    $data->$fieldFromModel = ($data->$fieldFromModel == 0) ? 1 : 0;
                    if ($data->save()) {
                        $result = $data->$fieldFromModel;
                    }
                }
            }

        }

        echo CJSON::encode(array('success' => $result));
    }

    public function actionUpdateDataField(){
        $result = null;

        if (isset($_POST['GalStudentPersonalcard'])) {
            $pnrec = null;
            $type = null;
            $dataField = null;
            $dataFieldName = null;
            foreach ($_POST['GalStudentPersonalcard'] as $key => $value){
                if ($key == 'pnrec') {
                    $pnrec = $value;
                } elseif ($key == 'type') {
                    $type = $value;
                } else {
                    $dataField = $value;
                    $dataFieldName = $key;
                }
            }

            if ($pnrec){
                $model = GalStudentPersonalcard::model()->find('pnrec = :pnrec', ['pnrec' => hex2bin($pnrec)]);
                if ($model instanceof GalStudentPersonalcard){
                    if (!$dataField) {
                        $model->$dataFieldName = null;
                    } else {
                        if ($type == 'date') {
                            $model->$dataFieldName = date("Y-m-d", strtotime($dataField));
                        } elseif ($type == 'nrec') {
                            $model->$dataFieldName = hex2bin($dataField);
                        } else {
                            $model->$dataFieldName = $dataField;
                        }
                    }

                    $result = $model->save();
                }
            }



        }

        echo CJSON::encode(array('success' => $result));
    }

    public function actionSchool($term) {
        $school = Yii::app()->db2->createCommand()
            ->select('HEX(u.nrec) value,                  
                  CONCAT(u.sname, \' (\', c.name, \', \', r.name, \', \', s.name, \')\') label')
            ->from('gal_u_school u')
            ->leftJoin('gal_u_country c', 'u.ccountry = c.nrec')
            ->leftJoin('gal_u_country r', 'u.cregion = r.nrec')
            ->leftJoin('gal_u_settlements s', 'u.ccity = s.nrec')
            ->where('LENGTH(u.sname) > 4')
            ->order('u.sname')
            ->queryAll();

        $maxCnt = 30;
        $result = array();
        foreach($school as $row) {
            if (mb_strstr(mb_strtolower($row['label'], Yii::app()->charset), mb_strtolower($term, Yii::app()->charset), false, Yii::app()->charset)) {
                $result[] = $row;
                if (--$maxCnt < 0) { break; }
            }
        }
        echo CJSON::encode($result);
    }

    public function actionEduDoc($term) {
        $edu = Yii::app()->db2->createCommand()
            ->select('HEX(c.nrec) value,                  
                  c.name label')
            ->from('gal_catalogs c')
            ->where('c.mainlink = 0x80000000000002D6')
            ->queryAll();

        $maxCnt = 30;
        $result = array();
        foreach($edu as $row) {
            if (mb_strstr(mb_strtolower($row['label'], Yii::app()->charset), mb_strtolower($term, Yii::app()->charset), false, Yii::app()->charset)) {
                $result[] = $row;
                if (--$maxCnt < 0) { break; }
            }
        }
        echo CJSON::encode($result);
    }

    public function actionEduLevel($term) {
        $edu = Yii::app()->db2->createCommand()
            ->select('HEX(c.nrec) value,                  
                  c.name label')
            ->from('gal_catalogs c')

            ->where('c.cparent = 0x80000000000002E2 and c.mainlink = 0x8000000000000200')
            ->queryAll();
        foreach ($edu as $row) {
            $eduparent = Yii::app()->db2->createCommand()
                ->select('HEX(c.nrec) value,                  
                  c.name label')
                ->from('gal_catalogs c')

                ->where('c.cparent = '. CMisc::_id($row['value']))
                ->queryAll();
            $edu = array_merge($edu, $eduparent);
        }

        $maxCnt = 30;
        $result = array();
        foreach($edu as $row) {
            if (mb_strstr(mb_strtolower($row['label'], Yii::app()->charset), mb_strtolower($term, Yii::app()->charset), false, Yii::app()->charset)) {
                $result[] = $row;
                if (--$maxCnt < 0) { break; }
            }
        }
        echo CJSON::encode($result);
    }

    public function actionAddress($term) {
        $edu = Yii::app()->db2->createCommand()
            ->select('HEX(s.nrec) value,                  
                  case
when s.wtype = 0 then s.sname
when s2.wtype = 0 then CONCAT_WS(\', \', s1.SNAME , s.SNAME)
when s3.wtype = 0 then CONCAT_WS(\', \', s2.SNAME , s1.SNAME , s.SNAME)
when s4.wtype = 0 then CONCAT_WS(\', \', s3.SNAME , s2.SNAME , s1.SNAME , s.SNAME)
when s5.wtype = 0 then CONCAT_WS(\', \', s4.SNAME , s3.SNAME , s2.SNAME , s1.SNAME , s.SNAME)
 END label')
            ->from('gal_sterr s')
            ->leftJoin('gal_sterr s1', 's.cparent = s1.nrec')
            ->leftJoin('gal_sterr s2', 's1.cparent = s2.nrec')
            ->leftJoin('gal_sterr s3', 's2.cparent = s3.nrec')
            ->leftJoin('gal_sterr s4', 's3.cparent = s4.nrec')
            ->leftJoin('gal_sterr s5', 's4.cparent = s5.nrec')
            ->where('s.wtype != 7 and s1.wtype != 7 and s2.wtype != 7 and s3.wtype != 7 and s4.wtype != 7 and s5.wtype != 7
            and s.sname LIKE \'%'.$term.'%\'  and s1.sname LIKE \'%'.$term.'%\' and s2.sname LIKE \'%'.$term.'%\' and s3.sname LIKE \'%'.$term.'%\' and s4.sname LIKE \'%'.$term.'%\' and s5.sname LIKE \'%'.$term.'%\'
             ')
            ->queryAll();


        echo CJSON::encode($edu);
    }

    public function actionGr($term) {
        $edu = Yii::app()->db2->createCommand()
            ->select('HEX(c.nrec) value,                  
                  c.name label')
            ->from('gal_catalogs c')
            ->where('c.mainlink = 0x80000000000001E6')
            ->queryAll();

        $maxCnt = 30;
        $result = array();
        foreach($edu as $row) {
            if (mb_strstr(mb_strtolower($row['label'], Yii::app()->charset), mb_strtolower($term, Yii::app()->charset), false, Yii::app()->charset)) {
                $result[] = $row;
                if (--$maxCnt < 0) { break; }
            }
        }
        echo CJSON::encode($result);
    }

    public function actionPassport($term) {
        $edu = Yii::app()->db2->createCommand()
            ->select('HEX(c.nrec) value,                  
                  c.name label')
            ->from('gal_catalogs c')
            ->where('c.MAINLINK = 0x8000000000000220 AND c.BMULTI = 1')
            ->queryAll();

        $maxCnt = 30;
        $result = array();
        foreach($edu as $row) {
            if (mb_strstr(mb_strtolower($row['label'], Yii::app()->charset), mb_strtolower($term, Yii::app()->charset), false, Yii::app()->charset)) {
                $result[] = $row;
                if (--$maxCnt < 0) { break; }
            }
        }
        echo CJSON::encode($result);
    }

    public function actionPrint($id=null){
        $galId = Yii::app()->user->getGalId();
        if (null === $galId) {
            return $this->redirect(['student/nolink']);
        }
        if(!isset($_SESSION)){
            session_start();
        }
        $galIdMassive = Yii::app()->user->getGalIdMass();
        if(isset($id)){
            $prov = 0;
            foreach ($galIdMassive as $galunid){
                if($galunid['Galid'] == $id){$prov = 1;}
            }
            if($prov == 1) {
                $galId = $id;
                $_SESSION['ID'] = $id;
            }
        }else{
            if(isset($_SESSION['ID'])) {
                $galId = $_SESSION['ID'];
            }else{
                $_SESSION['ID'] = $galId;
            }
        }

        $sql = "SELECT
CONCAT(fac.LONGNAME, ', ', tus.SDEPARTMENT, ', ', CASE WHEN bup.wformed = 0 THEN '??????????'
 WHEN bup.wformed = 1 THEN '??????????????'
 WHEN bup.wformed = 2 THEN '????????????????'
 end) AS 'placeOfStudy',

 tp.FIO AS 'fio',
 tp.nrec AS 'pnrec',
 CONCAT(spec.code, ' ', spec.name) AS 'spec',
 ts.name AS 'fin',
td.`sfld#1#` as 'contNmb', 
td.`DFLD#1#` as 'contBegin',
cel.`SFLD#1#` AS 'entName',
tp.sex AS 'sex',
tp.borndate AS 'borndate',
 gr.name AS 'gr',
 passtype.name AS 'passVid',
 tp1.nrec as 'passNrec',
  tp1.ser AS 'pser',
  tp1.nmb AS 'pnmb',
  tp1.givenby AS 'givenby',
tp1.givendate AS 'givendate',
  tp1.todate AS 'todate',
  tp1.givenpodr AS 'givenpodr',
  edu.name AS 'eduLevel',
  tc.name as 'eduDoc',
  te.series AS 'eduSeria',
  te.DIPLOMNMB AS 'eduNmb',
  te.DIPLOMDATE as 'eduDipDate',
  tus1.SNAME AS 'eduPlace',
  fam.name as 'familystate',
-- rup.COURSE '????????',
 
case
when uz1.wtype = 0 then uz.SADDRESS1
when uz2.wtype = 0 then CONCAT_WS(', ', uz1.SNAME, uz.SADDRESS1)
when uz3.wtype = 0 then CONCAT_WS(', ', uz2.SNAME, uz1.SNAME , uz.SADDRESS1)
when uz4.wtype = 0 then CONCAT_WS(', ', uz3.SNAME , uz2.SNAME , uz1.SNAME , uz.SADDRESS1)
when uz5.wtype = 0 then CONCAT_WS(', ', uz4.SNAME , uz3.SNAME , uz2.SNAME , uz1.SNAME , uz.SADDRESS1)
WHEN uz6.wtype = 0 then CONCAT_WS(', ', uz5.SNAME , uz4.SNAME , uz3.SNAME , uz2.SNAME , uz1.SNAME , uz.SADDRESS1)
 END 'eduAddr',


case
when ab1.wtype = 0 then ab.SNAME
when ab2.wtype = 0 then CONCAT_WS(', ', ab1.SNAME , ab.SNAME)
when ab3.wtype = 0 then CONCAT_WS(', ', ab2.SNAME , ab1.SNAME , ab.SNAME)
when ab4.wtype = 0 then CONCAT_WS(', ', ab3.SNAME , ab2.SNAME , ab1.SNAME , ab.SNAME)
when ab5.wtype = 0 then CONCAT_WS(', ', ab4.SNAME , ab3.SNAME , ab2.SNAME , ab1.SNAME , ab.SNAME)
 END 'bornAddr',

case
when ter.wtype = 0 then pass.SADDRESS1
when ter1.wtype = 0 then CONCAT_WS(', ', ter.SNAME , pass.SADDRESS1)
when ter2.wtype = 0 then CONCAT_WS(', ', ter1.SNAME , ter.SNAME , pass.SADDRESS1)
when ter3.wtype = 0 then CONCAT_WS(', ', ter2.SNAME , ter1.SNAME , ter.SNAME , pass.SADDRESS1)
when ter4.wtype = 0 then CONCAT_WS(', ', ter3.SNAME , ter2.SNAME , ter1.SNAME , ter.SNAME , pass.SADDRESS1)
when ter41.wtype = 0 then CONCAT_WS(', ', ter4.SNAME , ter3.SNAME , ter2.SNAME , ter1.SNAME , ter.SNAME , pass.SADDRESS1)
 END 'passAddr',

case
when ter10.wtype = 0 then live.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(', ', ter10.SNAME , live.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(', ', ter11.SNAME , ter10.SNAME , live.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(', ', ter12.SNAME , ter11.SNAME , ter10.SNAME , live.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(', ', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , live.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(', ', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , live.SADDRESS1)
 END 'liveAddr', 


case
when ter20.wtype = 0 then vrem.SADDRESS1
when ter21.wtype = 0 then CONCAT_WS(', ', ter20.SNAME , vrem.SADDRESS1)
when ter22.wtype = 0 then CONCAT_WS(', ', ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
when ter23.wtype = 0 then CONCAT_WS(', ', ter22.SNAME , ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
when ter24.wtype = 0 then CONCAT_WS(', ', ter23.SNAME , ter22.SNAME , ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
when ter25.wtype = 0 then CONCAT_WS(', ', ter24.SNAME , ter23.SNAME , ter22.SNAME , ter21.SNAME , ter20.SNAME , vrem.SADDRESS1)
 END 'tempAddr',
 
 (SELECT
 GROUP_CONCAT(tel1.ADDR )
 FROM gal_COMMUNICATIONS tel1 WHERE tp.nrec = tel1.PERSON and tel1.COMTYPE in (0x800000000000022A, 0x800000000000022B, 0x80000000000003A7)) as 'phone',

 (SELECT  GROUP_CONCAT(mail.EMAIL)  FROM gal_COMMUNICATIONS mail WHERE tp.nrec = mail.PERSON and mail.COMTYPE = 0x80000000000003A6) as 'email'



FROM gal_PERSONS tp
LEFT JOIN gal_U_STUDENT tus on tus.CPERSONS = tp.nrec
LEFT JOIN gal_CATALOGS gr on gr.nrec = tp.GR
LEFT JOIN gal_PASSPORTS tp1 ON tp1.nrec = tp.passprus
LEFT JOIN gal_CATALOGS fam ON fam.nrec = tp.familystate
LEFT JOIN gal_CATALOGS passtype ON passtype.nrec = tp1.DOCNAME
LEFT JOIN gal_EDUCATION te ON te.PERSON = tp.nrec AND te.IATTR = 1
LEFT JOIN gal_U_SCHOOL tus1 on tus1.nrec = te.NAME
LEFT JOIN gal_CATALOGS edu ON edu.nrec = te.LEVEL
LEFT JOIN gal_CATALOGS tc ON te.seqnmb = tc.CODE AND tc.CPARENT = 0x80000000000002D6
LEFT JOIN gal_APPOINTMENTS ta ON ta.nrec = CASE WHEN tp.APPOINTCUR = 0x8000000000000000 THEN tp.APPOINTLAST ELSE tp.APPOINTCUR END
LEFT JOIN gal_U_STUD_FINSOURCE tusf ON tusf.nrec = ta.CREF2 -- ??????.????????????
LEFT JOIN gal_SPKAU ts ON ts.nrec = tusf.CFINSOURCE -- ???? APPOINTMENTS
LEFT JOIN gal_STAFFSTRUCT st on ta.STAFFSTR = st.nrec
LEFT JOIN gal_U_CURRICULUM bup on bup.nrec = st.CSTR
-- LEFT JOIN gal_U_CURRICULUM rup ON rup.nrec = ta.CDOPINF
LEFT JOIN gal_CATALOGS spec ON spec.nrec = bup.CSPECIALITY
LEFT JOIN gal_CATALOGS fac ON fac.nrec = bup.CFACULTY
LEFT JOIN gal_DOPINFO td ON td.nrec = (SELECT cont.nrec from gal_dopinfo cont WHERE cont.CPERSON = tp.nrec AND cont.CDOPTBL = 0x8001000000000007 ORDER BY cont.`DFLD#1#` DESC LIMIT 1)
LEFT JOIN gal_DOPINFO za4 ON za4.CPERSON = tp.nrec AND za4.CDOPTBL = 0x8001000000000003
LEFT JOIN gal_DOPINFO cel ON cel.CPERSON = tp.nrec AND cel.CDOPTBL = 0x8001000000000001
LEFT JOIN gal_CATALOGS opk ON opk.nrec = cel.`CFLD#1#` AND opk.CPARENT = 0x8001000000002697

-- ?????????????? ??????????????????
LEFT JOIN gal_U_SCHOOL us ON us.nrec = te.NAME
LEFT JOIN gal_U_SETTLEMENTS adr1 ON us.CCITY = adr1.nrec -- ?????? ??????????
LEFT JOIN gal_U_COUNTRY tuc ON us.CCOUNTRY = tuc.nrec -- ????????????
LEFT JOIN gal_U_COUNTRY tuc1 ON us.CREGION = tuc1.nrec -- ????????????
LEFT JOIN gal_CATALOGS tc1 ON tc1.CODE = te.SEQNMB AND tc1.CPARENT = 0x80000000000002D6 -- ?????? ?????????????????? ???? ??????????????????????

-- ?????????? ???? 2 ???????????????? ????????????????
LEFT JOIN gal_ADDRESSN uz ON uz.nrec = te.LEARNADDR
left join gal_sterr uz1 on uz1.nrec = uz.CSTERR
left join gal_sterr uz2 on uz2.nrec = uz1.CPARENT
left join gal_sterr uz3 on uz3.nrec = uz2.CPARENT
left join gal_sterr uz4 on uz4.nrec = uz3.CPARENT
left join gal_sterr uz5 on uz5.nrec = uz4.CPARENT
left join gal_sterr uz6 on uz6.nrec = uz5.CPARENT

-- ???????????? ????????????
LEFT JOIN gal_ADDRESSN born ON tp.BORNADDR = born.nrec -- ?????????? ????????????????
left join gal_ADDRESSN live on tp.LIVEADDR = live.nrec -- ?????????? ????????????????????
left join gal_ADDRESSN pass on tp.PASSPADDR = pass.nrec -- ?????????? ????????????????
left join gal_ADDRESSN vrem on vrem.CPERSON = tp.nrec and vrem.OBJTYPE = 55 -- ?????????? ????.??????????????????????

-- ?????????? ????????????????
left join gal_sterr ab on ab.nrec = born.CSTERR
left join gal_sterr ab1 on ab1.nrec = ab.CPARENT
left join gal_sterr ab2 on ab2.nrec = ab1.CPARENT
left join gal_sterr ab3 on ab3.nrec = ab2.CPARENT
left join gal_sterr ab4 on ab4.nrec = ab3.CPARENT
left join gal_sterr ab5 on ab5.nrec = ab4.CPARENT

-- ????????????????
left join gal_sterr ter on ter.nrec = pass.CSTERR
left join gal_sterr ter1 on ter1.nrec = ter.CPARENT
left join gal_sterr ter2 on ter2.nrec = ter1.CPARENT
left join gal_sterr ter3 on ter3.nrec = ter2.CPARENT
left join gal_sterr ter4 on ter4.nrec = ter3.CPARENT
left join gal_sterr ter41 on ter41.nrec = ter4.CPARENT

-- ????????????????????
left join gal_sterr ter10 on ter10.nrec = live.CSTERR
left join gal_sterr ter11 on ter11.nrec = ter10.CPARENT
left join gal_sterr ter12 on ter12.nrec = ter11.CPARENT
left join gal_sterr ter13 on ter13.nrec = ter12.CPARENT
left join gal_sterr ter14 on ter14.nrec = ter13.CPARENT
left join gal_sterr ter141 on ter141.nrec = ter14.CPARENT

-- ?????????????????? ??????????????????????
left join gal_sterr ter20 on ter20.nrec = vrem.CSTERR
left join gal_sterr ter21 on ter21.nrec = ter20.CPARENT
left join gal_sterr ter22 on ter22.nrec = ter21.CPARENT
left join gal_sterr ter23 on ter23.nrec = ter22.CPARENT
left join gal_sterr ter24 on ter24.nrec = ter23.CPARENT
left join gal_sterr ter25 on ter25.nrec = ter24.CPARENT

WHERE tus.warch = 0
 AND tp.nrec = 0x" . bin2hex($galId);

        $data = Yii::app()->db2->createCommand($sql)->queryRow();

        $inn = Yii::app()->db2->createCommand()
            ->select('inn.nmb innNmb')
            ->from('gal_passports inn')
            ->where('inn.person = :id AND inn.docname = 0x8000000000000227', [':id' => $galId])
            ->order('inn.nrec')
            ->limit(1)
            ->queryRow();

        $snils = Yii::app()->db2->createCommand()
            ->select('snils.nmb snilsNmb')
            ->from('gal_passports snils')
            ->where('snils.person = :id AND snils.docname = 0x8000000000000223', [':id' => $galId])
            ->order('snils.nrec')
            ->limit(1)
            ->queryRow();


        $medPolicy = Yii::app()->db2->createCommand()
            ->select('medPolicy.nmb medPolicyNmb,
                  medPolicy.givenby medPolicyGivenby,
                  medPolicy.givendate medPolicyGivendate,
                  medPolicy.todate medPolicyTodate')
            ->from('gal_passports medPolicy')
            ->where('medPolicy.person = :id AND medPolicy.docname = 0x8001000000002695', [':id' => $galId])
            ->order('medPolicy.nrec')
            ->limit(1)
            ->queryRow();

        $socialProtection = Yii::app()->db2->createCommand()
            ->select('socialProtection.nmb socialProtectionNmb,
                  socialProtection.givenby socialProtectionGivenby,
                  socialProtection.givendate socialProtectionGivendate,
                  socialProtection.todate socialProtectionTodate')
            ->from('gal_passports socialProtection')
            ->where('socialProtection.person = :id AND socialProtection.docname = 0x8000000000000228', [':id' => $galId])
            ->order('socialProtection.nrec')
            ->limit(1)
            ->queryRow();

        $residence = Yii::app()->db2->createCommand()
            ->select('residence.nmb residenceNmb,                  
                  residence.givendate residenceGivendate,
                  residence.todate residenceTodate')
            ->from('gal_passports residence')
            ->where('residence.person = :id AND residence.docname = 0x8001000000001BA4', [':id' => $galId])
            ->order('residence.nrec')
            ->limit(1)
            ->queryRow();

        $migration = Yii::app()->db2->createCommand()
            ->select('migration.nmb migrationNmb,                  
                  migration.givendate migrationGivendate,
                  migration.todate migrationTodate')
            ->from('gal_passports migration')
            ->where('migration.person = :id AND migration.docname = 0x8001000000002773', [':id' => $galId])
            ->order('migration.nrec')
            ->limit(1)
            ->queryRow();

        $linkType = ($data['sex'] == '??') ? '8001000000002C08':'80000000000015E7';

        $husbandWife = Yii::app()->db2->createCommand()
            ->select('
                    p.nrec nrec,
                    p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                 
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = :link', [':id' => $galId, ':link' => hex2bin($linkType)])
            ->order('p.nrec DESC')
            ->limit(1)
            ->queryRow();

        $mother = Yii::app()->db2->createCommand()
            ->select('p.nrec nrec, p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                 
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = 0x80000000000015E5', [':id' => $galId])
            ->order('p.nrec DESC')
            ->limit(1)
            ->queryRow();


        $father = Yii::app()->db2->createCommand()
            ->select('p.nrec, p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                 
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = 0x80000000000015E6', [':id' => $galId])
            ->order('p.nrec DESC')
            ->limit(1)
            ->queryRow();

        $kinder = Yii::app()->db2->createCommand()
            ->select('p.nrec, p.rfio fio,
                    p.rborndate borndate,
                    p.str1 phone,  
                    case
when ter10.wtype = 0 then a.SADDRESS1
when ter11.wtype = 0 then CONCAT_WS(\', \', ter10.SNAME , a.SADDRESS1)
when ter12.wtype = 0 then CONCAT_WS(\', \', ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter13.wtype = 0 then CONCAT_WS(\', \', ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter14.wtype = 0 then CONCAT_WS(\', \', ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
when ter141.wtype = 0 then CONCAT_WS(\', \', ter14.SNAME , ter13.SNAME , ter12.SNAME , ter11.SNAME , ter10.SNAME , a.SADDRESS1)
 END addr                
                  ')
            ->from('gal_psnlinks p')
            ->leftJoin('gal_catalogs c', 'c.nrec = p.linktype')
            ->leftJoin('gal_addressn a', 'p.rpassaddr = a.nrec')
            ->leftJoin('gal_sterr ter10','ter10.nrec = a.CSTERR')
            ->leftJoin('gal_sterr ter11', 'ter11.nrec = ter10.CPARENT')
            ->leftJoin('gal_sterr ter12', 'ter12.nrec = ter11.CPARENT')
            ->leftJoin('gal_sterr ter13', 'ter13.nrec = ter12.CPARENT')
            ->leftJoin('gal_sterr ter14', 'ter14.nrec = ter13.CPARENT')
            ->leftJoin('gal_sterr ter141', 'ter141.nrec = ter14.CPARENT')
            ->where('p.fromperson = :id AND p.linktype = 0x80000000000002CE', [':id' => $galId])
            ->order('p.nrec DESC')
            ->limit(3)
            ->queryAll();



        $dataForEdit = GalStudentPersonalcard::model()->find('pnrec = :pnrec', ['pnrec' => $galId]);


        foreach ($dataForEdit as $field => $value) {
            if (stripos($field, 'date') > 0 && $value) {
                $dataForEdit->$field = date('d.m.Y', strtotime($value));
            }
        }

        foreach ($dataForEdit as $field => $value) {
            try{
                if (mb_stripos($field, 'Manual', null, Yii::app()->charset) > 0 && $value) {
                    $val = bin2hex($value);


                    if (mb_stripos($val, '8000', null,  Yii::app()->charset) === 0 || mb_stripos($val, '8001', null,  Yii::app()->charset) === 0) {
                        $dataForEdit->$field = $val;

                    }
                }
            } catch (Exception $e){

            }
        }

        isset($kinder[0]) ? ($dataForEdit->kinder1FromDB = $kinder[0]['nrec']) : '';
        isset($kinder[1]) ? ($dataForEdit->kinder2FromDB = $kinder[1]['nrec']) : '';
        isset($kinder[2]) ? ($dataForEdit->kinder3FromDB = $kinder[2]['nrec']) : '';

//        var_dump($dataForEdit);die;
        $this->renderPartial('//pdf/personalcard', array(
            'data' => $data,
            'inn' => $inn,
            'snils' => $snils,
            'medPolicy' => $medPolicy,
            'residence' => $residence,
            'migration' => $migration,
            'husbandWife' => $husbandWife,
            'socialProtection' =>$socialProtection,
            'dataForEdit' => $dataForEdit,
            'mother' => $mother,
            'father' => $father,
            'kinder' => $kinder,
        ));
    }




}
