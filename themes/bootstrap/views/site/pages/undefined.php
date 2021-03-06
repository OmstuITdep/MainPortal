<?php
/* @var $this SiteController */

$this->pageTitle = Yii::app()->name . ' - Ошибка определения';

?>
<h1>Не пугайтесь!</h1>

<p>
    Произошла ошибка идентификации. <br/>
    Просьба в кратчайшие сроки уведомить об этом сотрудников ЦТКиВТ через <a href="http://omgtu.ru/adm/service/e.php">электронную приёмную Service Desk ОмГТУ</a> (Сайт ОмГТУ/Сервис/Электронная приёмная подразделений ОмГТУ/исполнитель - ЦТКиВТ)<br/>
    При уведомлении необходимо сообщить число: <strong><?php echo (int)Yii::app()->user->id; ?>, свою должность и Ф.И.О.</strong>    
</p>
