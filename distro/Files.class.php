<?php 


/**
 * Детайл на разпределена група файлове
 *
 * @category  bgerp
 * @package   distro
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class distro_Files extends core_Detail
{
    
    
    /**
     * Заглавие на модела
     */
    public $title = 'Разпределена група файлове';
    
    
    /**
     * 
     */
    public $singleTitle = 'Файл';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'admin';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'no_one';
    
    
    /**
     * 
     */
    public $canEditsysdata = 'no_one';
    
    
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
     * Кой има право да го изтрие?
     */
    public $canDelete = 'no_one';
    
    
    /**
     * Кой има право да го оттегли?
     */
    public $canReject = 'no_one';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'distro_Wrapper, plg_Modified, plg_Created, plg_RowTools2';
    
    
    /**
     * Име на поле от модела, външен ключ към мастър записа
     */
    public $masterKey = 'groupId';
    
    
    /**
     * 
     */
    public $depends = 'fileman=0.1';
    
    
    /**
     * Хипервръзка на даденото поле и поставяне на икона за индивидуален изглед пред него
     * 
     * @see plg_RowTools2
     */
    public $rowToolsSingleField = 'id';
    
    
    /**
     * При колко линка в тулбара на реда да не се показва дропдауна
     *
     * @param int
     * @see plg_RowTools2
     */
    public $rowToolsMinLinksToShow = 2;
    
    
    /**
     * Кои полета ще извличаме, преди изтриване на заявката
     */
    public $fetchFieldsBeforeDelete = 'id, sourceFh, repos, groupId, name';
    
    
    /**
     * Флаг, който указва дали да се изтрие и файла след изтриване на хранилището
     */
    public $onlyDelRepo = FALSE;
    
    
    /**
     * 
     */
    public $currentTab = 'Групи';
    
    
    /**
     * Какво действие ще се прави с файловете
     */
    public $actionWithFile = array();
    
    
	/**
     * Описание на модела (таблицата)
     */
    function description()
    {
        $this->FLD('groupId', 'key(mvc=distro_Group, select=title)', 'caption=Група, mandatory');
        $this->FLD('sourceFh', 'fileman_FileType(bucket=' . distro_Group::$bucket . ')', 'caption=Файл, mandatory');
        $this->FLD('name', 'varchar', 'caption=Име, width=100%, input=none');
        $this->FLD('repoId', 'key(mvc=distro_Repositories, select=name)', 'caption=Хранилище, width=100%, input=none');
        $this->FNC('repos', 'keylist(mvc=distro_Repositories, select=name)', 'caption=Хранилища, width=100%, maxColumns=3, mandatory, input=input');
        $this->FLD('info', 'varchar', 'caption=Информация, width=100%');
        $this->FLD('md5', 'varchar(32)', 'caption=Хеш на файла, width=100%,input=none');
        
        $this->setDbUnique('groupId, name, repoId');
    }
    
    
    /**
     * Функция, която връща дали има запис към мастъра
     * 
     * @param int $masterId - id на мастъра
     * 
     * @return boolean
     */
    public static function haveRec($me, $masterId)
    {
        // Ако има мастер
        if ($masterKey = $me->masterKey) {
            
            // Ако има запис към мастера
            if (static::fetch(array("#{$masterKey} = '[#1#]'", $masterId))) {
                
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    
    /**
     * Връща пълния път до файла в хранилището
     * 
     * @param stdObject|integer $id
     * @param NULL|integer $repoId
     * @param NULL|integer $groupId
     * @param NULL|string $name
     */
    public function getRealPathOfFile($id, $repoId = NULL, $groupId = NULL, $name = NULL)
    {
        $rec = self::fetchRec((int) $id);
        
        $repoId = isset($repoId) ? $repoId : $rec->repoId;
        $groupId = isset($groupId) ? $groupId : $rec->{$this->masterKey};
        
        $rRec = distro_Repositories::fetch((int) $repoId);
        
        $subDirName = $this->Master->getSubDirName($groupId);
        
        $name = $name ? $name : $rec->name;
        
        $path = rtrim($rRec->path, '/') . '/' . $subDirName . '/' . $name;
        
        return $path;
    }
    
    
    /**
     * Връща уникално име за файла, който ще се добавя в хранилището
     * 
     * @param integer $id
     * @param NULL|integer $repoId
     * 
     * @return FALSE|string
     */
    public function getUniqFileName($id, $repoId = NULL)
    {
        $rec = self::fetchRec($id);
        
        $repoId = isset($repoId) ? $repoId : $rec->repoId;
        
        $sshObj = distro_Repositories::connectToRepo($repoId);
        
        if ($sshObj === FALSE) return FALSE; // TODO да репортва
        
        $destFilePath = $this->getRealPathOfFile($id, $repoId);
        
        $maxCnt = 32;
        
        while (TRUE) {
            $destFilePathE = escapeshellarg($destFilePath);
            
            $sshObj->exec("if [ ! -f {$destFilePathE} ]; then echo 'OK'; fi", $res);
            
            if (trim($res) == "OK") break;
            
            $destFilePath = $this->getNextFileName($destFilePath);
            
            expect($maxCnt--);
        }
        
        return $destFilePath;
    }
    
    
    /**
     * Връща масив със записи, където се среща съответния файл
     * 
     * @param integer $groupId
     * @param string|NULL $md5
     * @param string|NULL $name
     * @param boolean $group
     * 
     * @return array
     */
    public static function getRepoWithFile($groupId, $md5 = NULL, $name = NULL, $group = FALSE)
    {
        $query = self::getQuery();
        $query->where(array("#groupId = '[#1#]'", $groupId));
        
        if (isset($md5)) {
            $query->where(array("#md5 = '[#1#]'", $md5));
        }
        if (isset($name)) {
            $query->where(array("#name = '[#1#]'", $name));
        }
        
        if ($group) {
            $query->groupBy('repoId');
        }
        
        return $query->fetchAll();
    }
    
    
    /**
     * Връща следващото име за използване на файла
     * 
     * @param string $fName
     * 
     * @return string
     */
    protected function getNextFileName($fName)
    {
        // Вземаме името на файла и разширението
        $nameArr = fileman_Files::getNameAndExt($fName);
        
        // Намираме името на файла до последния '_'
        if(($underscorePos = mb_strrpos($nameArr['name'], '_')) !== FALSE) {
            $name = mb_substr($nameArr['name'], 0, $underscorePos);
            $nameId = mb_substr($nameArr['name'], $underscorePos+1);
        
            if (is_numeric($nameId)) {
                $nameId++;
            } else {
                $nameId .= '_1';
            }
        
            $nameArr['name'] = $name . '_' . $nameId;
        } else {
            $nameArr['name'] .= '_1';
        }
        
        $fName = $nameArr['name'] . '.' . $nameArr['ext'];
        
        return $fName;
    }
    
    
    /**
     * Синхронизира съдържанието на хранилищата с модела
     * 
     * @return array
     */
    protected function syncFiles()
    {
        $resArr = array();
        
        $reposArr = distro_Repositories::getReposArr();
        
        if (empty($reposArr)) return $resArr;
        
        $repoLineHash = distro_Repositories::getLinesHash();
        $repoFirstHash = array();
        
        foreach ($reposArr as $repoId) {
            $linesArr = distro_Repositories::parseLines($repoId);
            
            if (!isset($repoFirstHash[$repoId])) {
                $repoFirstHash[$repoId] = $linesArr[0]['lineHash'];
            }
            
            $repoActArr = array();
            
            foreach ($linesArr as $lArr) {
                if (isset($repoLineHash[$repoId])) {
                    
                    // Вече сме достигнали до тази обработка
                    if ($repoLineHash[$repoId] == $lArr['lineHash']) break;
                }
                
                // Опитваме се да определим id на групата от пътя на директорията
                $groupId = $this->Master->getGroupIdFromFolder($lArr['rPath']);
                
                if (empty($groupId)) continue;
                
                // Създадените/променени директории не ги пипаме
                if ($lArr['isDir']) continue;
                
                // Ако не са в поддиректрия, не ги обработваме
                if (!trim($lArr['rPath'])) continue ;
                
                if ($lArr['act'] == 'create' || $lArr['act'] == 'edit') {
                    
                    // Ако вече е бил изтрит, няма смисъл да се добавя
                    if ($repoActArr[$groupId]['delete'][$lArr['name']]) continue; // TODO - може и да не се прескача, ако ще се записва в лога
                }
                
                $repoActArr[$groupId][$lArr['act']][$lArr['name']] = $lArr['date'];
            }
            
            foreach ($repoActArr as $groupId => $actArr) {
                    
                foreach ((array)$actArr['create'] as $name => $date) {
                    
                    $subDir = $this->Master->getSubDirName($groupId);
                    
                    $nRec = new stdClass();
                    
                    $nRec->md5 = $this->getMd5($repoId, $subDir, $name);
                    
                    $fRec = $this->getRecForFile($groupId, $name, $repoId);
                    
                    if ($fRec) {
                        
                        // Ако хешовете им съвпадат
                        if ($fRec->md5 == $nRec->md5) {
                            $this->logNotice('Съществуващ файл', $fRec->id);
                            continue;
                        }
                        
                        // TODO - ако двата файла не съвпадат, трядбва да се преименува
                        
                        continue;
                    }
                    
                    $nRec->repoId = $repoId;
                    $nRec->groupId = $groupId;
                    $nRec->name = $name;
                    
                    $nRec->createdBy = -1;
                    
                    // Проверяваме дали подадената дата е коректна за използване
                    if ($this->checkDate($date)) {
                        $nRec->createdOn = $date;
                    }
                    
                    $this->save($nRec);
                    
                    $resArr['create'][$nRec->id] = $nRec->id;
                }
                
                foreach ((array)$actArr['edit'] as $name => $date) {
                    $fRec = $this->getRecForFile($groupId, $name, $repoId);
                    
                    if ($fRec === FALSE) {
                        $this->logWarning('Няма запис за файл, който да се редактира.');
                        
                        continue;
                    }
                    
                    $fRec->modifiedBy = -1;
                    
                    if ($this->checkDate($date)) {
                        $fRec->modifiedOn = $date;
                    }
                    
                    $subDir = $this->Master->getSubDirName($groupId);
                    $newMd5 = $this->getMd5($repoId, $subDir, $name);
                    
                    if ($newMd5 != $fRec->md5) {
                        $fRec->md5 = $newMd5;
                        $fRec->sourceFh = NULL;
                    }
                    
                    // Прекъсваемо е за да не се промянят от плъгина
                    $this->save_($fRec, 'modifiedOn, modifiedBy, md5, sourceFh');
                    $this->Master->touchRec($groupId);
                    
                    $resArr['edit'][$fRec->id] = $fRec->id;
                }
                
                foreach ((array)$actArr['delete'] as $name => $date) {
                    $fRec = $this->getRecForFile($groupId, $name, $repoId);
                    
                    if ($fRec === FALSE) {
                        $this->logNotice('Записът за файла е бил премахнат при изтриване');
                        
                        continue;
                    }
                    
                    $resArr['delete'][$fRec->id] = $fRec->id;
                    
                    $this->delete($fRec->id); // TODO - може да записва в лога или оттегля записа
                }
            }
            
            // Задаваме новата стойност на линията
            distro_Repositories::setLineHash($repoId, $repoFirstHash[$repoId]);
        }
        
        return $resArr;
    }
    
    
    /**
     * Връща md5 стойността на файла
     * 
     * @param integer $repoId
     * @param string $dir
     * @param string $name
     * 
     * @return FALSE|string
     */
    protected static function getMd5($repoId, $dir, $name)
    {
        $md5 = distro_Repositories::getFileMd5($repoId, $dir, $name);
        
        return $md5;
    }
    
    
    /**
     * Връща запис за файла от съответната група
     * 
     * @param integer $groupId
     * @param string $name
     * @param integer $repoId
     * @param boolean $cache
     * 
     * @return stdObject|FALSE
     */
    protected function getRecForFile($groupId, $name, $repoId, $cache = FALSE)
    {
        $rec = $this->fetch(array("#groupId = '[#1#]' AND #name = '[#2#]' AND #repoId = '[#3#]'", $groupId, $name, $repoId), NULL, $cache);
        
        return $rec;
    }
    
    
    /**
     * 
     * @param datetime $date
     * 
     * @return boolean
     */
    protected function checkDate($date)
    {
        $sBetween = dt::secsBetween(dt::now(), $date);
        if ($sBetween >= 0) {
            if ($sBetween > 300) {
                $this->logWarning('Разминаване във времето - файлът е създаден много отдавна: ' . dt::mysql2verbal($date));
                
                return FALSE;
            }
        } else {
            $this->logWarning('Разминаване във времето - файлът е създаден в бъдеще: ' . dt::mysql2verbal($date));
            
            return FALSE;
        }
        
        return TRUE;
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
        // Ако ще добавяме/редактираме записа
        if ($action == 'add') {
            
            // Ако има master
            if (($masterKey = $mvc->masterKey) && ($rec->$masterKey)) {
                
                // Ако няма права за добавяне на детайл
                if (!$mvc->Master->canAddDetail($rec->$masterKey)) {
                    
                    // Да не може да добавя
                    $requiredRoles = 'no_one';
                }
            }
        }
    }
    
    
	/**
     * След подготвяне на формата
     *
     * @param core_Manager $mvc
     * @param stdClass $data
     */
    public static function on_AfterPrepareEditForm($mvc, &$data)
    {
        // Записа
        $rec = $data->form->rec;
        
        // Ако редактираме записа
        if ($rec->id) {
            // Избора на файл да е задължителен
            $data->form->setField('sourceFh', 'input=none');
            $data->form->setField('repos', 'input=none');
        } else {
            $reposArr = array();
            
            // Ако има мастер
            if (($masterKey = $mvc->masterKey) && ($rec->$masterKey)) {
            
                // Вземаме масива с хранилищата, които са зададени в мастера
                $reposArr = $mvc->Master->getReposArr($rec->$masterKey);
            }
            
            expect(!empty($reposArr));
            
            $data->form->setSuggestions('repos', $reposArr);
        }
    }
    
    
    /**
     * Извиква се след въвеждането на данните от Request във формата ($form->rec)
     * 
     * @param distro_Files $mvc
     * @param core_Form $form
     */
    public static function on_AfterInputEditForm($mvc, &$form)
    {
        if ($form->isSubmitted()) {
            $rec = $form->rec;
            if (isset($rec->sourceFh)) {
                $rec->name = fileman_Files::fetchByFh($form->rec->sourceFh, 'name');
                $rec->md5 = fileman_Files::fetchByFh($form->rec->sourceFh, 'md5');
                
                if (!$rec->id) {
                    $rec->__addToRepo = TRUE;
                }
            }
        }
    }
    
    
    /**
     * 
     * 
     * @param distro_Files $mvc
     * @param stdObject $res
     * @param stdObject $rec
     */
    public static function on_BeforeSave($mvc, $res, $rec)
    {
        // Опитваме се от keylist поле да направим key
        // За целта правим записи за всяко repoId, а този запис го спираме
        if (isset($rec->repos)) {
            $reposArr = type_Keylist::toArray($rec->repos);
            
            foreach ($reposArr as $repoId) {
                $rec->repos = NULL;
                $rec->repoId = $repoId;
                
                // Опитваме се да генерираме уникално име на файла
                $origName = $rec->name;
                $maxCnt = 64;
                while (TRUE) {
                    if ($mvc->isUnique($rec)) break;
                    
                    $rec->name = $mvc->getNextFileName($rec->name);
                    
                    expect($maxCnt--);
                }
                
                $mvc->save($rec);
                
                $rec->name = $origName;
                
                unset($rec->id);
            }
            
            return FALSE;
        }
    }
    

    /**
     * Извиква се след успешен запис в модела
     *
     * @param core_Mvc $mvc
     * @param int $id първичния ключ на направения запис
     * @param stdClass $rec всички полета, които току-що са били записани
     */
    public static function on_AfterSave(core_Mvc $mvc, &$id, $rec)
    {
        // Сваляме файла в хранилището
        if (isset($rec->__addToRepo)) {
        
            distro_Actions::addToRepo($rec);
        }
    }
    
    
	/**
     * 
     * 
     * @param distro_Files $mvc
     * @param stdObject $res
     * @param stdObject $data
     */
    function on_AfterPrepareListRecs($mvc, &$res, $data)
    {
        // Масив с хранилищата и файловете в тях
        $reposAndFilesArr = array();
        
        $sameNameFileArr = array();
        $addMd5Arr = array();
        
        foreach ((array)$data->recs as $id => $rec) {
            
            // Разпределяме ги в масива
            $reposAndFilesArr[$rec->repoId][$id] = $id;
            
            // Ако има еднакви файлове с различен хеш, показваме хеша
            if (isset($sameNameFileArr[$rec->name])) {
                foreach ($sameNameFileArr[$rec->name] as $rId) {
                    if ($data->recs[$rId]->md5 == $rec->md5) continue;
                    $addMd5Arr[$rec->name] = $rec->name;
                }
            }
            
            $sameNameFileArr[$rec->name][] = $rec->id;
        }
        
        foreach ($addMd5Arr as $fName) {
            foreach ((array)$sameNameFileArr[$fName] as $rId) {
                $hashStr = tr('Файл|*: ') . substr($data->recs[$rId]->md5, 0, 6);
                
                $data->recs[$rId]->info = (trim($data->recs[$rId]->info)) ? $hashStr . ". " . $data->recs[$rId]->info : $hashStr;
            }
        }
        
        // Добавяме масива
        $data->reposAndFilesArr = $reposAndFilesArr;
    }
    
    
	/**
     * 
     * 
     * @param distro_Files $mvc
     * @param stdObject $res
     * @param stdObject $data
     */
    static function on_AfterPrepareListRows($mvc, &$res, $data)
    {
        // Обхождаме масива с хранилищата и файловете в тях
        foreach ((array)$data->reposAndFilesArr as $repoId => $idsArr) {
            
            // Масив с вербалните данни
            $data->rowReposAndFilesArr[$repoId] = array();
            
            // Заглавие на хранилището
            $repoTitle = distro_Repositories::getVerbal($repoId, 'name');
            
            // Обхождаме масива с id'та
            foreach ((array)$idsArr as $id) {
                
                // Името на файла
                // Ако има манипулатор, да е линка към сингъла
                if ($data->rows[$id]->sourceFh) {
                    $file = $data->rows[$id]->sourceFh;
                } else {
                    $file = $data->rows[$id]->name;
                    
                    $subDirName = $mvc->Master->getSubDirName($data->recs[$id]->groupId);
                    
                    $file = distro_Repositories::getUrlForFile($repoId, $subDirName, $data->recs[$id]->name);
                }
                
                // Ако няма създаден обект, създаваме такъв
                if (!$data->rowReposAndFilesArr[$repoId][$id]) {
                    $data->rowReposAndFilesArr[$repoId][$id] = new stdClass();
                }
                
                // Добавяме файла в масива
                $data->rowReposAndFilesArr[$repoId][$id]->file = $file;
                
                // Информация за файла
                $data->rowReposAndFilesArr[$repoId][$id]->info = $data->rows[$id]->info;
                
                // Данни за модифициране
                $data->rowReposAndFilesArr[$repoId][$id]->modified = $data->rows[$id]->modifiedOn . tr(' |от|* ') . $data->rows[$id]->modifiedBy;
                
                core_RowToolbar::createIfNotExists($data->rows[$id]->_rowTools);
                
                distro_Actions::addActionToFile($data->rows[$id]->_rowTools, $data->recs[$id]);
                
                // Бутони за действия
                $data->rowReposAndFilesArr[$repoId][$id]->tools = $data->rows[$id]->_rowTools->renderHtml($mvc->rowToolsMinLinksToShow);
            }
        }
    }
    
    
    /**
     * Подготовка на филтър формата
     *
     * @param core_Mvc $mvc
     * @param StdClass $data
     */
    protected static function on_AfterPrepareListFilter($mvc, &$data)
    {
        $data->query->orderBy('modifiedOn', 'DESC');
    }
    
    
	/**
     * След преобразуване на записа в четим за хора вид.
     *
     * @param core_Mvc $mvc
     * @param stdObject $row Това ще се покаже
     * @param stdObject $rec Това е записа в машинно представяне
     */
    public static function on_AfterRecToVerbal($mvc, &$row, $rec)
    {
        // Ако има манипулатор на файл и име на файл
        if ($rec->sourceFh && $rec->name) {
            
            // Вземаме линк с текущото име
            $row->sourceFh = fileman::getLinkToSingle($rec->sourceFh, FALSE, array(), $rec->name);
        }
    }
    
    
    /**
     * 
     * 
     * @param distro_Files $mvc
     * @param core_ET $tpl
     * @param stdObject $data
     */
    function on_BeforeRenderListTable($mvc, &$tpl, $data)
    {
        // Вземаме таблицата
        $tpl = $mvc->renderReposAndFiles($data);
        
        // Да не се изпълнява кода
        return FALSE;
    }
    
    
    /**
     * Рендира таблицата за хранилища и файлове
     * 
     * @param object $data - Данни
     */
    protected static function renderReposAndFiles($data)
    {
        // Шаблон за таблиците
        $tplRes = getTplFromFile('distro/tpl/FilesAllReposTables.shtml');
        
        // Ако няма записи
        if (!$data->rowReposAndFilesArr) {
            
            // Сетваме текста
            $tplRes->append(tr('Няма записи'), 'REPORES');
            
            // Връщаме шаблона
            return $tplRes;
        }
        
        // Обхождаме масива
        foreach ((array)$data->rowReposAndFilesArr as $repoId => $reposArr) {
            
            // Шаблон за таблица
            $tplTable = getTplFromFile('distro/tpl/FilesRepoTable.shtml');
            
            // Обхождаме масива с хранилищата
            foreach ($reposArr as $repo) {
                
                // Шаблон за ред в таблицата
                $tplRow = getTplFromFile('distro/tpl/FilesRepoTableRow.shtml');
                
                // Заместваме данните
                $tplRow->replace($repo->modified, 'modified');
                $tplRow->replace($repo->file, 'file');
                $tplRow->replace($repo->tools, 'tools');
                
                // Ако има информация
                if ($info = trim($repo->info)) {
                    
                    // Заместваме информацията
                    $tplRow->replace($info, 'fileInfo');
                }
                
                // Премахваме незаместените блокове
                $tplRow->removeBlocks();
                
                // Добавяме към шаблона за таблиците
                $tplTable->append($tplRow, 'repoRow');
            }
            
            // Линк към хранилището
            $repoTitleLink = distro_Repositories::getLinkToSingle($repoId, 'name');
            
            // Добавяме в шаблона
            $tplTable->append($repoTitleLink,'repoTitle');
            
            // Ако няма файлове
            if (!$reposArr) {
                
                // Шаблон за ред в таблицата
                $tplRow = getTplFromFile('distro/tpl/FilesRepoTableRow.shtml');
                
                // Заместваме информацията
                $tplRow->replace(tr('Няма файлове'), 'fileInfo');
                
                // Добавяме към шаблона за таблиците
                $tplTable->append($tplRow, 'repoRow');
            }
            
            // Добавяме в резултатния шаблон
            $tplRes->append($tplTable, 'REPORES');
        }
        
        // Премахваме незаместените шаблони
        $tplRes->removePlaces();
        
        // Премахваме празните блокове
        $tplRes->removeBlocks();
        
        return  $tplRes;
    }
    
    
    /**
     * Функция, която се вика от крон
     * Синрхоронизира файловете в хранилищитата с модела
     */
    public function cron_SyncFiles()
    {
        
        // Извикваме функцията и връщаме резултата му
        return core_Type::mixedToString($this->syncFiles());
    }
    
    
    /**
     * Изпълнява се след създаването на модела
     * 
     * @param distro_Files $mvc
     * @param string $res
     */
    static function on_AfterSetupMVC($mvc, &$res)
    {
        $rec = new stdClass();
        $rec->systemId = 'SyncFiles';
        $rec->description = 'Синхронизиране на файловете в хранилищата със записите в модела';
        $rec->controller = $mvc->className;
        $rec->action = 'SyncFiles';
        $rec->period = 1;
        $rec->offset = 0;
        $rec->delay = 0;
        $rec->timeLimit = 50;
        $res .= core_Cron::addOnce($rec);
    }
}
