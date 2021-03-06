<?php


/**
 *  Клас  'unit_MinkPbgERP' - PHP тестове - стандартни
 *
 * @category  bgerp
 * @package   tests
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @link
 */

class unit_MinkPbgERP extends core_Manager {
   
   /** Номерацията показва препоръчвания ред на изпълнение. Еднаквите номера могат да се разместват.
    *
    */
    //http://localhost/unit_MinkPbgERP/Run/
    public function act_Run()
    {
    
        $res = '';
        $res .= "1.".$this->act_CreateUser1();
        $res .= "  2.".$this->act_CreateUser2();
        $res .= "  3.".$this->act_CreateStore();
        $res .= "  4.".$this->act_CreateBankAcc1();
        $res .= "  5.".$this->act_CreateBankAcc2();
        $res .= "  6.".$this->act_CreateCase();
        $res .= "  7.".$this->act_CreateCategory();
        $res .= "  8.".$this->act_CreateParam();
        $res .= "  9.".$this->act_CreateMeasure();
        $res .= "  10.".$this->act_CreatePackage();
        $res .= "  11.".$this->act_CreateGroup();
        $res .= "  12.".$this->act_CreateProject();
        $res .= "  13.".$this->act_CreateDepartment1();
        $res .= "  14.".$this->act_CreateDepartment2();
        $res .= "  15.".$this->act_CreateProduct();
        $res .= "  16.".$this->act_CreateProductBom();
        $res .= "  17.".$this->act_CreateBom();
        $res .= "  18.".$this->act_CreateEditPerson();
        $res .= "  19.".$this->act_CreateCompany();
        $res .= "  20.".$this->act_EditCompany();
        $res .= "  21.".$this->act_CreateLocation1();
        $res .= "  22.".$this->act_CreateLocation2();
        $res .= "  23.".$this->act_CreateEditCompany();
        $res .= "  24.".$this->act_CreateInq();
        $res .= "  25.".$this->act_CreateQuotation();
        $res .= "  26.".$this->act_CreatePurchase();
        $res .= "  27.".$this->act_CreatePlanningJob();
        $res .= "  28.".$this->act_CreateSale();
        
        return $res;
    }
    /**
     * Логване
     */
    public function SetUp()
    {
        $browser = cls::get('unit_Browser');
        $browser->start('http://localhost/');
        $browser->click('Вход');
        $browser->setValue('nick', 'Pavlinka');
        $browser->setValue('pass', '111111');
        // проверка потребител/парола
        //Грешка:Грешна парола или ник!
        //$browser->hasText('Известия');
        //$browser->hasText('Pavlinka');
        $browser->press('Вход');
        return $browser;
    }
     
