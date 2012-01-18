<?php


/**
 * Клас 'doc_Folders' - Папки с нишки от документи
 *
 *
 * @category  bgerp
 * @package   doc
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class doc_Folders extends core_Master
{
    
    /**
     * Плъгини за зареждане
     */
    var $loadList = 'plg_Created,plg_Rejected,doc_Wrapper,plg_State,doc_FolderPlg,plg_Search ';
    
    
    /**
     * Заглавие
     */
    var $title = "Папки с нишки от документи";
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    var $listFields = 'id,title,type=Тип,inCharge=Отговорник,threads=Нишки,last=Последно';
    
    
    /**
     * Кой може да го прочете?
     */
    var $canRead = 'user';
    
    /**
     * Кой може да пише?
     */
    var $canWrite = 'user';
    
    /**
     * Кой може да го отхвърли?
     */
    var $canReject = 'user';
    
    
    /**
     * полета от БД по които ще се търси
     */
    var $searchFields = 'title';
    
    
    /**
     * Заглавие в единствено число
     */
    var $singleTitle = 'Папка';
    
    function description()
    {
        // Определящ обект за папката
        $this->FLD('coverClass' , 'class(interface=doc_FolderIntf)', 'caption=Корица->Клас');
        $this->FLD('coverId' , 'int', 'caption=Корица->Обект');
        
        // Информация за папката
        $this->FLD('title' , 'varchar(128)', 'caption=Заглавие');
        $this->FLD('status' , 'varchar(128)', 'caption=Статус');
        $this->FLD('state' , 'enum(active=Активно,opened=Отворено,rejected=Оттеглено)', 'caption=Състояние');
        $this->FLD('allThreadsCnt', 'int', 'caption=Нишки->Всички');
        $this->FLD('openThreadsCnt', 'int', 'caption=Нишки->Отворени');
        $this->FLD('last' , 'datetime(format=smartTime)', 'caption=Последно');
        
        $this->setDbUnique('coverId,coverClass');
    }
    
    
    
    /**
     * Филтър на on_AfterPrepareListFilter()
     * Малко манипулации след подготвянето на формата за филтриране
     *
     * @param core_Mvc $mvc
     * @param stdClass $data
     */
    function on_AfterPrepareListFilter($mvc, $data)
    {
        // Добавяме поле във формата за търсене
        $data->listFilter->FNC('users', 'users', 'caption=Потребител,input,silent');
        $data->listFilter->FNC('order', 'enum(pending=Първо чакащите,last=Сортиране по "последно")', 'caption=Подредба,input,silent');
        
        $data->listFilter->view = 'horizontal';
        
        $data->listFilter->toolbar->addSbBtn('Филтрирай', 'default', 'id=filter,class=btn-filter');
        
        // Показваме само това поле. Иначе и другите полета 
        // на модела ще се появят
        $data->listFilter->showFields = 'users,order,search';
        $data->listFilter->setField("users", array('value' => core_Users::getCurrent() ) );
        $data->listFilter->input('users,order,search', 'silent');
    }
    
    
    
    /**
     * Действия преди извличането на данните
     */
    function on_BeforePrepareListRecs($mvc, $res, $data)
    {
        if(!$data->listFilter->rec->users) {
            $data->listFilter->rec->users = '|' . core_Users::getCurrent() . '|';
        }
        
        if(!$data->listFilter->rec->search) {
            $data->query->where("'{$data->listFilter->rec->users}' LIKE CONCAT('%|', #inCharge, '|%')");
            $data->query->orLikeKeylist('shared', $data->listFilter->rec->users);
            $data->title = 'Папките на |*<font color="green">' .
            $data->listFilter->fields['users']->type->toVerbal($data->listFilter->rec->users) . '</font>';
        } else {
            $data->title = 'Търсене във всички папки на |*<font color="green">"' .
            $data->listFilter->fields['search']->type->toVerbal($data->listFilter->rec->search) . '"</font>';
        }
        
        switch($data->listFilter->rec->order) {
            case 'last':
                $data->query->orderBy('#last', 'DESC');
            case 'pending':
            default:
            $data->query->orderBy('#state=DESC,#last=DESC');
        }
    }
    
    
    
    /**
     * Връща информация дали потребителя има достъп до посочената папка
     */
    static function haveRightToFolder($folderId, $userId = NULL)
    {
        $rec = doc_Folders::fetch($folderId);
        
        return doc_Folders::haveRightToObject($rec, $userId);
    }
    
    
    
    /**
     * Дали посоченият (или текущият ако не е посочен) потребител има право на достъп до този обект
     * Обекта трябва да има полета inCharge, access и shared
     */
    function haveRightToObject($rec, $userId = NULL)
    {
        if(!$userId) {
            $userId = core_Users::getCurrent();
        }
        
        // Вземаме членовете на екипа на потребителя (TODO:)
        $teamMembers = core_Users::getTeammates($userId);
        
        // 'ceo' има достъп до всяка папка
        if( haveRole('ceo') ) return TRUE;
        
        // Всеки има право на достъп до папката за която отговаря
        if($rec->inCharge === $userId) return TRUE;
        
        // Всеки има право на достъп до папките, които са му споделени
        if(strpos($rec->shared, '|' . $userId . '|') !== FALSE) return TRUE;
        
        // Всеки има право на достъп до общите папки
        if( $rec->access == 'public' ) return TRUE;
        
        // Дали обекта има отговорник - съекипник
        $fromTeam = strpos($teamMembers, '|' . $rec->inCharge . '|') !== FALSE;
        
        // Ако папката е екипна, и е на член от екипа на потребителя, и потребителя е manager или officer - има достъп
        if( $rec->access == 'team' && $fromTeam && core_Users::haveRole('manager,officer', $userId) ) return TRUE;
        
        // Ако собственика на папката има права 'manager' или 'ceo' отказваме достъпа
        if( core_Users::haveRole('manager,ceo', $rec->inCharge) ) return FALSE;
        
        // Ако папката е лична на член от екпа, и потребителя има права 'manager' - има достъп
        if( $rec->access == 'private' && $fromTeam && haveRole('manager')) return TRUE;
        
        // Ако никое от горните не е изпълнено - отказваме достъпа
        return FALSE;
    }
    
    
    
    /**
     * След преобразуване към вербални данни на записа
     */
    function on_AfterRecToVerbal($mvc, $row, $rec)
    {
        
        $openThreads = $mvc->getVerbal($rec, 'openThreadsCnt');
        
        if($rec->openThreadsCnt) {
            $row->threads = "<span style='float-right; background-color:#aea;padding1px;border:solid 1px #9d9;'>$openThreads</span>";
        }
        
        $row->threads .= "<span style='float:right;'>&nbsp;&nbsp;&nbsp;" . $mvc->getVerbal($rec, 'allThreadsCnt') . "</span>";
        
        $attr['class'] = 'linkWithIcon';
        
        if($mvc->haveRightFor('single', $rec)) {
            // Иконката на папката според достъпа и
            
            switch($rec->access) {
                case 'secret':
                    $img = 'folder_key.png';
                    break;
                case 'private':
                    $img = 'folder_user.png';
                    break;
                case 'team':
                case 'public':
                default:
                $img = 'folder-icon.png';
            }
            
            $attr['style'] = 'background-image:url(' . sbf('img/16/' . $img) . ');';
            $row->title = ht::createLink($row->title, array('doc_Threads', 'list', 'folderId' => $rec->id), NULL, $attr);
        } else {
            $attr['style'] = 'color:#777;background-image:url(' . sbf('img/16/lock.png') . ');';
            $row->title = ht::createElement('span', $attr, $row->title);
        }
        
        $typeMvc = cls::get($rec->coverClass);
        
        $attr['style'] = 'background-image:url(' . sbf($typeMvc->singleIcon) . ');';
        
        if($typeMvc->haveRightFor('single', $rec->coverId)) {
            $row->type = ht::createLink($typeMvc->singleTitle, array($typeMvc, 'single', $rec->coverId), NULL, $attr);
        } else {
            $attr['style'] .= 'color:#777;';
            $row->type = ht::createElement('span', $attr, $typeMvc->singleTitle);
        }
    }
    
    
    
    /**
     * Обновява информацията за съдържанието на дадена папка
     */
    function updateFolderByContent($id)
    {
        $rec = doc_Folders::fetch($id);
        
        $thQuery = doc_Threads::getQuery();
        $rec->openThreadsCnt = $thQuery->count("#folderId = {$id} AND state = 'opened'");
        
        if($rec->openThreadsCnt) {
            $rec->state = 'opened';
        } else {
            $rec->state = 'active';
        }
        
        $thQuery = doc_Threads::getQuery();
        $rec->allThreadsCnt = $thQuery->count("#folderId = {$id}");
        
        $thQuery = doc_Threads::getQuery();
        $thQuery->orderBy("#last", 'DESC');
        $thQuery->limit(1);
        $lastThRec = $thQuery->fetch("#folderId = {$id} && #state != 'rejected'");
        
        $rec->last = $lastThRec->last;
        
        doc_Folders::save($rec, 'last,allThreadsCnt,openThreadsCnt,state');
        
        // Генерираме нотификация
        $msg = "Новости в папка \"{$rec->title}\"";
        
        $url = array('doc_Threads', 'list', 'folderId' => $id);
        
        $userId = $rec->inCharge;
        
        $priority = 'normal';
        
        bgerp_Notifications::add($msg, $url, $userId, $priority);
        
        if($rec->shared) {
            foreach(type_Keylist::toArray($rec->shared) as $userId) {
                bgerp_Notifications::add($msg, $url, $userId, $priority);
            }
        }
    }
    
    
    
    /**
     * Обновява информацията за корицата на посочената папка
     */
    static function updateByCover($id)
    {
        $rec = doc_Folders::fetch($id);
        
        if(!$rec) return;
        
        $coverMvc = cls::get($rec->coverClass);
        
        if(!$rec->coverId) {
            expect($coverRec = $coverMvc->fetch("#folderId = {$id}"));
            $rec->coverId = $coverRec->id;
            $mustSave = TRUE;
        } else {
            expect($coverRec = $coverMvc->fetch($rec->coverId));
        }
        
        $coverRec->title = $coverMvc->getFolderTitle($coverRec->id);
        $fields = 'title,state,inCharge,access,shared';
        
        foreach(arr::make($fields) as $field) {
            if($rec->{$field} != $coverRec->{$field}) {
                $rec->{$field} = $coverRec->{$field};
                $mustSave = TRUE;
            }
        }
        
        if($mustSave) {
            static::save($rec);
        }
    }
    
    
    
    /**
     * Създава празна папка за посочения тип корица
     * и връща нейното $rec->id
     */
    static function createNew($coverMvc)
    {
        $rec = new stdClass();
        $rec->coverClass = core_Classes::fetchIdByName($coverMvc);
        
        // Задаваме няколко параметъра по подразбиране за 
        $rec->status = '';
        $rec->allThreadsCnt = 0;
        $rec->openThreadsCnt = 0;
        $rec->last = dt::verbal2mysql();
        
        static::save($rec);
        
        return $rec->id;
    }
    
    
    
    /**
     * Изпълнява се след сетъп на doc_Folders
     * @todo Да се махне
     */
    function on_AfterSetupMVC($mvc, $res)
    {
        $query = $mvc->getQuery();
        
        while($rec = $query->fetch()) {
            if(($rec->state != 'active') && ($rec->state != 'rejected') && ($rec->state != 'opened') && ($rec->state != 'closed')) {
                $rec->state = 'active';
                $mvc->save($rec, 'state');
                $res .= "<li style='color:red'> $rec->title - active";
            }
        }
    }
}