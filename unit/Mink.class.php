<?php


/**
 * Клас  'tests_Test' - Разни тестове на PHP-to
 *
 *
 * @category  bgerp
 * @package   tests
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @link
 */
class unit_Mink extends core_Manager {

    
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
    * Създаване на фирма и папка към нея, допуска дублиране - ОК
    * Select2 трябва да е деинсталиран
    */
    //http://localhost/unit_Mink/CompanyName/
    function act_CompanyName()
    {
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нова фирма
        $Company = "";
        $browser->click('Визитник');
        $browser->press('Нова фирма');
        $browser->setValue('name', '<FONT COLOR=RED>!!! BUG !!!</FONT> " &lt; &#9829; \' [#title#]');
        ///!!redBUG!!
        //$browser->setValue('name', '<FONT COLOR=RED>!!! red BUG !!!</FONT> " &lt; &#9829; \' [#title#]');
        
        $browser->setValue('place', 'Ст. Загора');
        $browser->setValue('pCode', '6400');
        $browser->setValue('address', '<b>!BUG!</b>"&lt;&#9829;\'[#title#]');
        $browser->setValue('fax', '036111111');
        $browser->setValue('tel', '036111111');
        $browser->setValue('uicId', '110001322');
        $browser->setValue('Клиенти', '1');
        $browser->setValue('Доставчици', '2');
        $browser->press('Запис');
        return $browser->getText();
        
        if (strpos($browser->getText(),"Предупреждение:")){
            $browser->setValue('Ignore', 1);
            $browser->press('Запис');
        }
        
        if(strpos($browser->gettext(), '<FONT COLOR=RED>!!redBUG!!</FONT>"&lt;&#9829;\'[#title#]')) {
        // if(strpos($browser->gettext(), '<FONT COLOR=RED>!!redBUG!!</FONT>"<&#9829;[#title#]')) {    
            return "Име";
        } else {                       
            //return $browser->getHtml();
            return "Грешно име";
        }
        
    }
    
