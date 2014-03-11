<?php
/**
 * Помощен клас с функции за рутиране
 *
 * @category  bgerp
 * @package   marketing
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 *
 */
class marketing_Router extends core_Manager
{
	
	
	/**
	 * Намира кой ще е отговорника на папката, в следния ред
	 * 
	 * 1. Ако има папка "Несортирани - <име на град>", взимаме нейния отговорник
	 * 2. Ако има папка "Несортирани - <държава>", взимаме нейния отговорник
	 * 3. Ако има корпоративен имейл и има папка за този имейл, взимаме нейния отговорник
	 * 4. Първия регистриран потребител с роля 'ceo'
	 * 
	 * @param string $city   - град
	 * @param int $countryId - ид на държава
	 * @return int $inCharge - ид на потребител
	 */
	public static function getInChargeUser($city, $countryId)
	{
		$conf = core_Packs::getConfig('email');
		
		// Ако има град
		if($city){
			
			// Проверка имали несортирана папка с името на града
			$city = preg_replace('/\s+/', ' ', $city);
			$city = str::mbUcfirst($city);
			$unsortedName = sprintf($conf->EMAIL_UNSORTABLE_COUNTRY, $city);
			$inCharge = doc_UnsortedFolders::fetchField(array("#name = '[#1#]'", $unsortedName), 'inCharge');
		
			// Ако има такава папка, взимаме и отговорника
			if($inCharge) return $inCharge;
		}
		
		if($countryId){
			
			// Проверяваме имали несортирана папка с името на държавата
			$country = drdata_Countries::fetchField($countryId, 'commonNameBg');
			$unsortedName = sprintf($conf->EMAIL_UNSORTABLE_COUNTRY, $country);
			$inCharge = doc_UnsortedFolders::fetchField(array("#name = '[#1#]'", $unsortedName), 'inCharge');
			
			// Ако има, взимаме нейния отговорник
			if($inCharge) return $inCharge;
		}
		
		// Проверяваме имали корпоративна сметка
		$corpAcc = email_Accounts::getCorporateAcc();
		if($corpAcc){
			
			// Намираме отговорника на папката с корица кутията на корпоративния акаунт
			$corpAccId = email_Inboxes::fetchField("#email = '{$corpAcc->email}'");
			$inboxClassId = email_Inboxes::getClassId();
			$inCharge = doc_Folders::fetchField("#coverClass = {$inboxClassId} AND #coverId = {$corpAccId}", 'inCharge');
			
			// Ако има, взимаме нейния отговорник
			if($inCharge) return $inCharge;
		}
		
		// Ако няма нищо, намираме всички с роля 'ceo'
		$ceoRoleId = core_Roles::fetchByName('ceo');
		$ceos = core_users::getByRole($ceoRoleId);
		ksort($ceos);
		
		// Връщаме този с най-малко ид от тях
		return reset($ceos);
	}

	
	/**
	 * Рутира в папка на фирма с подадения имейл
	 * 
	 * @param string $email - имейл
	 * @param int $inCharge - отговорника на папката
	 * @return int - ид на папката
	 */
	public static function routeByCompanyEmail($email, $inCharge)
	{
		$companyId = crm_Companies::fetchField("#email LIKE '%{$email}%'", 'id');
		
		if($companyId){
			$rec = (object)array('id' => $companyId, 'inCharge' => $inCharge);
			
			return crm_Companies::forceCoverAndFolder($rec);
		}
		
		return FALSE;
	}
	
	
	/**
	 * Рутира в папка на лице с подадения имейл
	 * 
	 * @param string $email - имейл
	 * @param int $inCharge - отговорника на папката
	 * @return int - ид на папката
	 */
	public static function routeByPersonEmail($email, $inCharge)
	{
		$personId = crm_Persons::fetchField("#email LIKE '%{$email}%'", 'id');
		
		if($personId){
			$rec = (object)array('id' => $personId, 'inCharge' => $inCharge);
			
			return crm_Persons::forceCoverAndFolder($rec);
		}
		
		return FALSE;
	}
	
	
	/**
	 * Рутира в папка, намерена от имейл-рутера, само ако е от посочените корици
	 * 
	 * @param string $email - Имейл
	 * @param enum(contragent,company,person) $allowedCover - разрешена корица
	 * @return int - ид на папка
	 */
	public static function routeByEmail($email, $allowedCover)
	{
		$folderId = email_Router::getEmailFolder($email);
		if(empty($folderId)) return;
		
		$coverClassId = doc_Folders::fetchCoverClassId($folderId);
		$personsClassId = crm_Persons::getClassId();
		$companyClassId = crm_Companies::getClassId();
		
		switch ($allowedCover){
			case 'contragent':
				$res = ($coverClassId == $personsClassId || $coverClassId == $companyClassId);
				break;
			case 'person':
				$res = ($coverClassId == $personsClassId);
				break;
			case 'company':
				$res = ($coverClassId == $companyClassId);
				break;
		}
		
		return ($res) ? $folderId : NULL;
	}
	
	
	/**
	 * Рутира в папка на лице с подобно име от същата държава
	 * 
	 * @param string $name - име на лице
	 * @param int $countryId - ид на държава
	 * @return int - ид на папка
	 */
	public static function routeByPerson($name, $countryId, $inCharge)
	{
		$name = plg_Search::normalizeText($name);
		$nameArr = explode(' ', $name);
		
		if(count($nameArr) == 1) return;
		
		$names = static::normalizeNames('crm_Persons', $countryId);
		
		if($key = array_search($name, $names)){
			$rec = (object)array('id' => $key, 'inCharge' => $inCharge);
			
			return crm_Persons::forceCoverAndFolder($rec);
		}
	}
	
	
	/**
	 * Форсиране на папка на лице с подадените адресни данни
	 * 
	 * @param string $name    - име
	 * @param string $email   - имейл
	 * @param int $country    - държава
	 * @param string $tel     - телефон
	 * @param string $pCode   - п. код
	 * @param string $place   - населено място
	 * @param string $address - адрес
	 * @param int $inCharge   - отговорник
	 * @return int            - ид на папка
	 */
	public static function forcePersonFolder($name, $email, $country, $tel, $pCode, $place, $address, $inCharge)
	{
		$rec = new stdClass();
		foreach (array('name', 'email', 'country', 'tel', 'pCode', 'place', 'address') as $param){
			$rec->$param = ${$param};
		}
		$id = crm_Persons::save($rec);
		
		return crm_Persons::forceCoverAndFolder((object)array('id' => $id, 'inCharge' => $inCharge));
	}
	
	
	/**
	 * Форсиране на папка на фирма с подадените адресни данни
	 * 
	 * @param string $name    - име
	 * @param string $email   - имейл
	 * @param int $country    - държава
	 * @param string $tel     - телефон
	 * @param string $pCode   - п. код
	 * @param string $place   - населено място
	 * @param string $address - адрес
	 * @param int $inCharge   - отговорник
	 * @return int            - ид на папка
	 */
	public static function forceCompanyFolder($name, $email, $country, $tel, $pCode, $place, $address, $inCharge)
	{
		$rec = new stdClass();
		foreach (array('name', 'email', 'country', 'tel', 'pCode', 'place', 'address', 'inCharge') as $param){
			$rec->$param = ${$param};
		}
		
		$id = crm_Companies::save($rec);
		
		return crm_Companies::forceCoverAndFolder((object)array('id' => $id, 'inCharge' => $inCharge));
	}
	
	
	/**
	 * Рутира в папка на лице с подобно име от същата държава
	 * 
	 * @param string $name - име на лице
	 * @param int $countryId - ид на държава
	 * @return int - ид на папка
	 */
	public static function routeByCompanyName($name, $countryId, $inCharge)
	{
		$name = plg_Search::normalizeText($name);
		$nameArr = explode(' ', $name);
		if(count($nameArr) == 1) return;
		
		$names = static::normalizeNames('crm_Companies', $countryId);
		
		if($key = array_search($name, $names)){
			$rec = (object)array('id' => $key, 'inCharge' => $inCharge);
			
			return crm_Companies::forceCoverAndFolder($rec);
		}
	}
	
	
	/**
	 * Връща масив от нормализирани имена на даден модел
	 * 
	 * @param mixed $class - клас
	 * @param int $countryId - ид на държава
	 * @param string $nameFld - името на полето за заглавие
	 * @return int - ид на държава
	 */
	private static function normalizeNames($class, $countryId, $nameFld = 'name')
	{
		$Class = cls::get($class);
		
		$arr = array();
		$query = $Class::getQuery();
		$query->where("#country = {$countryId}");
		
		$conf = core_Packs::getConfig('crm');
		$ownCountryId = drdata_Countries::fetchField("#commonName = '{$conf->BGERP_OWN_COMPANY_COUNTRY}'");
		if($ownCountryId == $countryId){
			$query->orWhere("#country IS NULL");
		}
		
		$query->show('name,id,country');
		
		while($rec = $query->fetch()){
			$arr[$rec->id] = plg_Search::normalizeText($rec->{$nameFld});
		}
		
		return $arr;
	}
}