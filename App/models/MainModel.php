<?php
namespace App\models;
use App\core\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpImap\Mailbox;

class MainModel extends Model
{
    // Воссаздание отправки письма с условием разделителя "-"
    public function sendMail($site, $form, $text)
    {
        $mail = new PHPMailer(true);

        try {
            // Настройка сервера отправки почты использую одну и туже почту для поставленных задач
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = 'ssl://smtp.yandex.ru'; // Укажите SMTP-сервер, который вы используете
            $mail->SMTPAuth = true;
            $mail->Username = 'RZL1985@yandex.ru'; // Укажите вашу почту
            $mail->Password = 'mdkrmxogwxlppmsp'; // Укажите ваш пароль Он новый из за политики яндекса
            $mail->Port = 465; // Укажите порт для SMTP-сервера

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
    
            // Установка получателя, отправителя и темы письма
            $mail->setFrom('RZL1985@yandex.ru', 'from the test system'); // Укажите вашу почту и имя отправителя
            $mail->addAddress('RZL1985@yandex.ru', 'Уважаемый'); // Укажите адрес получателя
            $mail->Subject = $site . '-' . $form; // Укажите тему письма
    
            // Установка содержимого письма
            $mail->Body = $text; // Укажите текст письма
    
            // Отправка письма
            $mail->send();

            // эти сообщения я не прикреплял
            $_SESSION['message'] = "Письмо успешно отправлено на указынный адрес!";          

        } catch (Exception $e) {

            $_SESSION['message'] = "Произошла ошибка при отправке письма: {$mail->ErrorInfo}";           
        }
    }

    // делаем класс для проаерки почты
    public function checkMail()
    {
        // Создаем объект PHP_IMAP
        $imap = new Mailbox(
            '{imap.yandex.ru:993/imap/ssl}INBOX', // адрес почтового сервера и папка "Входящие"
            'RZL1985@yandex.ru', // имя пользователя (email)
            'mdkrmxogwxlppmsp', // пароль
            null, // путь к файлу с временным хранилищем (необязательно)
            'UTF-8' // кодировка (необязательно)
        );
    
        // Получаем список непрочитанных писем
        $mailIds = $imap->searchMailbox('UNSEEN');
        
        $arrayEmail = [];

        foreach ($mailIds as $mailId) {
            // Получаем данные письма
            $header = $imap->getMailHeader($mailId);
            $subject = $header->subject;
            $date = $header->date;

            // Разделяем тему на домен и название формы
            list($domain, $name_form) = explode('-', $subject);

            // Записываем данные о письме в массив
            $arrayEmail[] = [
                'domain' => $domain,
                'formName' => $name_form,
                'date' => $date
            ];

            // ставим метку прочитано
            $imap->markMailAsRead($mailId);   
        }

        // Закрываем соединение
        $imap->disconnect();
        
        // Возвращаем массив с данными о письмах
        return $arrayEmail;
    }

    // отдельный метод для записи в БД
    public function saveData($domain, $name_form, $data)
    {
        $sql = "INSERT INTO visits (domain, name_form, data)
                VALUES (:domain, :name_form, :data);";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':domain', $domain, \PDO::PARAM_STR);
        $stmt->bindParam(':name_form', $name_form, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, \PDO::PARAM_STR);  
        $stmt->execute();
    }
}