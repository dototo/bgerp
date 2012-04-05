<?php



/**
 * Клас 'doc_Search' - Търсене в документната система
 *
 *
 * @category  bgerp
 * @package   doc
 * @author    Stefan Stefanov <stefan.bg@gmail.com>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class doc_Search extends core_Manager
{
    
    
    /**
     * Заглавие
     */
    var $title = "Търсене на документи";
    
    /**
     * @todo Чака за документация...
     */
    var $loadList = 'doc_Wrapper, plg_Search';
    
    
    /**
     * Роли с права за добавяне.
     *
     * 'no_one', за да не се показва бутона "Нов запис"
     *
     * @var string
     */
    var $canAdd = 'no_one';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    var $listFields = "docLink=Документ, folderId=Папка, createdOn, createdBy";
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     *
     * Задаваме NULL за да избегнем обновяването на ключовите думи на контейнера след всеки
     * запис. Ключовите думи в контейнер се обновяват по различен механизъм - при промяна на
     * съотв. документ (@see doc_Containers::update_())
     */
    var $searchFields = NULL;
    
    /**
     * @todo Чака за документация...
     */
    function description()
    {
        $DC = cls::get('doc_Containers');
        
        $this->fields = $DC->fields;
        $this->dbTableName = $DC->dbTableName;
        $this->dbIndexes   = $DC->dbIndexes;
    }
    
    
    /**
     * Филтрира по id на нишка (threadId)
     */
    static function on_BeforePrepareListRecs($mvc, $res, $data)
    {
        $filterRec = $data->listFilter->rec;
        
        $isFiltered =
        !empty($filterRec->search) ||
        !empty($filterRec->scopeFolderId) ||
        !empty($filterRec->docClass) ||
        !empty($filterRec->fromDate) ||
        !empty($filterRec->toDate);
        
        // Има зададен условия за търсене - генерираме SQL заявка.
        if($data->listFilter->isSubmitted()) {
            
            // Търсене на определен тип документи
            if (!empty($filterRec->docClass)) {
                $data->query->where(array('#docClass = [#1#]', $filterRec->docClass));
            }
            
            // Търсене по дата на създаване на документи (от-до)
            if (!empty($filterRec->fromDate)) {
                $data->query->where(array("#createdOn >= '[#1#]'", $filterRec->fromDate));
            }
            
            if (!empty($filterRec->toDate)) {
                $data->query->where(array("#createdOn <= '[#1#] 23:59:59'", $filterRec->toDate));
            }
            
            // Ограничаване на търсенето до избрана папка
            if (!empty($filterRec->scopeFolderId)) {
                $data->query->where(array("#folderId = '[#1#]'", $filterRec->scopeFolderId));
            }
            
            
            // Ограничаване на заявката само до достъпните нишки
            doc_Threads::restrictAccess($data->query);
            
            // Експеримент за оптимизиране на бързодействието
            $data->query->setStraight();
            $data->query->orderBy('#createdOn=DESC');
            
            /**
             * Останалата част от заявката - търсенето по ключови думи - ще я допълни plg_Search
             */
        } else {
            // Няма условия за търсене - показваме само формата за търсене, без данни
            $data->query->where("0 = 1");
        }
    }
    
    
    /**
     * Изпълнява се след подготовката на филтъра за листовия изглед
     * Обикновено тук се въвеждат филтриращите променливи от Request
     */
    static function on_AfterPrepareListFilter($mvc, &$res, $data)
    {
        $data->listFilter->title = 'Tърсене на документи';
        $data->listFilter->FNC('fromDate', 'date', 'input,silent,caption=От,width=140px, placeholder=Дата');
        $data->listFilter->FNC('toDate', 'date', 'input,silent,caption=До,width=140px, placeholder=Дата');
        $data->listFilter->FNC('scopeFolderId', 'enum(0=Всички папки)', 'input,silent,caption=Обхват');
        
        // Ако има текуща папка, добавяме опция за търсене само в нея
        if (($lastfolderId = Request::get('scopeFolderId', 'int')) && ($lastFolderTitle = doc_Folders::fetchField($lastfolderId, title))) {
    		$data->listFilter->getField('scopeFolderId')->type->options[$lastfolderId] = $lastFolderTitle;
    	}
        
    	// Ако има текуща папка, добавяме опция за търсене само в нея
        if (($lastfolderId = Mode::get('lastfolderId')) && ($lastFolderTitle = doc_Folders::fetchField($lastfolderId, title))) {
    		$data->listFilter->getField('scopeFolderId')->type->options[$lastfolderId] = $lastFolderTitle;
    	}
    	
        $data->listFilter->getField('search')->caption = 'Ключови думи';
        $data->listFilter->getField('search')->width = '100%';
        $data->listFilter->getField('docClass')->caption = 'Вид документ';
        $data->listFilter->getField('docClass')->placeholder = 'Всички';
        $data->listFilter->showFields = 'search, scopeFolderId, docClass, fromDate, toDate';
        $data->listFilter->toolbar->addSbBtn('Търсене', 'default', 'id=filter,class=btn-filter');
    }
    
    /**
     * @todo Чака за документация...
     */
    function on_AfterPrepareListRows($mvc, $data)
    {
        if (count($data->recs) == 0) {
            return;
        }
        
        foreach ($data->recs as $i=>$rec) {
            $row = $data->rows[$i];
            $folderRec = doc_Folders::fetch($rec->folderId);
            $folderRow = doc_Folders::recToVerbal($folderRec);
            //$row->folderType = $folderRow->type;
            $row->folderId   = $folderRow->title;
            
            //$threadRec = doc_Threads::fetch($rec->threadId);
            //$threadRow = doc_Threads::recToVerbal($threadRec);
            //$row->threadHnd = $threadRow->hnd;
            //$row->threadId  = $threadRow->title;
            

            $doc = doc_Containers::getDocument($rec->id);
            $row->docLink = $doc->getLink(64);
        }
    }
    
    /**
     * @todo Чака за документация...
     */
    function on_BeforeRenderListTable($mvc, &$res, $data)
    {
        if (!$data->listFilter->isSubmitted()) {
            
            return FALSE;
        }
    }
    
    /**
     * @todo Чака за документация...
     */
    static function on_AfterPrepareListTitle($mvc, $data)
    {
        $data->title = null;
    }
}