    /**
     * Select2 трябва да се деактивира - не работи
     */
    //http://localhost/unit_MinkPbgERP/DeinstallSelect2/
    function act_DeinstallSelect2()
    {
        // Логване
        $browser = $this->SetUp();
    
        $browser->click('Админ');
        $browser->setValue('search', 'select2');
        $browser->press('Филтрирай');
        $browser->click('Деактивиране на пакета');
        //return $browser->getHtml();
        /////////////  не работи
        //$browser->click('select2-deinstall');
        return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на потребител
     */
    //http://localhost/unit_MinkPbgERP/CreateUser1/
    function act_CreateUser1()
    {
        // Логване
        $browser = $this->SetUp();
        // Създаване на потребител
        $browser->click('Админ');
        $browser->click('Потребители');
        $browser->press('Нов запис');
        $browser->setValue('nick', 'User1');
        $browser->setValue('passNew', '123456');
        $browser->setValue('passRe', '123456');
        $browser->setValue('names', 'Потребител 1');
        $browser->setValue('email', 'u1@abv.bg');
        $browser->setValue('rolesInput[71]', '71');
        //$browser->setValue('Дилър', '79');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            return 'Дублиране на потребител.';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на потребител от Визитник - профили
     */
    //http://localhost/unit_MinkPbgERP/CreateUser2/
    function act_CreateUser2()
    {
        // Логване
        $browser = $this->SetUp();
        // Създаване на потребител
        $browser->click('Визитник');
        $browser->click('Профили');
        $browser->press('Нов потребител');
        $browser->setValue('nick', 'User2');
        $browser->setValue('passNew', '123456');
        $browser->setValue('passRe', '123456');
        $browser->setValue('names', 'Потребител 2');
        $browser->setValue('email', 'u2@abv.bg');
        $browser->setValue('officer', '10');
        $browser->setValue('Главен офис', '13');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            return 'Дублиране на потребител.';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на склад
     */
    //http://localhost/unit_MinkPbgERP/CreateStore/
    function act_CreateStore()
    {
        // Логване
        $browser = $this->SetUp();
        
        // Създаване на нов склад
        $browser->click('Склад');
        $browser->click('Складове');
        $browser->press('Нов запис');
        //$browser->hasText('Добавяне на запис в "Складове"');
        $browser->setValue('name', 'Склад 1');
        //$ekip='Екип "Главен офис"';
        //$browser->setValue($ekip, '1');
        $browser->setValue('Pavlinka', '1');
        $browser->press('Запис');
        //if (strpos($browser->getText(),'Непопълнено задължително поле')){
        //    $browser->press('Отказ');
        //    Return Грешка;
        //}
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            return 'Дублиране на склад.';
        }
        //return $browser->getHtml();
    }  
    
    /**
    * 1. Създаване на банкова сметка от Финанси 
    */
    //http://localhost/unit_MinkPbgERP/CreateBankAcc1/
    function act_CreateBankAcc1()
    {
        // Логване
        $browser = $this->SetUp();
            
        // Създаване на банкова сметка
        $browser->click('Банки');
        $browser->press('Нов запис');
        //$browser->hasText('Добавяне на запис в "Банкови сметки на фирмата"');
        $browser->setValue('iban', '#BG11CREX92603114548401');
        //$browser->setValue('iban', '#BG22UNCR70001519562302');
        $browser->setValue('currencyId', '1');
        $browser->setValue('Pavlinka', '1');
        //$browser->setValue('operators_1', '1');
        $browser->press('Запис');
        //if (strpos($browser->getText(),'Непопълнено задължително поле')){
        //    $browser->press('Отказ');
        //    Return Грешка;
        //}
        if (strpos($browser->getText(),"Вече има наша сметка с този IBAN")){
            $browser->press('Отказ');
            Return 'Дублиране на банкова сметка.';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на банкова сметка от Визитник - фирма
     */
    //http://localhost/unit_MinkPbgERP/CreateBankAcc2/
    function act_CreateBankAcc2()
    {
        // Логване
        $browser = $this->SetUp();
        //Отваряне на папката на фирмата
        $browser->click('Визитник');
        $Company = "Моята Фирма ООД";
        $browser->click($Company);
        // Създаване на банкова сметка
        $browser->click('Банка');
        $browser->click('Добавяне на нова наша банкова сметка');
        //$browser->setValue('iban', '#BG33UNCR70001519562303');
        $browser->setValue('iban', '#BG22UNCR70001519562302');
        $browser->setValue('currencyId', '1');
        $browser->setValue('Pavlinka', '1');
        //$browser->setValue('operators_1', '1');
        $browser->press('Запис');
        //if (strpos($browser->getText(),'Непопълнено задължително поле')){
        //    $browser->press('Отказ');
        //    Return Грешка;
        //}
        if (strpos($browser->getText(),"Вече има наша сметка с този IBAN")){
            $browser->press('Отказ');
            Return 'Дублиране на банкова сметка.';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1.Създаване на каса
     */
    ///http://localhost/unit_MinkPbgERP/CreateCase/
    function act_CreateCase()
    {
        // Логване
        $browser = $this->SetUp();
        
        // Създаване на нова каса
        $browser->click('Каси');
        $browser->press('Нов запис');
        //$browser->hasText('Добавяне на запис в "Фирмени каси"');
        $browser->setValue('name', 'КАСА 1');
        $browser->setValue('Pavlinka', '1');
        //$browser->setValue('cashiers_1', '1');
        $browser->press('Запис');
        //if (strpos($browser->getText(),'Непопълнено задължително поле')){
        //    $browser->press('Отказ');
        //    Return Грешка;
        //}
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на каса';
        }
        //return $browser->getHtml();
    }   
    
    /**
     * 1. Създаване на категория.
     */
    //http://localhost/unit_MinkPbgERP/CreateCategory/
    function act_CreateCategory()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нова категория
        $browser->click('Каталог');
        $browser->click('Категории');
        $browser->press('Нов запис');
        $browser->setValue('name', 'Други');
        $browser->setValue('meta_canStore', 'canStore');
        $browser->setValue('meta_canConvert', 'canConvert');
        $browser->setValue('meta_canManifacture', 'canManifacture');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на категория';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на параметър.
     */
    //http://localhost/unit_MinkPbgERP/CreateParam/
    function act_CreateParam()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нов параметър
        $browser->click('Каталог');
        $browser->click('Параметри');
        $browser->press('Нов запис');
        $browser->setValue('driverClass', 'Символи');
        $browser->refresh('Запис');
        $browser->setValue('name', 'Състояние');
        $browser->setValue('lenght', '15'); 
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на параметър';
        }
        //return $browser->getHtml();
    }
    /**
     * 1. Създаване на мярка.
     */
    //http://localhost/unit_MinkPbgERP/CreateMeasure/
    function act_CreateMeasure()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нова мярка
        $browser->click('Каталог');
        $browser->click('Мерки');
        $browser->press('Нов запис');
        $browser->setValue('name', 'Човекочас');
        $browser->setValue('shortName', 'Чч');
        $browser->setValue('defQuantity', '1');
        $browser->setValue('round', '2');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на мярка';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на опаковка.
     */
    //http://localhost/unit_MinkPbgERP/CreatePackage/
    function act_CreatePackage()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нова опаковка
        $browser->click('Каталог');
        $browser->click('Мерки');
        $browser->click('Опаковки');
        $browser->press('Нов запис');
        $browser->setValue('name', 'Контейнер');
        $browser->setValue('shortName', 'Контейнер');
        $browser->setValue('baseUnitId', 'литър');
        $browser->setValue('baseUnitRatio', '1000');
        $browser->setValue('defQuantity', '1');
        $browser->setValue('round', '0');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на опаковка';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на група
     */
    //http://localhost/unit_MinkPbgERP/CreateGroup/
    function act_CreateGroup()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нова група
        $browser->click('Каталог');
        $browser->click('Групи');
        $browser->press('Нов запис');
        $browser->setValue('name', 'Промоция');
        $browser->setValue('parentId', 'Ценова група');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на група';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на проект
     */
    //http://localhost/unit_MinkPbgERP/CreateProject/
    function act_CreateProject()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на проект
        $browser->click('Всички');
        $browser->click('Проекти');
        $browser->press('Нов запис');
        $browser->setValue('name', 'Други проекти');
        $browser->setValue('Бележки', '54');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на проект';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на първо звено
     */
    //http://localhost/unit_MinkPbgERP/CreateDepartment1/
    function act_CreateDepartment1()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на звено
        $browser->click('HR');
        $browser->click('Структура');
        $browser->press('Нов запис');
        //$browser->hasText('Добавяне на запис в "Организационна структура"');
        $browser->setValue('name', 'Завод');
        //$ekip='Екип "Главен офис"';
        //$browser->setValue($ekip, '1');
        $browser->setValue('Pavlinka', '1');
        $browser->press('Запис');
        if (strpos($browser->getText(),'Непопълнено задължително поле')){
            $browser->press('Отказ');
            Return 'Непопълнено задължително поле - звено';
        }
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на звено';
        }
        //return $browser->getHtml();
    }
    /**
     * 2. Създаване на второ звено
     */
    //http://localhost/unit_MinkPbgERP/CreateDepartment2/
    function act_CreateDepartment2()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на звено
        $browser->click('HR');
        $browser->click('Структура');
        $browser->press('Нов запис');
        //$browser->hasText('Добавяне на запис в "Организационна структура"');
        $browser->setValue('name', 'Производство');
        $browser->setValue('parentId', 'Завод');
        //$ekip='Екип "Главен офис"';
        //$browser->setValue($ekip, '1');
        $browser->setValue('Pavlinka', '1');
        $browser->press('Запис');
         if (strpos($browser->getText(),'Непопълнено задължително поле')){
            $browser->press('Отказ');
            Return 'Непопълнено задължително поле - звено';
        }
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            Return 'Дублиране на звено';
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на артикул - продукт с параметри, ако го има - редакция.
     */
    //http://localhost/unit_MinkPbgERP/CreateProduct/
    function act_CreateProduct()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нов артикул - продукт
        $browser->click('Каталог');
        $browser->press('Нов запис');
        $browser->setValue('catcategorieId', 'Продукти');
        $browser->press('Напред');
        $browser->setValue('name', 'Чувал голям 50 L');
        $browser->setValue('code', 'smet50big');
        $browser->setValue('measureId', 'брой');
        $browser->setValue('info', 'черен');
        $browser->setValue('meta_canBuy', 'canBuy');
        $browser->press('Запис');
    
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            $browser->click('Продукти');
            $browser->click('Чувал голям 50 L');
            $browser->press('Редакция');
            $browser->setValue('info', 'прозрачен');
            //$browser->setValue('groups[4]', '4');
            $browser->press('Запис');
        } else {
            $browser->click('Добавяне на нов параметър');
            $browser->setValue('paramId', 'Дължина');
            $browser->refresh('Запис');
            $browser->setValue('paramValue', '50');
            $browser->press('Запис и Нов');
            $browser->setValue('paramId', 'Широчина');
            $browser->refresh('Запис');
            $browser->setValue('paramValue', '26');
            $browser->press('Запис');
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на артикул - продукт през папката. Добавяне на рецепта.
     */
    //http://localhost/unit_MinkPbgERP/CreateProductBom/
    function act_CreateProductBom()
    {
        // Логване
        $browser = $this->SetUp();
         
        // Създаване на нов артикул - продукт
        $browser->click('Каталог');
        $browser->click('Продукти');
        $browser->press('Артикул');
        $browser->setValue('name', 'Плик 7 л');
        $browser->setValue('code', 'plik7');
        $browser->setValue('measureId', 'брой');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
            $browser->click('Плик 7 л');
        }
        // Добавяне рецепта
        $browser->click('Рецепти');
        $browser->click('Добавяне на нова търговска технологична рецепта');
        //$browser->hasText('Добавяне на търговска рецепта към');
        $browser->setValue('expenses', '3');
        $browser->setValue('quantityForPrice', '100');
        $browser->press('Чернова');
        $browser->press('Влагане');
        $browser->setValue('resourceId', 'Друг труд');
        $browser->setValue('propQuantity', '6');
        $browser->refresh('Запис');
        // refresh('Запис') е нужен, когато мярката не излиза като отделно поле, напр. на труд, услуги
        $browser->press('Запис и Нов');
        $browser->setValue('resourceId', 'Други суровини и материали');
        $browser->setValue('propQuantity', '1 + $Начално= 10');
        $browser->refresh('Запис');
        $browser->press('Запис');
        $browser->press('Активиране');
        //return $browser->getHtml();
    }
    
