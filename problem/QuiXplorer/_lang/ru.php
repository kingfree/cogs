<?php

// Russian Language Module (translated by Mikhail M. Pigulsky - mikhail@mikhail.pp.ru)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(
      // error
      "error"                  => "ОШИБКА(И)",
      "back"                  => "Вернуться",

      // root
      "home"                  => "Домашняя директория не существует! Проверьте настройки.",
      "abovehome"            => "Текущая директория не может находится выше домашнего каталога.",
      "targetabovehome"      => "Запрошенная директория не может находится выше домашнего каталога.",

      // exist
      "direxist"            => "Директория не существует",
      "fileexist"            => "Такого файла не существует",
      "itemdoesexist"            => "Такой объект уже существует",
      "itemexist"            => "Такого объекта существует",
      "targetexist"            => "Назначенной директории не существует",
      "targetdoesexist"      => "Назначенного объекта не существует",

      // open
      "opendir"            => "Невозможно открыть директорию",
      "readdir"            => "Невозможно прочитать директорию",

      // access
      "accessdir"            => "Вам запрещено заходить в данную директорию",
      "accessfile"            => "Вам запрещено использовать данный файл",
      "accessitem"            => "Вам запрещено использовать данный объект",
      "accessfunc"            => "Вам запрещено использовать данную функцию",
      "accesstarget"            => "Вам запрещено входить в заданную директорию",

      // actions
      "permread"            => "Ошибка в получении прав доступа",
      "permchange"            => "Ошибка в смене прав доступа",
      "openfile"            => "Провал в открытии файла",
      "savefile"            => "Провал в сохранении файла",
      "createfile"            => "Провал в создании файла",
      "createdir"            => "Провал в создании директории",
      "uploadfile"            => "Провал в загрузке файла",
      "copyitem"            => "Провал в копировании",
      "moveitem"            => "Провал в переименовании",
      "delitem"            => "Провал в удалении",
      "chpass"            => "Провал в смене пароля",
      "deluser"            => "Провал в удалении пользователя",
      "adduser"            => "Провал в удалении пользователя",
      "saveuser"            => "Провал в сохранении пользователя",
      "searchnothing"            => "Строка поиска не должна быть пустой",

      // misc
      "miscnofunc"            => "Функция недоступна",
      "miscfilesize"            => "Файл превышает максимальный размер",
      "miscfilepart"            => "Файл был загружен частично",
      "miscnoname"            => "Вы должны дать задать имя",
      "miscselitems"            => "Вы не выбрали объект(ы)",
      "miscdelitems"            => "Вы уверены, что хотите удалить \"+num+\" объект(а/ов)?",
      "miscdeluser"            => "Вы уверены, что хотите удалить пользователя '\"+user+\"'?",
      "miscnopassdiff"      => "Новый пароль не отличается от текущего",
      "miscnopassmatch"      => "Пароли не совпадают",
      "miscfieldmissed"      => "Вы пропустили важное поле",
      "miscnouserpass"      => "Имя пользователя или пароль не правильны",
      "miscselfremove"      => "Вы не можете удалить самого себя",
      "miscuserexist"            => "Такой пользователь уже существует",
      "miscnofinduser"      => "Невозможно найти пользователя",
);
$GLOBALS["messages"] = array(
      // links
      "permlink"            => "ПОМЕНЯТЬ ПРАВА ДОСТУПА",
      "editlink"            => "РЕДАКТИРОВАТЬ",
      "downlink"            => "СКАЧАТЬ",
      "uplink"            => "НАВЕРХ",
      "homelink"            => "ДОМОЙ",
      "reloadlink"            => "ОБНОВИТЬ",
      "copylink"            => "КОПИРОВАТЬ",
      "movelink"            => "ПЕРЕМЕСТИТЬ",
      "dellink"            => "УДАЛИТЬ",
      "comprlink"            => "АРХИВИРОВАТЬ",
      "adminlink"            => "АДМИНИСТРИРОВАНИЕ",
      "logoutlink"            => "ВЫЙТИ",
      "uploadlink"            => "ЗАКАЧАТЬ",
      "searchlink"            => "ПОИСК",
	  "unziplink"			=> "UNZIP",

      // list
      "nameheader"            => "Файл",
      "sizeheader"            => "Размер",
      "typeheader"            => "Тип",
      "modifheader"            => "Изменен",
      "permheader"            => "Права",
      "actionheader"            => "Действия",
      "pathheader"            => "Путь",

      // buttons
      "btncancel"            => "Отменя",
      "btnsave"            => "Сохранить",
      "btnchange"            => "Изменить",
      "btnreset"            => "Очистить",
      "btnclose"            => "Закрыть",
      "btncreate"            => "Создать",
      "btnsearch"            => "Поиск",
      "btnupload"            => "Закачать",
      "btncopy"            => "Копировать",
      "btnmove"            => "Переместить",
      "btnlogin"            => "Войти",
      "btnlogout"            => "Выйти",
      "btnadd"            => "Добавить",
      "btnedit"            => "Редактировать",
      "btnremove"            => "Удалить",

      // actions
      "actdir"            => "Папка",
      "actperms"            => "Поменять права",
      "actedit"            => "Правит файл",
      "actsearchresults"      => "Результаты поиска",
      "actcopyitems"            => "Копировать объект(ы)",
      "actcopyfrom"            => "Копировать из /%s в /%s ",
      "actmoveitems"            => "Переместить объект(ы)",
      "actmovefrom"            => "Переместить из /%s в /%s ",
      "actlogin"            => "Войти",
      "actloginheader"      => "Войти, чтобы начать использовать QuiXplorer",
      "actadmin"            => "Администрирование",
      "actchpwd"            => "Сменить пароль",
      "actusers"            => "Пользователи",
      "actarchive"            => "Заархивировать объект(ы)",
      "actupload"            => "Закачать файл(ы)",

      // misc
      "miscitems"            => "Объект(а/ов)",
      "miscfree"            => "Свободно",
      "miscusername"            => "Пользователь",
      "miscpassword"            => "Пароль",
      "miscoldpass"            => "Старый пароль",
      "miscnewpass"            => "Новый пароль",
      "miscconfpass"            => "Подтвердите пароль",
      "miscconfnewpass"      => "Подтвердите новый пароль",
      "miscchpass"            => "Поменять пароль",
      "mischomedir"            => "Домашняя директория",
      "mischomeurl"            => "Домашний URL",
      "miscshowhidden"      => "Показывать спрятанные объекты",
      "mischidepattern"      => "Прятать файлы",
      "miscperms"            => "Права",
      "miscuseritems"            => "(имя, домашняя директория, показывать спрятанные объекты, права досутпа, активен)",
      "miscadduser"            => "добавить пользователя",
      "miscedituser"            => "редактировать пользователя '%s'",
      "miscactive"            => "Активен",
      "misclang"            => "Язык",
      "miscnoresult"            => "Нет результатов",
      "miscsubdirs"            => "Искать в поддиректориях",
      "miscpermnames"            => array("Только просмотр","Редактирование","Сменя пароля","Правка и смена пароля",
                              "Администратор"),
      "miscyesno"            => array("Да","Нет","Д","Н"),
      "miscchmod"            => array("Владелец", "Группа", "Интернет"),
);
?>
