<?php 


/**
 * Разпределена група файлове
 * 
 * @category  bgerp
 * @package   distro
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class distro_Group extends core_Master
{
    
    
    /**
     * Заглавие на модела
     */
    public $title = 'Разпределени групи файлове';
    
    
    /**
     * 
     */
    public $singleTitle = 'Група файлове';
    
    
    /**
     * Път към картинка 16x16
     */
    public $singleIcon = 'img/16/distro.png';
    
    
    /**
     * Шаблон за единичния изглед
     */
    public $singleLayoutFile = 'distro/tpl/SingleLayoutGroup.shtml';
    
    
    /**
     * Полета, които ще се клонират
     */
    public $fieldsNotToClone = 'title';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'admin';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'powerUser';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'powerUser';
    
    
    /**
     * Кой има право да го види?
     */
    public $canView = 'admin';
    
    
    /**
     * Кой може да го разглежда?
     */
    public $canList = 'admin';
    
    
    /**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'powerUser';
    
    
    /**
     * Необходими роли за оттегляне на документа
     */
    public $canReject = 'powerUser';
    
    
    /**
     * Кой има право да го изтрие?
     */
    public $canDelete = 'no_one';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'distro_Wrapper, doc_DocumentPlg, doc_ActivatePlg, plg_RowTools, plg_Search, plg_Printing, bgerp_plg_Blank, doc_SharablePlg, plg_Clone';
    
    
    /**
     * Интерфейси, поддържани от този мениджър
     */
    public $interfaces = 'doc_DocumentIntf';
    
    
    /**
     * Абревиатура
     */
    public $abbr = 'Dst';
    
    
    /**
     * Групиране на документите
     */
    public $newBtnGroup = "18.8|Други"; 
    
    
    /**
     * Хипервръзка на даденото поле и поставяне на икона за индивидуален изглед пред него
     */
    public $rowToolsSingleField = 'id';
    

    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    public $searchFields = 'title, repos';
    
    
    /**
     * Детайла, на модела
     */
    public $details = 'distro_Files, distro_Actions';
    
    
    /**
     * Името на кофата за файловете
     */
    public static $bucket = 'distroFiles';
    
    
	/**
     * Описание на модела (таблицата)
     */
    function description()
    {
        $this->FLD('title', 'varchar(128,ci)', 'caption=Заглавие, mandatory, width=100%');
        $this->FLD('repos', 'keylist(mvc=distro_Repositories, select=name, where=#state !\\= \\\'rejected\\\')', 'caption=Хранилища, mandatory, width=100%, maxColumns=3');
        
        $this->setDbUnique('title');
    }
    
    
	/**
     * Може ли документа да се добави в посочената папка?
     */
    public static function canAddToFolder($folderId)
    {
        // Ако няма права за добавяне
        if (!static::haveRightFor('add')) {
            
            // Да не може да добавя
            return FALSE;
        }
    }
    
    
    /**
     * 
     * @param string $path
     * 
     * @return NULL|integer
     */
    public function getGroupIdFromFolder($path)
    {
        $handleArr = doc_Containers::parseHandle($path);
        
        if ($handleArr === FALSE) return ;
        
        return $handleArr['id'];
    }
    
    
	/**
     * Проверка дали нов документ може да бъде добавен в
     * посочената нишка
     */
	public static function canAddToThread($threadId)
    {
        // Ако няма права за добавяне
        if (!static::haveRightFor('add')) {
            
            // Да не може да добавя
            return FALSE;
        }
    }
    
    
	/**
     * Изпълнява се след подготовката на ролите, които могат да изпълняват това действие.
     *
     * @param core_Mvc $mvc
     * @param string $requiredRoles
     * @param string $action
     * @param stdClass $rec
     * @param int $userId
     */
    public static function on_AfterGetRequiredRoles($mvc, &$requiredRoles, $action, $rec = NULL, $userId = NULL)
    {
        // Ако добавяме или променяме запис
        if ($action == 'add' || $action == 'edit') {
            
            // Вземаме всички хранилища
            $reposArr = distro_Repositories::getReposArr();
            
            // Ако няма достъп до някой от тях
            if (empty($reposArr)) {
                
                // Никой да не може да добавя
                $requiredRoles = 'no_one';
            }
        }
        
        // Ако ще разглеждаме сингъла на документа
        if ($action == 'single') {
            
            // Ако нямаме права в нишката
            if (!doc_Threads::haveRightFor('single', $rec->threadId)) {
                
                // Никой да не може
                $requiredRoles = 'no_one';
            }
        }
    }
    
    
	/**
	 * 
     * Функция, която се извиква след активирането на документа
	 * 
	 * @param distro_Group $mvc
	 * @param stdObject $rec
	 */
    public static function on_AfterActivation($mvc, &$rec)
    {
        // Ако са избрани хранилища
        if ($rec->repos) {
            
            // Масив с хранилищата
            $reposArr = type_Keylist::toArray($rec->repos);
            
            // Обхождаме масива
            foreach ((array)$reposArr as $repoId) {
                
                // Активираме хранилището
                distro_Repositories::activateRepo($repoId);
                
                $handle = $mvc->getSubDirName($rec->id);
                
                // TODO Async
                // Създаваме директория в хранилището
                distro_Repositories::createDir($repoId, $handle);
            }
        }
    }
    
    
    /**
     * 
     * 
     * @param integer $id
     * 
     * @return string
     */
    public static function getSubDirName($id)
    {
        
        return self::getHandle($id);
    }
    
    
    /**
     * Проверява дали може да се добави в детайла
     * 
     * @param integer $id - id на записи
     * @param integer $userId - id на потребител
     * 
     * @return boolean - Ако имаме права
     */
    static function canAddDetail($id, $userId=NULL)
    {
        // Ако няма id
        if (!$id) return FALSE;
            
        // Вземаме записа
        $rec = static::fetch($id);
        
        // Ако състоянието не е актвино
        if ($rec->state != 'active') {
            
            return FALSE;
        }
        
        // Ако имаме достъп до сингъла на документа
        if (static::haveRightFor('single', $rec, $userId)) {
                
            return TRUE;
        }
        
        return FALSE;
    }
    
    
    /**
     * Връща масив с хранилищата, които се използват в групата
     * 
     * @param integer $id
     * @param NULL|integer $userId
     * 
     * @return array 
     */
    static function getReposArr($id, $userId=NULL)
    {
        // Вземаме записа
        $rec = static::fetch($id);
        
        // Масив с хранилищатата
        $reposArr = type_Keylist::toArray($rec->repos);
        
        // Обхождаме масива
        foreach ((array)$reposArr as $repoId) {
            
            // Добавяме вербалното име в масива
            $reposArr[$repoId] = distro_Repositories::getVerbal($repoId, 'name');
        }
        
        // Връщаме масива
        return $reposArr;
    }
    
    
	/**
     * Реализация  на интерфейсния метод ::getThreadState()
     */
    static function getThreadState($id)
    {
        
        return 'opened';
    }
    
    
    /**
     * Интерфейсен метод на doc_DocumentInterface
     * 
     * @param integer $id
     */
    function getDocumentRow($id)
    {
        // Ако няма id
        if(!$id) return;
        
        // Вземаме записа
        $rec = $this->fetch($id);
        
        // Вземаме вербалните данни
        $row = new stdClass();
        $row->title = $this->getVerbal($rec, 'title');
        $row->author = $this->getVerbal($rec, 'createdBy');
        $row->state = $rec->state;
        $row->authorId = $rec->createdBy;
        $row->recTitle = $rec->title;
        
        return $row;
    }
    
    
    /**
     * 
     * 
     * @param core_Master $mvc
     * @param stdClass $data
     */
    function on_AfterPrepareSingle($mvc, $res, &$data)
    {
        // Вземаме масива с детайлите
        $detailsArr = arr::make($mvc->details);
        
        // Обхождаме записите
        foreach ($detailsArr as $className) {
            
            try {
                // Инстанция на класа
                $inst = core_Cls::get($className);
                
                // Ако има запис в детайла
                if ($inst->haveRec($inst, $data->rec->id)) {
                    
                    // Премахваме хранилищата
                    unset($data->row->repos);
                    
                    // Прекъсваме
                    break;
                }
            } catch (core_exception_Expect $e) {
                
                continue;
            }
        }
    }
    
    
    /**
     * Изпълнява се след създаването на модела
     * 
     * @param distro_Group $mvc
     * @param string $res
     */
    static function on_AfterSetupMVC($mvc, &$res)
    {
        //Създаваме, кофа, където ще държим всички прикачени файлове
        $res .= fileman_Buckets::createBucket(self::$bucket, 'Качени файлове в дистрибутива', NULL, '104857600', 'user', 'user');
    }
}
