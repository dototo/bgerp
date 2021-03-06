<?php


/**
 * Клас 'doc_ThreadRefreshPlg' - Ajax обновяване на нишка
 * 
 * 
 * @category  bgerp
 * @package   doc
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class doc_ThreadRefreshPlg extends core_Plugin
{
    
    
    /**
     * Преди рендиране на врапера
     * 
     * @param core_Mvc $mvc
     * @param core_ET $res
     * @param core_ET $tpl
     */
    function on_BeforeRenderWrapping($mvc, &$res, &$tpl, $data=NULL)
    {
        // Ако не се листва, да не се изпълнява
        if($data->action != 'list') return;
        
        // Ако не се вика по AJAX
        if (!Request::get('ajax_mode')) {
            
            // URL-то, което ще се вика по AJAX
            $refreshUrl = getCurrentUrl();
            
            // Локално URL
            $refreshUrlLocal = toUrl($refreshUrl, 'local');
            
            // URL, което ще се вика по AJAX
            $url = array($mvc, 'ajaxThreadRefresh', 'refreshUrl' => $refreshUrlLocal, 'threadId' => Request::get('threadId', 'int'));
            
            // Ако не е зададено, рефрешът се извършва на всеки 60 секунди
            $time = $mvc->refreshRowsTime ? $mvc->refreshRowsTime : 60000;
            
            // Името с което ще се добави в масива
            $name = $mvc->className . '_ThreadRefresh';
            
            // Абонираме URL-то
            core_Ajax::subscribe($tpl, $url, $name, $time);
            
            // Обграждаме с дивове
            $tpl->prepend("<div id='rowsContainer'>");
            $tpl->append("</div>");
            
            $res = $tpl;
        } else {
            
            // Ако се вика по AJAX
            
            $res = $tpl;
            
            return FALSE;
        }
    }
    
    
    /**
     * Извиква се преди изпълняването на екшън
     * 
     * @param core_Mvc $mvc
     * @param array $resStatus
     * @param string $action
     */
    public static function on_BeforeAction($mvc, &$resStatus, $action)
    {
        // Ако екшъна не е за обновяване на редовете, да не се изпълнява
        if ($action != 'ajaxthreadrefresh') return ;
        
        $resStatus = array();
        
        $ajaxMode = Request::get('ajax_mode');
        
        // Ако заявката не е по ajax
        if (!$ajaxMode) return FALSE;
        
        $threadId = Request::get('threadId', 'int');
        
        doc_Threads::requireRightFor('single', $threadId);
        
        $hitTime = Request::get('hitTime');
        
        $threadLastSendName = 'LastSendThread_' . $threadId . '_' . $hitTime;
        
        $lastSend = Mode::get($threadLastSendName);
        
        if(!$lastSend) {
            
            if ($hitTime) {
                $lastSend = dt::timestamp2Mysql($hitTime);
            }
            
            if (!$lastSend) {
                $lastSend = dt::verbal2mysql();
            }
            
            Mode::setPermanent($threadLastSendName, $lastSend);
        }
        
        // Определяме времето на последна модификация на контейнер в нишката
        $cQuery = doc_Containers::getQuery();
        $cQuery->where("#threadId = {$threadId}");
        $cQuery->orderBy('#modifiedOn', 'DESC');
        $cQuery->limit(1);
        $lastModifiedRec = $cQuery->fetch();
        $threadLastRec = doc_Threads::fetch($lastModifiedRec->threadId);
        $lastModified = max($threadLastRec->modifiedOn, $lastModifiedRec->modifiedOn);
        
        if($lastSend >= $lastModified && dt::addSecs(3*60, $lastSend) > dt::now()) {

            return FALSE;
        }
        
        // URL-то за рефрешване
        $refreshUrlStr = Request::get('refreshUrl');
        
        // Парсираме URL-то
        $refreshUrl = core_App::parseLocalUrl($refreshUrlStr);
        
        $refreshUrl['ajax_mode'] = $ajaxMode;
        
        // Вземаме шаблона
        $tpl = Request::forward($refreshUrl);
        
        // Ако липсва шаблона, да не се изпълнява
        if (!$tpl) return FALSE;
        
        // Вземаме съдържанието на шаблона
        $content = static::getContent($tpl);
        
        // Добавяме резултата
        $resObj = new stdClass();
        $resObj->func = 'html';
        $resObj->arg = array('id'=>'rowsContainer', 'html' => $content, 'replace' => TRUE);
        
        $resStatus[] = $resObj;
        
        // Да предизвикаме релоад след връщане назад
        $resObjReload = new stdClass();
        $resObjReload->func = 'forceReloadAfterBack';
        $resStatus[] = $resObjReload;
        
        // JS функции, които да се пуснат след AJAX
        jquery_Jquery::runAfterAjax($tpl, 'smartCenter');
        jquery_Jquery::runAfterAjax($tpl, 'makeTooltipFromTitle');
        jquery_Jquery::runAfterAjax($tpl, 'sumOfChildrenWidth');
        jquery_Jquery::runAfterAjax($tpl, 'editCopiedTextBeforePaste');
        jquery_Jquery::runAfterAjax($tpl, 'removeNarrowScroll');
        
        // Стойности на плейсхолдера
        $runAfterAjaxArr = $tpl->getArray('JQUERY_RUN_AFTER_AJAX');
        
        // Добавя всички функции в масива, които ще се виката
        if (!empty($runAfterAjaxArr)) {
            
            // Да няма повтарящи се функции
            $runAfterAjaxArr = array_unique($runAfterAjaxArr);
            
            foreach ((array)$runAfterAjaxArr as $runAfterAjax) {
                $resObj = new stdClass();
                $resObj->func = $runAfterAjax;
                
                $resStatus[] = $resObj;
            }
        }
        
        // Масив с id-тата на всички променени документи
        $docsArr = Mode::get('REFRESH_DOCS_ARR');
        
        // Ако има документи за обновяване
        if ($docsArr) {
            
            $modifiedDocsArr = array();
            $cu = core_Users::getCurrent();

            foreach ((array)$docsArr as $cid => $docId) {
                $cRec = doc_Containers::fetch($cid);
                if($cRec) {
                    $currUrl = getCurrentUrl();
                    $currUrl['#'] = $docId;
                    $link = ht::createLink('#' . $docId, $currUrl, NULL, array('onclick' => "getEO().scrollTo('$docId'); return false;"));
                    
                    if($cu == $cRec->modifiedBy) continue;

                    $user = crm_Profiles::createLink($cRec->modifiedBy);
                    $action = ($cRec->modifiedOn == $cRec->createdOn) ? tr("добави") : tr("промени");
                    $msg = "{$user} {$action} {$link}";
                    
                    $statusData = array();
                    $statusData['text'] = $msg;
                    $statusData['type'] = 'notice';
                    $statusData['timeOut'] = 700;
                    $statusData['isSticky'] = 0;
                    $statusData['stayTime'] = 15000;
                    
                    $statusObj = new stdClass();
                    $statusObj->func = 'showToast';
                    $statusObj->arg = $statusData;

                    $resStatus[] = $statusObj;
                }
            }
         }
        
        Mode::setPermanent($threadLastSendName, dt::now());

        return FALSE;
    }
    
    
    /**
     * Връща съдържанието на шаблона
     * 
     * @param core_ET $tpl
     * 
     * @return string
     */
    static function getContent($tpl)
    {
        // Ако не е обект или няма съдържание
        if (!$tpl instanceof core_ET || !$tpl) return $tpl;
        
        // Клонираме, за да не променяме оригиналния обект
        $cTpl = clone $tpl;
        
        // Премахваме празните блокове
        $cTpl->removePlaces();
        
        // Вземаме съсъджанието
        $status = $cTpl->getContent();
        
        return $status;
    }
    
    
    /**
     * След подготвяне на вербалната стойност на полетата
     * 
     * @param core_Mvc $mvc
     * @param object $res
     * @param object $data
     */
    function on_AfterPrepareListRows($mvc, &$res, $data)
    {
        // Масив с променените документи
        $docsArr = array();
        
        $threadId = Request::get('threadId', 'int');
        
        $threadLastSendName = 'LastSendThread_' . $threadId . '_' . Request::get('hitTime');
        
        $lastSend = Mode::get($threadLastSendName);

        // Намира всички документи, които са променени
        if (Request::get('ajax_mode') && $lastSend && count($data->recs)) {
            
            foreach($data->recs as $id => $r) {
                
                // Ако са променени след последно изтегленото време
                if($r->modifiedOn >= $lastSend) {
                    
                    // Добавяме хендълуте в масива
                    $docsArr[$id] = $data->rows[$id]->ROW_ATTR['id'];
                }
            }
            
            // Добавяме всички променени документи
            Mode::set('REFRESH_DOCS_ARR', $docsArr);
        }
    }
}
