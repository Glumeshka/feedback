<?php
namespace App\controllers;

use App\core\Controller;
use App\models\MainModel;
use App\core\View;

class Main extends Controller
{
    public function __construct()
    {
        $this->model = new MainModel();
        $this->view = new View();
    }

    // стоковый контролер при запуске этой страницы
    public function index()
    {      
        $this->pageData['title'] = "Пример форм";
        $this->pageData['js'] = "/js/js.js";
        $this->view->render('main.phtml', 'template.phtml', $this->pageData);
    }

    // Контроллер для отправки формы и дальнейшего отправления письма
    public function Input()
    {           
        $site = $_POST['site_name'];
        $form = $_POST['name_form'];
        $text = $_POST['text'];
        $this->model->sendMail($site, $form, $text);
        header("Location: /");  
    }
}