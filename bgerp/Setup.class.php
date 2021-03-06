<?php


/**
 * FileHandler на логото на фирмата на английски
 */
defIfNot(BGERP_COMPANY_LOGO_EN, '');


/**
 * FileHandler на логото на фирмата на български
 */
defIfNot(BGERP_COMPANY_LOGO, '');


/**
 * FileHandler на логото на фирмата на английски
 * Генерирано от svg файл
 */
defIfNot(BGERP_COMPANY_LOGO_SVG_EN, '');


/**
 * FileHandler на логото на фирмата на български
 * Генерирано от svg файл
*/
defIfNot(BGERP_COMPANY_LOGO_SVG, '');


/**
 * След колко време, ако не работи крона да бие нотификация
 */
defIfNot(BGERP_NON_WORKING_CRON_TIME, 3600);


/**
 * Звуков сигнал при нотификация
 */
defIfNot(BGERP_SOUND_ON_NOTIFICATION, 'scanner');


/**
 * class 'bgerp_Setup' - Начално установяване на 'bgerp'
 *
 *
 * @category  bgerp
 * @package   bgerp
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class bgerp_Setup extends core_ProtoSetup {
    
    
    /**
     * Версия на пакета
     */
    var $version = '0.1';
    
    
    /**
     * Мениджър - входна точка в пакета
     */
    var $startCtr = 'bgerp_Menu';
    
    
    /**
     * Екшън - входна точка в пакета
     */
    var $startAct;
    
    
    /**
     * Описание на модула
     */
    var $info = "Основно меню и портал на bgERP";
    
    
    /**
     * Описание на конфигурационните константи
     */
    var $configDescription = array(
        'BGERP_COMPANY_LOGO' => array ('fileman_FileType(bucket=pictures)', 'caption=Фирмена бланка->На български, customizeBy=powerUser'),
        
        'BGERP_COMPANY_LOGO_EN' => array ('fileman_FileType(bucket=pictures)', 'caption=Фирмена бланка->На английски, customizeBy=powerUser'),
        
        'BGERP_NON_WORKING_CRON_TIME' => array ('time(suggestions=30 мин.|1 час| 3 часа)', 'caption=След колко време да дава нотификация за неработещ cron->Време'),
                
        'BGERP_SOUND_ON_NOTIFICATION' => array ('enum(none=Няма,snap=Щракване,scanner=Скенер,notification=Нотификация,beep=Beep)', 'caption=Звуков сигнал при нотификация->Звук, customizeBy=user'),
     );
    
    
    /**
     * Описание на системните действия
     */
    var $systemActions = array(
        array('title' => 'Поправка', 'url' => array('doc_Containers', 'repair', 'ret_url' => TRUE), 'params' => array('title' => 'Поправка на системата'))
    );
    
    /**
     * Път до js файла
     */
    //    var $commonJS = 'js/PortalSearch.js';
    
    
    /**
     * Дали пакета е системен
     */
    public $isSystem = TRUE;
    
    
    /**
     * Инсталиране на пакета
     */
    function install()
    {
        // Предотвратяваме логването в Debug режим
        Debug::$isLogging = FALSE;
        
        // Зареждаме мениджъра на плъгините
        $Plugins = cls::get('core_Plugins');
        $html .= $Plugins->repair();
        
        $managers = array(
            'bgerp_Menu',
            'bgerp_Portal',
            'bgerp_Notifications',
            'bgerp_Recently',
            'bgerp_Bookmark',
            'bgerp_LastTouch',
            'bgerp_E',
            'bgerp_F',
        );
        
        $instances = array();
        
        foreach ($managers as $manager) {
            $instances[$manager] = &cls::get($manager);
            $html .= $instances[$manager]->setupMVC();
        }
        
        // Инстанция на мениджъра на пакетите
        $Packs = cls::get('core_Packs');
        
        // Това първо инсталиране ли е?
        $isFirstSetup = ($Packs->count() == 0);
        
        // Списък на основните модули на bgERP
        $packs = "core,log,fileman,drdata,bglocal,editwatch,recently,thumb,doc,acc,cond,currency,cms,
                  email,crm, cat, trans, price, blast,hr,trz,lab,sales,planning,marketing,store,cash,bank,
                  budget,purchase,accda,permanent,sens2,cams,frame,cal,fconv,doclog,fconv,cms,blogm,forum,deals,findeals,tasks,
                  vislog,docoffice,incoming,support,survey,pos,change,sass,
                  callcenter,social,hyphen,distro,dec,status,phpmailer,label,webkittopdf,jqcolorpicker";
        
        // Ако има private проект, добавяме и инсталатора на едноименния му модул
        if (defined('EF_PRIVATE_PATH')) {
            $packs .= ',' . strtolower(basename(EF_PRIVATE_PATH));
        }
        
        // Добавяме допълнителните пакети, само при първоначален Setup
        $Folders = cls::get('doc_Folders');
        
        if (!$Folders->db->tableExists($Folders->dbTableName) || ($isFirstSetup)) {
            $packs .= ",avatar,keyboard,statuses,google,catering,gdocs,jqdatepick,imagics,fastscroll,context,autosize,oembed,hclean,select2,help,toast,minify,rtac,hljs,pixlr,tnef";
        } else {
            $packs = arr::make($packs, TRUE);
            $pQuery = $Packs->getQuery();
            $pQuery->where("#state = 'active'");
            
            while ($pRec = $pQuery->fetch()) {
                if(!$packs[$pRec->name]) {
                    $packs[$pRec->name] = $pRec->name;
                }
            }
        }
        
        if (Request::get('SHUFFLE')) {
            
            // Ако е зададен параметър shuffle  в урл-то разбъркваме пакетите
            if (!is_array($packs)) {
                $packs = arr::make($packs);
            }
            shuffle($packs);
            $packs = implode(',', $packs);
        }
        
        $haveError = array();
        
        core_Debug::$isLogging = FALSE;

        do {
            $loop++;
            
            // Извършваме инициализирането на всички включени в списъка пакети
            foreach (arr::make($packs) as $p) {
                if (cls::load($p . '_Setup', TRUE) && !$isSetup[$p]) {
                    try {
                        $html .= $Packs->setupPack($p);
                        $isSetup[$p] = TRUE;
                        
                        // Махаме грешките, които са възникнали, но все пак
                        // са се поправили в не дебъг режим
                        if (!isDebug()) {
                            unset($haveError[$p]);
                        }
                    } catch (core_exception_Expect $exp) {
                        $force = TRUE;
                        $Packs->alreadySetup[$p . $force] = FALSE;
                        
                        //$haveError = TRUE;
                        file_put_contents(EF_TEMP_PATH . '/' . date('H-i-s') . '.log.html', ht::mixedToHtml($exp->getTrace()) . "\n\n",  FILE_APPEND);
                        $haveError[$p] .= "<h3 class='debug-error'>Грешка при инсталиране на пакета {$p}<br>" . $exp->getMessage() . " " . date('H:i:s') . "</h3>";
                        reportException($exp);
                    }
                }
            }
            
            // Форсираме системния потребител
            core_Users::forceSystemUser();
            
            // Първа итерация за захранване с данни
            $this->loadSetupDataProc($packs, $haveError, $html);
            
            // Втора итерация за захранване с данни
            $this->loadSetupDataProc($packs, $haveError, $html, '2');

            // Де-форсираме системния потребител
            core_Users::cancelSystemUser();
            
        } while (!empty($haveError) && ($loop<5));
        

        core_Debug::$isLogging = TRUE;

        $html .= implode("\n", $haveError);
        
        //Създаваме, кофа, където ще държим всички прикачени файлове на бележките
        $Bucket = cls::get('fileman_Buckets');
        $Bucket->createBucket('Notes', 'Прикачени файлове в бележки', NULL, '1GB', 'user', 'user');
        $Bucket->createBucket('bnav_importCsv', 'CSV за импорт', 'csv', '20MB', 'user', 'every_one');
        $Bucket->createBucket('exportCsv', 'Експортирани CSV-та', 'csv,txt,text,', '10MB', 'user', 'ceo');
        
        // Добавяме Импортиращия драйвър в core_Classes
        $html .= core_Classes::add('bgerp_BaseImporter');
        $html .= $Bucket->createBucket('import', 'Файлове при импортиране', NULL, '104857600', 'user', 'user');
        
        //TODO в момента се записват само при инсталация на целия пакет
        
        
        //Зарежда данни за инициализация от CSV файл за core_Lg
        $html .= bgerp_data_Translations::loadData();
        
        // Инсталираме плъгина за прихващане на първото логване на потребител в системата
        $html .= $Plugins->installPlugin('First Login', 'bgerp_plg_FirstLogin', 'core_Users', 'private');
        
        // Инсталираме плъгина за проверка дали работи cron
        $html .= $Plugins->installPlugin('Check cron', 'bgerp_plg_CheckCronOnLogin', 'core_Users', 'private');
        
        $Menu = cls::get('bgerp_Menu');
        
        // Да се изтрият необновените менюта
        $Menu->deleteNotInstalledMenu = TRUE;
        
        $html .= bgerp_Menu::addOnce(1.62, 'Система', 'Админ', 'core_Packs', 'default', 'admin');
        
        $html .= bgerp_Menu::addOnce(1.66, 'Система', 'Файлове', 'fileman_Log', 'default', 'powerUser');
        
        $html .= $Menu->repair();
        
        // Принудително обновяване на ролите
        $html .= core_Roles::rebuildRoles();
        $html .= core_Users::rebuildRoles();
        
        $html .= core_Classes::add('bgerp_plg_CsvExport');
        
        return $html;
    }

    
    /**
     * Захранва с начални данни посочените пакети
     * 
     * @param array $packs    Масив с пакети
     * @param int   $itr      Номер на итерацията
     *
     * @return array          Грешки
     */
    function loadSetupDataProc($packs, &$haveError = array(), $html = '', $itr = '')
    {
        // Кои пакети дотук сме засели с данни
        $isLoad = array();
        
        // Инстанции на пакетите;
        $packsInst = array();

        // Извършваме инициализирането на всички включени в списъка пакети
        foreach (arr::make($packs) as $p) {
            if (cls::load($p . '_Setup', TRUE) && !$isLoad[$p]) {
                $packsInst[$p] = cls::get($p . '_Setup');
                
                if (method_exists($packsInst[$p], 'loadSetupData')) {
                    try {
                        $html .= "<h2>Инициализиране на $p</h2>";
                        $html .= "<ul>";
                        $html .= $packsInst[$p]->loadSetupData($itr);
                        $html .= "</ul>";
                        $isLoad[$p] = TRUE;
                        
                        // Махаме грешките, които са възникнали, но все пак са се поправили
                        // в не дебъг режим
                        if (!isDebug()) {
                            unset($haveError[$p]);
                        }
                    } catch(core_exception_Expect $exp) {
                        //$haveError = TRUE;
                        file_put_contents(EF_TEMP_PATH . '/' . date('H-i-s') . '.log.html', ht::mixedToHtml($exp->getTrace()) . "\n\n",  FILE_APPEND);
                        $haveError[$p] .= "<h3 class='debug-error'>Грешка при зареждане данните на пакета {$p} <br>" . $exp->getMessage() . " " . date('H:i:s') . "</h3>";
                        reportException($exp);
                    }
                }
            }
        }
    }
}

