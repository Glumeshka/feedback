<?php

require_once 'vendor\autoload.php';
require_once 'App' . DIRECTORY_SEPARATOR .'core' . DIRECTORY_SEPARATOR . 'Config.php';

use App\models\MainModel;

// Создание экземпляра класса MainModel
$model = new MainModel();

while (true) {

    // Вызов метода проверки почты и получение массива данных
    $emailData = $model->checkMail();

    // Пакетная запись данных в БД
    foreach ($emailData as $data) {
        $model->saveData($data['domain'], $data['formName'], $data['date']);
    }

    sleep(600);    // Задержка на 10 минут
}