<?php

class ReadController extends Controller
{

    public $layout = '//layouts/column1';

    public static $pastTaskFnpp = [844586, 784796,767334, 712198];

    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('index',
                    'giveTheTask',
                    'taskList'),
                'users' => array('*'),
                'expression' => 'Controller::checkfnpp()',
            ),
            array('deny',  // deny all users
                'users' => array('*'),
            ),
        );
    }

    protected function beforeAction($action)
    {
        if (Yii::app()->user->getFnpp() == null) {
            $this->redirect(array('/site'));
        }
        if (ReadClass::checkpps()) {
            $this->redirect(array('/remote'));
        }
        return true;
    }

    public function actionIndex()
    {
        $list = Yii::app()->db2->createCommand()
            ->selectDistinct('ats.discipline, HEX(ats.disciplineNrec) disciplineId, REPLACE(GROUP_CONCAT(DISTINCT ats.teacherFio),\',\',\', \') examiners
            , (SELECT COUNT(*) FROM remote_task_list rtl WHERE rtl.`group` = ats.studGroupId AND rtl.discipline = ats.disciplineNrec) \'count\' ')
            ->from('attendance_schedule ats')
            ->join('attendance_galruz_group agg', 'agg.id = ats.studGroupId')
            ->where('ats.dateTimeStartOfClasses > \'' . date(RemoteModule::startDate()) . '\' OR ats.semesterTag like \'2021%\'')
//            ->andWhere('agg.id = '.ReadClass::mygroupId())
            ->andWhere(array('in', 'agg.id', ReadClass::mygroupId()))
            ->group('ats.discipline, ats.disciplineNrec')
            ->queryAll();
        $listExtra = Yii::app()->db2->createCommand()
            ->selectDistinct('gud.name discipline, HEX(ret.discipline) disciplineId, REPLACE(GROUP_CONCAT(DISTINCT UPPER(CONCAT_WS(\' \', f.fam, LEFT(f.nam,1), LEFT(f.otc,1)))),\',\',\', \') examiners
            , (SELECT COUNT(*) FROM remote_task_list rtl WHERE rtl.`group` = ret.`group` AND rtl.discipline = ret.discipline) \'count\'')
            ->from('remote_extra_task ret')
            ->join('gal_u_discipline gud', 'gud.nrec = ret.discipline')
            ->join('fdata f', 'f.npp = ret.teacher')
            ->where(['and', array('in', 'ret.group', ReadClass::mygroupId()),
                ['or', 'exists(select * from remote_extra_fnpp ref where ref.task_id = ret.id and ref.fnpp = ' . Yii::app()->user->getFnpp() . ')',
                    'not exists(select * from remote_extra_fnpp ref where ref.task_id = ret.id)']])
            ->group('ret.discipline')
            ->queryAll();


        // массив для отображения прошлой контактной работы
        if (in_array(Yii::app()->user->getFnpp(), ReadController::$pastTaskFnpp)) {
            $list2 = Yii::app()->db2->createCommand()
                ->selectDistinct('gal.name discipline, HEX(gal.nrec) disciplineId, REPLACE(GROUP_CONCAT(DISTINCT UPPER(CONCAT_WS(\' \', f.fam, LEFT(f.nam,1), LEFT(f.otc,1)))),\',\',\', \') examiners
            , (SELECT COUNT(*) FROM remote_task_list rtl WHERE rtl.`group` = ats.studGroupId AND rtl.discipline = rem.discipline) \'count\'')
                ->from('attendance_schedule ats')
                ->join('remote_task_list rem', 'rem.`group`=ats.studGroupId')
                ->join('gal_u_discipline gal', 'gal.nrec = rem.discipline')
                ->join('fdata f', 'f.npp = rem.author_fnpp')
                ->join('attendance_galruz_group agg', 'agg.id = rem.`group`')
                ->where('rem.create_date < \'' . date(RemoteModule::startDate()) . '\'')
                ->andWhere(array('in', 'agg.id', ReadClass::mygroupId()))
                ->group('gal.name, rem.discipline')
                ->queryAll();
            $listExtra2 = Yii::app()->db2->createCommand()
                ->selectDistinct('gud.name discipline, HEX(ret.discipline) disciplineId, REPLACE(GROUP_CONCAT(DISTINCT UPPER(CONCAT_WS(\' \', f.fam, LEFT(f.nam,1), LEFT(f.otc,1)))),\',\',\', \') examiners
            , (SELECT COUNT(*) FROM remote_task_list rtl WHERE rtl.`group` = ret.`group` AND rtl.discipline = ret.discipline) \'count\'')
                ->from('remote_extra_task ret')
                ->join('gal_u_discipline gud', 'gud.nrec = ret.discipline')
                ->join('fdata f', 'f.npp = ret.teacher')
                ->where(['and', array('in', 'ret.group', ReadClass::mygroupId()),
                    ['or', 'exists(select * from remote_extra_fnpp ref where ref.task_id = ret.id and ref.fnpp = ' . Yii::app()->user->getFnpp() . ')',
                        'not exists(select * from remote_extra_fnpp ref where ref.task_id = ret.id)']])
                ->andWhere('ret.create_date < \'' . date(RemoteModule::startDate()) . '\'')
                ->group('ret.discipline')
                ->queryAll();
            $list2 = array_merge($list2, $listExtra2);
        } else {
            $list2 = [];
        }
        $list = array_merge($list, $listExtra);


        $this->layout = '//layouts/column1';
        $this->render('index', ['list' => $list, 'list2' => $list2]);
    }

    public function actionTaskList($discipline, $time)
    {
        if ($time == 0) {
            ReadClass::checkMyDiscipline($this, $discipline);
        }
        $group = ReadClass::mygroupId();
        $list = RemoteTaskList::model()->findAllByAttributes(array('group' => $group, 'discipline' => hex2bin(CMisc::_bn($discipline)))
            , array('order' => 'create_date DESC'));

        $this->layout = '//layouts/column1';
        $this->render('taskList', ['list' => $list, 'group' => $group, 'discipline' => $discipline]);
    }


    public function actionDownloadFile($id, $name)
    {
        $save_path = RemoteModule::uploadPath($id);
        $file = $save_path . $id . '/' . $name;
        if (file_exists($file)) {
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename=\"" . $name . "\"");
            readfile($file);
        }
    }

}