    /**
     *  Създаване на параметър - текст.
     */
    //http://localhost/unit_Mink/CreateParam/
    function act_CreateParam()
    {
         
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нов параметър
        $browser->click('Каталог');
        $browser->click('Параметри');
        $browser->press('Нов запис');
        $browser->setValue('driverClass', 'Текст');
        $browser->refresh('Запис');
        $browser->setValue('name', '<FONT COLOR=RED>!!!red BUG !!!</FONT>;\'[#title#]');
        $browser->setValue('suffix', 'text');
        $browser->setValue('default', '<FONT COLOR=RED>!!! redBUG !!!</FONT> " &lt; &#9829; \' [#title#]'); 
        $browser->setValue('rows', '2'); 
        $browser->press('Запис');
       
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
        }
        if(strpos($browser->gettext(), '<FONT COLOR=RED>!!!red BUG !!!</FONT>;\'[#title#]]')) {
        } else {
            return "Грешно име";
        }
        //return $browser->getHtml();
    }  
    
    /**
    * Създаване на параметър - избор.
    */
    //http://localhost/unit_Mink/CreateParamChoise/
    function act_CreateParamChoise()
    {
         
        // Логване
        $browser = $this->SetUp();
    
        // Създаване на нов параметър
        $browser->click('Каталог');
        $browser->click('Параметри');
        $browser->press('Нов запис');
        $browser->setValue('driverClass', 'Избор');
        $browser->refresh('Запис');
        $browser->setValue('name', '<FONT COLOR=RED>!!! redBUG !!!</FONT> " &lt; &#9829; \'[#title#]');
        //$browser->setValue('name', '<FONT COLOR=RED>!!!red BUG !!!</FONT>;\'[#title#]');
        //CR, за да се избират като опции
        $browser->setValue('options', 'text');
        $browser->setValue('options', '<FONT COLOR=RED>!!! redBUG !!!</FONT> " &lt; &#9829; \'[#title#]');
        $browser->setValue('default', '<FONT COLOR=RED>!!! redBUG !!!</FONT> " &lt; &#9829; \'[#title#]');
        //bp($browser->gettext());
        $browser->press('Запис');
       
        if (strpos($browser->getText(),"Вече съществува запис със същите данни")){
            $browser->press('Отказ');
        }
        if(strpos($browser->gettext(), '<FONT COLOR=RED>!!! redBUG !!!</FONT> " &lt; &#9829; \'[#title#]')) {
        } else {
            return "Грешно име";
        } 
        return $browser->getHtml();
    }
    
    /**
     * Търсим фирма, ако я има - отваряме и редактираме, ако не - създаваме нова фирма. Ако има повече от една страница, не работи добре.  Да се търси по буква!!!
     */
    //if(strpos($browser->gettext(), $Company)  && 0) {  - не намира съществуваща фирма
    //if(strpos($browser->gettext(), $Company)) {намира фирмата, но дава грешка при търсене на несъществуваща,  заради търсенето
    //http://localhost/unit_MinkPbgERP/TestFirm/
    function act_TestFirm()
    {
    
        // Логване
        $browser = $this->SetUp();
         
        //$browser->hasText('Известия');
        //$browser->hasText('Pavlinka');
        $browser->click('Визитник');
        // търсим фирмата
        //$browser->click('P');
        $Company = "Пролет ООД";
         
        //$browser->open("/crm_Companies/?id=&Rejected=&alpha=&Cmd[default]=1&search={$Company}&users=all_users&order=alphabetic&groupId=&Cmd[default]=Филтрирай");
        //if(strpos($browser->gettext(), $Company)  && 0) {  - не намира съществуваща фирма
        //if(strpos($browser->gettext(), $Company)) { намира фирмата, но дава грешка при търсене на несъществуваща, заради търсенето
        if(strpos($browser->gettext(), $Company)) {
            //bp($browser->gettext());
            //има такава фирма - редакция
            $browser->click($Company);
            $browser->press('Редакция');
    
        } else {
             
            // Създаване на нова фирма
            $browser->press('Нова фирма');
            bp($browser->getText());
            //$browser->hasText('Добавяне на запис');
            //$browser->hasText('Фирма');
        }
        $browser->setValue('name', $Company);
        $browser->setValue('place', 'Плевен');
        $browser->setValue('pCode', '6400');
        $browser->setValue('address', 'ул.Днепър, №11');
        $browser->setValue('fax', '086898989');
        $browser->setValue('tel', '086799999');
        $browser->setValue('info', 'Тази фирма е редактирана');
        $browser->setValue('Клиенти', '1');
        $browser->press('Запис');
        // Създаване на папка
        $browser->press('Папка');
    } 
    
    function act_Test()
    {
        // Отваряме bgERP-а
        $browser = cls::get('unit_Browser');
        $browser->start('http://localhost/root/bgerp/');
        
        // Логваме се
        $browser->click('Enter');
        $browser->setValue('nick', 'milen');
        $browser->setValue('pass', 'parola');
        $browser->press('Вход');
        
        // Отваряме папката на фирма Експерта
        $browser->click('Всички');
        $browser->hasText('Експерта ООД - В. Търново');
        $browser->click('Експерта ООД - В. Търново');
        
        // Правим нова продажба
        $browser->press('Нов...');
        $browser->press('Продажба');
        
        // Попълваме някои полета
        $browser->setValue('deliveryTime[d]', date('d-m-Y'));
        $browser->setValue('deliveryTime[t]', '08:30');
        $browser->setValue('shipmentStoreId', 'Основен');
        
        // Записваме дотук черновата на продажбата
        $browser->press('Чернова');
        
        // Пак я редактираме
        $browser->press('Редакция');
        $browser->setValue('paymentMethodId', "До 3 дни след фактуриране");
        $browser->press('Чернова');
        
        // Добавяме нов артикул
        $browser->press('Артикул');
        $browser->setValue('productId', 'ЕП 22 (13)');
        $browser->refresh('Запис');
        $browser->setValue('packQuantity', 10);
        $browser->setValue('packPrice', 2);
        
        // Запосваме артикула
        $browser->press('Запис');
        
        // Игнорираме предупреждението за липсваща стока
        $browser->setValue('Ignore', 1);
        $browser->press('Запис');
        return $browser->getText();
    }
        
}