    /**
     *2. Създаване на рецепта
     *
     */
    //http://localhost/unit_MinkPbgERP/CreateBom/
    function act_CreateBom()
    {
        // Логване
        $browser = $this->SetUp();
         
        $browser->click('Каталог');
        $browser->click('Продукти');
        $browser->click('Чувал голям 50 L');
        $browser->click('Рецепти');
        $browser->click('Добавяне на нова търговска технологична рецепта');
        //$browser->hasText('Добавяне на търговска рецепта към');
        $browser->setValue('notes', 'CreateBom');
        $browser->setValue('expenses', '8');
        $browser->setValue('quantityForPrice', '100');
        $browser->press('Чернова');
        $browser->press('Влагане');
        $browser->setValue('resourceId', 'Други суровини и материали');
        $browser->setValue('propQuantity', '1,6');
        $browser->refresh('Запис');
        // refresh('Запис') е нужен, когато мярката не излиза като отделно поле, напр. на труд, услуги
        $browser->press('Запис и Нов');
        $browser->setValue('resourceId', 'Други консумативи');
        $browser->setValue('propQuantity', '1,2634');
        $browser->refresh('Запис');
        // refresh('Запис') е нужен, когато мярката не излиза като отделно поле, напр. на труд, услуги
        $browser->press('Запис и Нов');
        $browser->setValue('resourceId', 'Друг труд');
        $browser->setValue('propQuantity', '1 + $Начално= 10');
        $browser->refresh('Запис');
        $browser->press('Запис');
        $browser->press('Активиране');
        //return $browser->getHtml();
    }
    
