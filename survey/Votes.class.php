<?php



/**
 * Модел "Гласуване"
 *
 *
 * @category  bgerp
 * @package   survey
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class survey_Votes extends core_Manager {
    
    
    /**
     * Заглавие
     */
    var $title = 'Гласуване';
    
    
    /**
     * Плъгини за зареждане
     */
    var $loadList = 'survey_Wrapper, plg_Sorting, plg_Created';
    
  
    /**
     * Кои полета да се показват в листовия изглед
     */
    var $listFields = 'id, alternativeId, rate, userUid, createdOn';
    
    
    /**
     * Наименование на единичния обект
     */
    var $singleTitle = "Гласуване";

    
    /**
     * Брой записи на страница
     */
    var $listItemsPerPage = '40';
	
	
    /**
     * Кой има право да чете?
     */
    var $canRead = 'survey, ceo, admin';
    
    
    /**
     * Кой може да пише?
     */
    var $canWrite = 'no_one';
	
	
    /**
     * Описание на модела (таблицата)
     */
    function description()
    {
    	$this->FLD('alternativeId', 'key(mvc=survey_Alternatives)', 'caption=Въпрос, input=hidden, silent');
    	$this->FLD('rate', 'int', 'caption=Отговор');
    	$this->FLD('userUid', 'varchar(80)', 'caption=Потребител');
    	
    	$this->setDbUnique('alternativeId, userUid');
    }
    
    
    /**
     * Екшън който записва гласуването, и се вика от Ajax заявка
     */
    function act_Vote()
    {
    	//Намираме на кой въпрос, кой отговор е избран
    	expect($alternativeId = Request::get('alternativeId'));
    	expect($rowId = Request::get('id'));
    	
    	// Подготвяме записа
    	$rec = new stdClass();
    	$rec->alternativeId = $alternativeId;
    	$rec->rate = $rowId;
    	$rec->userUid = static::getUserUid();
    	
    	$altRec = new stdClass();
    	$altRec->id = $alternativeId;
  	
    	// Ако анкетата е активна и потребителя не е гласувал, може да се гласува
    	if(survey_Alternatives::haveRightFor('vote', $altRec)) {
    		$this->save($rec, NULL, 'ignore');
    		echo  json_encode(array('success' => 'yes'));
    		
    	} else {
    		echo  json_encode(array('success' => 'no'));
    	}
    	
    	shutdown();
    }
    
    
    /**
     * Намираме userUid-a  на гласувалия потребител:
     * Ако е потребител в системата това е ид-то му,
     * Ако анкетата е изпратена по поща това е мид-а на анкетата
     * Ако потребителя не е потребител в системата и нямаме мид, записваме
     * Ип-то му
     * @return varchar $userUid - Потребителя, който е гласувал
     */
    static function getUserUid()
    {
    	$uid = new stdClass();
    	if(core_Users::haveRole('user')) {
    		$uid->id = core_Users::getCurrent();
    	} elseif($mid = Request::get('m')) {
    		$uid->mid = $mid;
    	} else {
    		$uid->ip = $_SERVER['REMOTE_ADDR'];
    	}
    	
    	// Сериализираме uid-a  за да знаем от какъв е int/mid/Ip
    	return serialize($uid);
    }
    
    
    /**
     * Преброява гласовете които е получил даден въпрос, ако не е зададен 
     * номер на ред, връща броя на всички гласувания на въпроса, ако е зададен
     * преброява само гласовете които са дадени на въпросния ред
     * @param int alternativeId - ид на въпроса
     * @param int row - реда който е избран
     * @return int - Броя гласове
     */
    static function countVotes($alternativeId, $row = NULL)
    {
    	$query = static::getQuery();
    	$query->where(array("#alternativeId = [#1#]", $alternativeId));
    	if($row) {
    		$query->where(array("#rate = [#1#]", $row));
    	}
    	
    	return $query->count();
    }
    
    
    /**
     *  Обработки по вербалното представяне на данните
     */
    static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
    	if($fields['-list']) {
    		$varchar = cls::get('type_Varchar');
    		
    		// На кой въпрос е отговорено
    		$altRec = survey_Alternatives::fetch($rec->alternativeId);
    		$row->alternativeId = $varchar->toVerbal($altRec->label);
    		
    		// Кой отговор е избран
    		$rate = survey_Alternatives::getAnswerRow($rec->alternativeId, $rec->rate);
    		$row->rate = $varchar->toVerbal($rate);
    		
    		// Кой го е отговорил
    		$row->userUid = $mvc->verbalUserUid($rec->userUid);
    	}
    }
    
    
    /**
     * Връща вербалната стойност на подадения userUid
     * @param varchar(32) $userUid - ид на потребител/мид/Ип на
     * гласувалия потребител
     */
    function verbalUserUid($userUid)
    {
    	// десериализираме уид-а от базата 
    	$uid = unserialize($userUid);
    	$varchar = cls::get('type_Varchar');
    	
    	if($uid->id) {
    		
    		// ако е ид, намираме ника на потребителя
    		$nick = core_Users::fetchField($uid->id, 'nick');
    		$userUid = $varchar->toVerbal($nick);
    	} elseif($uid->mid) {
    		
    		// ако е mid
    		$userUid = $varchar->toVerbal("mid: {$uid->mid}");
    	} elseif($uid->ip) {
    		
    		// ако е Ип на потребител
    		$userUid = $varchar->toVerbal($uid->ip);
    		$userUid = ht::createLink("IP: {$userUid}", "http://bgwhois.com/?query={$uid->ip}", NULL, array('target' => '_blank'));
    	}
    	
    	return $userUid;
    }
    
    
    /**
     * Модификация на списъка с резултати
     */
    function on_AfterPrepareListRecs($mvc, $res, $data)
	{
		// За коя анкета филтрираме гласовете
		$surveyId = Request::get('surveyId', 'int');
		if($data->recs && $surveyId){
			foreach($data->recs as $rec) {
				
				// За всеки въпрос на който е отговорено, проверяваме дали
				// принадлежи на посочената анкета, ако не го премахваме
				$recSurveyId = survey_Alternatives::fetchField($rec->alternativeId, 'surveyId');	
				if($recSurveyId != $surveyId) {
					unset($data->recs[$rec->id]);
				}
			}
		}
	}
    
	
	/**
     * Извиква се преди подготовката на титлата в списъчния изглед
     */
    static function on_AfterPrepareListTitle($mvc, $data, $data)
    {
    	$surveyId = Request::get('surveyId', 'int');
    	if(isset($surveyId)) {
    		if($surveyTitleRec = survey_Surveys::fetch($surveyId)) {
    			$title = survey_Surveys::getVerbal($surveyTitleRec, 'title');
    			$data->title = "Гласуване за |* <font color=green>{$title}</font>";
    		}
    	}
    }
}