    /**
     * 3. Създаване на лице
     * Select2 трябва да е деинсталиран
     */
    //http://localhost/unit_MinkPbgERP/CreateEditPerson/
    function act_CreateEditPerson()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на лице
        $browser->click('Визитник');
        $browser->click('Лица');
        $person = "Стела Донева";
        if(strpos($browser->gettext(), $person)) {
            //има такова лице - редакция
            $browser->click($person);
            $browser->press('Редакция');
            $browser->setValue('place', 'Русе');
            $browser->setValue('address', 'ул.Дунав, №2');
            $browser->press('Запис');
            
        } else {
            // Създаване на лице
            $browser->press('Ново лице');
            $browser->setValue('name', $person);
            //$browser->setValue('groups[5]', '5');
            $browser->setValue('Служители', '5');
            $browser->press('Запис');
            if (strpos($browser->getText(),"Предупреждение:")){
                $browser->setValue('Ignore', 1);
                $browser->press('Запис');
            }
            // Добавяне на код и звено
            $browser->click('HR');
            $browser->click('Добавяне на служебни данни');
            $browser->setValue('code', 'STD');
            $browser->setValue('Завод » Производство','3');
            $browser->press('Запис');
        }
        //return $browser->getHtml();
    }
    
    /**
     * 1. Създаване на фирма и папка към нея, допуска дублиране - ОК
     * Select2 трябва да е деинсталиран
     */
    //http://localhost/unit_MinkPbgERP/CreateCompany/
    function act_CreateCompany()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нова фирма
        $browser->click('Визитник');
        $browser->press('Нова фирма');
        //$browser->hasText('Добавяне на запис в "Фирми"');
        //$browser->hasText('Фирма');
        $browser->setValue('name', 'Фирма bgErp');
        $browser->setValue('place', 'Ст. Загора');
        $browser->setValue('pCode', '6400');
        $browser->setValue('address', 'ул.Бояна, №122');
        $browser->setValue('fax', '036111111');
        $browser->setValue('tel', '036111111');
        $browser->setValue('vatId', 'BG814228908');
        $browser->setValue('Клиенти', '1');
        $browser->setValue('Доставчици', '2');
        $browser->press('Запис');
        if (strpos($browser->getText(),"Предупреждение:")){
            $browser->setValue('Ignore', 1);
            $browser->press('Запис');
        }
        // Създаване на папка на новата фирма
        $browser->press('Папка');
        //return $browser->getHtml();
    }
    
    /**
     * 2. Редакция на фирма
     */
    //http://localhost/unit_MinkPbgERP/EditCompany/
    function act_EditCompany()
    {
        // Логване
        $browser = $this->SetUp();
        //Отваряме папката на фирма Фирма bgErp
        $browser->click('Визитник');
        $browser->click('F');
        //$browser->hasText('Фирма bgErp');
        $browser->click('Фирма bgErp');
        //Проверка дали сме в Фирма bgErp
        //$browser->hasText('Фирма bgErp - .....');
        $browser->press('Редакция');
        //Проверка дали сме в редакция
        //$browser->hasText('Редактиране на запис в "Фирми"');
        $browser->setValue('address', 'ул.Втора, №2');
        $browser->setValue('fax', '042333333');
        $browser->setValue('tel', '042222222');
        $browser->press('Запис');
        //return $browser->getHtml();
    }
    
    /**
     * 2. Локация от фирма
     */
    //http://localhost/unit_MinkPbgERP/CreateLocation1/
    function act_CreateLocation1()
    {
        // Логване
        $browser = $this->SetUp();
        $browser->click('Визитник');
        // търсим фирмата
        $browser->click('F');
        //$browser->hasText('Фирма bgErp');
        $Company = 'Фирма bgErp';
        //Проверка дали сме в Фирма bgErp
        if(strpos($browser->gettext(), $Company)) {
            //има такава фирма - редакция
            $browser->click($Company);
            $browser->click('Добавяне на нова локация');
            $browser->setValue('title', 'Офис Пловдив');
            $browser->setValue('type', 'Офис');
            $browser->setValue('place', 'Пловдив');
            $browser->setValue('pCode', '4000');
            $browser->setValue('address', 'ул.Родопи, №52');
            $browser->press('Запис');
        } else {
            Return "Няма такава фирма";
        }
       
    } 
    /**
     * 2. Локация от таб Локации
     */
    //http://localhost/unit_MinkPbgERP/CreateLocation2/
    function act_CreateLocation2()
    {
        // Логване
        $browser = $this->SetUp();
        $browser->click('Визитник');
        $browser->click('Локации');
        $browser->press('Нов търговски обект');
        $browser->setValue('name', 'Фирма с локация');
        $browser->setValue('place', 'Варна');
        $browser->setValue('address', 'ул.Морска, №122');
        $browser->setValue('title', 'Централен офис');
        $browser->setValue('type', 'Главна квартира');
        $browser->setValue('dateFld', date('d-m-Y'));
        $browser->setValue('repeat', '1 мес.');
        $browser->press('Запис');
          
    }
    /**
     * 1. Фирма - чуждестранна, ако я има - отваряме и редактираме, ако не - създаваме я
     */
    //http://localhost/unit_MinkPbgERP/CreateEditCompany/
    function act_CreateEditCompany()
    {
        // Логване
        $browser = $this->SetUp();
    
        $browser->click('Визитник');
        // търсим фирмата
        $browser->click('N');
        $Company = "NEW INTERNATIONAL GMBH";
        if(strpos($browser->gettext(), $Company)) {
            //има такава фирма - редакция
            $browser->click($Company);
            $browser->press('Редакция');
            //Проверка дали сме в редакция
            //$browser->hasText('Редактиране на запис в "Фирми"');
        } else {
            // Създаване на нова фирма
            $browser->press('Нова фирма');
            //Проверка дали сме в добавяне
            //$browser->hasText('Добавяне на запис в "Фирми"');
        }
        $browser->setValue('name', $Company);
        $browser->setValue('country', 'Германия');
        $browser->setValue('place', 'Stuttgart');
        $browser->setValue('pCode', '70376');
        $browser->setValue('address', 'Brückenstraße 44А');
        $browser->setValue('vatId', 'DE813647335');
        $browser->setValue('website', 'http://www.new-international.com');
        $browser->setValue('Клиенти', '1');
        $browser->setValue('info', 'Фирма за тестове');
        $browser->press('Запис');
        // Създаване на папка на нова фирма/отваряне на папката на стара
        if(strpos($browser->gettext(), $Company)) {
            $browser->press('Папка');
        }
        //return $browser->getHtml();
    }
    /**
     * 2.Запитване от съществуваща фирма с папка и артикул от него
     */
    //http://localhost/unit_MinkPbgERP/CreateInq/
    function act_CreateInq()
    {
        // Логване
        $browser = $this->SetUp();
    
        //Отваряне на папката на фирмата
        $browser->click('Визитник');
        $browser->click('N');
        $Company = "NEW INTERNATIONAL GMBH";
        $browser->click($Company);
        $browser->press('Папка');
        // ново запитване
        $browser->press('Нов...');
        $browser->press('Запитване');
        //$browser->hasText('Създаване на запитване в');
        $browser->press('Чернова');
        $browser->setValue('inqDescription', 'Торбички');
        $browser->setValue('measureId', 'брой');
        $browser->setValue('quantity1', '1000');
        $browser->setValue('name', 'Peter Neumann');
        $browser->setValue('country', 'Германия');
        $browser->setValue('email', 'pneumann@gmail.com');
        $browser->press('Чернова');
        // Създаване на нов артикул по запитването
        $browser->press('Артикул');
        $browser->setValue('name', 'Артикул по запитване');
        $browser->press('Запис');
        //return $browser->getHtml();
    }
    
    /**
     * 2.Нова оферта на съществуваща фирма с папка
     */
    ///http://localhost/unit_MinkPbgERP/CreateQuotation/
    function act_CreateQuotation()
    {
        // Логване
        $browser = $this->SetUp();
    
        //Отваряне папката на фирмата
        $browser->click('Визитник');
        $browser->click('N');
        $Company = "NEW INTERNATIONAL GMBH";
        $browser->click($Company);
        $browser->press('Папка');
        // нова оферта
        $browser->press('Нов...');
        $browser->press('Оферта');
        $browser->setValue('others', 'MinkPTestCreateQuotation');
        //$browser->hasText('Създаване на оферта в');
        $browser->press('Чернова');
        // Добавяне на артикул - нестандартен
        $browser->press('Добавяне');
        $browser->setValue('productId', 'Артикул по запитване');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', 100);
        $browser->setValue('packPrice', 1);
        //$browser->setValue('discount', 1);
        // Записване на артикула и добавяне на нов
        $browser->press('Запис и Нов');
        $browser->setValue('productId', 'Чувал голям 50 L');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', 100);
        $browser->setValue('packPrice', 2);
        //$browser->setValue('discount', 2);
        // Записваме артикула
        $browser->press('Запис');
        // Записване на артикула и добавяне на опционален - услуга
        $browser->press('Опционален артикул');
        $browser->setValue('productId', 'Други услуги');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', 1);
        $browser->setValue('packPrice', 100);
        // Записване на артикула
        $browser->press('Запис');
        // Активиране на офертата
        $browser->press('Активиране');
        //return $browser->getHtml();
    }
    
    /**
     * 2. Нова покупка от съществуваща фирма с папка
     */
     
    //http://localhost/unit_MinkPbgERP/CreatePurchase/
    function act_CreatePurchase()
    {
        // Логваме се
        $browser = $this->SetUp();
    
        //Отваряме папката на фирмата
        $browser->click('Визитник');
        $browser->click('F');
        $Company = 'Фирма bgErp';
        $browser->click($Company);
        $browser->press('Папка');
    
        // нова покупка - проверка има ли бутон
        if(strpos($browser->gettext(), 'Покупка')) {
            $browser->press('Покупка');
        } else {
            $browser->press('Нов...');
            $browser->press('Покупка');
        }
         
        //$browser->setValue('bankAccountId', '');
        $browser->setValue('note', 'MinkPTestCreatePurchase');
        $browser->setValue('paymentMethodId', "До 3 дни след фактуриране");
        $browser->setValue('chargeVat', "Отделен ред за ДДС");
        $browser->press('Чернова');
    
        // Записваме черновата на покупката
        // Добавяме артикул
        $browser->press('Артикул');
        $browser->setValue('productId', 'Други стоки');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', '15');
        $browser->setValue('packPrice', '1,66');
        $browser->setValue('discount', 4);
    
        // Записваме артикула и добавяме нов - услуга
        $browser->press('Запис и Нов');
        $browser->setValue('productId', 'Други външни услуги');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', 1);
        $browser->setValue('packPrice', '6');
        $browser->setValue('discount', 5);
        // Записваме артикула
        $browser->press('Запис');
             
        // активираме покупката
        $browser->press('Активиране');
        //return  $browser->getHtml();
        //$browser->press('Активиране/Контиране');
        if(strpos($browser->gettext(), '5,92')) {
        } else {
            return "Грешно ДДС - MinkPbgERP/CreatePurchase";
        }
        if(strpos($browser->gettext(), 'Тридесет и пет BGN и 0,52')) {
        } else {
            return "Грешна обща сума - MinkPbgERP/CreatePurchase";
        }
    
        // Складова разписка
        $browser->press('Засклаждане');
        $browser->setValue('storeId', 'Склад 1');
        $browser->press('Чернова');
        $browser->press('Контиране');
    
        // протокол
        $browser->press('Приемане');
        $browser->press('Чернова');
        $browser->press('Контиране');
        //if(strpos($browser->gettext(), 'Контиране')) {
        //  $browser->press('Контиране');
        //}
    
        // Фактура
        $browser->press('Вх. фактура');
        $browser->setValue('number', '1176');
        $browser->press('Чернова');
        $browser->press('Контиране');
    
        // РКО
        $browser->press('РКО');
        $browser->setValue('beneficiary', 'Иван Петров');
        $browser->setValue('amountDeal', '10');
        $browser->setValue('peroCase', 'КАСА 1');
        $browser->press('Чернова');
        $browser->press('Контиране');
    
        // РБД
        $browser->press('РБД');
        $browser->setValue('ownAccount', '#BG11CREX92603114548401');
        $browser->press('Чернова');
        $browser->press('Контиране');
        //Приключване
        $browser->press('Приключване');
        $browser->setValue('valiorStrategy', 'Най-голям вальор в нишката');
        $browser->press('Чернова');
        $browser->press('Контиране');
        if(strpos($browser->gettext(), 'Чакащо плащане: Няма')) {
        } else {
            return "Грешно чакащо плащане - MinkPbgERP/CreatePurchase";
        }
        //return $browser->getHtml();
    }
    
    /**
     * 4. Създава задание за производство
     * (Ако има предишно задание, трябва да се приключи)
     */
    //http://localhost/unit_MinkPbgERP/CreatePlanningJob/
    function act_CreatePlanningJob()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Избиране на артикул
        $browser->click('Каталог');
        $browser->click('Продукти');
        $Item = "Чувал голям 50 L";
    
        if(strpos($browser->gettext(), $Item)) {
            $browser->click($Item);
            //Добавяне на задание
            $browser->click('Задания');
            //Проверка дали може да се добави - не работи
            //if(strpos($browser->gettext(), 'Добавяне на ново задание за производство')) {
            $browser->click('Добавяне на ново задание за производство');
            $valior=strtotime("+1 Day");
            $browser->setValue('dueDate', date('d-m-Y', $valior));
            $browser->setValue('quantity', '1000');
            $browser->setValue('notes', 'CreatePlanningJob');
    
            $browser->press('Чернова');
            $browser->press('Активиране');
            //Добавяне на задача
            $browser->click('Добавяне на нова задача за производство');
            $browser->setValue('hrdepartmentId', 'Производство');
            
            $browser->press('Напред');
            $browser->setValue('storeId', 'Склад 1');
            $browser->press('Чернова');
            
            //return $browser->gethtml();
            $browser->press('Активиране');
            //Произвеждане и влагане
            //$browser->press('Произвеждане'); -разпознава бутона за приключване в заданието
            $browser->press('Добавяне на произведен артикул');
            $browser->setValue('quantity', '1000');
            //$browser->setValue('employees[3]', '3');
            $browser->setValue('STD', '4');
            $browser->press('Запис');
           
            $browser->press('Влагане');
            $browser->setValue('taskProductId', 'Други суровини и материали');
            $browser->setValue('quantity', '1600');
            $browser->press('Запис и Нов');
            $browser->setValue('taskProductId', 'Други консумативи');
            $browser->setValue('quantity', '1263,4');
            $browser->press('Запис и Нов');
            $browser->setValue('taskProductId', 'Друг труд');
            $browser->setValue('quantity', '1010');
            $browser->press('Запис');
            // Приключване на задачата - когато са в една нишка, разпознава бутона за приключване на заданието, защото са с еднакви имена
            $browser->press('Приключване');
            //Протокол за производство - в заданието
            $browser->click('Задание за производство №');
            
            //$browser->press('Създаване на протокол за производство от заданието');
            $browser->press('Произвеждане');
            $browser->setValue('storeId', 'Склад 1');
            $browser->setValue('note', 'Test');
            $browser->press('Чернова');
            $browser->press('Контиране');
            $browser->press('Приключване');
        } else {
        Return "Няма такъв артикул";
        }
        //return $browser->getHtml();
    }
    
    /**
     * 2. Нова продажба на съществуваща фирма с папка
     */
     
    //http://localhost/unit_MinkPbgERP/CreateSale/
    function act_CreateSale()
    {
        // Логване
        $browser = $this->SetUp();
    
        //Отваряне папката на фирмата
        $browser->click('Визитник');
        $browser->click('F');
        $Company = 'Фирма bgErp';
        $browser->click($Company);
        $browser->press('Папка');
    
        // нова продажба - проверка има ли бутон
        if(strpos($browser->gettext(), 'Продажба')) {
            $browser->press('Продажба');
        } else {
            $browser->press('Нов...');
            $browser->press('Продажба');
        }
         
        //$browser->hasText('Създаване на продажба');
        $enddate=strtotime("+2 Days");
        $browser->setValue('deliveryTime[d]', date('d-m-Y', $enddate));
        $browser->setValue('deliveryTime[t]', '10:30');
        $browser->setValue('reff', 'MinkP');
        $browser->setValue('bankAccountId', '');
        $browser->setValue('note', 'MinkPbgErpCreateSale');
        //$browser->setValue('pricesAtDate', date('d-m-Y'));
        $browser->setValue('paymentMethodId', "До 3 дни след фактуриране");
        $browser->setValue('chargeVat', "Отделен ред за ДДС");
        // Записване черновата на продажбата
        $browser->press('Чернова');
    
        // Добавяне на артикул
        $browser->press('Артикул');
        $browser->setValue('productId', 'Други стоки');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', '23');
        $browser->setValue('packPrice', '1,12');
        $browser->setValue('discount', 3);
    
        // Записване артикула и добавяне нов - услуга
        $browser->press('Запис и Нов');
        $browser->setValue('productId', 'Други услуги');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', 10);
        $browser->setValue('packPrice', 1.1124);
        $browser->setValue('discount', 1);
    
        // Записване на артикула
        $browser->press('Запис');
        // Игнорираме предупреждението за липсваща стока
        //$browser->setValue('Ignore', 1);
        //$browser->press('Запис');
    
        // активиране на продажбата
        $browser->press('Активиране');
        //return  $browser->getHtml();
        //$browser->press('Активиране/Контиране');
         
        if(strpos($browser->gettext(), '7,20')) {
        } else {
            return "Грешно ДДС - MinkPbgERP/CreateSale";
        }
        if(strpos($browser->gettext(), 'Четиридесет и три BGN и 0,20')) {
        } else {
            return "Грешна обща сума - MinkPbgERP/CreateSale";
        }
    
        // експедиционно нареждане
        $browser->press('Експедиране');
        $browser->setValue('storeId', 'Склад 1');
        $browser->setValue('template', 'Експедиционно нареждане с цени');
        $browser->press('Чернова');
        $browser->press('Контиране');
        if(strpos($browser->gettext(), 'Двадесет и девет BGN и 0,99 ')) {
        } else {
            return "Грешна сума в ЕН";
        }
             
        // протокол
        $browser->press('Пр. услуги');
        $browser->press('Чернова');
        $browser->press('Контиране');
        //if(strpos($browser->gettext(), 'Контиране')) {
        //  $browser->press('Контиране');
        //}
        
        // Фактура
        $browser->press('Фактура');
        $browser->setValue('numlimit', '0 - 2000000');
        $browser->press('Чернова');
        //return 'paymentType';
        //$browser->setValue('paymentType', 'По банков път');
        $browser->press('Контиране');
        
        // ПКО
        $browser->press('ПКО');
        $browser->setValue('depositor', 'Иван Петров');
        $browser->setValue('amountDeal', '10');
        $browser->setValue('peroCase', 'КАСА 1');
        $browser->press('Чернова');
        $browser->press('Контиране');
        
        // ПБД
        $browser->press('ПБД');
        $browser->setValue('ownAccount', '#BG11CREX92603114548401');
        $browser->press('Чернова');
        $browser->press('Контиране');
        
        // Приключване
        $browser->press('Приключване');
        $browser->setValue('valiorStrategy', 'Най-голям вальор в нишката');
        $browser->press('Чернова');
        $browser->press('Контиране');
        if(strpos($browser->gettext(), 'Чакащо плащане: Няма')) {
        } else {
            return "Грешно чакащо плащане - MinkPbgERP/CreateSale";
        }
        //return $browser->getHtml();
    }       
         
}